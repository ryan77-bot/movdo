<?php
session_start();
require 'db.php';

// Cek Admin (Sederhana: Cek login saja)
if (!isset($_SESSION['user_logged_in'])) { header("Location: login.php"); exit; }

$msg = "";

if (isset($_POST['upload'])) {
    $title = $_POST['title'];
    $genre = $_POST['genre'];
    $desc = $_POST['description'];
    
    // 1. Upload Poster (Image)
    $poster_dir = "uploads/posters/";
    if (!file_exists($poster_dir)) mkdir($poster_dir, 0777, true);
    
    $poster_name = time() . "_" . $_FILES['poster']['name'];
    move_uploaded_file($_FILES['poster']['tmp_name'], $poster_dir . $poster_name);
    $poster_path = $poster_dir . $poster_name;

    // 2. Upload Video (Local File)
    $video_dir = "uploads/videos/";
    if (!file_exists($video_dir)) mkdir($video_dir, 0777, true);

    $video_name = time() . "_" . $_FILES['video_file']['name'];
    $target_video = $video_dir . $video_name;

    if (move_uploaded_file($_FILES['video_file']['tmp_name'], $target_video)) {
        // Simpan ke DB
        $stmt = $conn->prepare("INSERT INTO movies (title, genre, image, description, local_video_path, is_premium, type, rating) VALUES (?, ?, ?, ?, ?, 1, 'movie', 8.0)");
        $stmt->bind_param("sssss", $title, $genre, $poster_path, $desc, $target_video);
        
        if ($stmt->execute()) {
            $msg = "Film berhasil diupload!";
        } else {
            $msg = "Gagal simpan database.";
        }
    } else {
        $msg = "Gagal upload video. Cek ukuran file php.ini";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <title>Import Video - MOVDO</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { background: #050a14; color: white; font-family: 'Outfit', sans-serif; display: flex; justify-content: center; padding: 50px; }
        .upload-box { background: #0f172a; padding: 40px; border-radius: 20px; width: 500px; border: 1px solid #1e293b; }
        input, textarea, select { width: 100%; background: #1e293b; border: 1px solid #334155; color: white; padding: 10px; margin: 10px 0; border-radius: 8px; }
        button { width: 100%; background: #00c6ff; color: white; border: none; padding: 15px; border-radius: 10px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        h2 { margin-top: 0; color: #00c6ff; }
    </style>
</head>
<body>
    <div class="upload-box">
        <h2>Import Video Lokal</h2>
        <?php if($msg) echo "<p style='color:#4ade80'>$msg</p>"; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <label>Judul Film</label>
            <input type="text" name="title" required>

            <label>Genre (Pisahkan koma)</label>
            <input type="text" name="genre" placeholder="Action, Drama, Horror">

            <label>Deskripsi</label>
            <textarea name="description" rows="3"></textarea>

            <label>Poster (Gambar)</label>
            <input type="file" name="poster" accept="image/*" required>

            <label>File Video (MP4/MKV)</label>
            <input type="file" name="video_file" accept="video/*" required>
            <small style="color:#94a3b8">Pastikan ukuran video di bawah batas upload PHP.</small>

            <button type="submit" name="upload">UPLOAD FILM</button>
        </form>
        <br>
        <a href="index.php" style="color: #94a3b8; text-decoration: none;">&larr; Kembali ke Dashboard</a>
    </div>
</body>
</html>