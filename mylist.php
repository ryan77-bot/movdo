<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_logged_in'])) {
    header("Location: login.php");
    exit;
}

$current_user = $_SESSION['username'];

// AMBIL STATUS USER (Untuk Badge VIP)
$stmt_user = $conn->prepare("SELECT subscription_type FROM users WHERE username = ?");
$stmt_user->bind_param("s", $current_user);
$stmt_user->execute();
$status = $stmt_user->get_result()->fetch_assoc()['subscription_type'];

// AMBIL FILM DARI WATCHLIST (JOIN TABLE)
// Kita gabungkan tabel 'movies' dan 'watchlist' berdasarkan ID
$sql = "SELECT movies.* FROM movies 
        JOIN watchlist ON movies.id = watchlist.movie_id 
        WHERE watchlist.username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Saya - MOVDO</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { background: #0f1014; color: #fff; font-family: 'Segoe UI', sans-serif; margin: 0; }
        
        .header { padding: 20px 40px; border-bottom: 1px solid #333; display: flex; align-items: center; justify-content: space-between; }
        .logo { font-size: 24px; font-weight: bold; color: #fff; text-decoration: none; }
        .logo span { color: #e50914; }
        .back-btn { color: #aaa; text-decoration: none; font-weight: bold; }
        
        .container { padding: 40px; }
        .section-title { font-size: 24px; margin-bottom: 20px; border-left: 4px solid #e50914; padding-left: 15px; }
        
        /* Grid Sama seperti Index */
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); gap: 20px; }
        .card { cursor: pointer; transition: 0.3s; position: relative; background: #1f1f1f; border-radius: 5px; overflow: hidden; }
        .card:hover { transform: scale(1.05); z-index: 10; }
        .poster { position: relative; height: 250px; }
        .poster img { width: 100%; height: 100%; object-fit: cover; }
        .tag { position: absolute; top: 10px; left: 0; background: #e50914; padding: 2px 8px; font-size: 11px; font-weight: bold; }
        .vip-tag { position: absolute; top: 0; right: 0; background: #ffd700; color: #000; padding: 4px 8px; font-size: 10px; font-weight: bold; }
        
        /* Tombol Hapus Kecil */
        .btn-remove {
            position: absolute; bottom: 10px; right: 10px;
            background: rgba(0,0,0,0.7); color: #fff; border: 1px solid #555;
            width: 30px; height: 30px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; z-index: 5; transition: 0.3s;
        }
        .btn-remove:hover { background: #e50914; border-color: #e50914; }
    </style>
</head>
<body>

<div class="header">
    <a href="index.php" class="logo">MOV<span>DO</span></a>
    <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Dashboard</a>
</div>

<div class="container">
    <h2 class="section-title">My Watchlist</h2>

    <?php if ($result->num_rows > 0): ?>
        <div class="grid">
            <?php while($row = $result->fetch_assoc()): 
                $is_locked = ($row['is_premium'] == 1 && $status == 'Free');
            ?>
                <div class="card" onclick="location.href='watch.php?id=<?= $row['id'] ?>'">
                    <div class="poster">
                        <span class="tag"><?= $row['lang'] ?></span>
                        <?php if($row['is_premium']) echo '<span class="vip-tag">VIP</span>'; ?>
                        <img src="<?= $row['image'] ?>">
                        
                        <form method="POST" action="watch.php?id=<?= $row['id'] ?>" style="display:inline;" onclick="event.stopPropagation()">
                            <input type="hidden" name="toggle_list" value="1">
                            <button class="btn-remove" title="Hapus dari List"><i class="fas fa-times"></i></button>
                        </form>
                    </div>
                    <div style="padding:10px;">
                        <h4 style="margin:0; font-size:14px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?= $row['title'] ?></h4>
                        <p style="margin:5px 0 0; font-size:12px; color:#777;"><?= $row['genre'] ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div style="text-align:center; padding:50px; color:#777;">
            <i class="far fa-folder-open" style="font-size:50px; margin-bottom:20px;"></i>
            <p>Belum ada film yang disimpan.</p>
            <a href="index.php" style="color:#e50914;">Cari Film</a>
        </div>
    <?php endif; ?>
</div>

</body>
</html>