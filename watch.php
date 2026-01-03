<?php
session_start();
require 'db.php';

// --- 1. CEK LOGIN ---
if (!isset($_SESSION['user_logged_in'])) { header("Location: login.php"); exit; }

// --- 2. CEK ID FILM ---
if (!isset($_GET['id'])) { header("Location: index.php"); exit; }

$movie_id = $_GET['id'];
$current_user = $_SESSION['username'];

// --- 3. AMBIL STATUS USER ---
$stmt_user = $conn->prepare("SELECT subscription_type FROM users WHERE username = ?");
$stmt_user->bind_param("s", $current_user);
$stmt_user->execute();
$user_data = $stmt_user->get_result()->fetch_assoc();
$status = $user_data['subscription_type'];

// --- 4. AMBIL DATA FILM ---
$stmt_movie = $conn->prepare("SELECT * FROM movies WHERE id = ?");
$stmt_movie->bind_param("i", $movie_id);
$stmt_movie->execute();
$result_movie = $stmt_movie->get_result();

if ($result_movie->num_rows == 0) {
    echo "<script>alert('Film tidak ditemukan!'); window.location.href='index.php';</script>";
    exit;
}
$movie = $result_movie->fetch_assoc();

// --- 5. KEAMANAN PREMIUM ---
$is_premium_content = ($movie['is_premium'] == 1 && $status == 'Free');

// --- 6. VIDEO SOURCE LOGIC ---
function formatVideoUrl($url, $autoplay = 0) {
    if (empty($url)) return '';
    if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
        $symbol = (strpos($url, '?') === false) ? '?' : '&';
        return $url . $symbol . "autoplay={$autoplay}&modestbranding=1&rel=0&iv_load_policy=3&controls=1";
    }
    return $url;
}

// Sumber Video
$video_source = !empty($movie['video_url']) ? $movie['video_url'] : 'https://www.youtube.com/embed/dQw4w9WgXcQ';
$video_source = formatVideoUrl($video_source, 1);

// Sumber Trailer (Fallback ke video utama jika kolom trailer_url tidak ada di database)
$trailer_url_raw = $movie['trailer_url'] ?? 'https://www.youtube.com/embed/Ix0iszhC2t0'; 
$trailer_source = formatVideoUrl($trailer_url_raw, 1);

// --- 7. MY LIST LOGIC ---
if (isset($_POST['toggle_list'])) {
    $check_list = $conn->prepare("SELECT id FROM watchlist WHERE username = ? AND movie_id = ?");
    $check_list->bind_param("si", $current_user, $movie_id);
    $check_list->execute();
    if ($check_list->get_result()->num_rows > 0) {
        $conn->query("DELETE FROM watchlist WHERE username = '$current_user' AND movie_id = $movie_id");
    } else {
        $conn->query("INSERT INTO watchlist (username, movie_id) VALUES ('$current_user', $movie_id)");
    }
    header("Location: watch.php?id=$movie_id");
    exit;
}

