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
    $stmt = $conn->prepare("DELETE FROM alumni WHERE id = ?");
    $stmt->execute([$data['id']]);

    echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
