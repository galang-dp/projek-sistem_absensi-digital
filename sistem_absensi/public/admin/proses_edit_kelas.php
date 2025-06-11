<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin' || $_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['submit_edit_kelas'])) {
    $_SESSION['message'] = "<div class='info-message error'>Aksi tidak valid.</div>";
    header("Location: index.php?page=kelola_kelas");
    exit();
}

// Ambil data dari form baru
$kelas_id = isset($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : 0;
$tingkat = trim($_POST['tingkat']);
$nama_paralel = trim($_POST['nama_paralel']);
$tahun_ajaran = trim($_POST['tahun_ajaran']);
$semester = trim($_POST['semester']);
$wali_kelas_id = isset($_POST['wali_kelas_id']) && !empty($_POST['wali_kelas_id']) ? (int)$_POST['wali_kelas_id'] : null;

// Validasi dasar
if (empty($kelas_id)) {
    $_SESSION['message'] = "<div class='info-message error'>ID Kelas tidak valid untuk diedit.</div>";
    header("Location: index.php?page=kelola_kelas");
    exit();
}

if (empty($tingkat) || empty($nama_paralel) || empty($tahun_ajaran) || empty($semester)) {
    $_SESSION['error_message_form'] = "Semua field (Tingkat, Nama Paralel, Tahun Ajaran, Semester) wajib diisi.";
    header("Location: index.php?page=edit_kelas&id=" . $kelas_id);
    exit();
}

// Gabungkan tingkat dan nama paralel menjadi nama kelas yang unik
$nama_kelas_baru = "Kelas " . $tingkat . "-" . $nama_paralel;

// Cek keunikan kombinasi: nama_kelas, tahun_ajaran, dan semester terhadap data lain
$stmt_check = $conn->prepare("SELECT kelas_id FROM Kelas WHERE nama_kelas = ? AND tahun_ajaran = ? AND semester = ? AND kelas_id != ?");
if (!$stmt_check) {
    $_SESSION['error_message_form'] = "Gagal mempersiapkan statement pengecekan: " . $conn->error;
    header("Location: index.php?page=edit_kelas&id=" . $kelas_id);
    exit();
}
$stmt_check->bind_param("sssi", $nama_kelas_baru, $tahun_ajaran, $semester, $kelas_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $_SESSION['error_message_form'] = "Kombinasi Kelas, Tahun Ajaran, dan Semester ini sudah ada.";
    $stmt_check->close();
    header("Location: index.php?page=edit_kelas&id=" . $kelas_id);
    exit();
}
$stmt_check->close();

// Update data di tabel Kelas
$stmt_update = $conn->prepare("UPDATE Kelas SET nama_kelas = ?, tingkat = ?, tahun_ajaran = ?, semester = ?, wali_kelas_id = ? WHERE kelas_id = ?");
if (!$stmt_update) {
    $_SESSION['error_message_form'] = "Gagal mempersiapkan statement update: " . $conn->error;
    header("Location: index.php?page=edit_kelas&id=" . $kelas_id);
    exit();
}

$stmt_update->bind_param("ssssii", $nama_kelas_baru, $tingkat, $tahun_ajaran, $semester, $wali_kelas_id, $kelas_id);

if ($stmt_update->execute()) {
    $_SESSION['success_message_form'] = "Data kelas '" . htmlspecialchars($nama_kelas_baru) . "' berhasil diperbarui.";
} else {
    $_SESSION['error_message_form'] = "Gagal memperbarui data kelas: " . $stmt_update->error;
}
$stmt_update->close();
$conn->close();

// Redirect kembali ke halaman edit dengan pesan status
header("Location: index.php?page=edit_kelas&id=" . $kelas_id);
exit();
?>