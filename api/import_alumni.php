<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || !is_array($data)) {
    echo json_encode(["status" => "error", "message" => "Data tidak valid"]);
    exit;
}

try {
    $conn = getConnection();
    $conn->beginTransaction();

    $stmt = $conn->prepare("INSERT INTO alumni 
        (nama_lulusan, nim, tahun_masuk, tanggal_lulus, fakultas, program_studi, email, nohp, linkedin, ig, fb, tiktok, tempat_kerja, alamat_kerja, sosmed_kerja, posisi, jenis_pekerjaan, status, confidence, source) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    foreach ($data as $a) {
        $stmt->execute([
            $a['nama_lulusan'] ?? '',
            $a['nim'] ?? '',
            $a['tahun_masuk'] ?? '',
            $a['tanggal_lulus'] ?? '',
            $a['fakultas'] ?? '',
            $a['program_studi'] ?? '',
            $a['email'] ?? '',
            $a['nohp'] ?? '',
            $a['linkedin'] ?? '',
            $a['ig'] ?? '',
            $a['fb'] ?? '',
            $a['tiktok'] ?? '',
            $a['tempat_kerja'] ?? '',
            $a['alamat_kerja'] ?? '',
            $a['sosmed_kerja'] ?? '',
            $a['posisi'] ?? '',
            $a['jenis_pekerjaan'] ?? '',
            $a['status'] ?? 'Belum Dilacak',
            $a['confidence'] ?? 0,
            'import'  // ← Tandai sebagai data import resmi
        ]);
    }

    $conn->commit();
    echo json_encode(["status" => "success", "message" => "Data berhasil diimpor"]);

} catch (Exception $e) {
    $conn->rollBack();
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
