<?php
require_once 'koneksi.php';

try {
    // 1. DATA DUMMY UNTUK TABEL ASDOS
    // ID RFID di bawah ini adalah contoh. Nanti kamu bisa menggantinya dengan UID kartu fisik asli.
    $data_asdos = [
        [
            'id_rfid' => '1234567A', 
            'nama'    => 'Eki Sulastri', 
            'nim'     => '5231011022'
        ],
        [
            'id_rfid' => '89ABCDEF', 
            'nama'    => 'Alifia Sindi Ananda', 
            'nim'     => '5231011035'
        ],
        [
            'id_rfid' => 'A1B2C3D4', 
            'nama'    => 'Ummi Lathifa Nabila', 
            'nim'     => '5231011057'
        ],

        [
            'id_rfid' => 'A1B2C3D9', 
            'nama'    => 'Lidia Fitriana', 
            'nim'     => '5231011009'
        ],

         [
            'id_rfid' => 'A1B2C3D12', 
            'nama'    => 'Ritaningsih', 
            'nim'     => '5231011031'
        ]
    ];

    // Menggunakan INSERT IGNORE agar jika script dijalankan ulang, tidak terjadi error duplicate key
    $query_asdos = "INSERT IGNORE INTO asdos (id_rfid, nama, nim) VALUES (:id_rfid, :nama, :nim)";
    $stmt_asdos = $pdo->prepare($query_asdos);
    
    foreach ($data_asdos as $asdos) {
        $stmt_asdos->execute($asdos);
    }

    // 2. DATA DUMMY UNTUK TABEL IOT KITS (BOX)
    // Teks di 'id_qr' inilah yang nantinya kamu ketik/generate menjadi gambar QR Code untuk ditempel di box.
    $data_kits = [
        [
            'id_qr'    => 'KIT-AIR-01', 
            'nama_kit' => 'IoT Kit Box - Monitoring Kualitas Udara', 
            'status'   => 'tersedia'
        ],
        [
            'id_qr'    => 'KIT-SCALE-01', 
            'nama_kit' => 'IoT Kit Box - Timbangan Digital & Berat', 
            'status'   => 'tersedia'
        ],
        [
            'id_qr'    => 'KIT-SMART-01', 
            'nama_kit' => 'IoT Kit Box - Smart Home & Interface Systems', 
            'status'   => 'tersedia'
        ]
    ];

    $query_kits = "INSERT IGNORE INTO iot_kits (id_qr, nama_kit, status) VALUES (:id_qr, :nama_kit, :status)";
    $stmt_kits = $pdo->prepare($query_kits);
    
    foreach ($data_kits as $kit) {
        $stmt_kits->execute($kit);
    }

    echo "<div style='font-family: Arial; text-align: center; margin-top: 50px;'>";
    echo "<h2 style='color: #007bff;'>🚀 Sukses! Data Dummy Berhasil Dimasukkan!</h2>";
    echo "<p>Tabel Asdos dan IoT Kits kini sudah memiliki data awal untuk pengujian.</p>";
    echo "<p>Silakan kembali dan cek halaman <a href='dashboard.php'>Dashboard</a></p>";
    echo "</div>";

} catch (PDOException $e) {
    echo "<h3 style='color: red;'>Gagal memasukkan data dummy: " . $e->getMessage() . "</h3>";
}
?>