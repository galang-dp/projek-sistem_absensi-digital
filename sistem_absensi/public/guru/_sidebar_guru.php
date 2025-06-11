<?php
// File: public/guru/_sidebar_guru.php

// Ambil nilai 'page' dari URL untuk menandai menu yang aktif
$currentPage = isset($_GET['page']) ? $_GET['page'] : 'dashboard_main'; 
?>
<div class="sidebar">
    <div class="sidebar-header">
        <a href="index.php" style="text-decoration:none; color:white;">
             <h3>Panel Guru</h3>
        </a>
    </div>
    <ul class="sidebar-nav">
        <li class="<?php echo ($currentPage == 'dashboard_main') ? 'active' : ''; ?>">
            <a href="index.php?page=dashboard_main">Dashboard</a>
        </li>
        <li class="<?php echo ($currentPage == 'edit_absensi') ? 'active' : ''; ?>">
            <a href="index.php?page=edit_absensi">Edit Absensi Kelas</a>
        </li>
        <li class="<?php echo ($currentPage == 'lihat_absensi_kelas') ? 'active' : ''; ?>">
            <a href="index.php?page=lihat_absensi_kelas">Lihat Laporan Kelas</a>
        </li>
        <li>
            <a href="../logout.php" class="logout-link-sidebar">Logout</a>
        </li>
    </ul>
</div>