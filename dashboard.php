<?php
session_start();
include 'koneksi.php';

// Proteksi halaman, tendang ke halaman login kalau belum login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Lab IoT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f9; }
        .header { background-color: #343a40; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; font-size: 20px; }
        .logout-btn { background-color: #dc3545; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-size: 14px; }
        .logout-btn:hover { background-color: #c82333; }
        .container { padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 12px; text-align: left; font-size: 14px; }
        th { background-color: #007bff; color: white; }
        .badge-proses { background: #ffc107; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: #333; }
        .badge-selesai { background: #28a745; color: white; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Monitoring Peminjaman Alat Lab</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="card">
            <h3>Riwayat Transaksi Terkini</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nama Alat</th>
                        <th>Peminjam (Mahasiswa)</th>
                        <th>Waktu Pinjam</th>
                        <th>Status</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Pastikan query ini jalan saat database sudah terhubung
                    if ($koneksi) {
                        $query = "SELECT tp.id_transaksi, a.nama_alat, m.nama_mahasiswa, tp.waktu_pinjam, tp.status_transaksi, tp.keterangan 
                                  FROM transaksi_peminjaman tp
                                  JOIN alat_praktikum a ON tp.id_rfid = a.id_rfid
                                  JOIN mahasiswa m ON tp.nim = m.nim
                                  ORDER BY tp.waktu_pinjam DESC";
                        
                        $result = mysqli_query($koneksi, $query);

                        if($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc($result)) {
                                $badge = ($row['status_transaksi'] == 'Berlangsung') ? "badge-proses" : "badge-selesai";
                                $keterangan = !empty($row['keterangan']) ? $row['keterangan'] : "-";
                                
                                echo "<tr>
                                        <td>#{$row['id_transaksi']}</td>
                                        <td>{$row['nama_alat']}</td>
                                        <td>{$row['nama_mahasiswa']}</td>
                                        <td>{$row['waktu_pinjam']}</td>
                                        <td><span class='{$badge}'>{$row['status_transaksi']}</span></td>
                                        <td>{$keterangan}</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data transaksi peminjaman.</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; color: red;'>Menunggu koneksi database aktif...</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>