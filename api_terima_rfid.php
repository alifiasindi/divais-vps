<?php
require_once 'koneksi.php';

// Cek apakah data UID RFID dikirim via POST
if (isset($_POST['uid'])) {
    $uid = htmlspecialchars($_POST['uid']);
    $waktu = date('Y-m-d H:i:s');

    try {
        // Cek apakah UID terdaftar di database
        $stmt = $pdo->prepare("SELECT nama FROM asdos WHERE id_rfid = ?");
        $stmt->execute([$uid]);
        $asdos = $stmt->fetch();

        if ($asdos) {
            // Berhasil mengenali kartu
            echo json_encode([
                "status" => "success",
                "message" => "Kartu dikenali: " . $asdos['nama'],
                "uid" => $uid
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "UID Kartu Tidak Terdaftar"]);
        }
    } catch (Exception $e) {
        echo json_encode(["status" => "error", "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Metode tidak valid / data UID kosong"]);
}
?>