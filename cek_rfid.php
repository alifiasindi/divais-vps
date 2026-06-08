<?php
// FILE 3: cek_rfid.php (AJAX Polling untuk Dashboard)
// Dipanggil setiap ~1 detik oleh JavaScript.
// Logika:
// - Jika ada data di temp_rfid: ambil 1 baris terbaru/terakhir,
//   JOIN ke asdos untuk dapat nama & nim,
//   return JSON, lalu hapus baris tersebut agar popup tidak muncul berulang.
// - Jika tidak ada data: return status:false

session_start();
require_once 'koneksi.php';

// (Opsional) Proteksi admin jika dibutuhkan.
// Jika dashboard butuh login, Anda bisa aktifkan proteksi session yang sama.
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    // Tetap kembalikan JSON agar frontend tidak error parsing.
    header('Content-Type: application/json');
    echo json_encode(["status" => false, "message" => "Unauthenticated"]);
    exit;
}

header('Content-Type: application/json');

try {
    // Ambil 1 tap terbaru
   $sql = "
    SELECT tr.id,
           tr.id_rfid,
           a.nama,
           a.nim
    FROM temp_rfid tr
    JOIN asdos a ON a.id_rfid = tr.id_rfid
    ORDER BY tr.id DESC
    LIMIT 1
";

    $stmt = $pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(["status" => false]);
        exit;
    }

  $pdo->beginTransaction();

$del = $pdo->prepare("DELETE FROM temp_rfid WHERE id = ?");
$del->execute([$row['id']]);

$pdo->commit();

    echo json_encode([
        "status" => true,
        "id_rfid" => $row['id_rfid'],
        "nama" => $row['nama'],
        "nim" => $row['nim']
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status" => false,
        "message" => "Database Error",
        "error" => $e->getMessage()
    ]);
}
?>


