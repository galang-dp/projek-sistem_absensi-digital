<?php
session_start(); // Mulai session

// Cek jika user belum login atau bukan guru
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Guru') {
    header("Location: ../login.php?error=Anda harus login sebagai Guru untuk mengakses halaman ini");
    exit();
}

$username = $_SESSION['username'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guru - Sistem Absensi SDI Al-Hasanah</title>
    <link rel="stylesheet" href="../css/style.css"> </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Selamat Datang di Dashboard Guru</h1>
            <p>Halo, <?php echo htmlspecialchars($username); ?>!</p>
        </div>

        <nav class="dashboard-nav">
            <ul>
                <li><a href="edit_absensi.php" class="guru-link">Edit Daftar Hadir Siswa</a></li>
                <li><a href="lihat_absensi_kelas.php" class="guru-link">Lihat Absensi Kelas</a></li>
                </ul>
        </nav>

        <div class="dashboard-content">
            <p style="text-align:center; margin-top: 20px;">Pilih menu di atas untuk mengelola absensi.</p>
            </div>

        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>