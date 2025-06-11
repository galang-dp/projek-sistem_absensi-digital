<?php
// File: public/guru/pages/dashboard_main_content.php (Versi Baru dengan Statistik)
global $conn; // Mengambil koneksi database dari index.php

$guru_username = $_SESSION['username'];
$user_id_session = $_SESSION['user_id'];
$guru_id = null;
$kelas_id = null;
$nama_kelas = "Belum ada kelas yang diampu";

// Inisialisasi variabel statistik
$total_siswa = 0;
$hadir_hari_ini = 0;
$tidak_hadir_hari_ini = 0; // Izin + Sakit + Tidak Hadir

// 1. Dapatkan guru_id dari user_id
$stmt_guru = $conn->prepare("SELECT guru_id FROM Guru WHERE user_id = ?");
if ($stmt_guru) {
    $stmt_guru->bind_param("i", $user_id_session);
    $stmt_guru->execute();
    $result_guru = $stmt_guru->get_result();
    if ($result_guru->num_rows > 0) {
        $guru_data = $result_guru->fetch_assoc();
        $guru_id = $guru_data['guru_id'];
    }
    $stmt_guru->close();
}

// 2. Jika guru_id ditemukan, dapatkan info kelas dan statistik
if ($guru_id) {
    // Ambil info kelas
    $stmt_kelas = $conn->prepare("SELECT kelas_id, nama_kelas, tahun_ajaran, semester FROM Kelas WHERE wali_kelas_id = ?");
    if ($stmt_kelas) {
        $stmt_kelas->bind_param("i", $guru_id);
        $stmt_kelas->execute();
        $result_kelas = $stmt_kelas->get_result();
        if ($result_kelas->num_rows > 0) {
            $kelas_data = $result_kelas->fetch_assoc();
            $kelas_id = $kelas_data['kelas_id'];
            $nama_kelas = $kelas_data['nama_kelas'] . " (" . $kelas_data['tahun_ajaran'] . " - " . $kelas_data['semester'] . ")";
            
            // Hitung total siswa di kelasnya
            $stmt_total_siswa = $conn->prepare("SELECT COUNT(*) as total FROM Siswa WHERE kelas_id = ? AND status_aktif = TRUE");
            $stmt_total_siswa->bind_param("i", $kelas_id);
            $stmt_total_siswa->execute();
            $total_siswa = $stmt_total_siswa->get_result()->fetch_assoc()['total'];
            $stmt_total_siswa->close();

            // Hitung statistik kehadiran hari ini
            $tanggal_hari_ini = date("Y-m-d");
            $stmt_hadir = $conn->prepare("SELECT COUNT(*) as total FROM Absensi WHERE siswa_id IN (SELECT siswa_id FROM Siswa WHERE kelas_id = ?) AND tanggal_absensi = ? AND status_kehadiran = 'Hadir'");
            $stmt_hadir->bind_param("is", $kelas_id, $tanggal_hari_ini);
            $stmt_hadir->execute();
            $hadir_hari_ini = $stmt_hadir->get_result()->fetch_assoc()['total'];
            $stmt_hadir->close();
            
            // Hitung yang tidak hadir (Izin, Sakit, Tidak Hadir)
            $stmt_tidak_hadir = $conn->prepare("SELECT COUNT(*) as total FROM Absensi WHERE siswa_id IN (SELECT siswa_id FROM Siswa WHERE kelas_id = ?) AND tanggal_absensi = ? AND status_kehadiran IN ('Izin', 'Sakit', 'Tidak Hadir')");
            $stmt_tidak_hadir->bind_param("is", $kelas_id, $tanggal_hari_ini);
            $stmt_tidak_hadir->execute();
            $tidak_hadir_hari_ini = $stmt_tidak_hadir->get_result()->fetch_assoc()['total'];
            $stmt_tidak_hadir->close();
        }
    }
}
?>

<div class="stats-cards">
    <div class="stat-card">
        <h4>Total Siswa di Kelas</h4>
        <p class="stat-number"><?php echo $total_siswa; ?></p>
        <a href="index.php?page=edit_absensi">Kelola Kelas &rarr;</a>
    </div>
    <div class="stat-card">
        <h4>Siswa Hadir Hari Ini</h4>
        <p class="stat-number" style="color: #28a745;"><?php echo $hadir_hari_ini; ?></p>
        <a href="index.php?page=lihat_absensi_kelas">Lihat Detail &rarr;</a>
    </div>
    <div class="stat-card">
        <h4>Siswa Tidak Hadir</h4>
        <p class="stat-number" style="color: #dc3545;"><?php echo $tidak_hadir_hari_ini; ?></p>
        <a href="index.php?page=lihat_absensi_kelas&tanggal=<?php echo date('Y-m-d'); ?>">Lihat Detail &rarr;</a>
    </div>
</div>

<p>Selamat datang kembali, <strong><?php echo htmlspecialchars($guru_username); ?></strong>.</p>
<p>Anda adalah wali kelas untuk: <strong><?php echo htmlspecialchars($nama_kelas); ?></strong>.</p>
<p>Silakan gunakan menu di sidebar untuk mulai mengelola absensi kelas Anda atau melihat laporan.</p>