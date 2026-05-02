<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    $conn = getConnection();

    // 1. Coba tambah kolom 'source' — kalau sudah ada, lewati saja
    try {
        $conn->exec("ALTER TABLE alumni ADD COLUMN source VARCHAR(20) DEFAULT 'manual'");
    } catch (Exception $e) {
        // Kolom sudah ada — tidak masalah, lanjut
    }

    // 2. Tandai alumni yang punya NIM valid sebagai 'import'
    $conn->exec("UPDATE alumni SET source = 'import' 
                 WHERE nim IS NOT NULL 
                   AND nim != '' 
                   AND nim != '-'
                   AND source = 'manual'");

    // 3. Hitung dan hapus data manual/sampah (tanpa NIM)
    $stmtCount = $conn->query("SELECT COUNT(*) as total FROM alumni WHERE source = 'manual'");
    $manualCount = (int)$stmtCount->fetch(PDO::FETCH_ASSOC)['total'];

    $conn->exec("DELETE FROM alumni WHERE source = 'manual'");

    // 4. Hitung data import yang tersisa
    $stmtImport = $conn->query("SELECT COUNT(*) as total FROM alumni WHERE source = 'import'");
    $importCount = (int)$stmtImport->fetch(PDO::FETCH_ASSOC)['total'];

    echo json_encode([
        "status"  => "success",
        "message" => "Migrasi berhasil!",
        "detail"  => [
            "data_manual_dihapus" => $manualCount,
            "data_import_tersisa" => $importCount
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
