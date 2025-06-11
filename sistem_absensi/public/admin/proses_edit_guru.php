<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin, dan metode adalah POST
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin' || $_SERVER["REQUEST_METHOD"] != "POST" || !isset($_POST['submit_edit_guru'])) {
    $_SESSION['message'] = "<div class='info-message error'>Aksi tidak valid.</div>";
    header("Location: index.php?page=dashboard_main"); // Arahkan ke dashboard jika akses tidak sah
    exit();
}

// Ambil data dari form
$guru_id = isset($_POST['guru_id']) ? (int)$_POST['guru_id'] : 0;
$user_id = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;
$nip = trim($_POST['nip']);
$nama_lengkap = trim($_POST['nama_lengkap']);
$email = trim($_POST['email']);
$nomor_telepon = trim($_POST['nomor_telepon']);
$alamat = trim($_POST['alamat']);
$username = trim($_POST['username']);
$password_baru = $_POST['password_baru'];
$konfirmasi_password_baru = $_POST['konfirmasi_password_baru'];

// Validasi dasar
if (empty($guru_id) || empty($user_id) || empty($nama_lengkap) || empty($username)) {
    $_SESSION['error_message_form'] = "Nama lengkap dan username wajib diisi.";
    header("Location: index.php?page=edit_guru&id=" . $guru_id);
    exit();
}

// Validasi password baru jika diisi
$update_password = false;
if (!empty($password_baru)) {
    if (strlen($password_baru) < 6) {
        $_SESSION['error_message_form'] = "Password baru minimal harus 6 karakter.";
        header("Location: index.php?page=edit_guru&id=" . $guru_id);
        exit();
    }
    if ($password_baru !== $konfirmasi_password_baru) {
        $_SESSION['error_message_form'] = "Password baru dan konfirmasi password baru tidak cocok.";
        header("Location: index.php?page=edit_guru&id=" . $guru_id);
        exit();
    }
    $update_password = true;
    $password_hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
}

// Cek keunikan username (jika diubah) terhadap user lain
$stmt_check_user = $conn->prepare("SELECT user_id FROM Users WHERE username = ? AND user_id != ?");
$stmt_check_user->bind_param("si", $username, $user_id);
$stmt_check_user->execute();
if ($stmt_check_user->get_result()->num_rows > 0) {
    $_SESSION['error_message_form'] = "Username '" . htmlspecialchars($username) . "' sudah digunakan oleh user lain.";
    $stmt_check_user->close();
    header("Location: index.php?page=edit_guru&id=" . $guru_id);
    exit();
}
$stmt_check_user->close();

// ... (Tambahkan pengecekan keunikan NIP dan Email jika perlu, mirip dengan di atas) ...

// Mulai proses update database
// (Idealnya menggunakan transaksi di sini)

// Update tabel Users
$sql_update_user = "UPDATE Users SET username = ?";
$types_user = "s";
$params_user = [$username];

if ($update_password) {
    $sql_update_user .= ", password_hash = ?";
    $types_user .= "s";
    $params_user[] = $password_hash_baru;
}
$sql_update_user .= " WHERE user_id = ?";
$types_user .= "i";
$params_user[] = $user_id;

$stmt_update_user = $conn->prepare($sql_update_user);
$stmt_update_user->bind_param($types_user, ...$params_user);

if ($stmt_update_user->execute()) {
    $stmt_update_user->close();

    // Update tabel Guru
    $nip_db = !empty($nip) ? $nip : null;
    $email_db = !empty($email) ? $email : null;
    $nomor_telepon_db = !empty($nomor_telepon) ? $nomor_telepon : null;
    $alamat_db = !empty($alamat) ? $alamat : null;

    $stmt_update_guru = $conn->prepare("UPDATE Guru SET nip = ?, nama_lengkap = ?, email = ?, nomor_telepon = ?, alamat = ? WHERE guru_id = ?");
    $stmt_update_guru->bind_param("sssssi", $nip_db, $nama_lengkap, $email_db, $nomor_telepon_db, $alamat_db, $guru_id);

    if ($stmt_update_guru->execute()) {
        $_SESSION['success_message_form'] = "Data guru '" . htmlspecialchars($nama_lengkap) . "' berhasil diperbarui.";
    } else {
        $_SESSION['error_message_form'] = "Gagal memperbarui detail guru: " . $stmt_update_guru->error;
    }
    $stmt_update_guru->close();
} else {
    $_SESSION['error_message_form'] = "Gagal memperbarui data login guru: " . $stmt_update_user->error;
}

$conn->close();

// Redirect kembali ke halaman edit dengan pesan status
header("Location: index.php?page=edit_guru&id=" . $guru_id);
exit();
?>