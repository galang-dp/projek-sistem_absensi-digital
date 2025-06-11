<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['message'] = "<div class='info-message error'>Anda tidak memiliki akses untuk tindakan ini.</div>";
    header("Location: ../login.php");
    exit();
}

// 2. Ambil ID orang tua dari parameter GET dan pastikan valid
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $orang_tua_id_to_delete = (int)$_GET['id'];

    // 3. SEBELUM MENGHAPUS, CEK APAKAH ADA SISWA YANG TERKAIT DENGAN ORANG TUA INI
    // Kita asumsikan siswa yang statusnya aktif yang menjadi pertimbangan utama
    $stmt_check_siswa = $conn->prepare("SELECT COUNT(*) as jumlah_siswa FROM Siswa WHERE orang_tua_id = ? AND status_aktif = TRUE");
    if (!$stmt_check_siswa) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query pengecekan siswa: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_orangtua.php");
        exit();
    }
    $stmt_check_siswa->bind_param("i", $orang_tua_id_to_delete);
    $stmt_check_siswa->execute();
    $result_check_siswa = $stmt_check_siswa->get_result();
    $row_check_siswa = $result_check_siswa->fetch_assoc();
    $jumlah_siswa_terkait = $row_check_siswa['jumlah_siswa'];
    $stmt_check_siswa->close();

    if ($jumlah_siswa_terkait > 0) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus data orang tua. Masih ada " . $jumlah_siswa_terkait . " siswa aktif yang terkait dengan data orang tua ini. Harap perbarui data siswa tersebut terlebih dahulu.</div>";
        header("Location: kelola_orangtua.php");
        exit();
    }

    // 4. Jika tidak ada siswa terkait, lanjutkan proses penghapusan
    // Ambil nama orang tua untuk pesan feedback sebelum dihapus
    $nama_ortu_dihapus = "Data Orang Tua dengan ID " . $orang_tua_id_to_delete; // Default message
    $stmt_get_nama = $conn->prepare("SELECT nama_orang_tua FROM OrangTua WHERE orang_tua_id = ?");
    if ($stmt_get_nama) {
        $stmt_get_nama->bind_param("i", $orang_tua_id_to_delete);
        $stmt_get_nama->execute();
        $result_get_nama = $stmt_get_nama->get_result();
        if ($result_get_nama->num_rows === 1) {
            $ortu_data = $result_get_nama->fetch_assoc();
            $nama_ortu_dihapus = $ortu_data['nama_orang_tua'];
        }
        $stmt_get_nama->close();
    }

    // Lakukan penghapusan
    // Jika kolom Siswa.orang_tua_id memiliki FOREIGN KEY constraint dengan ON DELETE RESTRICT,
    // query ini akan gagal jika masih ada relasi (meskipun sudah dicek di atas).
    // Jika ON DELETE SET NULL, maka Siswa.orang_tua_id akan menjadi NULL.
    $stmt_delete_ortu = $conn->prepare("DELETE FROM OrangTua WHERE orang_tua_id = ?");
    if (!$stmt_delete_ortu) {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan statement penghapusan data orang tua: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_orangtua.php");
        exit();
    }
    $stmt_delete_ortu->bind_param("i", $orang_tua_id_to_delete);

    if ($stmt_delete_ortu->execute()) {
        if ($stmt_delete_ortu->affected_rows > 0) {
            $_SESSION['message'] = "<div class='info-message success'>Data Orang Tua/Wali '" . htmlspecialchars($nama_ortu_dihapus) . "' berhasil dihapus.</div>";
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Tidak ada data orang tua yang dihapus (mungkin ID tidak ditemukan).</div>";
        }
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal menghapus data orang tua: " . htmlspecialchars($stmt_delete_ortu->error) . ". Pastikan tidak ada data siswa yang terkait erat.</div>";
    }
    $stmt_delete_ortu->close();

} else {
    // Jika ID tidak valid atau tidak disediakan
    $_SESSION['message'] = "<div class='info-message error'>ID Orang Tua tidak valid atau tidak disediakan untuk dihapus.</div>";
}

$conn->close();
header("Location: kelola_orangtua.php"); // Selalu redirect kembali ke halaman kelola orang tua
exit();
?>