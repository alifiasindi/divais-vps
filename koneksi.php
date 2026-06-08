<?php
$host = 'mysql-db'; // Sesuai dengan nama service database di docker-compose
$db   = 'peminjaman_iot';
$user = 'alifia';
$pass = 'ananda';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// --- TAMBAHAN KODE UNTUK BIKIN TABEL OTOMATIS ---
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS temp_rfid (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_rfid VARCHAR(50) NOT NULL,
        waktu TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
} catch (PDOException $e) {
    // Biarkan kosong, abaikan jika terjadi error pembuatan tabel
}
// ------------------------------------------------

?>