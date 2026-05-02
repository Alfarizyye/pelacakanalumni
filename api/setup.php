<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    // Deteksi environment dari koneksi.php
    $is_localhost = ($_SERVER['HTTP_HOST'] == 'localhost' || $_SERVER['REMOTE_ADDR'] == '127.0.0.1');

    if ($is_localhost) {
        // 1. Koneksi ke MySQL server saja (tanpa DB)
        $conn = getConnection(false);
        // 2. Buat database jika belum ada
        $conn->exec("CREATE DATABASE IF NOT EXISTS db_lacak_alumni");
        // 3. Gunakan database tersebut
        $conn->exec("USE db_lacak_alumni");
    } else {
        // Di Hosting (InfinityFree), database harus dibuat manual lewat vPanel
        // Jadi kita langsung konek ke DB yang sudah didefinisikan
        $conn = getConnection(true);
    }

    // 4. Buat tabel alumni
    // Drop tabel lama jika ada agar skema baru bisa diterapkan
    $conn->exec("DROP TABLE IF EXISTS alumni");
    $sqlAlumni = "CREATE TABLE IF NOT EXISTS alumni (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nama_lulusan VARCHAR(255) NOT NULL,
        nim VARCHAR(50),
        tahun_masuk VARCHAR(50),
        tanggal_lulus VARCHAR(50),
        fakultas VARCHAR(100),
        program_studi VARCHAR(100),
        email VARCHAR(100),
        nohp VARCHAR(50),
        linkedin VARCHAR(255),
        ig VARCHAR(255),
        fb VARCHAR(255),
        tiktok VARCHAR(255),
        tempat_kerja VARCHAR(255),
        alamat_kerja TEXT,
        sosmed_kerja VARCHAR(255),
        posisi VARCHAR(100),
        jenis_pekerjaan VARCHAR(100),
        status VARCHAR(50) DEFAULT 'Belum Dilacak',
        confidence INT DEFAULT 0,
        source VARCHAR(20) DEFAULT 'manual',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX (status),
        INDEX (source),
        INDEX (nama_lulusan)
    )";
    $conn->exec($sqlAlumni);

    // 5. Buat tabel history
    $sqlHistory = "CREATE TABLE IF NOT EXISTS history (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        status VARCHAR(50),
        score INT,
        tanggal VARCHAR(50),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sqlHistory);

    echo json_encode([
        "status" => "success", 
        "message" => "Database dan Tabel berhasil dibuat atau sudah tersedia."
    ]);

} catch(PDOException $e) {
    echo json_encode([
        "status" => "error", 
        "message" => "Gagal membuat database/tabel: " . $e->getMessage()
    ]);
}
?>
