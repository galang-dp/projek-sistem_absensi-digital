<?php
// Ganti 'PasswordAdminRahasia123!' dengan password yang Anda inginkan untuk Admin
$passwordAsli = 'PasswordAdminRahasia123!';

// Membuat hash password
$hashedPassword = password_hash($passwordAsli, PASSWORD_DEFAULT);

echo "Password Asli: " . htmlspecialchars($passwordAsli) . "<br>";
echo "Password Hash: " . htmlspecialchars($hashedPassword);

// Anda juga bisa langsung menyalin hash di bawah ini untuk digunakan pada langkah berikutnya.
// Pastikan untuk MENGHAPUS file ini setelah selesai mendapatkan hash, atau pindahkan ke luar web root.
?>