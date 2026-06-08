<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

// Ambil UID dari POST ESP32
$uid = strtoupper(trim($_POST['uid'] ?? ''));

// Validasi UID kosong
if (empty($uid)) {
    echo json_encode([
        "status" => false,
        "message" => "UID kosong"
    ]);
    exit;
}

try {

    // Cari mahasiswa berdasarkan UID RFID
    $stmt = $pdo->prepare("
        SELECT
            id_rfid,
            nama,
            nim
        FROM asdos
        WHERE id_rfid = ?
        LIMIT 1
    ");

    $stmt->execute([$uid]);

    $mahasiswa = $stmt->fetch(PDO::FETCH_ASSOC);

    // Jika UID ditemukan
    if ($mahasiswa) {

        // --- TAMBAHAN: Simpan tap sementara ke temp_rfid agar dashboard bisa realtime ---
        // 1) Bersihkan tabel temp_rfid (sesuai requirement: selalu hanya 1 tap terbaru)
        $pdo->exec("TRUNCATE TABLE temp_rfid");

        // 2) Insert id_rfid terbaru
        $stmtTemp = $pdo->prepare("INSERT INTO temp_rfid (id_rfid, waktu) VALUES (?, CURRENT_TIMESTAMP)");
        $stmtTemp->execute([$mahasiswa['id_rfid']]);

        echo json_encode([
            "status" => true,
            "message" => $mahasiswa['nama'],
            "uid" => $mahasiswa['id_rfid'],
            "nama" => $mahasiswa['nama'],
            "nim" => $mahasiswa['nim']
        ]);

    } else {

        echo json_encode([
            "status" => false,
            "message" => "UID tidak terdaftar",
            "uid" => $uid
        ]);

    }

} catch (PDOException $e) {


    echo json_encode([
        "status" => false,
        "message" => "Database Error",
        "error" => $e->getMessage()
    ]);

}
?>