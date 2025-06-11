<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    $_SESSION['error_message'] = "Anda tidak memiliki akses untuk tindakan ini.";
    header("Location: ../login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tambah_guru'])) {

    // Ambil data dari form dan sanitasi dasar
    $nip = trim($_POST['nip']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $email = trim($_POST['email']);
    $nomor_telepon = trim($_POST['nomor_telepon']);
    $alamat = trim($_POST['alamat']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $konfirmasi_password = $_POST['konfirmasi_password'];

    // Simpan input ke session untuk 'sticky form' jika ada error
    $_SESSION['old_input_guru'] = $_POST; // DIUBAH: Nama session lebih spesifik

    // Validasi sederhana
    if (empty($nama_lengkap) || empty($username) || empty($password) || empty($konfirmasi_password)) {
        $_SESSION['error_message_form'] = "Nama lengkap, username, password, dan konfirmasi password wajib diisi."; // DIUBAH: Nama session lebih spesifik
        header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }

    if (strlen($password) < 6) {
        $_SESSION['error_message_form'] = "Password minimal harus 6 karakter.";
        header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }

    if ($password !== $konfirmasi_password) {
        $_SESSION['error_message_form'] = "Password dan konfirmasi password tidak cocok.";
        header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }

    if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error_message_form'] = "Format email tidak valid.";
        header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }

    // Cek keunikan username di tabel Users
    $stmt_check_user = $conn->prepare("SELECT user_id FROM Users WHERE username = ?");
    $stmt_check_user->bind_param("s", $username);
    $stmt_check_user->execute();
    if ($stmt_check_user->get_result()->num_rows > 0) {
        $_SESSION['error_message_form'] = "Username sudah digunakan. Silakan pilih username lain.";
        $stmt_check_user->close();
        header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }
    $stmt_check_user->close();

    // Cek keunikan NIP di tabel Guru (jika NIP diisi)
    if (!empty($nip)) {
        $stmt_check_nip = $conn->prepare("SELECT guru_id FROM Guru WHERE nip = ?");
        $stmt_check_nip->bind_param("s", $nip);
        $stmt_check_nip->execute();
        if ($stmt_check_nip->get_result()->num_rows > 0) {
            $_SESSION['error_message_form'] = "NIP sudah terdaftar untuk guru lain.";
            $stmt_check_nip->close();
            header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
            exit();
        }
        $stmt_check_nip->close();
    }
    
    // Cek keunikan Email di tabel Guru (jika Email diisi)
    if (!empty($email)) {
        $stmt_check_email_guru = $conn->prepare("SELECT guru_id FROM Guru WHERE email = ?");
        $stmt_check_email_guru->bind_param("s", $email);
        $stmt_check_email_guru->execute();
        if ($stmt_check_email_guru->get_result()->num_rows > 0) {
            $_SESSION['error_message_form'] = "Email sudah terdaftar untuk guru lain.";
            $stmt_check_email_guru->close();
            header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
            exit();
        }
        $stmt_check_email_guru->close();
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $role = 'Guru';

    // (Idealnya gunakan transaksi di sini untuk konsistensi data)

    // Insert ke tabel Users
    $stmt_insert_user = $conn->prepare("INSERT INTO Users (username, password_hash, role) VALUES (?, ?, ?)");
    $stmt_insert_user->bind_param("sss", $username, $password_hash, $role);

    if ($stmt_insert_user->execute()) {
        $new_user_id = $conn->insert_id;
        $stmt_insert_user->close();

        // Insert ke tabel Guru
        $stmt_insert_guru = $conn->prepare("INSERT INTO Guru (user_id, nip, nama_lengkap, email, nomor_telepon, alamat) VALUES (?, ?, ?, ?, ?, ?)");
        $nip_db = !empty($nip) ? $nip : null;
        $email_db = !empty($email) ? $email : null;
        $nomor_telepon_db = !empty($nomor_telepon) ? $nomor_telepon : null;
        $alamat_db = !empty($alamat) ? $alamat : null;
        
        $stmt_insert_guru->bind_param("isssss", $new_user_id, $nip_db, $nama_lengkap, $email_db, $nomor_telepon_db, $alamat_db);

        if ($stmt_insert_guru->execute()) {
            $_SESSION['message'] = "<div class='info-message success'>Data guru baru '" . htmlspecialchars($nama_lengkap) . "' berhasil ditambahkan.</div>"; // DIUBAH: Nama session umum untuk halaman daftar
            unset($_SESSION['old_input_guru']); // DIUBAH: Sesuaikan dengan nama session yang benar
            header("Location: index.php?page=kelola_guru"); // DIUBAH: Redirect ke layout sidebar
            exit();
        } else {
            // Jika insert ke Guru gagal
            $_SESSION['error_message_form'] = "Gagal menambahkan detail guru: " . $stmt_insert_guru->error; // DIUBAH: Nama session spesifik form
            // (Idealnya user yang baru dibuat di Users juga dihapus di sini untuk konsistensi)
            $stmt_insert_guru->close();
            header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
            exit();
        }
    } else {
        $_SESSION['error_message_form'] = "Gagal membuat akun login untuk guru: " . $stmt_insert_user->error; // DIUBAH: Nama session spesifik form
        $stmt_insert_user->close();
        header("Location: index.php?page=tambah_guru"); // DIUBAH: Redirect ke layout sidebar
        exit();
    }

} else {
    // Jika akses tidak sah
    $_SESSION['message'] = "<div class='info-message error'>Aksi tidak valid.</div>"; // DIUBAH: Nama session umum untuk halaman daftar
    header("Location: index.php?page=kelola_guru"); // DIUBAH: Redirect ke layout sidebar
    exit();
}

$conn->close();
?>