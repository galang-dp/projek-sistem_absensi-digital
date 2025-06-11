<?php
$host = "localhost"; // atau IP server database Anda
$username = "root"; // username database Anda
$password = ""; // password database Anda
$database = "db_absensi_sdi"; // nama database Anda

// Membuat koneksi
$conn = new mysqli($host, $username, $password, $database);

// Cek koneksi
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
// echo "Koneksi berhasil!"; // Hapus atau beri komentar setelah tes
?>