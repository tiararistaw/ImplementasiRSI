<?php
include 'includes/db_connect.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // ... (Logika Registrasi Mahasiswa dari file sebelumnya) ...
    $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

    // Validasi duplikasi dan insert Transaksional
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM Akun WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetchColumn() > 0) {
            $error = 'Username atau Email sudah terdaftar.';
        } else {
            $pdo->beginTransaction();
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO Akun (username, email, password) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hashed_password]);
            $id_akun = $pdo->lastInsertId();

            $stmt = $pdo->prepare("INSERT INTO Pengguna (id_akun, nama_pengguna) VALUES (?, ?)");
            $stmt->execute([$id_akun, $nama_lengkap]);

            $pdo->commit();
            $success = 'Registrasi berhasil! Silakan <a href="login.php">Login</a>.';
        }
    } catch (PDOException $e) {
        $pdo->rollBack();
        $error = 'Registrasi gagal karena kesalahan server.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Layanan Kesehatan Mental</title>

    <!-- Bootstrap & Custom CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

    <body class="bg-light"> 
        <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
            <div class="login-form">

            <!-- Judul & Deskripsi -->
            <h1 class="text-primary-blue text-center">Register</h1>
            <p class="text-secondary-light text-center">Daftarkan diri Anda</p>

            <?php if (isset($error) && $error): ?>
                <div class="alert alert-danger mb-4 text-center" role="alert"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Form Registrasi -->
            <form method="POST" name="registerForm" action="register.php">
                <div class="mb-3">
                    <input type="text" class="form-control" name="nama_lengkap" placeholder="Nama Lengkap" required>
                </div>

                <div class="mb-3">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>

                <div class="mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required>
                </div>

                <div class="mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>

                <div class="mb-4">
                    <input type="password" class="form-control" name="konfirmasi_password" placeholder="Konfirmasi Password" required>
                </div>

                <button type="submit" class="btn btn-login-custom w-50">Register</button>
            </form>

            <!-- Link ke Halaman Login -->
            <div class="mt-4 text-center">
                Sudah punya akun?
                <a href="login.php" class="text-primary-blue-link">Login</a>
            </div>

        </div>
    </div>

    <!-- Script -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>

</body>

</html>