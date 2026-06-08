<?php
session_start();
require_once 'koneksi.php';

// Proteksi halaman, tendang ke halaman login kalau belum login
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}

// Ringkasan data alat (agregat count) - tanpa mengubah logic transaksi utama
$totalAlat = 0;
$alatTersedia = 0;
$alatDipinjam = 0;
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) AS cnt FROM iot_kits GROUP BY status");
    while ($r = $stmt->fetch()) {
        if ($r['status'] === 'tersedia') $alatTersedia = (int)$r['cnt'];
        if ($r['status'] === 'dipinjam') $alatDipinjam = (int)$r['cnt'];
    }
    $totalAlat = $alatTersedia + $alatDipinjam;
} catch (Throwable $e) {
    // biarkan default 0
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Peminjaman IoT Kit</title>

    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="dashboard_styles.css">

    <style>
      /* Status badge untuk tabel (mengikuti requirement) */
      .badge-dipinjam{ background: rgba(32,201,151,.18); color:#0f5132; border:1px solid rgba(32,201,151,.35); }
      .badge-selesai{ background: rgba(107,114,128,.12); color:#374151; border:1px solid rgba(107,114,128,.18); }
      .badge-telat{ background: rgba(255,79,216,.14); color:#b0005c; border:1px solid rgba(255,79,216,.35); }
      .badge-telat-alt{ background: rgba(220,53,69,.12); color: #dc3545; border:1px solid rgba(220,53,69,.35); }

      .table td, .table th{ white-space:nowrap; }
    </style>
</head>
<body>

<div class="app-shell">
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">🧪</div>
            <div>
                <h1>Admin Lab IoT</h1>
                <small>RFID • Peminjaman Alat</small>
            </div>
        </div>

        <nav class="side-nav" aria-label="Sidebar Navigation">
            <a class="side-link active" href="dashboard.php">
                <span>📊</span><span>Dashboard Utama / Transaksi</span>
            </a>
            <a class="side-link" href="crud_asdos.php">
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
            <h2 class="page-title">Dashboard Admin</h2>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-muted fw-semibold">Mode Tampilan</span>
            </div>
        </div>

        <!-- Ringkasan Data Alat -->
        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <div class="card-soft kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Total Alat</div>
                            <div class="kpi-value">#<?= (int)$totalAlat ?></div>
                        </div>
                        <div class="kpi-icon">📦</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-soft kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Tersedia</div>
                            <div class="kpi-value"><?= (int)$alatTersedia ?></div>
                        </div>
                        <div class="kpi-icon">✅</div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card-soft kpi-card">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="kpi-label">Sedang Dipinjam</div>
                            <div class="kpi-value"><?= (int)$alatDipinjam ?></div>
                        </div>
                        <div class="kpi-icon">⏳</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi -->
        <section class="card-soft p-3">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                <h4 class="m-0 fw-black" style="font-weight:900;">Transaksi Peminjaman</h4>
                <button type="button" class="btn btn-sm btn-outline-pink" id="btnSimulasiTap">
                    Simulasi Tap RFID
                </button>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0" id="tableTransaksi">
                    <thead>
                        <tr>
                            <th>Nama Mahasiswa</th>
                            <th>NPM</th>
                            <th>Nama Alat</th>
                            <th>Waktu Pinjam</th>
                            <th>Waktu Kembali</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            // Tetap gunakan logic data transaksi utama yang sudah ada (JOIN transaksi/asdos/iot_kits)
                            $query = "SELECT p.id, k.nama_kit, a.nama, a.nim, p.waktu_pinjam, p.waktu_kembali, p.status_transaksi
                                      FROM peminjaman p
                                      JOIN asdos a ON p.id_rfid = a.id_rfid
                                      JOIN iot_kits k ON p.id_qr = k.id_qr
                                      ORDER BY p.waktu_pinjam DESC";
                            $stmt = $pdo->query($query);

                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch()) {
                                    $waktuKembali = !empty($row['waktu_kembali']) ? $row['waktu_kembali'] : "-";

                                    // Badge awal mengikuti status_transaksi
                                    $badgeClass = ($row['status_transaksi'] === 'aktif') ? 'badge-dipinjam' : 'badge-selesai';
                                    $badgeLabel = strtoupper($row['status_transaksi']);

                                    // Jika sudah kembali (selesai) tapi melewati 18:00 -> telat (akan diperjelas via JS juga)
                                    // Waktu format DB kemungkinan 'Y-m-d H:i:s'
                                    $waktuKembaliForJs = $row['waktu_kembali'] ? $row['waktu_kembali'] : '';

                                    echo "<tr ";
                                    echo "data-status='" . htmlspecialchars($row['status_transaksi']) . "' ";
                                    echo "data-waktu-kembali='" . htmlspecialchars($waktuKembaliForJs) . "' ";
                                    echo ">
                                            <td>{$row['nama']}</td>
                                            <td>{$row['nim']}</td>
                                            <td>{$row['nama_kit']}</td>
                                            <td>{$row['waktu_pinjam']}</td>
                                            <td>{$waktuKembali}</td>
                                            <td><span class='badge-status badge {$badgeClass}'>" . $badgeLabel . "</span></td>
                                        </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4'>Belum ada data transaksi peminjaman.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' class='text-center py-4 text-danger'>Gagal memuat data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Modal Pop-Up Tap RFID Mahasiswa -->
        <div class="modal fade" id="modalTapRFID" tabindex="-1" aria-labelledby="modalTapRFIDLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalTapRFIDLabel">Tap RFID Mahasiswa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card-soft p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="text-muted fw-semibold" style="font-size:13px;">Data Mahasiswa</div>
                                            <div class="fs-5 fw-black" id="mfNama">-</div>
                                            <div class="text-muted" id="mfNpm">-</div>
                                            <div class="text-muted" id="mfId" style="font-size:13px;">ID: -</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="badge badge-status badge-dipinjam" style="font-size:12px;">Tersedia untuk dipinjam</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="card-soft p-3">
                                    <div class="row g-3 align-items-end">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold">Pilih Alat Lab (Tersedia)</label>
                                            <select class="form-select" id="selectAlat">
                                                <!-- diisi placeholder JS -->
                                            </select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold">Scan Barcode Alat</label>
                                            <input type="text" class="form-control" id="inputBarcode" placeholder="Tempel/ketik hasil scan..." autocomplete="off">
                                        </div>

                                        <div class="col-12 d-flex justify-content-end">
                                            <button type="button" class="btn btn-pinjam" id="btnPinjam">
                                                Pinjam
                                            </button>
                                        </div>
                                    </div>
                                    <div class="text-muted mt-2" style="font-size:13px;">
                                        * Ini hanya simulasi tampilan. Tombol Pinjam tidak mengubah backend.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </main>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // --------- Placeholder Data untuk tampilan (simulasi) ---------
  const demoMahasiswa = [
    { nama: 'Eki Sulastri', npm: '5231011022', id: '1234567A' },
    { nama: 'Alifia Sindi Ananda', npm: '5231011035', id: '89ABCDEF' },
    { nama: 'Ummi Lathifa Nabila', npm: '5231011057', id: 'A1B2C3D4' }
  ];

  const demoAlatTersedia = [
    { id_qr: 'KIT-AIR-01', nama: 'IoT Kit Box - Monitoring Kualitas Udara' },
    { id_qr: 'KIT-SCALE-01', nama: 'IoT Kit Box - Timbangan Digital & Berat' },
    { id_qr: 'KIT-SMART-01', nama: 'IoT Kit Box - Smart Home & Interface Systems' }
  ];

  // Populate select alat saat load
  const selectAlat = document.getElementById('selectAlat');
  if (selectAlat) {
    demoAlatTersedia.forEach(a => {
      const opt = document.createElement('option');
      opt.value = a.id_qr;
      opt.textContent = `${a.nama}`;
      selectAlat.appendChild(opt);
    });
  }

  // Modal logic
  const modalEl = document.getElementById('modalTapRFID');
  const modal = new bootstrap.Modal(modalEl);

  function fillModalMahasiswa(m){
    document.getElementById('mfNama').textContent = m.nama;
    document.getElementById('mfNpm').textContent = `NPM: ${m.npm}`;
    document.getElementById('mfId').textContent = `ID: ${m.id}`;
    document.getElementById('inputBarcode').value = '';
  }

  // Simulasi tap RFID (tombol)
  const btnSim = document.getElementById('btnSimulasiTap');
  if (btnSim){
    btnSim.addEventListener('click', () => {
      const pick = demoMahasiswa[Math.floor(Math.random()*demoMahasiswa.length)];
      fillModalMahasiswa(pick);
      modal.show();
    });
  }

  // Placeholder tombol Pinjam
  const btnPinjam = document.getElementById('btnPinjam');
  if(btnPinjam){
    btnPinjam.addEventListener('click', () => {
      // Tidak memanggil backend; hanya tampilkan toast sederhana
      const barcode = document.getElementById('inputBarcode').value.trim();
      const alatId = selectAlat.value;
      const pickText = `Mahasiswa: ${document.getElementById('mfNama').textContent} | Alat: ${alatId}${barcode ? ' | Barcode: ' + barcode : ''}`;
      alert('Simulasi Pinjam:\n' + pickText);
    });
  }

  // --------- Badge Telat Dikembalikan (> 18:00) ---------
  // Requirement: jika melewati jam 18:00 -> badge merah/pink mencolok.
  (function applyTelatBadge(){
    const rows = document.querySelectorAll('#tableTransaksi tbody tr');
    rows.forEach(tr => {
      const status = tr.getAttribute('data-status') || '';
      const waktuKembali = tr.getAttribute('data-waktu-kembali') || '';
      const badge = tr.querySelector('.badge-status');
      if(!badge) return;

      // Jika masih aktif, biarkan badge dipinjam
      if(status.toLowerCase() === 'aktif') return;

      if(waktuKembali){
        // Ambil jam dari string 'YYYY-MM-DD HH:mm:ss'
        const parts = waktuKembali.split(' ');
        const time = parts[1] || '';
        const hh = parseInt(time.split(':')[0] || '0', 10);
        if(hh >= 18){
          badge.classList.remove('badge-selesai');
          badge.classList.add('badge-telat');
          badge.textContent = 'TELAT DIKEMBALIKAN';
        } else {
          badge.classList.remove('badge-telat');
          badge.classList.add('badge-selesai');
          badge.textContent = 'SELESAI / DIKEMBALIKAN';
        }
      }
    });
  })();
</script>

</body>
</html>

