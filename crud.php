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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Data Kit IoT</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Shared Theme CSS -->
    <link rel="stylesheet" href="dashboard_styles.css">

    <style>
        .table td{vertical-align:middle;}
        code{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;}
        .status-badge-tersedia{background: rgba(25,135,84,.14); color:#0f5132; border:1px solid rgba(25,135,84,.35); font-weight:900;}
        .status-badge-dipinjam{background: rgba(107,114,128,.12); color:#374151; border:1px solid rgba(107,114,128,.20); font-weight:900;}
    </style>
</head>
<body>
    <div class="app-shell">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">🧰</div>
                <div>
                    <h1>Admin Lab IoT</h1>
                    <small>RFID • Peminjaman Alat</small>
                </div>
            </div>

            <nav class="side-nav" aria-label="Sidebar Navigation">
                <a class="side-link" href="dashboard.php">
                    <span>📊</span><span>Dashboard Utama / Transaksi</span>
                </a>
                <a class="side-link" href="crud_asdos.php">
                    <span>🎓</span><span>Data Mahasiswa</span>
                </a>
                <a class="side-link active" href="crud.php">
                    <span>🧰</span><span>Data Alat Lab</span>
                </a>
            </nav>

            <div class="side-footer">
                <a href="logout.php" class="btn w-100" style="background: rgba(220,53,69,.95); color:#fff; border-radius:12px; font-weight:900;">
                    Logout
                </a>
            </div>
        </aside>

        <!-- Content -->
        <main class="content">
            <div class="topbar">
                <h2 class="page-title">Kelola Data Alat Lab / Box IoT</h2>
            </div>

            <div class="row g-3 align-items-start">
                <!-- Form -->
                <div class="col-12 col-lg-4">
                    <div class="card-soft p-3">
                        <h4 style="font-weight:900;">
                            <?= $edit_mode ? "Edit Data Box" : "Tambah Box Baru" ?>
                        </h4>

                        <?php
                        // $pesan dari backend masih bentuk <div class='alert success|error'>...</div>
                        // Render ulang sebagai alert Bootstrap 5 sesuai requirement UI.
                        if (!empty($pesan)) {
                            $pesanStr = (string)$pesan;
                            $text = strip_tags($pesanStr);
                            if (str_contains($pesanStr, "alert success")) {
                                echo "<div class=\"alert alert-success\" role=\"alert\">" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</div>";
                            } elseif (str_contains($pesanStr, "alert error")) {
                                echo "<div class=\"alert alert-danger\" role=\"alert\">" . htmlspecialchars($text, ENT_QUOTES, 'UTF-8') . "</div>";
                            } else {
                                echo $pesan;
                            }
                        }
                        ?>

                        <form method="POST" action="">
                            <?php if($edit_mode): ?>
                                <input type="hidden" name="is_edit" value="1">
                                <input type="hidden" name="old_id" value="<?= htmlspecialchars($data_edit['id_qr']) ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold">ID QR (Kode Stiker)</label>
                                <input type="text" class="form-control" name="id_qr" value="<?= htmlspecialchars($data_edit['id_qr']) ?>" required placeholder="Contoh: KIT-01">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Kit / Box</label>
                                <input type="text" class="form-control" name="nama_kit" value="<?= htmlspecialchars($data_edit['nama_kit']) ?>" required placeholder="Contoh: Box Sensor Suhu">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Status Saat Ini</label>
                                <select name="status" class="form-select">
                                    <option value="tersedia" <?= $data_edit['status'] == 'tersedia' ? 'selected' : '' ?>>Tersedia</option>
                                    <option value="dipinjam" <?= $data_edit['status'] == 'dipinjam' ? 'selected' : '' ?>>Sedang Dipinjam</option>
                                </select>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="simpan" class="btn <?= $edit_mode ? 'btn-warning' : 'btn-success' ?> flex-grow-1 fw-bold" style="border-radius:12px;">
                                    <?= $edit_mode ? "Update Data" : "Simpan Data" ?>
                                </button>

                                <?php if($edit_mode): ?>
                                    <a href="crud.php" class="btn btn-secondary" style="border-radius:12px; font-weight:900;">
                                        Batal
                                    </a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table -->
                <div class="col-12 col-lg-8">
                    <div class="card-soft p-3">
                        <h4 style="font-weight:900;" class="mb-3">Daftar Box IoT Terdaftar</h4>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID QR</th>
                                        <th>Nama Kit Box</th>
                                        <th>Status</th>
                                        <th style="width:170px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM iot_kits ORDER BY id_qr ASC");
                                    while($row = $stmt->fetch()) {
                                        $id = htmlspecialchars($row['id_qr']);
                                        $nama = htmlspecialchars($row['nama_kit']);
                                        $status = $row['status'];
                                        $badgeClass = ($status === 'tersedia') ? 'status-badge-tersedia' : 'status-badge-dipinjam';

                                        echo "<tr>
                                                <td><code>{$id}</code></td>
                                                <td>{$nama}</td>
                                                <td><span class=\"badge {$badgeClass}\">" . htmlspecialchars($status) . "</span></td>
                                                <td>
                                                    <div class='d-flex gap-2'>
                                                        <a href='?edit={$id}' class='btn btn-sm btn-outline-success fw-bold' style='border-radius:10px;'>Edit</a>
                                                        <a href='?hapus={$id}' class='btn btn-sm btn-outline-danger fw-bold' style='border-radius:10px;' onclick='return confirm(\"Yakin ingin menghapus box ini?\")'>Hapus</a>
                                                    </div>
                                                </td>
                                            </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

