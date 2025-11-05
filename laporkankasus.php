<?php
$required_role = 'mahasiswa';
include '../includes/db_connect.php';
include '../includes/check_auth.php';

// Ambil id_pengguna dan Nama Pengguna
$stmt_user = $pdo->prepare("SELECT id_pengguna, nama_pengguna FROM Pengguna WHERE id_akun = ?");
$stmt_user->execute([$_SESSION['id_akun']]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$id_pengguna = $user['id_pengguna'] ?? 0;
$nama_user = $user['nama_pengguna'] ?? 'Pengguna';

// Ambil Riwayat Laporan untuk tabel
$stmt_history = $pdo->prepare("SELECT * FROM Pelaporan_Kasus WHERE id_pengguna = ? ORDER BY waktu_kejadian DESC");
$stmt_history->execute([$id_pengguna]);
$riwayat_laporan = $stmt_history->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
   
  <meta charset="UTF-8">
   
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Mahasiswa - Laporkan Kasus</title>

     
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
   
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
   
     
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
   
  <link rel="stylesheet" href="../assets/css/style.css">
    <style>
    /* Styling card — konsisten dengan desain login */
    .card-form {
      background-color: #ffffff;
      border: 1px solid #e0e0e0;
      border-radius: var(--border-radius-custom, 0.5rem);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    /* Warna teks */
    .text-primary-blue {
      color: var(--primary-blue) !important;
    }

    .text-secondary-light {
      color: var(--text-color-description, #6c757d);
    }

    /* Tombol utama — konsisten dengan .btn-login-custom */
    .btn-custom-primary {
      background-color: var(--primary-blue);
      border-color: var(--primary-blue);
      border-radius: var(--border-radius-custom);
      padding: 12px 30px;
      font-weight: 600;
      color: #fff;
      transition: background-color 0.3s, border-color 0.3s;
    }

    .btn-custom-primary:hover {
      background-color: #4A89DC;
      border-color: #4A89DC;
    }
  </style>
</head>

<body style="background-color: #f2f2f2;">

  <nav class="navbar navbar-expand-lg bg-white fixed-top shadow-sm border-bottom">
    <div class="container-fluid px-4">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="konseling.php">Konseling Online</a></li>
          <li class="nav-item"><a class="nav-link" href="dailyreflection.php">Daily Reflection</a></li>
          <li class="nav-item"><a class="nav-link" href="assessmenttest.php">Assessment Test</a></li>
          <li class="nav-item"><a class="nav-link" href="kontenedukasi.php">Konten Edukasi</a></li>
          <li class="nav-item"><a class="nav-link active" href="laporkankasus.php">Laporkan Kasus</a></li>
          <li class="nav-item"><a class="nav-link" href="moodtracker.php">Mood Tracker</a></li>
          <li class="nav-item"><a class="nav-link" href="wellnessmission.php">Wellness Mission</a></li>
        </ul>
        <ul class="navbar-nav align-items-center">
          <a class="nav-link text-danger fw-semibold d-flex align-items-center" href="../logout.php">
            <i class="fas fa-sign-out-alt me-1"></i> Logout
          </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <main class="container py-5" style="margin-top: 60px;">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3 mb-5">
      <div class_laporan="flex flex-col">
        <h1 class="text-primary-blue h3 fw-bold">Pelaporan Kasus</h1>
        <p class="text-secondary-light mb-0" style="max-width: 600px;">
          Laporkan insiden yang Anda alami atau saksikan dan pantau status perkembangannya di sini. Privasi Anda adalah prioritas kami.
        </p>
      </div>
      <i class="fas fa-shield-alt fa-3x text-primary-blue opacity-50 d-none d-md-block"></i>
    </div>

    <div id="alert-container" class_laporan="mb-4"></div>

    <div class="card card-form mb-5">
      <div class="card-body p-4 p-md-5">
        <h2 class="text-primary-blue h5 fw-bold mb-4">Formulir Pelaporan Kasus</h2>

        <form method="POST" action="process_laporan.php" name="reportForm" onsubmit="return validateReportForm()">
          <input type="hidden" name="id_pengguna" value="<?php echo $id_pengguna; ?>">

          <div class="row g-4">
            <div class="col-md-6">
              <div class="mb-3">
                <label for="jenis_kasus" class="form-label fw-semibold">Jenis Kasus</label>
                <select class="form-select" id="jenis_kasus" name="jenis_kasus" required>
                  <option value="" selected disabled>Pilih jenis kasus</option>
                  <option value="bullying">Perundungan (Bullying)</option>
                  <option value="pelecehan">Pelecehan</option>
                  <option value="kekerasan verbal">Kekerasan Verbal</option>
                  <option value="kekerasan fisik">Kekerasan Fisik</option>
                  <option value="stres berat">Stres Akademik / Berat</option>
                  <option value="diskriminasi">Diskriminasi</option>
                  <option value="lainnya">Lainnya</option>
                </select>
              </div>

              <div class="mb-3">
                <label for="lokasi" class="form-label fw-semibold">Lokasi Kejadian</label>
                <input type="text" class="form-control" id="lokasi" name="lokasi" placeholder="Contoh: Gedung FILKOM, Lantai 5" required>
              </div>

              <div class="mb-3">
                <label for="waktu_kejadian" class="form-label fw-semibold">Waktu Kejadian</label>
                <input type="datetime-local" class="form-control" id="waktu_kejadian" name="waktu_kejadian" required>
              </div>
            </div>

            <div class="col-md-6">
              <div class="mb-3">
                <label for="deskripsi_kasus" class="form-label fw-semibold">Deskripsi Kejadian</label>
                <textarea class="form-control" id="deskripsi_kasus" name="deskripsi_kasus" rows="8" placeholder="Ceritakan kejadian secara detail..." required></textarea>
              </div>
            </div>
          </div>

          <hr class="my-4">

          <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="anonim" name="anonim" value="1">
              <label class="form-check-label text-secondary-light" for="anonim">
                Laporkan secara anonim (Nama Anda tidak akan ditampilkan)
              </label>
            </div>

            <button type="submit" class="btn btn-custom-primary px-5 py-2">Kirim Laporan</button>
          </div>

        </form>
      </div>
    </div>
    <div class="card card-form">
      <div class="card-header bg-white p-4">
        <h2 class="text-primary-blue h5 fw-bold mb-0">Riwayat dan Status Laporan Saya</h2>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0" style="font-size: 0.9rem;">
            <thead class="table-light">
              <tr>
                <th class="px-4 py-3">No.</th>
                <th class="px-4 py-3">Jenis Kasus</th>
                <th class="px-4 py-3">Lokasi</th>
                <th class="px-4 py-3">Waktu Kejadian</th>
                <th class="px-4 py-3">Mode</th>
                <th class="px-4 py-3">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($riwayat_laporan)): ?>
                <tr>
                  <td colspan="6" class="text-center text-secondary-light p-4">Anda belum pernah membuat laporan.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($riwayat_laporan as $index => $laporan): ?>
                  <tr>
                    <td class="px-4 py-3"><?php echo $index + 1; ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars(ucfirst($laporan['jenis_kasus'])); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars($laporan['lokasi']); ?></td>
                    <td class="px-4 py-3"><?php echo htmlspecialchars(date('d M Y, H:i', strtotime($laporan['waktu_kejadian']))); ?></td>
                    <td class="px-4 py-3">
                      <?php if ($laporan['anonim']): ?>
                        <span class="badge bg-secondary">Anonim</span>
                      <?php else: ?>
                        <span class="badge bg-light text-dark border">Normal</span>
                      <?php endif; ?>
                    </td>
                    <td class="px-4 py-3">
                      <?php
                      // Logika Badge Status (Sesuai contoh Tailwind & Modul DD_41)
                      $status = $laporan['status_laporan'];
                      $badge_class = 'bg-primary'; // Default 'dikirim'
                      if ($status == 'selesai') {
                        $badge_class = 'bg-success';
                      } elseif ($status == 'ditinjau' || $status == 'ditindaklanjuti') {
                        $badge_class = 'bg-warning text-dark';
                      } elseif ($status == 'ditolak') {
                        $badge_class = 'bg-danger';
                      }
                      ?>
                      <span class="badge <?php echo $badge_class; ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="../assets/js/script.js"></script>

  <script>
    // --- 1. Validasi Form (Sesuai permintaan: "alert JS bawaan script.js") ---
    function validateReportForm() {
      const form = document.forms['reportForm'];
      const jenis = form['jenis_kasus'].value;
      const lokasi = form['lokasi'].value.trim();
      const waktu = form['waktu_kejadian'].value;
      const deskripsi = form['deskripsi_kasus'].value.trim();

      if (jenis === "") {
        alert('Peringatan: Jenis kasus wajib dipilih.');
        return false;
      }
      if (lokasi === "") {
        alert('Peringatan: Lokasi kejadian wajib diisi.');
        return false;
      }
      if (waktu === "") {
        alert('Peringatan: Waktu kejadian wajib diisi.');
        return false;
      }
      // Sesuai PSPEC Modul 2 (STN) - Deskripsi wajib diisi
      if (deskripsi === "") {
        alert('Peringatan: Deskripsi kejadian wajib diisi.');
        return false;
      }

      return true; // Lanjutkan submit jika lolos
    }

    // --- 2. Penanganan Notifikasi (Alert) dari URL ---
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');
      const error = urlParams.get('error');
      const alertContainer = document.getElementById('alert-container');

      let alertHtml = '';

      if (status === 'success') {
        alertHtml = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                           <strong>Berhasil!</strong> Laporan Anda telah berhasil dikirim dan akan segera ditinjau.
                           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
      } else if (error === 'validation') {
        alertHtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                           <strong>Gagal:</strong> Pastikan semua kolom (Jenis, Lokasi, Waktu, Deskripsi) terisi dengan benar.
                           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
      } else if (error === 'dberror') {
        alertHtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                           <strong>Gagal:</strong> Terjadi kesalahan pada server. Coba lagi nanti.
                           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
      }


      if (alertContainer && alertHtml) {
        alertContainer.innerHTML = alertHtml;
        // Membersihkan URL
        if (history.replaceState) {
          history.replaceState(null, '', window.location.pathname);
        }
      }
    });
  </script>

</body>

</html>
