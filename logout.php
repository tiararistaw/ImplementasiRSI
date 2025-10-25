<?php
session_start();
session_unset(); // Hapus semua variabel sesi
session_destroy(); // Hancurkan sesi
header('Location: login.php?status=logout'); // Redirect ke login
exit;
?>