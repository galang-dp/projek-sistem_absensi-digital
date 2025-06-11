<?php
// File: public/admin/pages/dashboard_main_content.php
// Variabel $conn sudah tersedia dari index.php

// Anda bisa menambahkan logika PHP di sini jika perlu mengambil data untuk dashboard
// Contoh: Menghitung jumlah siswa, guru, kelas, dll.

$jumlah_siswa = 0;
$jumlah_guru = 0;
$jumlah_kelas = 0;

$result_siswa = $conn->query("SELECT COUNT(*) as total FROM Siswa WHERE status_aktif = TRUE");
if($result_siswa) $jumlah_siswa = $result_siswa->fetch_assoc()['total'];

$result_guru = $conn->query("SELECT COUNT(*) as total FROM Guru"); // Asumsi semua guru aktif
if($result_guru) $jumlah_guru = $result_guru->fetch_assoc()['total'];

$result_kelas = $conn->query("SELECT COUNT(*) as total FROM Kelas");
if($result_kelas) $jumlah_kelas = $result_kelas->fetch_assoc()['total'];

?>

<div class="stats-cards">
    <div class="stat-card">
        <h4>Total Siswa Aktif</h4>
        <p class="stat-number"><?php echo $jumlah_siswa; ?></p>
        <a href="index.php?page=kelola_siswa">Lihat Detail &rarr;</a>
    </div>
    <div class="stat-card">
        <h4>Total Guru</h4>
        <p class="stat-number"><?php echo $jumlah_guru; ?></p>
        <a href="index.php?page=kelola_guru">Lihat Detail &rarr;</a>
    </div>
    <div class="stat-card">
        <h4>Total Kelas</h4>
        <p class="stat-number"><?php echo $jumlah_kelas; ?></p>
        <a href="index.php?page=kelola_kelas">Lihat Detail &rarr;</a>
    </div>
</div>

<p>Selamat datang di Panel Admin Sistem Absensi Digital SDI Al-Hasanah.</p>
<p>Silakan gunakan menu di sidebar untuk mengelola data sistem.</p>