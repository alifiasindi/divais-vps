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
                            // Query utama untuk menampillkan data JOIN dari 3 tabel
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
                                    $badgeLabel = strtoupper($row['status_transaksi']);
                                    $waktuKembaliForJs = $row['waktu_kembali'] ? $row['waktu_kembali'] : '';

                                    echo "<tr data-status='" . htmlspecialchars($row['status_transaksi']) . "' data-waktu-kembali='" . htmlspecialchars($waktuKembaliForJs) . "'>";
                                    echo "<td>" . htmlspecialchars($row['nama']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nim']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['nama_kit']) . "</td>";
                                    echo "<td>" . htmlspecialchars($row['waktu_pinjam']) . "</td>";
                                    echo "<td>" . htmlspecialchars($waktuKembali) . "</td>";
                                    echo "<td><span class='badge-status badge {$badgeClass}'>" . $badgeLabel . "</span></td>";
                                    echo "</tr>";
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
                                            <select class="form-select" id="selectAlat"></select>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label fw-bold">Scan Barcode Alat</label>
                                            <input type="text" class="form-control" id="inputBarcode" placeholder="Tempel/ketik hasil scan..." autocomplete="off">
                                        </div>

                                        <div class="col-12">
                                            <div id="reader" class="mt-2" style="min-height:120px; border:1px dashed rgba(255,79,216,.35); border-radius:12px; padding:10px; overflow:hidden;"></div>
                                            <div class="text-muted mt-2" style="font-size:13px;">Arahkan kamera ke QR ID Alat.</div>
                                        </div>

                                        <div class="col-12">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div>
                                                    <div class="form-label fw-bold mb-1">Keranjang Alat</div>
                                                    <div class="text-muted" style="font-size:13px;">Total: <span id="keranjangCount">0</span> item</div>
                                                </div>
                                            </div>

                                            <div class="table-responsive mt-2">
                                                <table class="table table-sm align-middle mb-0" id="keranjangAlat">
                                                    <thead>
                                                        <tr>
                                                            <th>QR ID</th>
                                                            <th style="width:90px;">Aksi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="keranjangAlatBody">
                                                        <tr id="keranjangKosongRow">
                                                            <td colspan="2" class="text-center text-muted">Keranjang kosong</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="col-12 d-flex justify-content-end">
                                            <button type="button" class="btn btn-pinjam" id="btnPinjam">Pinjam</button>
                                        </div>
                                    </div>
                                    <div class="text-muted mt-2" style="font-size:13px;">
                                        * Scan QR alat untuk menambah ke keranjang. RFID mahasiswa via ESP32 memunculkan modal secara realtime.
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
  // ============================================================
  // REALTIME RFID + KERANJANG + SCAN QR (html5-qrcode)
  // ============================================================

  // Elemen DOM
  const modalEl = document.getElementById('modalTapRFID');
  const modal = new bootstrap.Modal(modalEl);

  const mfNamaEl = document.getElementById('mfNama');
  const mfNpmEl = document.getElementById('mfNpm');
  const mfIdEl = document.getElementById('mfId');
  const inputBarcodeEl = document.getElementById('inputBarcode');

  const keranjangAlatBody = document.getElementById('keranjangAlatBody');
  const keranjangCountEl = document.getElementById('keranjangCount');
  const btnPinjamEl = document.getElementById('btnPinjam');
  const selectAlat = document.getElementById('selectAlat');

  // State
  let currentIdRfid = null;
  let keranjang = []; 
  let modalOpened = false;
  let lastRfidId = null;

  modalEl.addEventListener('hidden.bs.modal', () => {
      modalOpened = false;
      lastRfidId = null;
      currentIdRfid = null;
      resetKeranjang();
  });

  function renderKeranjang(){
      if(!keranjangAlatBody) return;
      keranjangAlatBody.innerHTML = '';

      const total = keranjang.length;
      if(keranjangCountEl) keranjangCountEl.textContent = String(total);

      if(total === 0){
          keranjangAlatBody.innerHTML = `
            <tr id="keranjangKosongRow">
              <td colspan="2" class="text-center text-muted">Keranjang kosong</td>
            </tr>
          `;
          return;
      }

      keranjang.forEach((id_qr, idx) => {
          const tr = document.createElement('tr');
          const tdId = document.createElement('td');
          tdId.textContent = id_qr;

          const tdAksi = document.createElement('td');
          tdAksi.innerHTML = `
            <button type="button" class="btn btn-sm btn-outline-danger fw-bold" style="border-radius:10px;" data-index="${idx}">
              Hapus
            </button>
          `;

          tr.appendChild(tdId);
          tr.appendChild(tdAksi);
          keranjangAlatBody.appendChild(tr);
      });

      keranjangAlatBody.querySelectorAll('button[data-index]').forEach(btn => {
          btn.addEventListener('click', (e) => {
              const index = parseInt(e.currentTarget.getAttribute('data-index'), 10);
              if(!Number.isNaN(index)){
                  keranjang.splice(index, 1);
                  renderKeranjang();
              }
          });
      });
  }

  function resetKeranjang(){
      keranjang = [];
      renderKeranjang();
  }

  function fillModalMahasiswa(m){
      mfNamaEl.textContent = m.nama;
      mfNpmEl.textContent = `NPM: ${m.nim}`;
      mfIdEl.textContent = `ID: ${m.id_rfid}`;
      currentIdRfid = m.id_rfid;

      inputBarcodeEl.value = '';
      resetKeranjang();
  }

  async function pollCekRfid(){
      try{
          const resp = await fetch('cek_rfid.php', { cache: 'no-store', credentials: 'include' });
          if(!resp.ok) return;

          const data = await resp.json();

          if(data && data.status === true){
              if (modalOpened) return;
              if (data.id_rfid === lastRfidId) return;

              lastRfidId = data.id_rfid;
              fillModalMahasiswa({
                  id_rfid: data.id_rfid,
                  nama: data.nama,
                  nim: data.nim
              });

              modalOpened = true;
              modal.show();
          }
      }catch(err){
          console.warn(err);
      }
  }

  setInterval(pollCekRfid, 1000);
  pollCekRfid();

  // Load HTML5 QR Code Scanner
  function loadHtml5Qrcode(){
      return new Promise((resolve, reject) => {
          if(window.Html5Qrcode){
              resolve();
              return;
          }
          const s = document.createElement('script');
          s.src = 'https://unpkg.com/html5-qrcode/build/html5-qrcode.js';
          s.async = true;
          s.onload = () => resolve();
          s.onerror = reject;
          document.head.appendChild(s);
      });
  }

  function addToKeranjang(id_qr){
      id_qr = String(id_qr || '').trim();
      if(!id_qr) return;
      if(keranjang.includes(id_qr)) return;

      keranjang.push(id_qr);
      renderKeranjang();

      if(inputBarcodeEl) inputBarcodeEl.value = id_qr;
  }

  (async function initQrScanner(){
      const readerEl = document.getElementById('reader');
      if(!readerEl) return;

      try{
          await loadHtml5Qrcode();
          const qrScanner = new Html5Qrcode(readerEl.id);

          let lastText = '';
          let lastTime = 0;

          await qrScanner.start(
              { facingMode: 'environment' },
              { fps: 10, qrbox: { width: 250, height: 250 }, aspectRatio: 1 },
              (decodedText) => {
                  const text = String(decodedText || '').trim();
                  if(!text) return;

                  const now = Date.now();
                  if(text === lastText && (now - lastTime) < 1200) return;

                  lastText = text;
                  lastTime = now;
                  addToKeranjang(text);
              },
              (errorMessage) => { /* ignore */ }
          );
      } catch (e) {
          console.warn('initQrScanner error:', e);
      }
  })();

  // Submit Pinjam
  btnPinjamEl?.addEventListener('click', async () => {
      if (!currentIdRfid) {
          alert('Belum ada mahasiswa yang tap RFID. Silakan tap RFID mahasiswa terlebih dahulu.');
          return;
      }

      if (!keranjang.length) {
          alert('Keranjang masih kosong. Scan QR/Barcode alat terlebih dahulu atau pilih alat dari dropdown.');
          return;
      }

      try {
          const payload = {
              id_rfid: currentIdRfid,
              items: keranjang
          };

          const resp = await fetch('proses_pinjam.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json' },
              body: JSON.stringify(payload)
          });

          if (!resp.ok) throw new Error('Request gagal: ' + resp.status);

          const result = await resp.json().catch(() => null);

          if (result && result.status === true) {
              alert(result.message || 'Pinjam berhasil!');
              modalOpened = false;
              lastRfidId = null;
              currentIdRfid = null;
          } else {
              alert((result && result.message) ? result.message : 'Pinjam diproses (cek status di sistem).');
          }

          resetKeranjang();
          modal.hide();
          window.location.reload();
      } catch (err) {
          console.warn('btnPinjam error:', err);
          alert('Gagal memproses pinjam. Periksa format response backend atau koneksi.');
      }
  });

  // Setup Dropdown
  if (selectAlat) {
      const placeholderOpt = document.createElement('option');
      placeholderOpt.value = '';
      placeholderOpt.textContent = '— Pilih alat tersedia —';
      placeholderOpt.disabled = true;
      placeholderOpt.selected = true;

      if (selectAlat.options.length === 0) {
          selectAlat.appendChild(placeholderOpt);
      }

      if (!document.getElementById('btnTambahAlat')) {
          const btn = document.createElement('button');
          btn.type = 'button';
          btn.id = 'btnTambahAlat';
          btn.className = 'btn btn-outline-pink fw-bold mt-2';
          btn.style.borderRadius = '12px';
          btn.style.fontWeight = '900';
          btn.textContent = 'Tambah ke Keranjang';

          const selectParent = selectAlat.closest('.col-12, .mb-3, .form-group, .row');
          if (selectParent && selectParent.parentElement) {
              selectParent.appendChild(btn);
          }
      }

      const btnTambah = document.getElementById('btnTambahAlat');
      btnTambah?.addEventListener('click', () => {
          const id_qr = selectAlat.value;
          if (!id_qr) {
              alert('Pilih alat dari dropdown terlebih dahulu.');
              return;
          }
          addToKeranjang(id_qr);
      });
  }

  // Barcode Input
  inputBarcodeEl?.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
          e.preventDefault();
          const text = String(inputBarcodeEl.value || '').trim();
          addToKeranjang(text);
      }
  });

</script>
</body>
</html>