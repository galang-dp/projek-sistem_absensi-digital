<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// Inisialisasi variabel untuk form (jika ada old input atau error)
$nip = '';
$nama_lengkap = '';
$email = '';
$nomor_telepon = '';
$alamat = '';
$username = '';

// Pesan feedback
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message'], $_SESSION['success_message']); // Hapus pesan setelah ditampilkan

// Ambil input lama jika ada (setelah redirect karena error)
if (isset($_SESSION['old_input'])) {
    $old_input = $_SESSION['old_input'];
    $nip = htmlspecialchars($old_input['nip'] ?? '');
    $nama_lengkap = htmlspecialchars($old_input['nama_lengkap'] ?? '');
    $email = htmlspecialchars($old_input['email'] ?? '');
    $nomor_telepon = htmlspecialchars($old_input['nomor_telepon'] ?? '');
    $alamat = htmlspecialchars($old_input['alamat'] ?? '');
    $username = htmlspecialchars($old_input['username'] ?? '');
    unset($_SESSION['old_input']);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Guru Baru - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Tambah Data Guru Baru</h1>
            <p><a href="kelola_guru.php" class="admin-link">&laquo; Kembali ke Kelola Guru</a></p>
        </div>

        <div class="form-container" style="max-width: 600px; margin-left:auto; margin-right:auto;"> <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_tambah_guru.php" method="POST">
                <h2>Informasi Detail Guru</h2>
                <div>
                    <label for="nip">NIP (Nomor Induk Pegawai):</label>
                    <input type="text" id="nip" name="nip" value="<?php echo $nip; ?>" placeholder="Opsional">
                </div>
                <div>
                    <label for="nama_lengkap">Nama Lengkap Guru:</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $nama_lengkap; ?>" required>
                </div>
                <div>
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>" placeholder="Opsional, contoh: guru@sekolah.com">
                </div>
                <div>
                    <label for="nomor_telepon">Nomor Telepon:</label>
                    <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?php echo $nomor_telepon; ?>" placeholder="Opsional, contoh: 08123456789">
                </div>
                <div>
                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" placeholder="Opsional"><?php echo $alamat; ?></textarea>
                </div>

                <h2 style="margin-top: 30px;">Informasi Akun Login Guru</h2>
                <div>
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>" required placeholder="Untuk login guru">
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" required placeholder="Min. 6 karakter">
                </div>
                <div>
                    <label for="konfirmasi_password">Konfirmasi Password:</label>
                    <input type="password" id="konfirmasi_password" name="konfirmasi_password" required placeholder="Ulangi password">
                </div>

                <div>
                    <button type="submit" name="submit_tambah_guru">Simpan Data Guru</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>