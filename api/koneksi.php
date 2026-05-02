<?php
// Deteksi apakah berjalan di localhost atau Hosting
$is_localhost = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1');

if ($is_localhost) {
    // KONFIGURASI LOKAL (LARAGON)
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_lacak_alumni";
} else {
    // KONFIGURASI INFINITYFREE (Isi sesuai data dari vPanel Anda)
    $host = "sqlXXX.infinityfree.com"; // Ganti XXX dengan nomor server Anda
    $user = "epiz_XXXXXXXX";           // Ganti dengan Username MySQL Anda
    $pass = "P4ssw0rdAnda";            // Ganti dengan Password MySQL Anda
    $db   = "epiz_XXXXXXXX_db_name";   // Ganti dengan Nama Database Anda
}

// Fungsi untuk mendapatkan koneksi
function getConnection($withDb = true) {
    global $host, $user, $pass, $db;
    try {
        if ($withDb) {
            $conn = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
        } else {
            $conn = new PDO("mysql:host=$host;charset=utf8", $user, $pass);
        }
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        $error_msg = ($_SERVER['HTTP_HOST'] == 'localhost') ? $e->getMessage() : "Gagal terhubung ke database hosting.";
        die(json_encode(["status" => "error", "message" => "Koneksi gagal: " . $error_msg]));
    }
}
?>
