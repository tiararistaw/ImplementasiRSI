<?php
$required_role = 'mahasiswa';
include '../includes/db_connect.php';
include '../includes/check_auth.php';

// Ambil nama pengguna
$stmt = $pdo->prepare("SELECT nama_pengguna FROM Pengguna WHERE id_akun = ?");
$stmt->execute([$_SESSION['id_akun']]);
$user = $stmt->fetch();
$nama_user = $user['nama_pengguna'] ?? 'Pengguna';

// Ambil semua data konselor
$query = $pdo->query("SELECT * FROM konselor");
$konselors = $query->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Konseling Online - Mahasiswa</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    :root {
      --primary-color: #13a4ec;
      --text-color: #333;
    }

    body {
      background-color: #f6f7f8;
      font-family: 'Inter', sans-serif;
    }

    .navbar {
      padding-top: 0.7rem;
      padding-bottom: 0.7rem;
    }

    .navbar-nav .nav-link {
      color: #2b3a4e;
      font-weight: 500;
      font-size: 0.8rem;
      padding: 0.8rem 0.5rem !important;
      border-bottom: 3px solid transparent;
      transition: all 0.2s ease;
    }

    .navbar-nav .nav-link:hover {
      color: #6D94C5 !important;
    }

    .navbar-nav .nav-link.active {
      color: #6D94C5 !important;
      border-bottom-color: #6D94C5;
      font-weight: 700
    }

    .navbar .nav-link.text-danger {
      font-weight: 600;
      display: flex;
      align-items: center;
      transition: color 0.2s;
    }

    .navbar .nav-link.text-danger:hover {
      color: #b02a37 !important;
    }

    /* Ikon logout */
    .fa-sign-out-alt {
      font-size: 1rem;
    }

    /* 🔹 CARD STYLE */
    .card {
      border: none;
      border-radius: 1rem;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08);
    }

    .btn-whatsapp {
      background-color: #25D366;
      color: white;
      font-weight: 600;
      border: none;
    }

    .btn-whatsapp:hover {
      background-color: #1ebe57;
      color: white;
    }

    main {
      padding-top: 100px;
      padding-bottom: 40px;
    }
  </style>
</head>

<body>

  <!-- 🔹 NAVBAR -->
  <nav class="navbar navbar-expand-lg fixed-top shadow-sm border-bottom">
    <div class="container-fluid px-4">
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link active" href="konseling.php">Konseling Online</a></li>
          <li class="nav-item"><a class="nav-link" href="dailyreflection.php">Daily Reflection</a></li>
          <li class="nav-item"><a class="nav-link" href="assessmenttest.php">Assessment Test</a></li>
          <li class="nav-item"><a class="nav-link" href="kontenedukasi.php">Konten Edukasi</a></li>
          <li class="nav-item"><a class="nav-link" href="laporkankasus.php">Laporkan Kasus</a></li>
          <li class="nav-item"><a class="nav-link" href="moodtracker.php">Mood Tracker</a></li>
          <li class="nav-item"><a class="nav-link" href="wellnessmission.php">Wellness Mission</a></li>
        </ul>
        <ul class="navbar-nav align-items-center">
          <li class="nav-item">
            <a class="nav-link text-danger fw-semibold d-flex align-items-center" href="../logout.php">
              <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- 🔹 MAIN CONTENT -->
  <main class="container">
    <div class="text-center mb-5">
      <h1 class="fw-bold">Penjadwalan Konseling Mahasiswa</h1>
      <p class="text-secondary">Temukan dan hubungi konselor kampus yang tepat untuk mendukung kesehatan mental Anda.</p>
    </div>

    <div class="row g-4">
      <?php foreach ($konselors as $k): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
          <div class="card h-100">
            <img src="<?= htmlspecialchars($k['foto_url']) ?>" class="card-img-top" alt="<?= htmlspecialchars($k['nama_konselor']) ?>">
            <div class="card-body d-flex flex-column">
              <h5 class="card-title fw-bold"><?= htmlspecialchars($k['nama_konselor']) ?></h5>
              <p class="text-primary fw-semibold mb-2"><?= htmlspecialchars($k['spesialisasi']) ?></p>
              <p class="text-secondary mb-3"><i class="fas fa-clock me-1"></i> <?= htmlspecialchars($k['jadwal']) ?></p>
              <div class="mt-auto">
                <a href="<?= htmlspecialchars($k['whatsapp_link']) ?>" target="_blank" class="btn btn-whatsapp w-100">
                  <i class="fab fa-whatsapp me-2"></i>Hubungi via WhatsApp
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
