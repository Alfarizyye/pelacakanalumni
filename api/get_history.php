<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    $conn = getConnection();
    $stmt = $conn->query("SELECT * FROM history ORDER BY id DESC");
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(["status" => "success", "data" => $history]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
