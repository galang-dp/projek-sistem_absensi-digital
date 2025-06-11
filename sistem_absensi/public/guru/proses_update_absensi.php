<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan guru, dan metodenya adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Guru') {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk tindakan ini.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_absensi'])) {

    // Ambil data umum dari form
    $tanggal_absensi = $_POST['tanggal_absensi'];
    $guru_id = (int)$_POST['guru_id'];
    
    // Ambil data array dari form
    $siswa_ids = $_POST['siswa_id'];
    $absensi_ids = $_POST['absensi_id'];
    $status_kehadiran_list = $_POST['status_kehadiran'];
    $keterangan_list = $_POST['keterangan'];

    // Validasi dasar
    if (empty($tanggal_absensi) || empty($guru_id) || empty($siswa_ids)) {
        // Kembali ke halaman edit absensi dengan pesan error
        header("Location: index.php?page=edit_absensi&status=gagal&pesan=" . urlencode("Data tidak lengkap, gagal memproses."));
        exit();
    }

    // Siapkan statement SQL di luar loop untuk efisiensi
    $sql_update = "UPDATE Absensi SET status_kehadiran = ?, keterangan = ?, diedit_oleh_guru_id = ? WHERE absensi_id = ?";
    $stmt_update = $conn->prepare($sql_update);
    if (!$stmt_update) {
        die("Error saat mempersiapkan UPDATE: " . $conn->error);
    }
    
    $sql_insert = "INSERT INTO Absensi (siswa_id, tanggal_absensi, status_kehadiran, keterangan, diedit_oleh_guru_id) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    if (!$stmt_insert) {
        die("Error saat mempersiapkan INSERT: " . $conn->error);
    }


    // Mulai transaksi database untuk memastikan semua data disimpan atau tidak sama sekali
    $conn->begin_transaction();
    try {
        // Loop melalui setiap siswa yang datanya dikirim dari form
        foreach ($siswa_ids as $siswa_id) {
            $siswa_id = (int)$siswa_id;
            // Ambil data spesifik untuk siswa saat ini
            $absensi_id = isset($absensi_ids[$siswa_id]) && !empty($absensi_ids[$siswa_id]) ? (int)$absensi_ids[$siswa_id] : 0;
            $status = $status_kehadiran_list[$siswa_id];
            $keterangan = trim($keterangan_list[$siswa_id]);

            // Logika: Jangan buat record baru jika statusnya "Belum Absen"
            if ($absensi_id == 0 && $status == 'Belum Absen') {
                continue; // Lanjut ke siswa berikutnya, tidak ada yang perlu disimpan
            }

            if ($absensi_id > 0) {
                // Jika sudah ada record absensi (misalnya dari scan), lakukan UPDATE
                $stmt_update->bind_param("ssii", $status, $keterangan, $guru_id, $absensi_id);
                if (!$stmt_update->execute()) {
                    // Jika eksekusi gagal, lemparkan exception untuk memicu rollback
                    throw new Exception($stmt_update->error);
                }
            } else {
                // Jika belum ada record absensi, lakukan INSERT
                $stmt_insert->bind_param("isssi", $siswa_id, $tanggal_absensi, $status, $keterangan, $guru_id);
                if (!$stmt_insert->execute()) {
                    // Jika eksekusi gagal, lemparkan exception
                    throw new Exception($stmt_insert->error);
                }
            }
        }

        // Jika semua query di dalam loop berhasil, commit transaksi untuk menyimpan permanen
        $conn->commit();
        $success = true;

    } catch (Exception $e) {
        // Jika ada satu saja error, batalkan semua perubahan yang sudah dilakukan
        $conn->rollback();
        $success = false;
        $db_error = $e->getMessage();
    }

    // Tutup statement
    $stmt_update->close();
    $stmt_insert->close();
    $conn->close();

    // === PERUBAHAN UTAMA: REDIRECT KE LAYOUT SIDEBAR ===
    // Redirect kembali ke halaman edit absensi dengan pesan status
    if ($success) {
        header("Location: index.php?page=edit_absensi&status=sukses");
    } else {
        header("Location: index.php?page=edit_absensi&status=gagal&pesan=" . urlencode($db_error ?? 'Terjadi kesalahan pada database.'));
    }
    exit();

} else {
    // Jika akses tidak sah
    header("Location: index.php?page=dashboard_main");
    exit();
}
?>