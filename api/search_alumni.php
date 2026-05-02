<?php
header('Content-Type: application/json');
require_once 'koneksi.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!$data || empty($data['nama_lulusan'])) {
    echo json_encode(["status" => "error", "message" => "Nama wajib diisi"]);
    exit;
}

try {
    $conn = getConnection();

    $nama  = trim($data['nama_lulusan']);
    $nim   = trim($data['nim'] ?? '');
    $tahunMasuk   = trim($data['tahun_masuk'] ?? '');
    $tanggalLulus = trim($data['tanggal_lulus'] ?? '');
    $fakultas     = trim($data['fakultas'] ?? '');
    $prodi        = trim($data['program_studi'] ?? '');

    // Bangun kondisi WHERE secara dinamis
    // Nama selalu wajib; field lain hanya dipakai jika diisi pengguna
    $conditions = ["nama_lulusan LIKE :nama"];
    $params     = [':nama' => "%$nama%"];

    if ($nim !== '') {
        $conditions[] = "nim = :nim";
        $params[':nim'] = $nim;
    }
    if ($tahunMasuk !== '') {
        $conditions[] = "tahun_masuk = :tahun_masuk";
        $params[':tahun_masuk'] = $tahunMasuk;
    }
    if ($tanggalLulus !== '') {
        $conditions[] = "tanggal_lulus = :tanggal_lulus";
        $params[':tanggal_lulus'] = $tanggalLulus;
    }
    if ($fakultas !== '') {
        $conditions[] = "fakultas LIKE :fakultas";
        $params[':fakultas'] = "%$fakultas%";
    }
    if ($prodi !== '') {
        $conditions[] = "program_studi LIKE :program_studi";
        $params[':program_studi'] = "%$prodi%";
    }

    // ⚠️ Hanya cari di data yang diimport resmi (bukan data manual/palsu)
    $where = implode(' AND ', $conditions);
    $stmt  = $conn->prepare("SELECT * FROM alumni WHERE source = 'import' AND $where LIMIT 10");
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($results) === 0) {
        echo json_encode([
            "status"  => "not_found",
            "message" => "Mahasiswa tidak ditemukan dalam database. Pastikan nama, NIM, atau informasi lainnya sesuai data alumni UMM."
        ]);
    } else {
        // Konversi confidence ke integer
        foreach ($results as &$r) {
            $r['confidence'] = (int)$r['confidence'];
        }
        echo json_encode([
            "status" => "found",
            "data"   => $results
        ]);
    }

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}
?>
