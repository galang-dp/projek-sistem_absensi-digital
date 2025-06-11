<?php
session_start(); // Mulai session

// Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin untuk mengakses halaman ini");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Absensi SDI Al-Hasanah</title>
    <link rel="stylesheet" href="../css/style.css"> </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Selamat Datang di Dashboard Admin</h1>
            <p>Halo, <?php echo htmlspecialchars($username); ?>!</p>
        </div>

        <nav class="dashboard-nav">
            <ul>
                <li><a href="kelola_siswa.php" class="admin-link">Kelola Data Siswa</a></li>
                <li><a href="tambah_siswa.php" class="admin-link">Tambah Siswa Baru</a></li>
                <li><a href="kelola_guru.php" class="admin-link">Kelola Data Guru</a></li>
                <li><a href="kelola_kelas.php" class="admin-link">Kelola Data Kelas</a></li>
                <li><a href="kelola_orangtua.php" class="admin-link">Kelola Data Orang Tua</a></li>
                <li><a href="laporan_absensi.php" class="admin-link">Lihat Laporan Absensi</a></li>
                </ul>
        </nav>

        <div class="dashboard-content">
            <p style="text-align:center; margin-top: 20px;">Pilih menu di atas untuk memulai.</p>
            </div>

        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html> 