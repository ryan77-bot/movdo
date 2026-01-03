<?php
session_start();
require 'db.php'; 

// --- 1. PENGATURAN BAHASA (LANGUAGE LOGIC) ---

// Cek apakah user meminta ganti bahasa via URL (misal: ?lang=en)
if (isset($_GET['lang'])) {
    $lang_code = $_GET['lang'];
    if ($lang_code == 'en' || $lang_code == 'id') {
        $_SESSION['lang'] = $lang_code;
    }
}

// Ambil bahasa dari session. Default 'id'.
$l = $_SESSION['lang'] ?? 'id'; 

// Kamus Bahasa
$trans = [
    'id' => [
        'search_ph' => 'Cari di',
        'nav_logout' => 'Keluar',
        'toast_coll' => 'Ditambahkan ke Koleksi',
        'toast_title' => 'Notifikasi',
        'cat_movie' => 'Film',
        'cat_drakor' => 'Drama Korea',
        'cat_indo' => 'Series Indonesia',
        'hero_trending' => 'SEDANG TREN',
        'hero_desc_default' => 'Saksikan keseruan cerita terbaik pilihan kami. Streaming tanpa batas dengan kualitas tinggi.',
        'btn_play' => 'Putar Sekarang',
        'btn_unlock' => 'Buka Premium',
        'btn_coll' => 'Koleksi',
        'sort_newest' => 'Terbaru',
        'sort_popular' => 'Populer',
        'sort_az' => 'A-Z',
        'card_locked' => 'TERKUNCI',
        'card_match' => 'Cocok',
        'empty_content' => 'Belum ada konten untuk kategori',
        'no_search' => 'Tidak ditemukan hasil pencarian.',
        'up_title' => 'Upgrade Pro',
        'up_desc' => 'Buka akses ke semua Drakor dan Film eksklusif tanpa batas.',
        'up_btn' => 'MULAI SEKARANG',
        'genre_title' => 'Genre',
        'chip_all' => 'Semua',
        'foot_desc' => 'Platform streaming terbaik untuk menikmati ribuan film, drakor, dan serial Indonesia secara eksklusif.',
        'head_explore' => 'Jelajahi',
        'head_help' => 'Bantuan',
        'link_latest' => 'Film Terbaru',
        'link_top' => 'Top Rating',
        'link_help_center' => 'Pusat Bantuan',
        'link_terms' => 'Syarat & Ketentuan',
        'link_privacy' => 'Kebijakan Privasi',
        'link_sub' => 'Cara Berlangganan',
        'rights' => 'Hak cipta dilindungi.',
        'js_confirm_prem' => 'Konten Premium! Ingin upgrade akun Anda?'
    ],
    'en' => [
        'search_ph' => 'Search in',
        'nav_logout' => 'Logout',
        'toast_coll' => 'Added to Collection',
        'toast_title' => 'Notification',
        'cat_movie' => 'Movies',
        'cat_drakor' => 'K-Drama',
        'cat_indo' => 'Indo Series',
        'hero_trending' => 'TRENDING',
        'hero_desc_default' => 'Watch our best selected stories. Unlimited streaming with high quality.',
        'btn_play' => 'Play Now',
        'btn_unlock' => 'Unlock Premium',
        'btn_coll' => 'My List',
        'sort_newest' => 'Newest',
        'sort_popular' => 'Popular',
        'sort_az' => 'A-Z',
        'card_locked' => 'LOCKED',
        'card_match' => 'Match',
        'empty_content' => 'No content available for category',
        'no_search' => 'No search results found.',
        'up_title' => 'Go Pro',
        'up_desc' => 'Unlock access to all exclusive K-Dramas and Movies without limits.',
        'up_btn' => 'START NOW',
        'genre_title' => 'Genre',
        'chip_all' => 'All',
        'foot_desc' => 'The best streaming platform to enjoy thousands of movies, K-dramas, and Indonesian series exclusively.',
        'head_explore' => 'Explore',
        'head_help' => 'Help',
        'link_latest' => 'Latest Movies',
        'link_top' => 'Top Rated',
        'link_help_center' => 'Help Center',
        'link_terms' => 'Terms & Conditions',
        'link_privacy' => 'Privacy Policy',
        'link_sub' => 'How to Subscribe',
        'rights' => 'All rights reserved.',
        'js_confirm_prem' => 'Premium Content! Do you want to upgrade your account?'
    ]
];

