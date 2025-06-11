<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['message'] = "<div class='info-message error'>Anda tidak memiliki akses untuk tindakan ini.</div>";
    header("Location: ../login.php");
    exit();
}

// 2. Ambil ID guru dari parameter GET dan pastikan valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $guru_id_to_delete = (int)$_GET['id'];
    $user_id_guru = null; // Untuk menyimpan user_id terkait guru

    // Ambil nama guru dan user_id untuk pesan feedback dan penghapusan user
    $nama_guru_dihapus = "Guru dengan ID " . $guru_id_to_delete;
    $stmt_get_info = $conn->prepare("SELECT nama_lengkap, user_id FROM Guru WHERE guru_id = ?");
    if ($stmt_get_info) {
        $stmt_get_info->bind_param("i", $guru_id_to_delete);
        $stmt_get_info->execute();
        $result_get_info = $stmt_get_info->get_result();
        if ($result_get_info->num_rows === 1) {
            $guru_data = $result_get_info->fetch_assoc();
            $nama_guru_dihapus = $guru_data['nama_lengkap'];
            $user_id_guru = $guru_data['user_id'];
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data guru tidak ditemukan untuk dihapus.</div>";
            header("Location: kelola_guru.php");
            exit();
        }
        $stmt_get_info->close();
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query info guru: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_guru.php");
        exit();
    }

    // 3. SEBELUM MENGHAPUS GURU, CEK APAKAH GURU INI MASIH MENJADI WALI KELAS
    $stmt_check_wali = $conn->prepare("SELECT COUNT(*) as jumlah_kelas_wali FROM Kelas WHERE wali_kelas_id = ?");
    if (!$stmt_check_wali) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query pengecekan wali kelas: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_guru.php");
        exit();
    }
    $stmt_check_wali->bind_param("i", $guru_id_to_delete);
    $stmt_check_wali->execute();
    $result_check_wali = $stmt_check_wali->get_result();
    $row_check_wali = $result_check_wali->fetch_assoc();
    $jumlah_kelas_diampu = $row_check_wali['jumlah_kelas_wali'];
    $stmt_check_wali->close();

    if ($jumlah_kelas_diampu > 0) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus guru '" . htmlspecialchars($nama_guru_dihapus) . "'. Guru ini masih terdaftar sebagai wali kelas untuk " . $jumlah_kelas_diampu . " kelas. Harap ubah wali kelas tersebut terlebih dahulu.</div>";
        header("Location: kelola_guru.php");
        exit();
    }

    // 4. Jika tidak ada ketergantungan, lanjutkan proses penghapusan
    // Idealnya menggunakan transaksi database di sini
    // $conn->begin_transaction();

    // Hapus dari tabel Guru terlebih dahulu
    $stmt_delete_guru = $conn->prepare("DELETE FROM Guru WHERE guru_id = ?");
    if (!$stmt_delete_guru) {
        // $conn->rollback();
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan statement penghapusan data guru: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_guru.php");
        exit();
    }
    $stmt_delete_guru->bind_param("i", $guru_id_to_delete);

    if ($stmt_delete_guru->execute()) {
        if ($stmt_delete_guru->affected_rows > 0) {
            // Jika berhasil hapus dari tabel Guru, hapus juga dari tabel Users
            if ($user_id_guru !== null) {
                $stmt_delete_user = $conn->prepare("DELETE FROM Users WHERE user_id = ?");
                if (!$stmt_delete_user) {
                    // $conn->rollback();
                    // Ini kondisi rumit, guru terhapus tapi user loginnya tidak.
                    // Sebaiknya ada mekanisme logging atau notifikasi admin.
                    $_SESSION['message'] = "<div class='info-message warning'>Data detail guru '" . htmlspecialchars($nama_guru_dihapus) . "' berhasil dihapus, tetapi gagal mempersiapkan penghapusan akun login terkait: " . htmlspecialchars($conn->error) . ". Harap periksa manual.</div>";
                    header("Location: kelola_guru.php");
                    exit();
                }
                $stmt_delete_user->bind_param("i", $user_id_guru);
                if ($stmt_delete_user->execute()) {
                    if ($stmt_delete_user->affected_rows > 0) {
                        // $conn->commit();
                        $_SESSION['message'] = "<div class='info-message success'>Data guru '" . htmlspecialchars($nama_guru_dihapus) . "' dan akun login terkait berhasil dihapus.</div>";
                    } else {
                        // $conn->rollback();
                        $_SESSION['message'] = "<div class='info-message warning'>Data detail guru '" . htmlspecialchars($nama_guru_dihapus) . "' berhasil dihapus, tetapi akun login tidak ditemukan atau tidak terhapus.</div>";
                    }
                } else {
                    // $conn->rollback();
                    $_SESSION['message'] = "<div class='info-message error'>Data detail guru '" . htmlspecialchars($nama_guru_dihapus) . "' berhasil dihapus, tetapi gagal menghapus akun login terkait: " . htmlspecialchars($stmt_delete_user->error) . "</div>";
                }
                $stmt_delete_user->close();
            } else {
                // $conn->commit(); // Guru terhapus, tapi tidak ada user_id terkait (kasus aneh)
                $_SESSION['message'] = "<div class='info-message success'>Data detail guru '" . htmlspecialchars($nama_guru_dihapus) . "' berhasil dihapus (tidak ada akun login terkait yang ditemukan).</div>";
            }
        } else {
            // $conn->rollback(); // Tidak ada yang di-commit jika guru tidak terhapus
            $_SESSION['message'] = "<div class='info-message error'>Tidak ada data guru yang dihapus (mungkin ID tidak ditemukan).</div>";
        }
    } else {
        // $conn->rollback();
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus data detail guru: " . htmlspecialchars($stmt_delete_guru->error) . "</div>";
    }
    $stmt_delete_guru->close();

} else {
    // Jika ID tidak valid atau tidak disediakan
    $_SESSION['message'] = "<div class='info-message error'>ID Guru tidak valid atau tidak disediakan untuk dihapus.</div>";
}

$conn->close();
header("Location: index.php?page=kelola_guru");
exit();
?>