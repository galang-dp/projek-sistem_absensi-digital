<?php
session_start();
// Path ke db_connect.php relatif dari public/admin/index.php
require_once '../../config/db_connect.php'; 

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Sesi Anda telah berakhir atau tidak valid. Silakan login kembali.");
    exit();
}

$admin_username = $_SESSION['username']; // Ambil username admin dari session

// 2. Tentukan halaman konten yang akan dimuat berdasarkan parameter 'page' di URL
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard_main'; // Default ke 'dashboard_main'

// 3. Daftar halaman konten yang valid dan path ke filenya
// Ini akan kita kembangkan seiring kita mengubah halaman-halaman lain menjadi file konten
$content_pages_map = [
    'dashboard_main'        => ['file' => 'pages/dashboard_main_content.php', 'title' => 'Dashboard Utama'],
    'kelola_siswa'          => ['file' => 'pages/kelola_siswa_content.php', 'title' => 'Kelola Siswa'],
    'tambah_siswa'          => ['file' => 'pages/tambah_siswa_content.php', 'title' => 'Tambah Siswa'],
    'edit_siswa'            => ['file' => 'pages/edit_siswa_content.php', 'title' => 'Edit Siswa'],
    'kelola_guru'           => ['file' => 'pages/kelola_guru_content.php', 'title' => 'Kelola Guru'],
    'tambah_guru'           => ['file' => 'pages/tambah_guru_content.php', 'title' => 'Tambah Guru'],
    'edit_guru'             => ['file' => 'pages/edit_guru_content.php', 'title' => 'Edit Guru'],
    'kelola_kelas'          => ['file' => 'pages/kelola_kelas_content.php', 'title' => 'Kelola Kelas'],
    'tambah_kelas'          => ['file' => 'pages/tambah_kelas_content.php', 'title' => 'Tambah Kelas'],
    'edit_kelas'            => ['file' => 'pages/edit_kelas_content.php', 'title' => 'Edit Kelas'],
    'kelola_orangtua'       => ['file' => 'pages/kelola_orangtua_content.php', 'title' => 'Kelola Orang Tua'],
    'tambah_orangtua'       => ['file' => 'pages/tambah_orangtua_content.php', 'title' => 'Tambah Orang Tua'],
    'edit_orangtua'         => ['file' => 'pages/edit_orangtua_content.php', 'title' => 'Edit Orang Tua'],
    'laporan_absensi'       => ['file' => 'pages/laporan_absensi_content.php', 'title' => 'Laporan Absensi']
    // Tambahkan halaman konten lainnya di sini
];

$page_file_to_load = 'pages/404_content.php'; // Halaman default jika 'page' tidak valid
$page_title = 'Halaman Tidak Ditemukan';

if (array_key_exists($page, $content_pages_map)) {
    // Cek apakah file konten benar-benar ada sebelum di-include
    if (file_exists($content_pages_map[$page]['file'])) {
        $page_file_to_load = $content_pages_map[$page]['file'];
        $page_title = $content_pages_map[$page]['title'];
    } else {
        // Jika file tidak ada, tetap gunakan 404_content.php
        // Anda bisa juga membuat pesan error spesifik di sini
        // $page_file_to_load = "pages/error_file_tidak_ada.php";
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($page_title); ?> - Admin Panel SDI Al-Hasanah</title>
    <link rel="stylesheet" href="../css/style.css"> 
</head>
<body>
    <div class="app-layout">
        <?php include '_sidebar_admin.php'; // Memasukkan file sidebar ?>

        <div class="main-content">
            <header class="main-content-header">
                <h1><?php echo htmlspecialchars($page_title); ?></h1>
                <span>Selamat datang, <?php echo htmlspecialchars($admin_username); ?>!</span>
            </header>

            <main class="content-area">
                <?php
                // Muat konten halaman yang dipilih
                // Variabel $conn (dari db_connect.php yang di-require di atas) akan tersedia
                // secara global untuk file yang di-include di sini.
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