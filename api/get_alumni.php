<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    $conn = getConnection();
    
    // Pagination parameters
    $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
    $offset = ($page - 1) * $limit;
    $trackedOnly = isset($_GET['tracked']) && $_GET['tracked'] == '1';

    $whereClause = $trackedOnly ? "WHERE status != 'Belum Dilacak'" : "";

    // Get total rows
    $stmtTotal = $conn->query("SELECT COUNT(*) as total FROM alumni $whereClause");
    $totalRows = $stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRows / $limit);

    // Get paginated data
    $stmt = $conn->prepare("SELECT * FROM alumni $whereClause ORDER BY updated_at DESC, id DESC LIMIT :limit OFFSET :offset");
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $alumni = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Konversi nilai integer confidence agar tetap jadi number di JS
    foreach($alumni as &$a) {
        $a['confidence'] = (int)$a['confidence'];
    }

    echo json_encode([
        "status" => "success", 
        "data" => $alumni,
        "pagination" => [
            "current_page" => $page,
            "total_pages" => $totalPages,
            "total_records" => $totalRows,
            "limit" => $limit
        ]
    ]);
} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
