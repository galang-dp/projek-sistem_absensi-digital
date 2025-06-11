<?php
session_start();
require_once '../../config/db_connect.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin' || $_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['submit_tambah_kelas'])) {
    $_SESSION['message'] = "<div class='info-message error'>Aksi tidak valid.</div>";
    header("Location: index.php?page=kelola_kelas");
    exit();
}

// Ambil data dari form baru
$tingkat = trim($_POST['tingkat']);
$nama_paralel = trim($_POST['nama_paralel']);
$tahun_ajaran = trim($_POST['tahun_ajaran']);
$semester = trim($_POST['semester']);
$wali_kelas_id = isset($_POST['wali_kelas_id']) && !empty($_POST['wali_kelas_id']) ? (int)$_POST['wali_kelas_id'] : null;

$_SESSION['old_input_kelas'] = $_POST;

// Validasi
if (empty($tingkat) || empty($nama_paralel) || empty($tahun_ajaran) || empty($semester)) {
    $_SESSION['error_message_form'] = "Semua field (Tingkat, Nama Paralel, Tahun Ajaran, Semester) wajib diisi.";
    header("Location: index.php?page=tambah_kelas");
    exit();
}

// Gabungkan tingkat dan nama paralel menjadi nama kelas yang unik
$nama_kelas = "Kelas " . $tingkat . "-" . $nama_paralel;

// Cek keunikan kombinasi: nama_kelas, tahun_ajaran, dan semester
$stmt_check = $conn->prepare("SELECT kelas_id FROM Kelas WHERE nama_kelas = ? AND tahun_ajaran = ? AND semester = ?");
$stmt_check->bind_param("sss", $nama_kelas, $tahun_ajaran, $semester);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    $_SESSION['error_message_form'] = "Kelas '" . htmlspecialchars($nama_kelas) . "' untuk Tahun Ajaran " . htmlspecialchars($tahun_ajaran) . " Semester " . htmlspecialchars($semester) . " sudah ada.";
    $stmt_check->close();
    header("Location: index.php?page=tambah_kelas");
    exit();
}
$stmt_check->close();

// Insert data baru ke tabel Kelas
$stmt_insert = $conn->prepare("INSERT INTO Kelas (nama_kelas, tingkat, tahun_ajaran, semester, wali_kelas_id) VALUES (?, ?, ?, ?, ?)");
$stmt_insert->bind_param("ssssi", $nama_kelas, $tingkat, $tahun_ajaran, $semester, $wali_kelas_id);

if ($stmt_insert->execute()) {
    $_SESSION['message'] = "<div class='info-message success'>Kelas baru '" . htmlspecialchars($nama_kelas) . " (" . htmlspecialchars($tahun_ajaran) . " - " . htmlspecialchars($semester) . ")' berhasil ditambahkan.</div>";
    unset($_SESSION['old_input_kelas']);
    header("Location: index.php?page=kelola_kelas");
    exit();
} else {
    $_SESSION['error_message_form'] = "Gagal menambahkan kelas baru: " . $stmt_insert->error;
    $stmt_insert->close();
    header("Location: index.php?page=tambah_kelas");
    exit();
}
?>