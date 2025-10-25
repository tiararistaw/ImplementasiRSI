<?php
$required_role = 'mahasiswa';
include '../includes/db_connect.php';
include '../includes/check_auth.php'; 

// Ambil Nama Pengguna untuk display
$stmt = $pdo->prepare("SELECT nama_pengguna FROM Pengguna WHERE id_akun = ?");
$stmt->execute([$_SESSION['id_akun']]);
$user = $stmt->fetch();
$nama_user = $user['nama_pengguna'] ?? 'Pengguna';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Mahasiswa - Assessment Test</title>

  <!-- Bootstrap & Font Awesome -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="style.css">
</head>

<body style="background-color: #f2f2f2;">

  <!-- 🔹 NAVBAR -->
  <nav class="navbar navbar-expand-lg bg-white fixed-top shadow-sm border-bottom">
    <div class="container-fluid px-4">

      <!-- Tombol Toggle (Mobile) -->
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
        aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <!-- Menu Navbar -->
      <div class="collapse navbar-collapse justify-content-between" id="navbarNav">
        <!-- Menu Tengah -->
        <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
          <li class="nav-item"><a class="nav-link" href="konseling.php">Konseling Online</a></li>
          <li class="nav-item"><a class="nav-link" href="dailyreflection.php">Daily Reflection</a></li>
          <li class="nav-item"><a class="nav-link active" href="assessmenttest.php">Assessment Test</a></li>
          <li class="nav-item"><a class="nav-link" href="kontenedukasi.php">Konten Edukasi</a></li>
          <li class="nav-item"><a class="nav-link" href="laporkankasus.php">Laporkan Kasus</a></li>
          <li class="nav-item"><a class="nav-link" href="moodtracker.php">Mood Tracker</a></li>
          <li class="nav-item"><a class="nav-link" href="wellnessmission.php">Wellness Mission</a></li>
        </ul>

        <!-- Kanan Atas -->
        <ul class="navbar-nav align-items-center">
            <a class="nav-link text-danger fw-semibold d-flex align-items-center" href="../logout.php">
              <i class="fas fa-sign-out-alt me-1"></i> Logout
            </a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <!-- 🔹 Konten Utama -->
  <main class="container-fluid py-5" style="margin-top: 60px;">
    <div class="p-5 bg-white rounded shadow-sm">
      <p class="text-muted">Ini area konten utama untuk fitur assessment test (Mahasiswa).</p>
    </div>
  </main>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>