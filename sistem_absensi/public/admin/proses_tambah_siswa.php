<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    // Jika tidak punya akses, arahkan ke login
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk tindakan ini.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tambah_siswa'])) {

    // Simpan semua input ke session untuk 'sticky form' jika terjadi error
    $_SESSION['old_input_siswa'] = $_POST;

    // Ambil data dari form dan sanitasi dasar
    // Informasi Siswa
    $nis = trim($_POST['nis']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $tanggal_lahir = !empty($_POST['tanggal_lahir']) ? trim($_POST['tanggal_lahir']) : null;
    $jenis_kelamin = !empty($_POST['jenis_kelamin']) ? trim($_POST['jenis_kelamin']) : null;
    $alamat = !empty($_POST['alamat']) ? trim($_POST['alamat']) : null;
    $kelas_id = !empty($_POST['kelas_id']) ? (int)$_POST['kelas_id'] : null;
    $fingerprint_template_1 = !empty($_POST['fingerprint_template_1']) ? trim($_POST['fingerprint_template_1']) : null;

    // Informasi Orang Tua
    $nama_ayah = !empty($_POST['nama_ayah']) ? trim($_POST['nama_ayah']) : null;
    $pekerjaan_ayah = !empty($_POST['pekerjaan_ayah']) ? trim($_POST['pekerjaan_ayah']) : null;
    $nama_ibu = !empty($_POST['nama_ibu']) ? trim($_POST['nama_ibu']) : null;
    $pekerjaan_ibu = !empty($_POST['pekerjaan_ibu']) ? trim($_POST['pekerjaan_ibu']) : null;
    $telepon_ortu = !empty($_POST['telepon_ortu']) ? trim($_POST['telepon_ortu']) : null;
    
    // Validasi sederhana
    if (empty($nis) || empty($nama_lengkap) || empty($kelas_id)) {
        $_SESSION['error_message_form'] = "NIS, Nama Lengkap, dan Kelas wajib diisi.";
        header("Location: index.php?page=tambah_siswa");
        exit();
    }
    
    if (empty($telepon_ortu)) {
        $_SESSION['error_message_form'] = "Nomor Telepon Orang Tua wajib diisi untuk keperluan notifikasi.";
        header("Location: index.php?page=tambah_siswa");
        exit();
    }

    // Cek keunikan NIS di tabel Siswa
    $stmt_check_nis = $conn->prepare("SELECT siswa_id FROM Siswa WHERE nis = ?");
    $stmt_check_nis->bind_param("s", $nis);
    $stmt_check_nis->execute();
    $result_check_nis = $stmt_check_nis->get_result();
    if ($result_check_nis->num_rows > 0) {
        $_SESSION['error_message_form'] = "NIS '" . htmlspecialchars($nis) . "' sudah digunakan. Silakan gunakan NIS lain.";
        $stmt_check_nis->close();
        header("Location: index.php?page=tambah_siswa");
        exit();
    }
    $stmt_check_nis->close();

    // Handle Upload Foto Siswa (jika ada)
    $foto_siswa_path_db = null;
    if (isset($_FILES['foto_siswa']) && $_FILES['foto_siswa']['error'] == 0 && $_FILES['foto_siswa']['size'] > 0) {
        $target_dir_relative = "public/uploads/foto_siswa/";
        $target_dir_absolute = __DIR__ . "/../../uploads/foto_siswa/";

        if (!is_dir($target_dir_absolute)) {
            mkdir($target_dir_absolute, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES["foto_siswa"]["name"], PATHINFO_EXTENSION));
        $foto_siswa_filename = "siswa_" . str_replace(' ', '_', $nis) . "_" . time() . "." . $file_extension;
        $target_file_absolute = $target_dir_absolute . $foto_siswa_filename;
        $foto_siswa_path_db = $target_dir_relative . $foto_siswa_filename;

        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        if (!in_array($file_extension, $allowed_types) || $_FILES["foto_siswa"]["size"] > 5000000) {
            $_SESSION['error_message_form'] = "Format foto tidak valid atau ukuran terlalu besar (Maks 5MB).";
            header("Location: index.php?page=tambah_siswa");
            exit();
        }

        if (!move_uploaded_file($_FILES["foto_siswa"]["tmp_name"], $target_file_absolute)) {
            $_SESSION['error_message_form'] = "Gagal mengupload foto.";
            header("Location: index.php?page=tambah_siswa");
            exit();
        }
    }


    // Query INSERT baru sesuai struktur tabel Siswa yang telah diubah
    $sql_insert_siswa = "INSERT INTO Siswa (
                            nis, nama_lengkap, kelas_id, tanggal_lahir, jenis_kelamin, alamat,
                            fingerprint_template_1, foto_siswa,
                            nama_ayah, pekerjaan_ayah, nama_ibu, pekerjaan_ibu, telepon_ortu
                        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_insert_siswa = $conn->prepare($sql_insert_siswa);
    if (!$stmt_insert_siswa) {
        $_SESSION['error_message_form'] = "Gagal mempersiapkan statement: " . $conn->error;
        header("Location: index.php?page=tambah_siswa");
        exit();
    }

    // Bind parameter sesuai dengan kolom baru dan tipe datanya
    $stmt_insert_siswa->bind_param(
        "ssissssssssss", // s: string, i: integer
        $nis,
        $nama_lengkap,
        $kelas_id,
        $tanggal_lahir,
        $jenis_kelamin,
        $alamat,
        $fingerprint_template_1,
        $foto_siswa_path_db,
        $nama_ayah,
        $pekerjaan_ayah,
        $nama_ibu,
        $pekerjaan_ibu,
        $telepon_ortu
    );

    if ($stmt_insert_siswa->execute()) {
        // Hapus input lama dari session jika berhasil
        unset($_SESSION['old_input_siswa']); 
        // Set pesan sukses untuk ditampilkan di halaman kelola siswa
        $_SESSION['message'] = "<div class='info-message success'>Data siswa baru '" . htmlspecialchars($nama_lengkap) . "' berhasil ditambahkan.</div>";
        header("Location: index.php?page=kelola_siswa"); // Arahkan ke halaman kelola siswa
        exit();
    } else {
        $_SESSION['error_message_form'] = "Gagal menambahkan data siswa ke database: " . $stmt_insert_siswa->error;
        $stmt_insert_siswa->close();
        header("Location: index.php?page=tambah_siswa");
        exit();
    }

} else {
    // Jika akses tidak sah
    $_SESSION['message'] = "<div class='info-message error'>Aksi tidak valid.</div>";
    header("Location: index.php?page=kelola_siswa");
    exit();
}

$conn->close();
?>