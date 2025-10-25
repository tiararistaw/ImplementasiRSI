<?php
include 'includes/db_connect.php';

$error = '';
if (isset($_SESSION['id_akun'])) {
    // Jika sudah login, langsung redirect
    header('Location: ' . $_SESSION['role'] . '/konseling.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Logika Login
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    //Logika Autentikasi dan Otorisasi)
    $stmt = $pdo->prepare("SELECT id_akun, password FROM Akun WHERE username = :user OR email = :user");
    $stmt->execute(['user' => $username]);
    $akun = $stmt->fetch();

    if ($akun && password_verify($password, $akun['password'])) {
        $id_akun = $akun['id_akun'];
        $_SESSION['id_akun'] = $id_akun;

        $roles = ['administrator', 'konselor', 'mahasiswa'];
        $found_role = false;

        foreach ($roles as $role_key) {
            $table_name = ($role_key == 'mahasiswa') ? 'Pengguna' : ucfirst($role_key);
            $stmt_check = $pdo->prepare("SELECT 1 FROM $table_name WHERE id_akun = ?");
            $stmt_check->execute([$id_akun]);
            if ($stmt_check->fetch()) {
                $_SESSION['role'] = $role_key;
                header('Location: ' . $role_key . '/konseling.php');
                exit;
            }
        }

        $error = 'Username atau password yang Anda masukkan tidak valid.';
        unset($_SESSION['id_akun']);
    } else {
        $error = 'Username atau password yang Anda masukkan tidak valid.';
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Layanan Kesehatan Mental</title>

    <!-- Bootstrap & Custom CSS -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-light d-flex vh-100 align-items-center justify-content-center">

    <div class="col-12 col-sm-8 col-md-6 col-lg-4">
        <div class="login-form">

            <!-- Judul & Deskripsi -->
            <h1 class="text-primary-blue text-center">Login</h1>
            <p class="text-secondary-light text-center">Masukkan username dan password</p>

            <!-- Pesan Error -->
            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Form Login -->
            <form method="POST" name="loginForm" action="login.php">
                <div class="mb-4">
                    <input type="text" class="form-control" name="username" placeholder="Username" required>
                </div>

                <div class="mb-5">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>

                <button type="submit" class="btn btn-login-custom w-50">Login</button>
            </form>

            <!-- Link Register -->
            <div class="mt-4 text-center">
                Belum punya akun?
                <a href="register.php" class="text-primary-blue-link">Register</a>
            </div>

        </div>
    </div>

    <!-- Script -->
    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>

</body>

</html>