$in_watchlist = false;
$res_list = $conn->query("SELECT id FROM watchlist WHERE username = '$current_user' AND movie_id = $movie_id");
if ($res_list->num_rows > 0) $in_watchlist = true;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nonton <?= htmlspecialchars($movie['title']) ?> - MOVDO</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --bg-body: #050a14;
            --primary: #3b82f6;
            --accent: #06b6d4;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --gradient-main: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; outline: none; }
        
        body { 
            background-color: var(--bg-body); color: var(--text-main); 
            font-family: 'Plus Jakarta Sans', sans-serif; min-height: 100vh; overflow-x: hidden;
        }

        .backdrop {
            position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background: url('<?= $movie['image'] ?>') no-repeat center top/cover;
            filter: blur(80px) brightness(0.3); z-index: -1; transform: scale(1.1); 
        }
        .backdrop-overlay {
            position: fixed; inset: 0; background: linear-gradient(to bottom, rgba(5,10,20,0.3) 0%, rgba(5,10,20,0.95) 80%); z-index: -1;
        }

        .watch-header {
            padding: 20px 40px; display: flex; align-items: center; justify-content: space-between;
            position: absolute; top: 0; left: 0; width: 100%; z-index: 50;
        }
        .back-link { 
            text-decoration: none; color: #fff; font-weight: 700; font-size: 14px; 
            display: flex; align-items: center; gap: 10px; opacity: 0.8; transition: 0.3s;
        }
        .back-link:hover { opacity: 1; transform: translateX(-5px); }
        .back-icon { 
            width: 32px; height: 32px; background: rgba(255,255,255,0.1); border-radius: 50%; 
            display: flex; align-items: center; justify-content: center; backdrop-filter: blur(5px);
        }

        .watch-container { max-width: 1200px; margin: 0 auto; padding: 100px 4% 60px; }

        /* VIDEO PLAYER */
        .player-frame {
            position: relative; width: 100%; padding-top: 56.25%; /* 16:9 */
            background: #000; border-radius: 16px; overflow: hidden;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1); margin-bottom: 40px;
        }
        .player-frame iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }
        
        /* WATERMARK STYLE */
        .watermark {
            position: absolute; top: 25px; right: 25px; z-index: 20;
            font-size: 18px; font-weight: 900; color: rgba(255,255,255,0.4);
            letter-spacing: 2px; pointer-events: none;
            text-shadow: 0 2px 10px rgba(0,0,0,0.5);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .watermark span { color: rgba(59, 130, 246, 0.6); }

        /* LOCK OVERLAY */
        .premium-lock-overlay {
            position: absolute; top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0,0,0,0.85); display: flex; flex-direction: column; align-items: center; justify-content: center;
            z-index: 15; text-align: center; backdrop-filter: blur(5px);
        }
        .lock-icon-large { font-size: 3rem; color: #fbbf24; margin-bottom: 20px; }
        .lock-title { font-size: 1.5rem; font-weight: 800; margin-bottom: 10px; }
        .lock-desc { color: var(--text-muted); margin-bottom: 25px; max-width: 400px; }

        /* DETAILS */
        .details-grid { display: grid; grid-template-columns: 1fr 300px; gap: 50px; }
        .main-info h1 { font-size: 36px; font-weight: 800; margin-bottom: 15px; line-height: 1.2; text-shadow: 0 5px 15px rgba(0,0,0,0.5); }
        .meta-tags { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 25px; align-items: center; }
        .tag { font-size: 12px; padding: 5px 12px; border-radius: 6px; font-weight: 600; background: rgba(255,255,255,0.1); color: #e2e8f0; }
        .tag.hd { border: 1px solid rgba(255,255,255,0.3); background: transparent; }
        .tag.rating { color: #facc15; background: rgba(250, 204, 21, 0.1); }
        .desc { font-size: 15px; line-height: 1.7; color: #cbd5e1; margin-bottom: 30px; max-width: 700px; }

        .actions { display: flex; gap: 15px; flex-wrap: wrap; }
        .act-btn {
            background: rgba(255,255,255,0.1); border: none; color: white;
            padding: 12px 24px; border-radius: 10px; font-weight: 600; cursor: pointer;
            display: flex; align-items: center; gap: 8px; transition: 0.3s;
            font-family: inherit; font-size: 14px;
        }
        .act-btn:hover { background: rgba(255,255,255,0.2); transform: translateY(-2px); }
        .act-btn.primary { background: var(--text-main); color: #020617; }
        .act-btn.primary:hover { background: #e2e8f0; }
        .act-btn.trailer { background: transparent; border: 1px solid var(--text-main); }
        .act-btn.trailer:hover { background: rgba(255,255,255,0.1); }
        .act-btn.watchlist.active { background: rgba(16, 185, 129, 0.2); color: #34d399; }

        .side-info h4 { color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        .side-info p { font-size: 15px; margin-bottom: 20px; font-weight: 500; }
        .genre-list span { display: inline-block; margin-right: 5px; color: var(--accent); }
        
        .playing-indicator { display: inline-block; margin-left: 10px; font-size: 12px; color: #ef4444; font-weight: 800; text-transform: uppercase; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.5; } 100% { opacity: 1; } }

        @media (max-width: 900px) {
            .details-grid { grid-template-columns: 1fr; gap: 30px; }
            .watch-container { padding-top: 80px; }
            .actions { flex-direction: row; flex-wrap: wrap; }
            .act-btn { flex: 1 1 auto; justify-content: center; }
        }
    </style>
</head>
<body>

    <div class="backdrop"></div>
    <div class="backdrop-overlay"></div>

    <div class="watch-header">
        <a href="index.php" class="back-link">
            <div class="back-icon"><i class="fas fa-arrow-left"></i></div>
            Kembali ke Beranda
        </a>
    </div>

    <div class="watch-container">
        
        <div class="player-frame">
            <div class="watermark">MOV<span>DO</span></div>

            <iframe id="videoPlayer" src="<?= $is_premium_content ? '' : $video_source ?>" allow="autoplay; encrypted-media; fullscreen" allowfullscreen></iframe>
            
            <?php if($is_premium_content): ?>
            <div id="premiumOverlay" class="premium-lock-overlay">
                <div class="lock-icon-large"><i class="fas fa-crown"></i></div>
                <div class="lock-title">Konten Premium</div>
                <div class="lock-desc">Film ini tersedia khusus untuk member Premium. Upgrade akun Anda untuk menonton film ini.</div>
                <a href="payment.php" class="act-btn primary" style="width: auto;">Upgrade Sekarang</a>
                
                <div style="margin-top: 20px;">
                    <button class="act-btn trailer" onclick="playTrailer()">
                        <i class="fas fa-play"></i> Tonton Trailer Saja
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <div class="details-grid">
            <div class="main-info">
                <h1>
                    <?= htmlspecialchars($movie['title']) ?>
                    <?php if($movie['is_premium']): ?>
                        <i class="fas fa-crown" style="font-size:0.5em; color:#facc15; vertical-align:middle; margin-left:10px;"></i>
                    <?php endif; ?>
                    <span id="playingLabel" class="playing-indicator" style="display:none;">• NOW PLAYING</span>
                </h1>

                <div class="meta-tags">
                    <span class="tag rating"><i class="fas fa-star"></i> <?= $movie['rating'] ?></span>
                    <span class="tag"><?= date('Y', strtotime($movie['date_release'] ?? 'now')) ?></span>
                    <span class="tag hd">4K ULTRA HD</span>
                    <span class="tag">5.1 SOUND</span>
                </div>

                <p class="desc">
                    Nikmati pengalaman sinematik terbaik dari <b><?= htmlspecialchars($movie['title']) ?></b>. 
                    Film ini menghadirkan cerita yang mendalam dengan visual memukau. 
                    Tonton sekarang tanpa gangguan iklan dan dengan kualitas audio visual tertinggi.
                </p>

                <div class="actions">
                    <button class="act-btn primary" onclick="playMovie()">
                        <i class="fas fa-play"></i> Play Movie
                    </button>

                    <button class="act-btn trailer" onclick="playTrailer()">
                        <i class="fas fa-film"></i> Trailer
                    </button>

                    <button class="act-btn" onclick="downloadMovie()">
                        <i class="fas fa-download"></i> Download
                    </button>

                    <form method="POST" style="display:contents;">
                        <button type="submit" name="toggle_list" class="act-btn watchlist <?= $in_watchlist ? 'active' : '' ?>">
                            <?php if($in_watchlist): ?>
                                <i class="fas fa-check"></i> Tersimpan
                            <?php else: ?>
                                <i class="fas fa-plus"></i> My List
                            <?php endif; ?>
                        </button>
                    </form>

                    <button class="act-btn" onclick="shareMovie()">
                        <i class="fas fa-share-nodes"></i> Share
                    </button>
                </div>
            </div>

            <div class="side-info">
                <h4>Bahasa Audio</h4>
                <p><?= $movie['lang'] ?? 'Original' ?> (Dolby Atmos)</p>

                <h4>Subtitle</h4>
                <p>Indonesia, English, Korean</p>

                <h4>Genre</h4>
                <p class="genre-list">
                    <?php 
                        $genres = explode(',', $movie['genre']);
                        foreach($genres as $g) echo '<span>'.trim($g).'</span>';
                    ?>
                </p>

                <h4>Status</h4>
                <p style="color:#86efac;">Available to Stream</p>
            </div>
        </div>

    </div>

    <script>
        // Data Sources from PHP
        const movieSrc = "<?= $video_source ?>";
        const trailerSrc = "<?= $trailer_source ?>";
        // URL asli untuk download (bersih dari parameter embed)
        const downloadSrc = "<?= !empty($movie['video_url']) ? $movie['video_url'] : '' ?>"; 
        const isPremiumLocked = <?= $is_premium_content ? 'true' : 'false' ?>;

        const iframe = document.getElementById('videoPlayer');
        const overlay = document.getElementById('premiumOverlay');
        const label = document.getElementById('playingLabel');

        function playMovie() {
            if (isPremiumLocked) {
                if(overlay) overlay.style.display = 'flex';
                alert("Konten ini terkunci. Silakan upgrade ke Premium.");
            } else {
                iframe.src = movieSrc;
                if(overlay) overlay.style.display = 'none';
                label.style.display = 'inline-block';
                label.innerText = "• PLAYING MOVIE";
                label.style.color = "#ef4444"; 
            }
        }

        function playTrailer() {
            iframe.src = trailerSrc;
            if(overlay) overlay.style.display = 'none';
            label.style.display = 'inline-block';
            label.innerText = "• PLAYING TRAILER";
            label.style.color = "#fbbf24"; 
        }

        function downloadMovie() {
            if (isPremiumLocked) {
                alert("Fitur download hanya untuk member Premium!");
                return;
            }
            
            if (!downloadSrc) {
                alert("Link download tidak tersedia.");
                return;
            }

            if(confirm("Anda akan diarahkan ke sumber video asli untuk mengunduh. Lanjutkan?")) {
                window.open(downloadSrc, '_blank');
            }
        }

        function shareMovie() {
            const data = {
                title: '<?= addslashes($movie['title']) ?>',
                text: 'Nonton <?= addslashes($movie['title']) ?> di MOVDO sekarang!',
                url: window.location.href
            };
            if (navigator.share) {
                navigator.share(data).catch(console.error);
            } else {
                navigator.clipboard.writeText(window.location.href);
                alert('Link film berhasil disalin ke clipboard!');
            }
        }
    </script>

</body>
</html>