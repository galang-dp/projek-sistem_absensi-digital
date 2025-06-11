<?php
session_start();
require_once '../../config/db_connect.php'; // Path ke db_connect dari public/guru/

// 1. Cek jika user belum login atau bukan GURU
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Guru') {
    header("Location: ../login.php?error=Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.");
    exit();
}

$guru_username = $_SESSION['username'];

// 2. Tentukan halaman konten yang akan dimuat
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard_main'; // Default ke dashboard utama guru

// 3. Daftar halaman konten yang valid untuk guru
$content_pages_map = [
    'dashboard_main'        => ['file' => 'pages/dashboard_main_content.php', 'title' => 'Dashboard Guru'],
    'edit_absensi'          => ['file' => 'pages/edit_absensi_content.php', 'title' => 'Edit Absensi Siswa'],
    'lihat_absensi_kelas'   => ['file' => 'pages/lihat_absensi_kelas_content.php', 'title' => 'Lihat Absensi Kelas']
];

$page_file_to_load = 'pages/404_content.php'; // Halaman default jika 'page' tidak valid
$page_title = 'Halaman Tidak Ditemukan';

if (array_key_exists($page, $content_pages_map)) {
    if (file_exists($content_pages_map[$page]['file'])) {
        $page_file_to_load = $content_pages_map[$page]['file'];
        $page_title = $content_pages_map[$page]['title'];
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Panel Guru SDI Al-Hasanah</title>
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
    <div class="app-layout">
        <?php include '_sidebar_guru.php'; // Memasukkan file sidebar guru ?>

        <div class="main-content">
            <header class="main-content-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <span>Selamat datang, <?php echo htmlspecialchars($guru_username); ?>!</span>
            </header>

            <main class="content-area">
                <?php
                // Muat konten halaman yang dipilih
                include $page_file_to_load; 
                ?>
            </main>

            <footer class="main-content-footer">
                <p>&copy; <?php echo date("Y"); ?> SDI Al-Hasanah. Hak Cipta Dilindungi.</p>
            </footer>
        </div>
    </div>
</body>
</html>