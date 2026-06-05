<?php
session_start();
require_once 'koneksi.php'; // Menggunakan require_once agar aman

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
    <title>Dashboard Admin - Peminjaman IoT Kit</title>
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
        <h2>Monitoring Peminjaman IoT Kit Box</h2>
        <a href="logout.php" class="logout-btn">Logout</a>
    </div>

    <div class="container">
        <div class="card">
            <h3>Riwayat Transaksi Terkini</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID Transaksi</th>
                        <th>Nama Kit (Box)</th>
                        <th>Peminjam (Asdos)</th>
                        <th>Waktu Pinjam</th>
                        <th>Waktu Kembali</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        // Menggunakan JOIN untuk menggabungkan data dari tabel peminjaman, asdos, dan iot_kits
                        $query = "SELECT p.id, k.nama_kit, a.nama, p.waktu_pinjam, p.waktu_kembali, p.status_transaksi 
                                  FROM peminjaman p
                                  JOIN asdos a ON p.id_rfid = a.id_rfid
                                  JOIN iot_kits k ON p.id_qr = k.id_qr
                                  ORDER BY p.waktu_pinjam DESC";
                        
                        // Eksekusi query menggunakan objek $pdo dari koneksi.php
                        $stmt = $pdo->query($query);

                        if($stmt->rowCount() > 0) {
                            while($row = $stmt->fetch()) {
                                // Menyesuaikan warna badge berdasarkan status ENUM ('aktif', 'selesai')
                                $badge = ($row['status_transaksi'] == 'aktif') ? "badge-proses" : "badge-selesai";
                                $waktu_kembali = !empty($row['waktu_kembali']) ? $row['waktu_kembali'] : "-";
                                
                                echo "<tr>
                                        <td>#{$row['id']}</td>
                                        <td>{$row['nama_kit']}</td>
                                        <td>{$row['nama']}</td>
                                        <td>{$row['waktu_pinjam']}</td>
                                        <td>{$waktu_kembali}</td>
                                        <td><span class='{$badge}'>" . strtoupper($row['status_transaksi']) . "</span></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center;'>Belum ada data transaksi peminjaman.</td></tr>";
                        }
                    } catch (PDOException $e) {
                        // Menangkap jika ada error pada query atau database
                        echo "<tr><td colspan='6' style='text-align:center; color: red;'>Gagal memuat data: " . $e->getMessage() . "</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>