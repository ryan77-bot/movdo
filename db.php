<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "movdo_db";

$conn = new mysqli($host, $user, $pass, $db);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
?>