$t = $trans[$l]; 

// --- 2. CEK LOGIN ---
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

// --- 3. LOGIKA LOGOUT ---
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: login.php");
    exit;
}

$current_user = $_SESSION['username'] ?? 'Guest';

// --- 4. AMBIL DATA USER ---
$status = 'Free'; 
$user_pic = null; 

if (isset($conn)) {
    $sql_query = "SELECT subscription_type FROM users WHERE username = ?";
    $check_col = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
    if($check_col && $check_col->num_rows > 0) {
        $sql_query = "SELECT subscription_type, profile_picture FROM users WHERE username = ?";
    }

    $stmt_user = $conn->prepare($sql_query);
    if ($stmt_user) {
        $stmt_user->bind_param("s", $current_user);
        $stmt_user->execute();
        $res_user = $stmt_user->get_result();
        if ($res_user->num_rows > 0) {
            $user_data = $res_user->fetch_assoc();
            $status = $user_data['subscription_type'];
            if (isset($user_data['profile_picture'])) {
                $user_pic = $user_data['profile_picture'];
            }
        }
    }
}

// --- 5. KONFIGURASI VIEW & SORTING ---
$view = $_GET['view'] ?? 'movie';
$valid_views = ['movie', 'drakor', 'indo'];
if (!in_array($view, $valid_views)) { $view = 'movie'; }

$sort = $_GET['sort'] ?? 'newest';

// --- 6. LOGIKA PAGINATION ---
$limit = 18;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

$order_clause = "ORDER BY id DESC";
if ($sort == 'rating') { $order_clause = "ORDER BY rating DESC"; }
elseif ($sort == 'oldest') { $order_clause = "ORDER BY id ASC"; }
elseif ($sort == 'az') { $order_clause = "ORDER BY title ASC"; }

// --- 7. QUERY DATA ---
$data_list = [];
$genres_list = [];
$hero_item = null;
$total_pages = 1;
$result = false;

if (isset($conn)) {
    $where_type = "WHERE type = '$view'"; 
    
    $sql_count = "SELECT COUNT(*) as total FROM movies $where_type";
    $total_items = $conn->query($sql_count)->fetch_assoc()['total'];
    $total_pages = ceil($total_items / $limit);

    $sql = "SELECT * FROM movies $where_type $order_clause LIMIT $limit OFFSET $offset";
    $result = $conn->query($sql);

    $sql_genre = "SELECT genre FROM movies $where_type";
    $result_raw = $conn->query($sql_genre);
    if ($result_raw->num_rows > 0) {
        while($row = $result_raw->fetch_assoc()) {
            $parts = explode(',', $row['genre']);
            foreach($parts as $p) {
                $clean = trim($p);
                if(!empty($clean)) $genres_list[] = $clean;
            }
        }
    }
    $genres_list = array_unique($genres_list);
    sort($genres_list);

    $hero_res = $conn->query("SELECT * FROM movies $where_type AND is_premium = 1 ORDER BY RAND() LIMIT 1");
    if($hero_res->num_rows == 0) {
        $hero_res = $conn->query("SELECT * FROM movies $where_type ORDER BY RAND() LIMIT 1");
    }
    if($hero_res->num_rows > 0) $hero_item = $hero_res->fetch_assoc();
}

$current_title = $t['cat_' . $view];
?>

