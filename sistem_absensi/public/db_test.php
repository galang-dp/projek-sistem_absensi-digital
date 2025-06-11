<?php
// File: public/db_test.php
// Skrip ini hanya untuk menguji koneksi ke database

echo "<h1>Tes Koneksi Database</h1>";

// Menggunakan path yang sama persis seperti di skrip lain
require_once '../config/db_connect.php';

// Cek apakah variabel koneksi $conn ada dan tidak ada error
if (isset($conn) && $conn->connect_error) {
    // Jika ada error saat koneksi
    echo "<p style='color: red; font-weight: bold;'>KONEKSI GAGAL!</p>";
    echo "<p>Error: " . htmlspecialchars($conn->connect_error) . "</p>";
} elseif (isset($conn)) {
    // Jika koneksi berhasil
    echo "<p style='color: green; font-weight: bold;'>KONEKSI KE DATABASE BERHASIL!</p>";
    echo "<p>Server MySQL terhubung.</p>";
    $conn->close();
} else {
    // Jika variabel $conn tidak ada sama sekali (masalah di file db_connect.php)
    echo "<p style='color: red; font-weight: bold;'>VARIABEL KONEKSI TIDAK DITEMUKAN!</p>";
    echo "<p>Pastikan file 'config/db_connect.php' sudah benar.</p>";
}
?>