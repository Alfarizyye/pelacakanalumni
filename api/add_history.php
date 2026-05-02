<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['name']) || !isset($data['status'])) {
    echo json_encode(["status" => "error", "message" => "Data tidak lengkap"]);
    exit;
}

try {
    $conn = getConnection();
    $stmt = $conn->prepare("INSERT INTO history (name, status, score, tanggal) VALUES (?, ?, ?, ?)");
    
    $stmt->execute([
        $data['name'],
        $data['status'],
        $data['score'] ?? 0,
        $data['tanggal'] ?? date('Y-m-d H:i:s')
    ]);

    echo json_encode(["status" => "success", "message" => "History berhasil ditambahkan"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
