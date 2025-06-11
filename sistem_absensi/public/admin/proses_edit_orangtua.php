<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk tindakan ini.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_edit_orangtua'])) {

    // Ambil data dari form dan sanitasi dasar
    $orang_tua_id = isset($_POST['orang_tua_id']) ? (int)$_POST['orang_tua_id'] : 0;
    $nama_orang_tua = trim($_POST['nama_orang_tua']);
    $nomor_telepon_notifikasi = trim($_POST['nomor_telepon_notifikasi']);
    $email_notifikasi = trim($_POST['email_notifikasi']);
    $alamat = trim($_POST['alamat']);

    // Validasi dasar
    if (empty($orang_tua_id)) {
        $_SESSION['message'] = "<div class='info-message error'>ID Orang Tua tidak valid untuk diedit.</div>";
        header("Location: kelola_orangtua.php");
        exit();
    }

    if (empty($nama_orang_tua) || empty($nomor_telepon_notifikasi)) {
        $_SESSION['error_message'] = "Nama Orang Tua/Wali dan Nomor Telepon Notifikasi wajib diisi.";
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }

    // Validasi nomor telepon
    if (!preg_match("/^[0-9\-\+\s\(\)]*$/", $nomor_telepon_notifikasi)) {
        $_SESSION['error_message'] = "Format Nomor Telepon Notifikasi tidak valid.";
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }
    
    // Validasi email jika diisi
    if (!empty($email_notifikasi) && !filter_var($email_notifikasi, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message'] = "Format Email Notifikasi tidak valid.";
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }

    // Cek keunikan nomor_telepon_notifikasi (jika diubah)
    $stmt_check_telepon = $conn->prepare("SELECT orang_tua_id FROM OrangTua WHERE nomor_telepon_notifikasi = ? AND orang_tua_id != ?");
    if (!$stmt_check_telepon) {
        $_SESSION['error_message'] = "Gagal mempersiapkan statement pengecekan telepon: " . $conn->error;
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }
    $stmt_check_telepon->bind_param("si", $nomor_telepon_notifikasi, $orang_tua_id);
    $stmt_check_telepon->execute();
    $result_check_telepon = $stmt_check_telepon->get_result();
    if ($result_check_telepon->num_rows > 0) {
        $_SESSION['error_message'] = "Nomor Telepon Notifikasi '" . htmlspecialchars($nomor_telepon_notifikasi) . "' sudah terdaftar untuk orang tua lain.";
        $stmt_check_telepon->close();
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }
    $stmt_check_telepon->close();

    // Cek keunikan email_notifikasi jika diisi (dan jika diubah)
    if (!empty($email_notifikasi)) {
        $stmt_check_email = $conn->prepare("SELECT orang_tua_id FROM OrangTua WHERE email_notifikasi = ? AND orang_tua_id != ?");
        if (!$stmt_check_email) {
            $_SESSION['error_message'] = "Gagal mempersiapkan statement pengecekan email: " . $conn->error;
            header("Location: edit_orangtua.php?id=" . $orang_tua_id);
            exit();
        }
        $stmt_check_email->bind_param("si", $email_notifikasi, $orang_tua_id);
        $stmt_check_email->execute();
        $result_check_email = $stmt_check_email->get_result();
        if ($result_check_email->num_rows > 0) {
            $_SESSION['error_message'] = "Email Notifikasi '" . htmlspecialchars($email_notifikasi) . "' sudah terdaftar untuk orang tua lain.";
            $stmt_check_email->close();
            header("Location: edit_orangtua.php?id=" . $orang_tua_id);
            exit();
        }
        $stmt_check_email->close();
    }

    // Siapkan data untuk update (kolom opsional bisa NULL jika kosong)
    $email_db = !empty($email_notifikasi) ? $email_notifikasi : null;
    $alamat_db = !empty($alamat) ? $alamat : null;

    // Update data di tabel OrangTua
    $stmt_update_orangtua = $conn->prepare("UPDATE OrangTua SET nama_orang_tua = ?, nomor_telepon_notifikasi = ?, email_notifikasi = ?, alamat = ? WHERE orang_tua_id = ?");
    if (!$stmt_update_orangtua) {
        $_SESSION['error_message'] = "Gagal mempersiapkan statement update data orang tua: " . $conn->error;
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }
    $stmt_update_orangtua->bind_param("ssssi", $nama_orang_tua, $nomor_telepon_notifikasi, $email_db, $alamat_db, $orang_tua_id);

    if ($stmt_update_orangtua->execute()) {
        $_SESSION['success_message'] = "Data Orang Tua/Wali '" . htmlspecialchars($nama_orang_tua) . "' berhasil diperbarui.";
        header("Location: edit_orangtua.php?id=" . $orang_tua_id); // Kembali ke halaman edit
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui data orang tua: " . $stmt_update_orangtua->error;
        $stmt_update_orangtua->close();
        header("Location: edit_orangtua.php?id=" . $orang_tua_id);
        exit();
    }

} else {
    // Jika bukan metode POST atau tombol submit tidak ditekan
    $_SESSION['message'] = "<div class='info-message error'>Aksi tidak valid.</div>";
    header("Location: kelola_orangtua.php"); // Arahkan ke halaman kelola jika akses tidak benar
    exit();
}

$conn->close();
?>