<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['message'] = "<div class='info-message error'>Anda tidak memiliki akses untuk tindakan ini.</div>";
    header("Location: ../login.php");
    exit();
}

// 2. Ambil ID kelas dari parameter GET dan pastikan valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $kelas_id_to_delete = (int)$_GET['id'];

    // 3. SEBELUM MENGHAPUS KELAS, CEK APAKAH ADA SISWA YANG TERDAFTAR DI KELAS INI
    $stmt_check_siswa = $conn->prepare("SELECT COUNT(*) as jumlah_siswa FROM Siswa WHERE kelas_id = ? AND status_aktif = TRUE");
    if (!$stmt_check_siswa) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query pengecekan siswa: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: index.php?page=kelola_kelas"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }
    $stmt_check_siswa->bind_param("i", $kelas_id_to_delete);
    $stmt_check_siswa->execute();
    $result_check_siswa = $stmt_check_siswa->get_result();
    $row_check_siswa = $result_check_siswa->fetch_assoc();
    $jumlah_siswa_di_kelas = $row_check_siswa['jumlah_siswa'];
    $stmt_check_siswa->close();

    if ($jumlah_siswa_di_kelas > 0) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus kelas. Masih ada " . $jumlah_siswa_di_kelas . " siswa aktif yang terdaftar di kelas ini. Pindahkan siswa tersebut terlebih dahulu.</div>";
        header("Location: index.php?page=kelola_kelas"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }

    // 4. Jika tidak ada siswa, lanjutkan proses penghapusan kelas
    // Ambil nama kelas untuk pesan feedback sebelum dihapus
    $nama_kelas_dihapus = "Kelas dengan ID " . $kelas_id_to_delete; // Default message
    $stmt_get_nama = $conn->prepare("SELECT nama_kelas FROM Kelas WHERE kelas_id = ?");
    if ($stmt_get_nama) {
        $stmt_get_nama->bind_param("i", $kelas_id_to_delete);
        $stmt_get_nama->execute();
        $result_get_nama = $stmt_get_nama->get_result();
        if ($result_get_nama->num_rows === 1) {
            $kelas_data = $result_get_nama->fetch_assoc();
            $nama_kelas_dihapus = $kelas_data['nama_kelas'];
        }
        $stmt_get_nama->close();
    }

    // Lakukan penghapusan
    $stmt_delete_kelas = $conn->prepare("DELETE FROM Kelas WHERE kelas_id = ?");
    if (!$stmt_delete_kelas) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan statement penghapusan kelas: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: index.php?page=kelola_kelas"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }
    $stmt_delete_kelas->bind_param("i", $kelas_id_to_delete);

    if ($stmt_delete_kelas->execute()) {
        if ($stmt_delete_kelas->affected_rows > 0) {
            $_SESSION['message'] = "<div class='info-message success'>Data kelas '" . htmlspecialchars($nama_kelas_dihapus) . "' berhasil dihapus.</div>";
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Tidak ada kelas yang dihapus (mungkin ID tidak ditemukan).</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus data kelas: " . htmlspecialchars($stmt_delete_kelas->error) . ". Pastikan tidak ada data lain yang terkait erat.</div>";
    }
    $stmt_delete_kelas->close();

} else {
    // Jika ID tidak valid atau tidak disediakan
    $_SESSION['message'] = "<div class='info-message error'>ID Kelas tidak valid atau tidak disediakan untuk dihapus.</div>";
}

$conn->close();
header("Location: index.php?page=kelola_kelas"); // DIUBAH: Selalu redirect kembali ke halaman kelola kelas dengan layout sidebar
exit();
?>