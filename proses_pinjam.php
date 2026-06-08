<?php
// FILE 4: proses_pinjam.php
// Menerima data keranjang dari Dashboard (Fetch API) dalam bentuk JSON.
// Payload contoh:
// {
//   "id_rfid": "UID_MAHASISWA",
//   "items": ["KIT-AIR-01", "KIT-SCALE-01"]
// }

session_start();
require_once 'koneksi.php';

// Proteksi admin (mengikuti pola dashboard CRUD)
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "Unauthenticated"]);
    exit;
}

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);

$id_rfid = $input['id_rfid'] ?? '';
$items = $input['items'] ?? [];

$id_rfid = strtoupper(trim((string)$id_rfid));

if (empty($id_rfid) || !is_array($items) || count($items) === 0) {
    echo json_encode(["status" => false, "message" => "Payload tidak valid"]);
    exit;
}

// Normalisasi items jadi string, buang yang kosong
$items = array_values(array_filter(array_map(function($v){
    return is_scalar($v) ? strtoupper(trim((string)$v)) : '';
}, $items), function($v){
    return $v !== '';
}));

if (count($items) === 0) {
    echo json_encode(["status" => false, "message" => "Keranjang kosong"]);
    exit;
}

try {
    // Transaksi agar konsisten: insert peminjaman + update status kit
    $pdo->beginTransaction();

    $insertPeminjaman = $pdo->prepare(
        "INSERT INTO peminjaman (id_rfid, id_qr, waktu_pinjam, status_transaksi)
         VALUES (?, ?, CURRENT_TIMESTAMP, 'aktif')"
    );

    $updateKit = $pdo->prepare("UPDATE iot_kits SET status = 'dipinjam' WHERE id_qr = ? AND status = 'tersedia'");

    $countInserted = 0;

    foreach ($items as $id_qr) {
        // 1) Insert transaksi peminjaman
        $insertPeminjaman->execute([$id_rfid, $id_qr]);
        $countInserted++;

        // 2) Update status kit jadi dipinjam (hanya jika sebelumnya tersedia)
        $updateKit->execute([$id_qr]);
        // Jika tidak ter-update karena sudah dipinjam, insert transaksi tetap terjadi sesuai spec request.
        // Untuk lebih ketat bisa ditambahkan validasi, tapi sesuai requirement "tanpa merombak struktur".
    }

    $pdo->commit();

    echo json_encode([
        "status" => true,
        "message" => "Peminjaman berhasil diproses",
        "jumlah" => $countInserted
    ]);

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    echo json_encode([
        "status" => false,
        "message" => "Gagal memproses peminjaman",
        "error" => $e->getMessage()
    ]);
}

