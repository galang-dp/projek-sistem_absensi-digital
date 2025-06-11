<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['message'] = "<div class='info-message error'>Anda tidak memiliki akses untuk tindakan ini.</div>";
    header("Location: ../login.php");
    exit();
}

// 2. Ambil ID siswa dari parameter GET dan pastikan valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $siswa_id_to_deactivate = (int)$_GET['id'];

    // Ambil nama siswa untuk pesan feedback
    $nama_siswa_info = "Siswa dengan ID " . $siswa_id_to_deactivate;
    $stmt_get_nama = $conn->prepare("SELECT nama_lengkap FROM Siswa WHERE siswa_id = ?");
    if ($stmt_get_nama) {
        $stmt_get_nama->bind_param("i", $siswa_id_to_deactivate);
        $stmt_get_nama->execute();
        $result_get_nama = $stmt_get_nama->get_result();
        if ($result_get_nama->num_rows === 1) {
            $siswa_data = $result_get_nama->fetch_assoc();
            $nama_siswa_info = $siswa_data['nama_lengkap'];
        }
        $stmt_get_nama->close();
    }

    // 3. Lakukan "Soft Delete" dengan mengubah status_aktif menjadi 0 (Non-Aktif)
    $stmt_deactivate_siswa = $conn->prepare("UPDATE Siswa SET status_aktif = 0 WHERE siswa_id = ?");
    if (!$stmt_deactivate_siswa) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan statement untuk menonaktifkan siswa: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_siswa.php");
        exit();
    }
    $stmt_deactivate_siswa->bind_param("i", $siswa_id_to_deactivate);

    if ($stmt_deactivate_siswa->execute()) {
        if ($stmt_deactivate_siswa->affected_rows > 0) {
            $_SESSION['message'] = "<div class='info-message success'>Siswa '" . htmlspecialchars($nama_siswa_info) . "' berhasil dinonaktifkan (dihapus dari daftar aktif).</div>";
        } else {
            // Mungkin siswa sudah non-aktif atau ID tidak ditemukan
            $_SESSION['message'] = "<div class='info-message warning'>Tidak ada perubahan status untuk siswa '" . htmlspecialchars($nama_siswa_info) . "' (mungkin sudah non-aktif atau ID tidak ditemukan).</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menonaktifkan siswa: " . htmlspecialchars($stmt_deactivate_siswa->error) . "</div>";
    }
    $stmt_deactivate_siswa->close();

} else {
    // Jika ID tidak valid atau tidak disediakan
    $_SESSION['message'] = "<div class='info-message error'>ID Siswa tidak valid atau tidak disediakan untuk dihapus.</div>";
}

$conn->close();
header("Location: index.php?page=kelola_siswa");
exit();
?>