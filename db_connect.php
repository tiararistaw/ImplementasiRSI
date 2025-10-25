<?php
// Mulai sesi jika belum dimulai.
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Konfigurasi Database XAMPP
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'mental_health_db');

try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Tampilkan pesan error jika koneksi gagal (hanya di lingkungan pengembangan)
    die("Koneksi gagal: " . $e->getMessage());
}
?>