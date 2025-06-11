<?php
// File: public/admin/_sidebar_admin.php

// Ambil nilai 'page' dari URL untuk menandai menu yang aktif (opsional, untuk styling)
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard_main'; // Default ke dashboard_main
?>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" style="text-decoration:none; color:white;"> <h3>Admin Panel</h3>
        </a>
    </div>
    <ul class="sidebar-nav">
        <li class="<?php echo ($currentPage == 'dashboard_main') ? 'active' : ''; ?>">
            <a href="index.php?page=dashboard_main">Dashboard Utama</a>
        </li>
        <li class="<?php echo (strpos($currentPage, 'siswa') !== false) ? 'active' : ''; ?>">
            <a href="index.php?page=kelola_siswa">Kelola Siswa</a>
        </li>
        <li class="<?php echo (strpos($currentPage, 'guru') !== false) ? 'active' : ''; ?>">
            <a href="index.php?page=kelola_guru">Kelola Guru</a>
        </li>
        <li class="<?php echo (strpos($currentPage, 'kelas') !== false) ? 'active' : ''; ?>">
            <a href="index.php?page=kelola_kelas">Kelola Kelas</a>
        </li>
        <li class="<?php echo ($currentPage == 'laporan_absensi') ? 'active' : ''; ?>">
            <a href="index.php?page=laporan_absensi">Laporan Absensi</a>
        </li>
        <li>
            <a href="../logout.php" class="logout-link-sidebar">Logout</a>
        </li>
    </ul>
</div>