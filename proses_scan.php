<?php
session_start();
require_once 'koneksi.php';

header('Content-Type: application/json');

// Proteksi admin
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    echo json_encode(["status" => false, "message" => "Unauthenticated"]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_rfid = strtoupper(trim((string)($input['id_rfid'] ?? '')));
$id_qr = strtoupper(trim((string)($input['id_qr'] ?? '')));

if (empty($id_rfid) || empty($id_qr)) {
    echo json_encode(["status" => false, "message" => "Data RFID atau QR kosong!"]);
    exit;
}

try {
    $pdo->beginTransaction();

    // Cek apakah QR alat terdaftar di database
    $stmtKit = $pdo->prepare("SELECT * FROM iot_kits WHERE id_qr = ?");
    $stmtKit->execute([$id_qr]);
    $kit = $stmtKit->fetch(PDO::FETCH_ASSOC);

    if (!$kit) {
        echo json_encode(["status" => false, "message" => "Barcode/QR Alat tidak dikenali!"]);
        exit;
    }

    $namaAlat = $kit['nama_kit'];

    if ($kit['status'] === 'tersedia') {
        // --- LOGIKA PEMINJAMAN ---
        $ins = $pdo->prepare("INSERT INTO peminjaman (id_rfid, id_qr, waktu_pinjam, status_transaksi) VALUES (?, ?, CURRENT_TIMESTAMP, 'aktif')");
        $ins->execute([$id_rfid, $id_qr]);

        $upd = $pdo->prepare("UPDATE iot_kits SET status = 'dipinjam' WHERE id_qr = ?");
        $upd->execute([$id_qr]);

        $pdo->commit();
        echo json_encode(["status" => true, "message" => "✅ BERHASIL PINJAM: $namaAlat"]);

    } else if ($kit['status'] === 'dipinjam') {
        // --- LOGIKA PENGEMBALIAN ---
        // Cek apakah mahasiswa ini yang meminjam alat tersebut
        $cek = $pdo->prepare("SELECT id FROM peminjaman WHERE id_qr = ? AND id_rfid = ? AND status_transaksi = 'aktif'");
        $cek->execute([$id_qr, $id_rfid]);
        $pinjamanAktif = $cek->fetch(PDO::FETCH_ASSOC);

        if ($pinjamanAktif) {
            // Update waktu kembali di tabel peminjaman
            $updPinjam = $pdo->prepare("UPDATE peminjaman SET waktu_kembali = CURRENT_TIMESTAMP, status_transaksi = 'selesai' WHERE id = ?");
            $updPinjam->execute([$pinjamanAktif['id']]);

            // Ubah status alat menjadi tersedia lagi
            $updKit = $pdo->prepare("UPDATE iot_kits SET status = 'tersedia' WHERE id_qr = ?");
            $updKit->execute([$id_qr]);

            $pdo->commit();
            echo json_encode(["status" => true, "message" => "🔄 BERHASIL KEMBALI: $namaAlat telah dikembalikan."]);
        } else {
            echo json_encode(["status" => false, "message" => "❌ GAGAL: Alat ini sedang dipinjam oleh mahasiswa lain!"]);
        }
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(["status" => false, "message" => "Database Error: " . $e->getMessage()]);
}
?>