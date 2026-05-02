<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['nama_lulusan'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak valid"]);
    exit;
}

try {
    $conn = getConnection();
    $nama = trim($data['nama_lulusan']);

    // ✅ VALIDASI SERVER-SIDE: Cek apakah nama ada di data import resmi
    // Cek apakah kolom 'source' sudah ada
    $hasSource = false;
    try {
        $testCol = $conn->query("SELECT source FROM alumni LIMIT 1");
        $hasSource = true;
    } catch (Exception $e) {
        $hasSource = false;
    }

    if ($hasSource) {
        // Cari nama di alumni yang source = 'import'
        $cek = $conn->prepare("SELECT id FROM alumni WHERE nama_lulusan LIKE ? AND source = 'import' LIMIT 1");
        $cek->execute(["%$nama%"]);
        $found = $cek->fetch(PDO::FETCH_ASSOC);

        if (!$found) {
            echo json_encode([
                "status"  => "not_found",
                "message" => "Mahasiswa \"$nama\" tidak ditemukan dalam database alumni UMM. Data tidak disimpan."
            ]);
            exit;
        }

        // Alumni ditemukan — kembalikan ID-nya agar JS bisa langsung tracking
        echo json_encode([
            "status"  => "exists",
            "message" => "Alumni ditemukan di database.",
            "id"      => (int)$found['id']
        ]);
        exit;
    }

    // Fallback jika kolom source belum ada (migration belum dijalankan):
    // Cari berdasarkan nama saja + NIM tidak kosong
    $cek2 = $conn->prepare("SELECT id FROM alumni WHERE nama_lulusan LIKE ? AND nim IS NOT NULL AND nim != '' AND nim != '-' LIMIT 1");
    $cek2->execute(["%$nama%"]);
    $found2 = $cek2->fetch(PDO::FETCH_ASSOC);

    if (!$found2) {
        echo json_encode([
            "status"  => "not_found",
            "message" => "Mahasiswa \"$nama\" tidak ditemukan dalam database alumni UMM. Data tidak disimpan."
        ]);
        exit;
    }

    echo json_encode([
        "status"  => "exists",
        "message" => "Alumni ditemukan di database.",
        "id"      => (int)$found2['id']
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
