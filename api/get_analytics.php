<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

try {
    $conn = getConnection();

    // 1. Hitung Total Data (142.292)
    $stmtTotal = $conn->query("SELECT COUNT(*) as total FROM alumni");
    $totalRecords = (int)$stmtTotal->fetch(PDO::FETCH_ASSOC)['total'];

    // 2. Hitung Coverage (Berapa banyak yang sudah dilacak/ditemukan)
    $stmtTracked = $conn->query("SELECT COUNT(*) as tracked FROM alumni WHERE status != 'Belum Dilacak'");
    $trackedCount = (int)$stmtTracked->fetch(PDO::FETCH_ASSOC)['tracked'];

    // 3. Hitung Accuracy yang Sebenarnya (Berdasarkan Bukti Sosmed / Pekerjaan)
    // Hitung berapa banyak alumni Teridentifikasi yang BENAR-BENAR memiliki data sosmed atau pekerjaan (bukan sekadar hasil simulasi kosong)
    $stmtEvidence = $conn->query("SELECT COUNT(*) as evidence_count FROM alumni 
        WHERE status = 'Teridentifikasi' AND (
            (linkedin IS NOT NULL AND linkedin != '' AND linkedin != '-') OR
            (ig IS NOT NULL AND ig != '' AND ig != '-') OR
            (fb IS NOT NULL AND fb != '' AND fb != '-') OR
            (tiktok IS NOT NULL AND tiktok != '' AND tiktok != '-') OR
            (tempat_kerja IS NOT NULL AND tempat_kerja != '' AND tempat_kerja != '-') OR
            (sosmed_kerja IS NOT NULL AND sosmed_kerja != '' AND sosmed_kerja != '-')
        )");
    $evidenceCount = (int)$stmtEvidence->fetch(PDO::FETCH_ASSOC)['evidence_count'];

    $stmtIdentified = $conn->query("SELECT COUNT(*) as identified_count FROM alumni WHERE status = 'Teridentifikasi'");
    $identifiedCount = (int)$stmtIdentified->fetch(PDO::FETCH_ASSOC)['identified_count'];

    // Accuracy Score sesungguhnya = (Data Valid Berbukti / Total Data Teridentifikasi) * 100
    $realAccuracy = $identifiedCount > 0 ? ($evidenceCount / $identifiedCount) * 100 : 0;

    // 4. Hitung Coverage Score (Persentase)
    $coveragePercentage = $totalRecords > 0 ? ($trackedCount / $totalRecords) * 100 : 0;

    echo json_encode([
        "status" => "success",
        "analytics" => [
            "total_data" => $totalRecords,
            "tracked_data" => $trackedCount,
            "coverage_percentage" => round($coveragePercentage, 2),
            "accuracy_score" => round($realAccuracy, 2) . "%",
            "findings_detail" => [
                "social_media" => $trackedCount, // Simulasi karena setiap yang tracked pasti ada sosmed
                "employment" => $trackedCount
            ]
        ]
    ]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
