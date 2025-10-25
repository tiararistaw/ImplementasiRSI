<?php
// Password teks biasa yang ingin Anda gunakan untuk Admin/Konselor
$password_plain = 'konselor123'; 

// Hasilkan hash yang aman menggunakan algoritma Blowfish (default)
$password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

echo "Password teks biasa: " . $password_plain . "<br>";
echo "Hash yang perlu disalin ke database: " . $password_hash;
?>