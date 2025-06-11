<?php
// File: public/admin/proses_edit_siswa.php
session_start();
require_once '../../config/db_connect.php';

// Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin' || $_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['submit_edit_siswa'])) {
    $_SESSION['error_message'] = "Aksi tidak valid.";
    // Arahkan ke dashboard jika akses tidak sah
    header("Location: index.php?page=dashboard_main");
    exit();
}

// Ambil data dari form
$siswa_id = isset($_POST['siswa_id']) ? (int)$_POST['siswa_id'] : 0;
// Info Siswa
$nis = trim($_POST['nis']);
$nama_lengkap = trim($_POST['nama_lengkap']);
$tanggal_lahir = !empty($_POST['tanggal_lahir']) ? trim($_POST['tanggal_lahir']) : null;
$jenis_kelamin = !empty($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : null;
$alamat = !empty($_POST['alamat']) ? trim($_POST['alamat']) : null;
$kelas_id = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
$fingerprint_template_1 = !empty($_POST['fingerprint_template_1']) ? trim($_POST['fingerprint_template_1']) : null;
$status_aktif = isset($_POST['status_aktif']) ? (int)$_POST['status_aktif'] : 0;
// Info Orang Tua
$nama_ayah = !empty($_POST['nama_ayah']) ? trim($_POST['nama_ayah']) : null;
$pekerjaan_ayah = !empty($_POST['pekerjaan_ayah']) ? trim($_POST['pekerjaan_ayah']) : null;
$nama_ibu = !empty($_POST['nama_ibu']) ? trim($_POST['nama_ibu']) : null;
$pekerjaan_ibu = !empty($_POST['pekerjaan_ibu']) ? trim($_POST['pekerjaan_ibu']) : null;
$telepon_ortu = !empty($_POST['telepon_ortu']) ? trim($_POST['telepon_ortu']) : null;
// Foto
$foto_siswa_lama = $_POST['foto_siswa_lama'];

// Validasi dasar
if (empty($siswa_id) || empty($nis) || empty($nama_lengkap) || empty($kelas_id)) {
    $_SESSION['error_message_form'] = "NIS, Nama Lengkap, dan Kelas wajib diisi.";
    header("Location: index.php?page=edit_siswa&id=" . $siswa_id);
    exit();
}
if (empty($telepon_ortu)) {
    $_SESSION['error_message_form'] = "Nomor Telepon Orang Tua wajib diisi untuk notifikasi.";
    header("Location: index.php?page=edit_siswa&id=" . $siswa_id);
    exit();
}

// Cek keunikan NIS (jika NIS diubah)
$stmt_check_nis = $conn->prepare("SELECT siswa_id FROM Siswa WHERE nis = ? AND siswa_id != ?");
$stmt_check_nis->bind_param("si", $nis, $siswa_id);
$stmt_check_nis->execute();
if ($stmt_check_nis->get_result()->num_rows > 0) {
    $_SESSION['error_message_form'] = "NIS '" . htmlspecialchars($nis) . "' sudah digunakan oleh siswa lain.";
    $stmt_check_nis->close();
    header("Location: index.php?page=edit_siswa&id=" . $siswa_id);
    exit();
}
$stmt_check_nis->close();

// Handle Upload Foto Baru (jika ada)
$foto_siswa_path_db = $foto_siswa_lama; // Defaultnya tetap menggunakan foto lama
if (isset($_FILES['foto_siswa']) && $_FILES['foto_siswa']['error'] == 0 && $_FILES['foto_siswa']['size'] > 0) {
    // Logika upload foto sama seperti di proses_tambah_siswa.php
    $target_dir_relative = "public/uploads/foto_siswa/";
    $target_dir_absolute = __DIR__ . "/../../uploads/foto_siswa/";
    
    // ... (validasi tipe, ukuran, dan pemindahan file) ...
    // ... (jika berhasil, hapus foto lama dan update $foto_siswa_path_db) ...
    $file_extension = strtolower(pathinfo($_FILES["foto_siswa"]["name"], PATHINFO_EXTENSION));
    $foto_siswa_filename = "siswa_" . str_replace(' ', '_', $nis) . "_" . time() . "." . $file_extension;
    $target_file_absolute = $target_dir_absolute . $foto_siswa_filename;
    
    // Pastikan direktori ada
    if (!is_dir($target_dir_absolute)) { mkdir($target_dir_absolute, 0777, true); }

    if (move_uploaded_file($_FILES["foto_siswa"]["tmp_name"], $target_file_absolute)) {
        // Hapus foto lama jika ada dan upload baru berhasil
        if (!empty($foto_siswa_lama) && file_exists(__DIR__ . "/../../" . $foto_siswa_lama)) {
            unlink(__DIR__ . "/../../" . $foto_siswa_lama);
        }
        $foto_siswa_path_db = $target_dir_relative . $foto_siswa_filename;
    } else {
        // Jika gagal upload, bisa berikan pesan error atau biarkan menggunakan foto lama
        $_SESSION['error_message_form'] = "Gagal mengupload foto baru, perubahan lain tetap disimpan.";
    }
}

// Query UPDATE dengan kolom-kolom baru
$sql_update_siswa = "UPDATE Siswa SET 
                        nis = ?, nama_lengkap = ?, kelas_id = ?, tanggal_lahir = ?, jenis_kelamin = ?, alamat = ?,
                        fingerprint_template_1 = ?, foto_siswa = ?, status_aktif = ?,
                        nama_ayah = ?, pekerjaan_ayah = ?, nama_ibu = ?, pekerjaan_ibu = ?, telepon_ortu = ?
                    WHERE siswa_id = ?";

$stmt_update_siswa = $conn->prepare($sql_update_siswa);
if (!$stmt_update_siswa) {
    $_SESSION['error_message_form'] = "Gagal mempersiapkan statement update: " . $conn->error;
    header("Location: index.php?page=edit_siswa&id=" . $siswa_id);
    exit();
}

$stmt_update_siswa->bind_param(
    "ssisssssisssssi",
    $nis, $nama_lengkap, $kelas_id, $tanggal_lahir, $jenis_kelamin, $alamat,
    $fingerprint_template_1, $foto_siswa_path_db, $status_aktif,
    $nama_ayah, $pekerjaan_ayah, $nama_ibu, $pekerjaan_ibu, $telepon_ortu,
    $siswa_id
);

if ($stmt_update_siswa->execute()) {
    $_SESSION['success_message_form'] = "Data siswa '" . htmlspecialchars($nama_lengkap) . "' berhasil diperbarui.";
} else {
    $_SESSION['error_message_form'] = "Gagal memperbarui data siswa: " . $stmt_update_siswa->error;
}
$stmt_update_siswa->close();
$conn->close();

// Redirect kembali ke halaman edit
header("Location: index.php?page=edit_siswa&id=" . $siswa_id);
exit();
?>