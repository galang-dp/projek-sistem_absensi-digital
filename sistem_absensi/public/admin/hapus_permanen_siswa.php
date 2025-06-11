<?php
session_start();
require_once '../../config/db_connect.php';

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['message'] = "<div class='info-message error'>Anda tidak memiliki akses untuk tindakan ini.</div>";
    header("Location: ../login.php");
    exit();
}

// 2. Ambil ID siswa dari parameter GET dan pastikan valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $siswa_id_to_delete = (int)$_GET['id'];

    // 3. SEBELUM MENGHAPUS PERMANEN, CEK APAKAH ADA RIWAYAT ABSENSI
    $stmt_check_absensi = $conn->prepare("SELECT COUNT(*) as jumlah_absensi FROM Absensi WHERE siswa_id = ?");
    $stmt_check_absensi->bind_param("i", $siswa_id_to_delete);
    $stmt_check_absensi->execute();
    $jumlah_absensi = $stmt_check_absensi->get_result()->fetch_assoc()['jumlah_absensi'];
    $stmt_check_absensi->close();

    if ($jumlah_absensi > 0) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus permanen. Siswa ini memiliki " . $jumlah_absensi . " riwayat absensi. Harap nonaktifkan saja siswa ini untuk menjaga riwayat data.</div>";
        header("Location: index.php?page=kelola_siswa");
        exit();
    }

    // 4. Jika tidak ada riwayat absensi, lanjutkan proses penghapusan permanen
    // Ambil info siswa (nama dan foto) untuk dihapus
    $nama_siswa_dihapus = "Siswa dengan ID " . $siswa_id_to_delete;
    $foto_siswa_path = null;
    $stmt_get_info = $conn->prepare("SELECT nama_lengkap, foto_siswa FROM Siswa WHERE siswa_id = ?");
    if ($stmt_get_info) {
        $stmt_get_info->bind_param("i", $siswa_id_to_delete);
        $stmt_get_info->execute();
        $result_get_info = $stmt_get_info->get_result();
        if ($result_get_info->num_rows === 1) {
            $siswa_data = $result_get_info->fetch_assoc();
            $nama_siswa_dihapus = $siswa_data['nama_lengkap'];
            $foto_siswa_path = $siswa_data['foto_siswa'];
        }
        $stmt_get_info->close();
    }

    // Lakukan penghapusan dari database
    $stmt_delete_siswa = $conn->prepare("DELETE FROM Siswa WHERE siswa_id = ?");
    $stmt_delete_siswa->bind_param("i", $siswa_id_to_delete);

    if ($stmt_delete_siswa->execute()) {
        if ($stmt_delete_siswa->affected_rows > 0) {
            // Jika berhasil hapus dari DB, hapus juga file fotonya jika ada
            if (!empty($foto_siswa_path) && file_exists(__DIR__ . "/../../" . $foto_siswa_path)) {
                unlink(__DIR__ . "/../../" . $foto_siswa_path);
            }
            $_SESSION['message'] = "<div class='info-message success'>Data siswa '" . htmlspecialchars($nama_siswa_dihapus) . "' telah berhasil dihapus secara permanen.</div>";
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Tidak ada siswa yang dihapus (mungkin ID tidak ditemukan).</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus data siswa: " . htmlspecialchars($stmt_delete_siswa->error) . "</div>";
    }
    $stmt_delete_siswa->close();

} else {
    // Jika ID tidak valid atau tidak disediakan
    $_SESSION['message'] = "<div class='info-message error'>ID Siswa tidak valid atau tidak disediakan untuk dihapus.</div>";
}

$conn->close();
header("Location: index.php?page=kelola_siswa"); // Redirect kembali ke halaman kelola siswa
exit();
?>