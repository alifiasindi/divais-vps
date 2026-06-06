<?php
session_start();
require_once 'koneksi.php';

// Proteksi halaman admin
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}

$pesan = "";

// --- LOGIK DELETE (HAPUS) ---
if (isset($_GET['hapus'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM iot_kits WHERE id_qr = ?");
        $stmt->execute([$_GET['hapus']]);
        header("Location: crud.php"); // Refresh halaman agar bersih
        exit;
    } catch (PDOException $e) {
        $pesan = "<div class='alert error'>Gagal menghapus: Data ini mungkin sedang digunakan di tabel peminjaman.</div>";
    }
}

// --- LOGIK CREATE & UPDATE (SIMPAN) ---
if (isset($_POST['simpan'])) {
    $id_qr = $_POST['id_qr'];
    $nama_kit = $_POST['nama_kit'];
    $status = $_POST['status'];

    try {
        if (isset($_POST['is_edit']) && $_POST['is_edit'] == '1') {
            // Mode Update
            $old_id = $_POST['old_id'];
            $stmt = $pdo->prepare("UPDATE iot_kits SET id_qr=?, nama_kit=?, status=? WHERE id_qr=?");
            $stmt->execute([$id_qr, $nama_kit, $status, $old_id]);
            $pesan = "<div class='alert success'>Data berhasil diperbarui!</div>";
        } else {
            // Mode Insert
            $stmt = $pdo->prepare("INSERT INTO iot_kits (id_qr, nama_kit, status) VALUES (?, ?, ?)");
            $stmt->execute([$id_qr, $nama_kit, $status]);
            $pesan = "<div class='alert success'>Data baru berhasil ditambahkan!</div>";
        }
    } catch (PDOException $e) {
        $pesan = "<div class='alert error'>Gagal menyimpan: Pastikan ID QR tidak duplikat.</div>";
    }
}

// --- LOGIK PERSIAPAN EDIT ---
$edit_mode = false;
$data_edit = ['id_qr' => '', 'nama_kit' => '', 'status' => 'tersedia'];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM iot_kits WHERE id_qr = ?");
    $stmt->execute([$_GET['edit']]);
    $data_edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Kit IoT</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background-color: #f4f4f9; }
        .header { background-color: #343a40; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .header h2 { margin: 0; font-size: 20px; }
        .nav-links a { color: white; text-decoration: none; margin-left: 15px; font-size: 14px; padding: 8px 12px; border-radius: 4px; background-color: #495057; }
        .nav-links a:hover { background-color: #6c757d; }
        .nav-links a.logout { background-color: #dc3545; }
        
        .container { padding: 20px; display: flex; gap: 20px; align-items: flex-start; flex-wrap: wrap; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); flex: 1; min-width: 300px; }
        
        /* Form Styles */
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 14px; }
        .form-group input, .form-group select { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; color: white; font-weight: bold; }
        .btn-primary { background-color: #007bff; }
        .btn-warning { background-color: #ffc107; color: black; }
        .btn-danger { background-color: #dc3545; text-decoration: none; font-size: 12px; padding: 5px 10px; }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; font-size: 14px; }
        th { background-color: #007bff; color: white; }
        .aksi-links a { text-decoration: none; margin-right: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin-bottom: 15px; font-size: 14px; }
        .alert.success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert.error { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Kelola Data Box IoT</h2>
        <div class="nav-links">
            <a href="dashboard.php">Kembali ke Dashboard</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card" style="flex: 0.4;">
            <h3><?= $edit_mode ? "Edit Data Box" : "Tambah Box Baru" ?></h3>
            <?= $pesan ?>
            <form method="POST" action="">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="is_edit" value="1">
                    <input type="hidden" name="old_id" value="<?= htmlspecialchars($data_edit['id_qr']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>ID QR (Kode Stiker)</label>
                    <input type="text" name="id_qr" value="<?= htmlspecialchars($data_edit['id_qr']) ?>" required placeholder="Contoh: KIT-01">
                </div>
                <div class="form-group">
                    <label>Nama Kit / Box</label>
                    <input type="text" name="nama_kit" value="<?= htmlspecialchars($data_edit['nama_kit']) ?>" required placeholder="Contoh: Box Sensor Suhu">
                </div>
                <div class="form-group">
                    <label>Status Saat Ini</label>
                    <select name="status">
                        <option value="tersedia" <?= $data_edit['status'] == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                        <option value="dipinjam" <?= $data_edit['status'] == 'dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                    </select>
                </div>
                
                <button type="submit" name="simpan" class="btn <?= $edit_mode ? 'btn-warning' : 'btn-primary' ?>">
                    <?= $edit_mode ? "Update Data" : "Simpan Data" ?>
                </button>
                <?php if($edit_mode): ?>
                    <a href="crud.php" class="btn" style="background: #6c757d; text-decoration: none;">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="flex: 1;">
            <h3>Daftar Box IoT Terdaftar</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID QR</th>
                        <th>Nama Kit Box</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM iot_kits ORDER BY id_qr ASC");
                    while($row = $stmt->fetch()) {
                        echo "<tr>
                                <td><strong>{$row['id_qr']}</strong></td>
                                <td>{$row['nama_kit']}</td>
                                <td>{$row['status']}</td>
                                <td class='aksi-links'>
                                    <a href='?edit={$row['id_qr']}' style='color: #28a745;'>✏️ Edit</a> | 
                                    <a href='?hapus={$row['id_qr']}' style='color: #dc3545;' onclick='return confirm(\"Yakin ingin menghapus box ini?\")'>🗑️ Hapus</a>
                                </td>
                              </tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>