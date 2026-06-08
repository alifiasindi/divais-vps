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
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kelola Data Asdos</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

   
    <link rel="stylesheet" href="dashboard_styles.css">

    <style>
        .table td{vertical-align:middle;}
        code{font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;}
    </style>
</head>
<body>
    <div class="app-shell">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="brand">
                <div class="logo">🎓</div>
                <div>
                    <h1>Admin Lab IoT</h1>
                    <small>RFID • Peminjaman Alat</small>
                </div>
            </div>

            <nav class="side-nav" aria-label="Sidebar Navigation">
                <a class="side-link" href="dashboard.php">
                    <span>📊</span><span>Dashboard Utama / Transaksi</span>
                </a>
                <a class="side-link active" href="crud_asdos.php">
                    <span>🎓</span><span>Data Mahasiswa</span>
                </a>
                <a class="side-link" href="crud.php">
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
                <h2 class="page-title">Kelola Data Mahasiswa / Asdos</h2>
            </div>

            <div class="row g-3 align-items-start">
                <!-- Form -->
                <div class="col-12 col-lg-4">
                    <div class="card-soft p-3">
                        <h4 style="font-weight:900;"><?= $edit_mode ? "Edit Data Asdos" : "Tambah Asdos Baru" ?></h4>

                        <?php
                        // $pesan dari backend masih bentuk <div class='alert success|error'>...</div>
                        // Kita render ulang sebagai alert Bootstrap 5 sesuai requirement UI.
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
                                <input type="hidden" name="old_id" value="<?= htmlspecialchars($data_edit['id_rfid']) ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label fw-bold">ID RFID (UID Kartu)</label>
                                <input type="text" class="form-control" name="id_rfid" value="<?= htmlspecialchars($data_edit['id_rfid']) ?>" required placeholder="Contoh: 1234567A" autocomplete="off">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Nama Lengkap</label>
                                <input type="text" class="form-control" name="nama" value="<?= htmlspecialchars($data_edit['nama']) ?>" required placeholder="Contoh: Nama Asdos">
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">NIM</label>
                                <input type="text" class="form-control" name="nim" value="<?= htmlspecialchars($data_edit['nim']) ?>" required placeholder="Contoh: 1103220001">
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" name="simpan" class="btn <?= $edit_mode ? 'btn-warning' : 'btn-success' ?> flex-grow-1 fw-bold" style="border-radius:12px;">
                                    <?= $edit_mode ? "Update Data" : "Simpan Data" ?>
                                </button>

                                <?php if($edit_mode): ?>
                                    <a href="crud_asdos.php" class="btn" style="background:#6c757d; color:#fff; border-radius:12px; font-weight:900; text-decoration:none;">
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
                        <h4 style="font-weight:900;" class="mb-3">Daftar Asdos Terdaftar</h4>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>ID RFID</th>
                                        <th>Nama Asdos</th>
                                        <th>NIM</th>
                                        <th style="width:170px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->query("SELECT * FROM asdos ORDER BY nama ASC");
                                    while($row = $stmt->fetch()) {
                                        $id = htmlspecialchars($row['id_rfid']);
                                        echo "<tr>
                                                <td><code>{$id}</code></td>
                                                <td>{$row['nama']}</td>
                                                <td>{$row['nim']}</td>
                                                <td>
                                                    <div class='d-flex gap-2'>
                                                        <a href='?edit={$id}' class='btn btn-sm btn-success fw-bold' style='border-radius:10px;'>Edit</a>
                                                        <a href='?hapus={$id}' class='btn btn-sm btn-outline-danger fw-bold' style='border-radius:10px;' onclick='return confirm(\"Yakin ingin menghapus data asdos ini?\")'>Hapus</a>
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

