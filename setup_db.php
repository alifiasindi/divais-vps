<?php
require_once 'koneksi.php';

try {
    // Kumpulan perintah SQL untuk membuat 3 tabel utama
    $sql = "
    CREATE TABLE IF NOT EXISTS asdos (
        id_rfid VARCHAR(50) PRIMARY KEY,
        nama VARCHAR(100) NOT NULL,
        nim VARCHAR(20) NOT NULL
    );

    CREATE TABLE IF NOT EXISTS iot_kits (
        id_qr VARCHAR(50) PRIMARY KEY,
        nama_kit VARCHAR(100) NOT NULL,
        status ENUM('tersedia', 'dipinjam') DEFAULT 'tersedia'
    );

    CREATE TABLE IF NOT EXISTS peminjaman (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_rfid VARCHAR(50),
        id_qr VARCHAR(50),
        waktu_pinjam TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        waktu_kembali TIMESTAMP NULL DEFAULT NULL,
        status_transaksi ENUM('aktif', 'selesai') DEFAULT 'aktif',
        FOREIGN KEY (id_rfid) REFERENCES asdos(id_rfid) ON DELETE CASCADE,
        FOREIGN KEY (id_qr) REFERENCES iot_kits(id_qr) ON DELETE CASCADE
    );
    ";

    // Eksekusi perintah SQL di atas
    $pdo->exec($sql);
    
    echo "<div style='font-family: Arial; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: green;'>✅ Mantap! Semua tabel berhasil dibuat!</h2>";
    echo "<p>Sekarang kamu bisa kembali ke <a href='dashboard.php'>Dashboard</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Gagal membuat tabel: " . $e->getMessage() . "</h3>";
}
?>