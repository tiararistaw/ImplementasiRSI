<?php
// Catatan: Variabel $required_role harus di-set di file yang meng-include ini.

// Cek apakah pengguna sudah login dan memiliki role yang benar
if (!isset($_SESSION['id_akun']) || $_SESSION['role'] !== $required_role) {
    // Redirect ke login dengan parameter error
    header('Location: ../login.php?error=unauthorized');
    exit;
}
?>