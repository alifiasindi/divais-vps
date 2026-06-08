<?php
require_once 'koneksi.php';

header('Content-Type: application/json');

// Ambil data JSON dari ESP32
$input = json_decode(file_get_contents("php://input"), true);

// Ambil UID
$uid = strtoupper(trim($input['uid'] ?? ''));

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

        echo json_encode([
            "status" => true,
            "message" => "RFID dikenali",
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