<!DOCTYPE html>
<html lang="<?= $l ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MOVDO - <?= $current_title ?></title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&display=swap" rel="stylesheet">
    
    <style>
        /* --- CORE VARIABLES & RESET --- */
        :root {
            --bg-body: #050a14; 
            --bg-card: #0f172a; 
            --bg-sidebar: #0b1120;
            --bg-footer: #02060e;
            --primary-start: #00c6ff;
            --primary-end: #0072ff;
            --primary-gradient: linear-gradient(135deg, var(--primary-start), var(--primary-end));
            --text-main: #ffffff;
            --text-muted: #94a3b8;
            --glass: rgba(15, 23, 42, 0.6);
            --border-glass: rgba(255, 255, 255, 0.08);
            --radius: 12px;
        }

        * { box-sizing: border-box; outline: none; }
        body { 
            background-color: var(--bg-body); color: var(--text-main); font-family: 'Outfit', sans-serif; 
            margin: 0; padding-bottom: 0; overflow-x: hidden;
            background-image: radial-gradient(circle at top center, #111a33 0%, var(--bg-body) 60%);
            display: flex; flex-direction: column; min-height: 100vh;
        }
        a { text-decoration: none; color: inherit; transition: 0.2s; }

        /* --- HEADER --- */
        header {
            position: fixed; top: 0; left: 0; width: 100%; padding: 15px 4%;
            display: flex; justify-content: space-between; align-items: center;
            z-index: 1000; background: transparent; transition: all 0.4s ease;
        }
        header.scrolled {
            background: rgba(5, 10, 20, 0.9); backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--border-glass); box-shadow: 0 4px 30px rgba(0, 0, 0, 0.2);
        }
        .logo { font-size: 28px; font-weight: 800; letter-spacing: -1px; color: #fff; text-transform: uppercase; }
        .logo span { background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }

        /* Search */
        .search-container { position: relative; margin: 0 20px; flex: 1; max-width: 400px; display: none; }
        @media(min-width: 768px) { .search-container { display: block; } }
        
        .search-input {
            width: 100%; background: rgba(30, 41, 59, 0.5); border: 1px solid var(--border-glass);
            padding: 10px 15px 10px 45px; border-radius: 30px; color: #fff; transition: 0.3s;
        }
        .search-input:focus { background: rgba(30, 41, 59, 0.9); border-color: var(--primary-start); box-shadow: 0 0 15px rgba(0, 198, 255, 0.2); }
        .search-icon { position: absolute; left: 18px; top: 50%; transform: translateY(-50%); color: var(--primary-start); }

        .nav-actions { display: flex; align-items: center; gap: 20px; }
        
        /* Language Switcher */
        .lang-switch { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 13px; }
        .lang-switch a { color: var(--text-muted); padding: 4px; transition: 0.3s; }
        .lang-switch a.active, .lang-switch a:hover { color: #fff; text-shadow: 0 0 10px var(--primary-start); }
        .lang-divider { color: var(--border-glass); }

        .user-pill {
            display: flex; align-items: center; gap: 10px;
            background: rgba(255,255,255,0.05); padding: 5px 15px 5px 5px;
            border-radius: 30px; border: 1px solid var(--border-glass); cursor: pointer; transition: 0.3s;
        }
        .user-pill:hover { background: rgba(255,255,255,0.1); border-color: var(--primary-start); }
        .avatar { width: 32px; height: 32px; background: var(--primary-gradient); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; color: #fff; overflow: hidden; }
        .badge-pro { background: linear-gradient(90deg, #FFD700, #FDB931); color: #000; font-size: 10px; padding: 2px 8px; border-radius: 4px; font-weight: 800; margin-left: auto; }

        /* --- HERO --- */
        .hero { position: relative; height: 80vh; width: 100%; background-size: cover; background-position: center top; display: flex; align-items: center; padding: 0 4%; margin-bottom: -80px; }
        .hero::before { content: ''; position: absolute; inset: 0; background: linear-gradient(to top, var(--bg-body) 5%, rgba(5,10,20,0.7) 40%, rgba(0,198,255,0.1) 100%); mix-blend-mode: multiply; }
        .hero::after { content: ''; position: absolute; inset: 0; background: linear-gradient(to right, rgba(5,10,20,0.95) 0%, transparent 70%); }
        .hero-content { position: relative; z-index: 2; max-width: 650px; animation: fadeInUp 1s ease; }
        .hero-tag { display: inline-block; font-size: 13px; font-weight: 700; letter-spacing: 2px; color: var(--primary-start); margin-bottom: 15px; text-transform: uppercase; border: 1px solid rgba(0, 198, 255, 0.3); padding: 5px 12px; border-radius: 20px; background: rgba(0, 198, 255, 0.1); box-shadow: 0 0 15px rgba(0, 198, 255, 0.2); }
        .hero-title { font-size: clamp(40px, 5vw, 70px); font-weight: 900; line-height: 1.1; margin: 0 0 20px; background: linear-gradient(to bottom right, #fff, #a5f3fc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .hero-desc { color: var(--text-muted); font-size: 18px; line-height: 1.6; margin-bottom: 30px; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; text-shadow: 0 2px 4px rgba(0,0,0,0.8); }
        .btn { padding: 14px 35px; border-radius: 30px; font-weight: 700; border: none; cursor: pointer; font-size: 16px; display: inline-flex; align-items: center; gap: 10px; transition: 0.3s; }
        .btn-primary { background: var(--primary-gradient); color: #fff; box-shadow: 0 4px 20px rgba(0, 114, 255, 0.4); }
        .btn-primary:hover { transform: translateY(-3px); box-shadow: 0 8px 30px rgba(0, 198, 255, 0.6); }
        .btn-glass { background: rgba(255, 255, 255, 0.1); color: #fff; margin-left: 15px; backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        .btn-glass:hover { background: rgba(255,255,255,0.2); transform: translateY(-3px); }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }

        /* --- MAIN LAYOUT --- */
        .wrapper { position: relative; z-index: 10; padding: 0 4%; display: flex; gap: 40px; flex-direction: column; flex: 1; margin-bottom: 60px; }
        @media(min-width: 1024px) { .wrapper { flex-direction: row; } }
        .sidebar { width: 100%; min-width: 250px; order: 2; }
        .main-content { flex: 1; order: 1; }
        @media(min-width: 1024px) { .sidebar { width: 250px; position: sticky; top: 100px; height: fit-content; order: 2; } .main-content { order: 1; } }

        /* Toolbar & Tabs */
        .toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid var(--border-glass); padding-bottom: 15px; flex-wrap: wrap; gap: 15px; }
        .tabs { display: flex; gap: 20px; }
        .tab-link { font-size: 16px; font-weight: 600; color: var(--text-muted); position: relative; padding-bottom: 5px; white-space: nowrap; }
        .tab-link.active { color: #fff; }
        .tab-link.active::after { content:''; position: absolute; bottom: -16px; left: 0; width: 100%; height: 3px; background: var(--primary-gradient); box-shadow: 0 -2px 10px var(--primary-start); }
        .filter-select { background: #1e293b; color: #fff; border: 1px solid var(--border-glass); padding: 8px 15px; border-radius: 8px; font-family: inherit; cursor: pointer; }

        /* --- GRID & CARDS --- */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; row-gap: 35px; }
        .card { position: relative; border-radius: var(--radius); cursor: pointer; transition: transform 0.3s; background: transparent; }
        .poster { position: relative; aspect-ratio: 2/3; overflow: hidden; border-radius: var(--radius); box-shadow: 0 10px 20px rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.05); }
        .poster img { width: 100%; height: 100%; object-fit: cover; transition: 0.5s; }
        .vip-badge { position: absolute; top: 10px; right: 10px; background: var(--primary-gradient); color: #fff; padding: 4px 10px; font-size: 10px; font-weight: 800; border-radius: 20px; z-index: 5; box-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .card-overlay { position: absolute; inset: 0; background: rgba(15, 23, 42, 0.8); display: flex; flex-direction: column; justify-content: center; align-items: center; opacity: 0; transition: 0.3s; backdrop-filter: blur(4px); }
        .card:hover { transform: scale(1.05); z-index: 10; }
        .card:hover .card-overlay { opacity: 1; }
        .action-btn { width: 50px; height: 50px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #fff; transition: 0.3s; font-size: 18px; margin-bottom: 10px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); }
        .action-btn:hover { background: #fff; color: var(--primary-end); transform: scale(1.1); }
        .action-btn.play { background: var(--primary-gradient); border: none; box-shadow: 0 0 20px rgba(0, 198, 255, 0.5); }
        .card-details { margin-top: 12px; }
        .card-title { font-size: 15px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin: 0 0 5px; color: #e2e8f0; }
        .card:hover .card-title { color: var(--primary-start); }
        .card-meta { font-size: 12px; color: var(--text-muted); display: flex; justify-content: space-between; }
        .match-score { color: var(--primary-start); font-weight: 700; }

        /* Sidebar & Chips */
        .genre-box { background: var(--bg-card); padding: 25px; border-radius: 16px; border: 1px solid var(--border-glass); }
        .genre-title { font-size: 18px; margin: 0 0 20px; color: #fff; border-left: 4px solid var(--primary-start); padding-left: 12px; }
        .chip-container { display: flex; flex-wrap: wrap; gap: 10px; }
        .chip { background: rgba(255,255,255,0.05); color: #cbd5e1; padding: 8px 14px; border-radius: 20px; font-size: 12px; cursor: pointer; transition: 0.2s; border: 1px solid transparent; }
        .chip:hover, .chip.active { background: var(--primary-gradient); color: #fff; border-color: transparent; box-shadow: 0 4px 15px rgba(0, 198, 255, 0.2); }
        .premium-box { background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); padding: 25px; border-radius: 16px; margin-bottom: 30px; text-align: center; border: 1px solid rgba(59, 130, 246, 0.3); position: relative; overflow: hidden; }
        .premium-box::before { content:''; position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%); animation: rotate 10s linear infinite; }
        @keyframes rotate { from {transform: rotate(0deg);} to {transform: rotate(360deg);} }

        /* Pagination */
        .pagination { display: flex; justify-content: center; gap: 10px; margin-top: 60px; }
        .page-num { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: #1e293b; border-radius: 12px; color: #fff; transition: 0.2s; }
        .page-num.active, .page-num:hover { background: var(--primary-gradient); box-shadow: 0 4px 15px rgba(0, 114, 255, 0.3); }

        /* --- FOOTER --- */
        .site-footer { background: var(--bg-footer); border-top: 1px solid var(--border-glass); padding: 60px 0 30px; margin-top: auto; position: relative; }
        .footer-content { max-width: 1200px; margin: 0 auto; padding: 0 4%; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 40px; }
        .footer-brand h2 { font-size: 28px; margin: 0 0 15px; font-weight: 800; color: #fff; text-transform: uppercase; }
        .footer-brand h2 span { background: var(--primary-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .footer-brand p { color: var(--text-muted); font-size: 14px; line-height: 1.6; margin-bottom: 20px; }
        .footer-heading { color: #fff; font-size: 16px; font-weight: 700; margin-bottom: 20px; display: block; }
        .footer-links { list-style: none; padding: 0; margin: 0; }
        .footer-links li { margin-bottom: 12px; }
        .footer-links a { color: var(--text-muted); font-size: 14px; transition: 0.3s; }
        .footer-links a:hover { color: var(--primary-start); padding-left: 5px; }
        .social-icons { display: flex; gap: 15px; }
        .social-icons a { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.05); display: flex; align-items: center; justify-content: center; color: #fff; border: 1px solid var(--border-glass); transition: 0.3s; }
        .social-icons a:hover { background: var(--primary-gradient); transform: translateY(-3px); border-color: transparent; }
        .footer-bottom { max-width: 1200px; margin: 40px auto 0; padding: 25px 4% 0; border-top: 1px solid rgba(255,255,255,0.05); text-align: center; color: #64748b; font-size: 13px; }

        /* Toast */
        #toast { visibility: hidden; min-width: 250px; background: rgba(15, 23, 42, 0.95); color: #fff; text-align: center; border-radius: 12px; padding: 16px; position: fixed; z-index: 2000; left: 50%; bottom: 30px; transform: translateX(-50%); border: 1px solid var(--primary-start); box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>

<header id="mainHeader">
    <a href="index.php" class="logo">MOV<span>DO</span></a>
    
    <div class="search-container">
        <i class="fas fa-search search-icon"></i>
        <input type="text" id="searchInput" class="search-input" placeholder="<?= $t['search_ph'] ?> <?= $current_title ?>..." onkeyup="filterLocal()">
    </div>

    <div class="nav-actions">
        <div class="lang-switch">
            <?php 
                // Kita gunakan helper sederhana untuk mempertahankan parameter URL lain (seperti ?view=drakor) saat ganti bahasa
                $params_id = $_GET; $params_id['lang'] = 'id';
                $url_id = '?' . http_build_query($params_id);
                
                $params_en = $_GET; $params_en['lang'] = 'en';
                $url_en = '?' . http_build_query($params_en);
            ?>
            <a href="<?= $url_id ?>" class="<?= $l == 'id' ? 'active' : '' ?>">ID</a>
            <span class="lang-divider">|</span>
            <a href="<?= $url_en ?>" class="<?= $l == 'en' ? 'active' : '' ?>">EN</a>
        </div>

        <i class="fas fa-search" style="font-size: 18px; cursor: pointer; color: #fff; display:none; @media(max-width:767px){display:block;}" onclick="document.querySelector('.search-container').style.display='block'"></i>
        
        <div class="user-pill" onclick="window.location='settings.php'">
            <div class="avatar">
                <?php if (!empty($user_pic)): ?>
                    <img src="<?= htmlspecialchars($user_pic) ?>" alt="Profile" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                <?php else: ?>
                    <?= strtoupper(substr($current_user, 0, 1)) ?>
                <?php endif; ?>
            </div>
            <?php if($status == 'Premium'): ?> <span class="badge-pro">PRO</span> <?php endif; ?>
        </div>
        <a href="?logout=true" title="<?= $t['nav_logout'] ?>" style="color:#94a3b8;"><i class="fas fa-sign-out-alt"></i></a>
    </div>
</header>

<?php if($hero_item): ?>
    <section class="hero" style="background-image: url('<?= $hero_item['image'] ?>');">
        <div class="hero-content">
            <span class="hero-tag"><?= $t['hero_trending'] ?> <?= strtoupper($t['cat_' . $view]) ?></span>
            <h1 class="hero-title"><?= $hero_item['title'] ?></h1>
            <p class="hero-desc"><?= $hero_item['description'] ?? $t['hero_desc_default'] ?></p>
            
            <?php 
                $h_locked = ($hero_item['is_premium'] == 1 && $status == 'Free');
            ?>
            
            <div>
                <button onclick="handlePlay(<?= $hero_item['id'] ?>, <?= $h_locked ? 'true' : 'false' ?>)" class="btn btn-primary">
                    <i class="fas <?= $h_locked ? 'fa-lock' : 'fa-play' ?>"></i> <?= $h_locked ? $t['btn_unlock'] : $t['btn_play'] ?>
                </button>
                <button onclick="showToast('<?= $t['toast_coll'] ?>')" class="btn btn-glass">
                    <i class="fas fa-plus"></i> <?= $t['btn_coll'] ?>
                </button>
            </div>
        </div>
    </section>
<?php else: ?>
    <div style="height: 120px;"></div> 
<?php endif; ?>

<div class="wrapper">
    
    <main class="main-content">
        <div class="toolbar">
            <div class="tabs">
                <a href="?view=movie" class="tab-link <?= ($view == 'movie') ? 'active' : '' ?>"><?= $t['cat_movie'] ?></a>
                <a href="?view=drakor" class="tab-link <?= ($view == 'drakor') ? 'active' : '' ?>"><?= $t['cat_drakor'] ?></a>
                <a href="?view=indo" class="tab-link <?= ($view == 'indo') ? 'active' : '' ?>"><?= $t['cat_indo'] ?></a>
            </div>
            
            <form action="" method="GET">
                <input type="hidden" name="view" value="<?= $view ?>">
                <?php if(isset($_GET['lang'])): ?><input type="hidden" name="lang" value="<?= $_GET['lang'] ?>"><?php endif; ?>
                
                <select name="sort" class="filter-select" onchange="this.form.submit()">
                    <option value="newest" <?= ($sort=='newest')?'selected':'' ?>><?= $t['sort_newest'] ?></option>
                    <option value="rating" <?= ($sort=='rating')?'selected':'' ?>><?= $t['sort_popular'] ?></option>
                    <option value="az" <?= ($sort=='az')?'selected':'' ?>><?= $t['sort_az'] ?></option>
                </select>
            </form>
        </div>

        <div class="grid" id="contentGrid">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <?php 
                        $is_locked = ($row['is_premium'] == 1 && $status == 'Free'); 
                        $cat_filter = explode(',', $row['genre'])[0]; 
                        $year = date('Y', strtotime($row['release_date'] ?? 'now'));
                    ?>

                    <div class="card data-item" data-category="<?= $cat_filter ?>">
                        <div class="poster">
                            <?php if($row['is_premium']): ?> <div class="vip-badge"><i class="fas fa-crown"></i> VIP</div> <?php endif; ?>
                            <img src="<?= $row['image'] ?>" onerror="this.src='https://via.placeholder.com/300x450/0f172a/00c6ff?text=No+Poster'" alt="<?= $row['title'] ?>">
                            
                            <div class="card-overlay" onclick="handlePlay(<?= $row['id'] ?>, <?= $is_locked ? 'true' : 'false' ?>)">
                                <?php if($is_locked): ?>
                                    <div class="action-btn" style="background: rgba(0,0,0,0.5); border-color: #555;"><i class="fas fa-lock"></i></div>
                                    <span style="font-size:12px; font-weight:bold; letter-spacing:1px; color:#cbd5e1;"><?= $t['card_locked'] ?></span>
                                <?php else: ?>
                                    <div class="action-btn play"><i class="fas fa-play" style="margin-left:4px;"></i></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card-details">
                            <h3 class="card-title"><?= $row['title'] ?></h3>
                            <div class="card-meta">
                                <span><?= $year ?></span>
                                <span class="match-score"><?= $row['rating'] ?>% <?= $t['card_match'] ?></span>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 60px; color: var(--text-muted);">
                    <i class="fas fa-film fa-3x" style="margin-bottom: 20px; color: var(--primary-start); opacity: 0.5;"></i>
                    <p><?= $t['empty_content'] ?> <b><?= $current_title ?></b>.</p>
                </div>
            <?php endif; ?>
        </div>
        
        <p id="noResult" style="display:none; text-align:center; padding:40px; color:var(--text-muted);"><?= $t['no_search'] ?></p>

        <?php if($total_pages > 1): ?>
        <div class="pagination">
            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                <a href="?view=<?= $view ?>&sort=<?= $sort ?>&page=<?= $i ?><?= isset($_GET['lang']) ? '&lang='.$_GET['lang'] : '' ?>" class="page-num <?= ($page == $i) ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </main>

    <aside class="sidebar">
        <?php if($status == 'Free'): ?>
        <div class="premium-box">
            <div style="position:relative; z-index:2;">
                <h3 style="margin:0 0 8px; color:#fff;"><?= $t['up_title'] ?></h3>
                <p style="font-size:13px; color:#bfdbfe; margin-bottom:15px;"><?= $t['up_desc'] ?></p>
                <a href="payment.php" style="display:inline-block; background:#fff; color:#0f172a; padding:10px 24px; border-radius:30px; font-weight:800; font-size:12px; transition:0.3s; box-shadow:0 5px 15px rgba(255,255,255,0.2);"><?= $t['up_btn'] ?></a>
            </div>
        </div>
        <?php endif; ?>

        <div class="genre-box">
            <h4 class="genre-title"><?= $t['genre_title'] ?> <?= $current_title ?></h4>
            <div class="chip-container">
                <div class="chip active" onclick="filterGenre('all', this)"><?= $t['chip_all'] ?></div>
                <?php foreach($genres_list as $g): ?>
                    <div class="chip" onclick="filterGenre('<?= $g ?>', this)"><?= $g ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </aside>

</div>

<footer class="site-footer">
    <div class="footer-content">
        <div class="footer-brand">
            <h2>MOV<span>DO</span></h2>
            <p><?= $t['foot_desc'] ?></p>
            <div class="social-icons">
                <a href="#"><i class="fab fa-instagram"></i></a>
                <a href="#"><i class="fab fa-twitter"></i></a>
                <a href="#"><i class="fab fa-tiktok"></i></a>
                <a href="#"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <div>
            <span class="footer-heading"><?= $t['head_explore'] ?></span>
            <ul class="footer-links">
                <li><a href="?view=movie"><?= $t['link_latest'] ?></a></li>
                <li><a href="?view=drakor"><?= $t['cat_drakor'] ?></a></li>
                <li><a href="?view=indo"><?= $t['cat_indo'] ?></a></li>
                <li><a href="#"><?= $t['link_top'] ?></a></li>
            </ul>
        </div>
        
        <div>
            <span class="footer-heading"><?= $t['head_help'] ?></span>
            <ul class="footer-links">
                <li><a href="#"><?= $t['link_help_center'] ?></a></li>
                <li><a href="#"><?= $t['link_terms'] ?></a></li>
                <li><a href="#"><?= $t['link_privacy'] ?></a></li>
                <li><a href="payment.php"><?= $t['link_sub'] ?></a></li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <p>&copy; 2025 <b>MOVDO Entertainment</b>. <?= $t['rights'] ?></p>
    </div>
</footer>

<div id="toast"><i class="fas fa-check-circle"></i> <?= $t['toast_title'] ?></div>

<script>
    window.addEventListener('scroll', function() {
        const header = document.getElementById('mainHeader');
        if(window.scrollY > 50) { header.classList.add('scrolled'); } 
        else { header.classList.remove('scrolled'); }
    });

    function handlePlay(id, isLocked) {
        if(isLocked) {
            if(confirm("<?= $t['js_confirm_prem'] ?>")) {
                window.location.href = 'payment.php';
            }
        } else {
            window.location.href = "watch.php?id=" + id;
        }
    }

    function filterLocal() {
        let input = document.getElementById('searchInput').value.toUpperCase();
        let items = document.querySelectorAll('.data-item');
        let visibleCount = 0;

        items.forEach(item => {
            let txt = item.innerText;
            if (txt.toUpperCase().indexOf(input) > -1) {
                item.style.display = "";
                visibleCount++;
            } else {
                item.style.display = "none";
            }
        });
        
        document.getElementById('noResult').style.display = (visibleCount === 0) ? 'block' : 'none';
    }

    function filterGenre(cat, element) {
        document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
        element.classList.add('active');

        let items = document.querySelectorAll('.data-item');
        items.forEach(item => {
            let itemCat = item.getAttribute('data-category');
            if (cat === 'all' || (itemCat && itemCat.includes(cat))) {
                item.style.display = "";
            } else {
                item.style.display = "none";
            }
        });
    }

    function showToast(msg) {
        let x = document.getElementById("toast");
        x.innerHTML = '<i class="fas fa-check-circle" style="color: #00c6ff; margin-right: 8px;"></i> ' + msg;
        x.className = "show";
        setTimeout(function(){ x.className = x.className.replace("show", ""); }, 3000);
    }
</script>

</body>
</html>