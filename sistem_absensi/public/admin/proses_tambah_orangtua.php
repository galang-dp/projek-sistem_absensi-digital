<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk tindakan ini.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tambah_orangtua'])) {

    // Ambil data dari form dan sanitasi dasar
    $nama_orang_tua = trim($_POST['nama_orang_tua']);
    $nomor_telepon_notifikasi = trim($_POST['nomor_telepon_notifikasi']);
    $email_notifikasi = trim($_POST['email_notifikasi']);
    $alamat = trim($_POST['alamat']);

    // Simpan input untuk dikembalikan jika ada error
    $_SESSION['old_input_orangtua'] = $_POST;

    // Validasi sederhana
    if (empty($nama_orang_tua) || empty($nomor_telepon_notifikasi)) {
        $_SESSION['error_message'] = "Nama Orang Tua/Wali dan Nomor Telepon Notifikasi wajib diisi.";
        header("Location: tambah_orangtua.php");
        exit();
    }

    // Validasi nomor telepon (hanya angka, opsional: panjang tertentu)
    if (!preg_match("/^[0-9\-\+\s\(\)]*$/", $nomor_telepon_notifikasi)) {
        $_SESSION['error_message'] = "Format Nomor Telepon Notifikasi tidak valid.";
        header("Location: tambah_orangtua.php");
        exit();
    }
    
    // Validasi email jika diisi
    if (!empty($email_notifikasi) && !filter_var($email_notifikasi, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Format Email Notifikasi tidak valid.";
        header("Location: tambah_orangtua.php");
        exit();
    }

    // Cek keunikan nomor_telepon_notifikasi
    $stmt_check_telepon = $conn->prepare("SELECT orang_tua_id FROM OrangTua WHERE nomor_telepon_notifikasi = ?");
    if (!$stmt_check_telepon) {
        $_SESSION['error_message'] = "Gagal mempersiapkan statement pengecekan telepon: " . $conn->error;
        header("Location: tambah_orangtua.php");
        exit();
    }
    $stmt_check_telepon->bind_param("s", $nomor_telepon_notifikasi);
    $stmt_check_telepon->execute();
    $result_check_telepon = $stmt_check_telepon->get_result();
    if ($result_check_telepon->num_rows > 0) {
        $_SESSION['error_message'] = "Nomor Telepon Notifikasi '" . htmlspecialchars($nomor_telepon_notifikasi) . "' sudah terdaftar.";
        $stmt_check_telepon->close();
        header("Location: tambah_orangtua.php");
        exit();
    }
    $stmt_check_telepon->close();

    // Cek keunikan email_notifikasi jika diisi
    if (!empty($email_notifikasi)) {
        $stmt_check_email = $conn->prepare("SELECT orang_tua_id FROM OrangTua WHERE email_notifikasi = ?");
        if (!$stmt_check_email) {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement pengecekan email: " . $conn->error;
            header("Location: tambah_orangtua.php");
            exit();
        }
        $stmt_check_email->bind_param("s", $email_notifikasi);
        $stmt_check_email->execute();
        $result_check_email = $stmt_check_email->get_result();
        if ($result_check_email->num_rows > 0) {
            $_SESSION['error_message'] = "Email Notifikasi '" . htmlspecialchars($email_notifikasi) . "' sudah terdaftar.";
            $stmt_check_email->close();
            header("Location: tambah_orangtua.php");
            exit();
        }
        $stmt_check_email->close();
    }

    // Siapkan data untuk insert (kolom opsional bisa NULL jika kosong)
    $email_db = !empty($email_notifikasi) ? $email_notifikasi : null;
    $alamat_db = !empty($alamat) ? $alamat : null;

    // Insert ke tabel OrangTua
    $stmt_insert_orangtua = $conn->prepare("INSERT INTO OrangTua (nama_orang_tua, nomor_telepon_notifikasi, email_notifikasi, alamat) VALUES (?, ?, ?, ?)");
    if (!$stmt_insert_orangtua) {
        $_SESSION['error_message'] = "Gagal mempersiapkan statement insert data orang tua: " . $conn->error;
        header("Location: tambah_orangtua.php");
        exit();
    }
    $stmt_insert_orangtua->bind_param("ssss", $nama_orang_tua, $nomor_telepon_notifikasi, $email_db, $alamat_db);

    if ($stmt_insert_orangtua->execute()) {
        $_SESSION['message'] = "<div class='info-message success'>Data Orang Tua/Wali '" . htmlspecialchars($nama_orang_tua) . "' berhasil ditambahkan.</div>";
        unset($_SESSION['old_input_orangtua']); // Hapus input lama jika berhasil
        header("Location: kelola_orangtua.php"); // Arahkan ke halaman kelola
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan data orang tua: " . $stmt_insert_orangtua->error;
        $stmt_insert_orangtua->close();
        header("Location: tambah_orangtua.php");
        exit();
    }

} else {
    // Jika bukan metode POST atau tombol submit tidak ditekan
    $_SESSION['error_message'] = "Aksi tidak valid.";
    header("Location: tambah_orangtua.php");
    exit();
}

$conn->close();
?>