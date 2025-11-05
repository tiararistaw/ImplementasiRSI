<?php
$required_role = 'mahasiswa';
include '../includes/db_connect.php'; // Koneksi PDO
include '../includes/check_auth.php'; // Cek sesi dan role

// Ambil id_pengguna dan Nama Pengguna dari session
$stmt_user = $pdo->prepare("SELECT id_pengguna, nama_pengguna FROM Pengguna WHERE id_akun = ?");
$stmt_user->execute([$_SESSION['id_akun']]);
$user = $stmt_user->fetch(PDO::FETCH_ASSOC);

$id_pengguna = $user['id_pengguna'] ?? 0;
$nama_user = $user['nama_pengguna'] ?? 'Pengguna';

// --- Ambil Data untuk Tampilan ---

// 1. Ambil riwayat emosi untuk TABEL (Urut terbaru)
$stmt_history = $pdo->prepare("SELECT tanggal_emosi, jenis_emosi, catatan_emosi 
                             FROM Input_Emosi 
                             WHERE id_pengguna = ? 
                             ORDER BY tanggal_emosi DESC");
$stmt_history->execute([$id_pengguna]);
$riwayat_emosi = $stmt_history->fetchAll(PDO::FETCH_ASSOC);

// 2. Ambil data emosi untuk GRAFIK (7 hari terakhir, Urut terlama)
$stmt_chart = $pdo->prepare(
    "SELECT tanggal_emosi, jenis_emosi 
     FROM Input_Emosi 
     WHERE id_pengguna = ? AND tanggal_emosi >= CURDATE() - INTERVAL 6 DAY 
     ORDER BY tanggal_emosi ASC"
);
$stmt_chart->execute([$id_pengguna]);
$chart_data_raw = $stmt_chart->fetchAll(PDO::FETCH_ASSOC);

// Mapping emosi ke nilai numerik untuk grafik (sesuai modul/permintaan)
// Senang (5) -> Tenang (4) -> Netral (3) -> Sedih (2) -> Cemas (1) -> Marah (0)
$emotion_values = [
    'senang' => 5,
    'tenang' => 4,
    'netral' => 3,
    'sedih' => 2,
    'cemas' => 1,
    'marah' => 0,
];

$chart_labels = [];
$chart_data = [];

foreach ($chart_data_raw as $row) {
    $chart_labels[] = date('d M', strtotime($row['tanggal_emosi']));
    // Default ke netral (3) jika ada data tidak valid di DB
    $chart_data[] = $emotion_values[$row['jenis_emosi']] ?? 3; 
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - Mood Tracker</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  
  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  <style>
    /* Menggunakan variabel warna dari style.css */
    :root {
        --primary-blue: #5D9CEC;
        --text-color-description: #6c757d;
        --border-radius-custom: 0.5rem;
    }

    /* Menyamakan style card dengan .login-form (Sesuai permintaan) */
    .card-form {
        background-color: #ffffff;
        border-radius: var(--border-radius-custom, 0.5rem);
        /* Menggunakan shadow yang lebih soft seperti di contoh */
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        border: 1px solid #e0e0e0; 
    }
    
    .text-primary-blue {
        color: var(--primary-blue) !important;
    }
    .text-secondary-light {
        color: var(--text-color-description, #6c757d);
    }
    
    /* Tombol (mengambil dari .btn-login-custom di style.css) */
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

    /* Style untuk Grid Emosi (Meniru contoh Tailwind) */
    .emotion-grid-label {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        align-items: center;
        justify-content: center;
        aspect-ratio: 1 / 1; /* Membuat kotak */
        border-radius: var(--border-radius-custom);
        border: 1px solid #e0e0e0;
        background-color: #ffffff;
        cursor: pointer;
        transition: all 0.2s ease-in-out;
        padding: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .emotion-grid-label:hover {
        border-color: var(--primary-blue);
        box-shadow: 0 4px 8px rgba(0,0,0,0.07);
        transform: translateY(-2px);
    }
    
    /* Sembunyikan radio button asli */
    .emotion-grid-input {
        display: none;
    }
    
    /* Style saat radio button dipilih */
    .emotion-grid-input:checked + .emotion-grid-label {
        border-color: var(--primary-blue);
        background-color: #f0f6ff; /* Warna biru sangat muda */
        box-shadow: 0 4px 8px rgba(0,0,0,0.07);
        transform: translateY(-2px);
    }
    
    .emotion-icon {
        font-size: 2.5rem; /* Ukuran ikon */
        color: #4F4F4F;
    }
    
    .emotion-grid-input:checked + .emotion-grid-label .emotion-icon {
        color: var(--primary-blue);
    }
    
    .emotion-label-text {
        font-weight: 500;
        color: #4F4F4F;
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
          <li class="nav-item"><a class="nav-link" href="laporkankasus.php">Laporkan Kasus</a></li>
          <li class="nav-item"><a class="nav-link active" href="moodtracker.php">Mood Tracker</a></li>
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
    
    <div id="alert-container" class="mb-4"></div>

    <div class="row g-4">
      
      <div class="col-lg-7">
        <div class="card card-form">
          <div class="card-body p-4 p-md-5">

            <div class="mb-4">
              <h1 class="text-primary-blue h3 fw-bold">Bagaimana Perasaanmu Hari Ini?</h1>
              <p class="text-secondary-light mb-0">Halo <?php echo htmlspecialchars($nama_user); ?>, pilih emosi yang kamu rasakan.</p>
            </div>

            <form method="POST" action="process_input_emosi.php" name="moodForm" onsubmit="return validateMoodForm()">
              
              <input type="hidden" name="id_pengguna" value="<?php echo $id_pengguna; ?>">

              <div class="row row-cols-3 row-cols-md-6 g-3 mb-4">
                
                <div class="col">
                  <input type="radio" class="emotion-grid-input" name="jenis_emosi" id="emo-senang" value="senang" required>
                  <label class="emotion-grid-label" for="emo-senang">
                    <i class="fa-regular fa-face-laugh-beam emotion-icon"></i>
                    <span class="emotion-label-text">Senang</span>
                  </label>
                </div>
                
                <div class="col">
                  <input type="radio" class="emotion-grid-input" name="jenis_emosi" id="emo-tenang" value="tenang" required>
                  <label class="emotion-grid-label" for="emo-tenang">
                    <i class="fa-regular fa-face-smile-beam emotion-icon"></i>
                    <span class="emotion-label-text">Tenang</span>
                  </label>
                </div>

                <div class="col">
                  <input type="radio" class="emotion-grid-input" name="jenis_emosi" id="emo-netral" value="netral" required>
                  <label class="emotion-grid-label" for="emo-netral">
                    <i class="fa-regular fa-face-meh emotion-icon"></i>
                    <span class="emotion-label-text">Netral</span>
                  </label>
                </div>

                <div class="col">
                  <input type="radio" class="emotion-grid-input" name="jenis_emosi" id="emo-sedih" value="sedih" required>
                  <label class="emotion-grid-label" for="emo-sedih">
                    <i class="fa-regular fa-face-frown emotion-icon"></i>
                    <span class="emotion-label-text">Sedih</span>
                  </label>
                </div>

                <div class="col">
                  <input type="radio" class="emotion-grid-input" name="jenis_emosi" id="emo-cemas" value="cemas" required>
                  <label class="emotion-grid-label" for="emo-cemas">
                    <i class="fa-regular fa-face-surprise emotion-icon"></i>
                    <span class="emotion-label-text">Cemas</span>
                  </label>
                </div>

                <div class="col">
                  <input type="radio" class="emotion-grid-input" name="jenis_emosi" id="emo-marah" value="marah" required>
                  <label class="emotion-grid-label" for="emo-marah">
                    <i class="fa-regular fa-face-angry emotion-icon"></i>
                    <span class="emotion-label-text">Marah</span>
                  </label>
                </div>
              </div> <div class="mb-3">
                <label for="catatan_emosi" class="form-label fw-semibold">Tambahkan Catatan Singkat</label>
                <textarea class="form-control" id="catatan_emosi" name="catatan_emosi" rows="4" 
                          maxlength="200" placeholder="Apa yang membuatmu merasa seperti ini?" required></textarea>
                <div id="char-count" class="form-text text-end">0/200</div>
              </div>

              <div class="mb-4">
                 <label for="tanggal_emosi" class="form-label fw-semibold">Tanggal</label>
                 <input type="date" class="form-control" id="tanggal_emosi" name="tanggal_emosi" 
                        value="<?php echo date('Y-m-d'); ?>" required>
              </div>

              <div class="text-center">
                 <button type="submit" class="btn btn-custom-primary w-100" style="max-width: 480px;">Simpan</button>
              </div>

            </form>
          </div>
        </div>
      </div> <div class="col-lg-5">
        
        <div class="card card-form mb-4">
          <div class="card-body p-4">
            <h5 class="text-primary-blue mb-3">Tren Emosi 7 Hari Terakhir</h5>
            <?php if (empty($chart_data)): ?>
              <p class="text-secondary-light text-center">Belum ada data untuk menampilkan grafik.</p>
            <?php else: ?>
              <div style="height: 250px;"> <canvas id="emotionChart"></canvas>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <div class="card card-form">
          <div class="card-body p-4">
            <h5 class="text-primary-blue mb-3">Riwayat Emosi Harian</h5>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
              <table class="table table-striped table-hover">
                <thead class="table-light sticky-top">
                  <tr>
                    <th>Tanggal</th>
                    <th>Emosi</th>
                    <th>Catatan</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($riwayat_emosi)): ?>
                    <tr>
                      <td colspan="3" class="text-center text-secondary-light">Belum ada riwayat emosi.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($riwayat_emosi as $riwayat): ?>
                      <tr>
                        <td class="text-nowrap"><?php echo htmlspecialchars(date('d M Y', strtotime($riwayat['tanggal_emosi']))); ?></td>
                        <td class="text-nowrap"><?php echo htmlspecialchars(ucfirst($riwayat['jenis_emosi'])); ?></td>
                        <td><?php echo htmlspecialchars($riwayat['catatan_emosi']); ?></td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        
      </div> </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  
  <script src="../assets/js/script.js"></script> 

  <script>
    // --- 1. Validasi Form (Sesuai permintaan: "alert JS bawaan script.js") ---
    function validateMoodForm() {
      const form = document.forms['moodForm'];
      const emotion = form['jenis_emosi'].value;
      const note = form['catatan_emosi'].value.trim();
      const date = form['tanggal_emosi'].value;

      // Validasi wajib diisi
      if (!emotion) {
        alert('Peringatan: Jenis emosi wajib dipilih.');
        return false;
      }
      if (note === '') {
        alert('Peringatan: Catatan wajib diisi.');
        return false;
      }
      if (date === '') {
        alert('Peringatan: Tanggal wajib diisi.');
        return false;
      }
      
      // Validasi max 200 karakter
      if (note.length > 200) {
        alert('Peringatan: Catatan tidak boleh lebih dari 200 karakter.');
        return false;
      }
      
      return true; // Lanjutkan submit jika lolos
    }

    // --- 2. Character Counter untuk Catatan ---
    const noteTextarea = document.getElementById('catatan_emosi');
    const charCount = document.getElementById('char-count');
    if (noteTextarea) {
        noteTextarea.addEventListener('input', function() {
            const count = this.value.length;
            charCount.textContent = count + '/200';
            if (count > 200) {
                charCount.classList.add('text-danger');
            } else {
                charCount.classList.remove('text-danger');
            }
        });
    }

    // --- 3. Penanganan Notifikasi (Alert) dari URL ---
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const error = urlParams.get('error');
        const alertContainer = document.getElementById('alert-container');

        let alertHtml = '';

        if (status === 'success') {
            alertHtml = `<div class="alert alert-success alert-dismissible fade show" role="alert">
                           <strong>Berhasil!</strong> Data emosi Anda telah berhasil disimpan.
                           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
        } else if (error === 'exists') {
            alertHtml = `<div class="alert alert-warning alert-dismissible fade show" role="alert">
                           <strong>Gagal:</strong> Anda sudah menginput emosi untuk tanggal tersebut.
                           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
        } else if (error === 'dberror' || error === 'validation') {
             alertHtml = `<div class="alert alert-danger alert-dismissible fade show" role="alert">
                           <strong>Gagal:</strong> Terjadi kesalahan. Pastikan semua data terisi dengan benar.
                           <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                         </div>`;
        }

        if(alertContainer && alertHtml) {
            alertContainer.innerHTML = alertHtml;
            // Membersihkan URL
            if (history.replaceState) {
                history.replaceState(null, '', window.location.pathname);
            }
        }
    });

    // --- 4. Inisialisasi Chart.js (Sesuai permintaan) ---
    <?php if (!empty($chart_data)): ?>
      const ctx = document.getElementById('emotionChart');
      if (ctx) {
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: <?php echo json_encode($chart_labels); ?>,
            datasets: [{
              label: 'Level Emosi',
              data: <?php echo json_encode($chart_data); ?>,
              borderColor: 'var(--primary-blue, #5D9CEC)',
              backgroundColor: 'rgba(93, 156, 236, 0.1)',
              fill: true,
              tension: 0.3,
              borderWidth: 3
            }]
          },
          options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
              y: {
                beginAtZero: true,
                max: 5, // Sesuai mapping (0=Marah, 5=Senang)
                ticks: {
                  stepSize: 1,
                  callback: function(value, index, ticks) {
                    const labels = ['Marah', 'Cemas', 'Sedih', 'Netral', 'Tenang', 'Senang'];
                    return labels[value] || '';
                  }
                }
              },
              x: {
                grid: {
                    display: false // Sembunyikan grid X-axis
                }
              }
            },
            plugins: {
              legend: {
                display: false
              },
              tooltip: {
                 callbacks: {
                    label: function(context) {
                        let label = ' Emosi';
                        const value = context.parsed.y;
                        const labels = ['Marah', 'Cemas', 'Sedih', 'Netral', 'Tenang', 'Senang'];
                        if (labels[value] !== undefined) {
                            label = ' ' + labels[value];
                        }
                        return label;
                    }
                }
              }
            }
          }
        });
      }
    <?php endif; ?>
  </script>

</body>
</html>
