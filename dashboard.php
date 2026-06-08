<?php
session_start();
require_once 'koneksi.php';

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] !== true) {
    header("Location: index.php");
    exit;
}

$totalAlat = 0; $alatTersedia = 0; $alatDipinjam = 0;
try {
    $stmt = $pdo->query("SELECT status, COUNT(*) AS cnt FROM iot_kits GROUP BY status");
    while ($r = $stmt->fetch()) {
        if ($r['status'] === 'tersedia') $alatTersedia = (int)$r['cnt'];
        if ($r['status'] === 'dipinjam') $alatDipinjam = (int)$r['cnt'];
    }
    $totalAlat = $alatTersedia + $alatDipinjam;
} catch (Throwable $e) {}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard Admin - Peminjaman IoT Kit</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="dashboard_styles.css">
    <style>
      .badge-dipinjam{ background: rgba(32,201,151,.18); color:#0f5132; border:1px solid rgba(32,201,151,.35); }
      .badge-selesai{ background: rgba(107,114,128,.12); color:#374151; border:1px solid rgba(107,114,128,.18); }
      .table td, .table th{ white-space:nowrap; }
    </style>
</head>
<body>

<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">🧪</div>
            <div>
                <h1>Admin Lab IoT</h1>
                <small>RFID • Peminjaman Alat</small>
            </div>
        </div>
        <nav class="side-nav" aria-label="Sidebar Navigation">
            <a class="side-link active" href="dashboard.php"><span>📊</span><span>Dashboard Utama</span></a>
            <a class="side-link" href="crud_asdos.php"><span>🎓</span><span>Data Mahasiswa</span></a>
            <a class="side-link" href="crud.php"><span>🧰</span><span>Data Alat Lab</span></a>
        </nav>
        <div class="side-footer">
            <a href="logout.php" class="btn w-100" style="background: rgba(220,53,69,.95); color:#fff; border-radius:12px; font-weight:900;">Logout</a>
        </div>
    </aside>

    <main class="content">
        <div class="topbar">
            <h2 class="page-title">Dashboard Admin</h2>
            <div class="d-flex gap-2 align-items-center">
                <span class="text-muted fw-semibold">Mode Tampilan</span>
            </div>
        </div>

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

        <section class="card-soft p-3">
            <div class="d-flex align-items-center justify-content-between gap-3 mb-2">
                <h4 class="m-0 fw-black" style="font-weight:900;">Transaksi Peminjaman</h4>
                <button type="button" class="btn btn-sm btn-outline-pink" id="btnSimulasiTap">Simulasi Tap RFID</button>
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
                            $query = "SELECT p.id, k.nama_kit, a.nama, a.nim, p.waktu_pinjam, p.waktu_kembali, p.status_transaksi
                                      FROM peminjaman p
                                      JOIN asdos a ON p.id_rfid = a.id_rfid
                                      JOIN iot_kits k ON p.id_qr = k.id_qr
                                      ORDER BY p.waktu_pinjam DESC";
                            $stmt = $pdo->query($query);
                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch()) {
                                    $waktuKembali = !empty($row['waktu_kembali']) ? $row['waktu_kembali'] : "-";
                                    $badgeClass = ($row['status_transaksi'] === 'aktif') ? 'badge-dipinjam' : 'badge-selesai';
                                    echo "<tr>";
                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_kit']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['waktu_pinjam']) . "</td>";
                                    echo "<td>" . htmlspecialchars($waktuKembali) . "</td>";
                                    echo "<td><span class='badge-status badge {$badgeClass}'>" . strtoupper($row['status_transaksi']) . "</span></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='6' class='text-center py-4'>Belum ada data transaksi peminjaman.</td></tr>";
                            }
                        } catch (PDOException $e) {
                            echo "<tr><td colspan='6' class='text-center text-danger'>Gagal memuat data</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </section>

        <div class="modal fade" id="modalTapRFID" data-bs-backdrop="static" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content" style="border-radius:16px;">
                    <div class="modal-header">
                        <h5 class="modal-title">Proses Alat Lab</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="card-soft p-3">
                                    <div class="d-flex align-items-start justify-content-between gap-3">
                                        <div>
                                            <div class="text-muted fw-semibold" style="font-size:13px;">Data Mahasiswa Terdeteksi</div>
                                            <div class="fs-5 fw-black" id="mfNama">-</div>
                                            <div class="text-muted" id="mfNpm">-</div>
                                            <div class="text-muted" id="mfId" style="font-size:13px;">ID: -</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 text-center">
                                <div class="card-soft p-3">
                                    <h6 class="fw-bold text-primary mb-3">📸 SCAN QR ALAT SEKARANG</h6>
                                    
                                    <div id="reader" style="width: 100%; max-width: 450px; margin: 0 auto; border: 3px dashed #ff4fd8; border-radius: 12px; overflow: hidden; min-height: 250px;"></div>
                                    <p class="text-muted mt-2 small">* Scan alat yang Tersedia untuk <b>Meminjam</b>.<br>* Scan alat yang Dipinjam untuk <b>Mengembalikan</b>.</p>

                                    <hr>
                                    <label class="form-label fw-bold mt-2">Atau Ketik ID QR Manual:</label>
                                    <input type="text" class="form-control text-center mx-auto" id="inputBarcode" placeholder="Misal: KIT-AIR-01" style="max-width: 300px; font-weight: bold;" autocomplete="off">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/html5-qrcode/build/html5-qrcode.js"></script>

<script>
  const modalEl = document.getElementById('modalTapRFID');
  const modal = new bootstrap.Modal(modalEl);
  const inputBarcodeEl = document.getElementById('inputBarcode');
  
  let currentIdRfid = null;
  let lastRfidId = null;
  let isProcessing = false;
  let html5QrCode;

  // Render Data Mahasiswa ke Modal
  function fillModalMahasiswa(m){
      document.getElementById('mfNama').textContent = m.nama;
      document.getElementById('mfNpm').textContent = `NPM: ${m.nim}`;
      document.getElementById('mfId').textContent = `ID: ${m.id_rfid}`;
      currentIdRfid = m.id_rfid;
      inputBarcodeEl.value = '';
  }

  // API Cek RFID Realtime dari ESP32
  async function pollCekRfid(){
      try{
          const resp = await fetch('cek_rfid.php', { cache: 'no-store', credentials: 'include' });
          if(!resp.ok) return;
          const data = await resp.json();

          if(data && data.status === true){
              if (modalEl.classList.contains('show')) return;
              if (data.id_rfid === lastRfidId) return;

              lastRfidId = data.id_rfid;
              fillModalMahasiswa({
                  id_rfid: data.id_rfid,
                  nama: data.nama,
                  nim: data.nim
              });
              modal.show();
          }
      }catch(err){ console.warn(err); }
  }
  setInterval(pollCekRfid, 1000);

  // LOGIKA PEMROSESAN OTOMATIS (PINJAM / KEMBALI)
  async function prosesScanAlat(id_qr) {
      if (!currentIdRfid) {
          alert('ID Mahasiswa tidak ditemukan. Silakan tap kartu lagi.'); return;
      }
      if (!id_qr || isProcessing) return; 

      isProcessing = true; // Kunci biar gak ngirim berkali-kali

      try {
          const payload = { id_rfid: currentIdRfid, id_qr: id_qr };
          
          const resp = await fetch('proses_scan.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          });
          
          const result = await resp.json();

          if (result.status) {
              alert(result.message); // Notifikasi berhasil
              modal.hide();
              window.location.reload(); // Refresh data tabel
          } else {
              alert(result.message); // Notifikasi error
          }
      } catch (err) {
          console.error(err);
          alert('Terjadi kesalahan saat menghubungi server.');
      } finally {
          setTimeout(() => { isProcessing = false; }, 2000); // Buka kunci setelah 2 detik
      }
  }

  // --- KONTROL KAMERA ---
  modalEl.addEventListener('shown.bs.modal', () => {
      // Nyalakan Kamera saat Modal Terbuka
      html5QrCode = new Html5Qrcode("reader");
      html5QrCode.start(
          { facingMode: "environment" },
          { fps: 10, qrbox: { width: 250, height: 250 } },
          (decodedText) => {
              // Jika kamera berhasil membaca kode
              prosesScanAlat(decodedText.trim());
          },
          (errorMessage) => { /* Ignore read errors */ }
      ).catch(err => console.log('Error Kamera:', err));
  });

  modalEl.addEventListener('hidden.bs.modal', () => {
      // Matikan Kamera & Bersihkan State saat Modal Tertutup
      if (html5QrCode) {
          html5QrCode.stop().catch(err => console.error(err));
      }
      currentIdRfid = null;
      lastRfidId = null;
      isProcessing = false;
  });

  // --- INPUT MANUAL JIKA KAMERA RUSAK ---
  inputBarcodeEl.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
          e.preventDefault();
          const text = inputBarcodeEl.value.trim();
          prosesScanAlat(text);
          inputBarcodeEl.value = '';
      }
  });

</script>
</body>
</html>