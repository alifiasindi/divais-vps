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
        $stmt = $pdo->prepare("DELETE FROM asdos WHERE id_rfid = ?");
        $stmt->execute([$_GET['hapus']]);
        header("Location: crud_asdos.php"); // Refresh halaman agar bersih
        exit;
    } catch (PDOException $e) {
        $pesan = "<div class='alert error'>Gagal menghapus: Data Asdos ini tidak bisa dihapus karena memiliki riwayat transaksi di tabel peminjaman.</div>";
    }
}

// --- LOGIK CREATE & UPDATE (SIMPAN) ---
if (isset($_POST['simpan'])) {
    $id_rfid = $_POST['id_rfid'];
    $nama = $_POST['nama'];
    $nim = $_POST['nim'];

    try {
        if (isset($_POST['is_edit']) && $_POST['is_edit'] == '1') {
            // Mode Update
            $old_id = $_POST['old_id'];
            $stmt = $pdo->prepare("UPDATE asdos SET id_rfid=?, nama=?, nim=? WHERE id_rfid=?");
            $stmt->execute([$id_rfid, $nama, $nim, $old_id]);
            $pesan = "<div class='alert success'>Data Asdos berhasil diperbarui!</div>";
        } else {
            // Mode Insert
            $stmt = $pdo->prepare("INSERT INTO asdos (id_rfid, nama, nim) VALUES (?, ?, ?)");
            $stmt->execute([$id_rfid, $nama, $nim]);
            $pesan = "<div class='alert success'>Data Asdos baru berhasil ditambahkan!</div>";
        }
    } catch (PDOException $e) {
        $pesan = "<div class='alert error'>Gagal menyimpan: Pastikan ID RFID atau NIM tidak duplikat.</div>";
    }
}

// --- LOGIK PERSIAPAN EDIT ---
$edit_mode = false;
$data_edit = ['id_rfid' => '', 'nama' => '', 'nim' => ''];
if (isset($_GET['edit'])) {
    $edit_mode = true;
    $stmt = $pdo->prepare("SELECT * FROM asdos WHERE id_rfid = ?");
    $stmt->execute([$_GET['edit']]);
    $data_edit = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Data Asdos</title>
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
        .form-group input { width: 100%; padding: 8px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
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
        <h2>Kelola Data Keanggotaan Asdos</h2>
        <div class="nav-links">
            <a href="dashboard.php">Kembali ke Dashboard</a>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="card" style="flex: 0.4;">
            <h3><?= $edit_mode ? "Edit Data Asdos" : "Tambah Asdos Baru" ?></h3>
            <?= $pesan ?>
            <form method="POST" action="">
                <?php if($edit_mode): ?>
                    <input type="hidden" name="is_edit" value="1">
                    <input type="hidden" name="old_id" value="<?= htmlspecialchars($data_edit['id_rfid']) ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>ID RFID (UID Kartu)</label>
                    <input type="text" name="id_rfid" value="<?= htmlspecialchars($data_edit['id_rfid']) ?>" required placeholder="Contoh: 1234567A" autocomplete="off">
                </div>
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" value="<?= htmlspecialchars($data_edit['nama']) ?>" required placeholder="Contoh: Nama Asdos">
                </div>
                <div class="form-group">
                    <label>NIM</label>
                    <input type="text" name="nim" value="<?= htmlspecialchars($data_edit['nim']) ?>" required placeholder="Contoh: 1103220001">
                </div>
                
                <button type="submit" name="simpan" class="btn <?= $edit_mode ? 'btn-warning' : 'btn-primary' ?>">
                    <?= $edit_mode ? "Update Data" : "Simpan Data" ?>
                </button>
                <?php if($edit_mode): ?>
                    <a href="crud_asdos.php" class="btn" style="background: #6c757d; text-decoration: none;">Batal</a>
                <?php endif; ?>
            </form>
        </div>

        <div class="card" style="flex: 1;">
            <h3>Daftar Asdos Terdaftar</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID RFID</th>
                        <th>Nama Asdos</th>
                        <th>NIM</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $stmt = $pdo->query("SELECT * FROM asdos ORDER BY nama ASC");
                    while($row = $stmt->fetch()) {
                        echo "<tr>
                                <td><code>{$row['id_rfid']}</code></td>
                                <td>{$row['nama']}</td>
                                <td>{$row['nim']}</td>
                                <td class='aksi-links'>
                                    <a href='?edit={$row['id_rfid']}' style='color: #28a745;'>✏️ Edit</a> | 
                                    <a href='?hapus={$row['id_rfid']}' style='color: #dc3545;' onclick='return confirm(\"Yakin ingin menghapus data asdos ini?\")'>🗑️ Hapus</a>
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