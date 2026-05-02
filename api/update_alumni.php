<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(["status" => "error", "message" => "ID tidak diberikan"]);
    exit;
}

try {
    $conn = getConnection();
    
    $stmt = $conn->prepare("UPDATE alumni SET 
        email = ?, nohp = ?, linkedin = ?, ig = ?, fb = ?, tiktok = ?, 
        tempat_kerja = ?, alamat_kerja = ?, sosmed_kerja = ?, posisi = ?, 
        jenis_pekerjaan = ?, status = ?, confidence = ? WHERE id = ?");

    $stmt->execute([
        $data['email'] ?? '',
        $data['nohp'] ?? '',
        $data['linkedin'] ?? '',
        $data['ig'] ?? '',
        $data['fb'] ?? '',
        $data['tiktok'] ?? '',
        $data['tempat_kerja'] ?? '',
        $data['alamat_kerja'] ?? '',
        $data['sosmed_kerja'] ?? '',
        $data['posisi'] ?? '',
        $data['jenis_pekerjaan'] ?? '',
        $data['status'] ?? 'Belum Dilacak',
        $data['confidence'] ?? 0,
        $data['id']
    ]);

    echo json_encode(["status" => "success", "message" => "Data berhasil diupdate"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
