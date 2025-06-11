<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// Inisialisasi variabel untuk form (jika ada old input atau error)
$nama_orang_tua_input = '';
$nomor_telepon_notifikasi_input = '';
$email_notifikasi_input = '';
$alamat_input = '';

// Pesan feedback
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message'], $_SESSION['success_message']);

// Ambil input lama jika ada (setelah redirect karena error)
if (isset($_SESSION['old_input_orangtua'])) {
    $old_input = $_SESSION['old_input_orangtua'];
    $nama_orang_tua_input = htmlspecialchars($old_input['nama_orang_tua'] ?? '');
    $nomor_telepon_notifikasi_input = htmlspecialchars($old_input['nomor_telepon_notifikasi'] ?? '');
    $email_notifikasi_input = htmlspecialchars($old_input['email_notifikasi'] ?? '');
    $alamat_input = htmlspecialchars($old_input['alamat'] ?? '');
    unset($_SESSION['old_input_orangtua']);
}

$conn->close(); // Tutup koneksi jika tidak ada query lagi di halaman ini
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Data Orang Tua - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Tambah Data Orang Tua/Wali Baru</h1>
            <p><a href="kelola_orangtua.php" class="admin-link">&laquo; Kembali ke Kelola Orang Tua</a></p>
        </div>

        <div class="form-container" style="max-width: 600px; margin-left:auto; margin-right:auto;">
            <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_tambah_orangtua.php" method="POST">
                <div>
                    <label for="nama_orang_tua">Nama Orang Tua/Wali:</label>
                    <input type="text" id="nama_orang_tua" name="nama_orang_tua" value="<?php echo $nama_orang_tua_input; ?>" required placeholder="Nama lengkap orang tua atau wali">
                </div>
                <div>
                    <label for="nomor_telepon_notifikasi">Nomor Telepon (untuk Notifikasi):</label>
                    <input type="text" id="nomor_telepon_notifikasi" name="nomor_telepon_notifikasi" value="<?php echo $nomor_telepon_notifikasi_input; ?>" required placeholder="Contoh: 081234567890">
                </div>
                <div>
                    <label for="email_notifikasi">Email (untuk Notifikasi):</label>
                    <input type="email" id="email_notifikasi" name="email_notifikasi" value="<?php echo $email_notifikasi_input; ?>" placeholder="Opsional, contoh: ortu@email.com">
                </div>
                <div>
                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" placeholder="Opsional"><?php echo $alamat_input; ?></textarea>
                </div>
                
                <div>
                    <button type="submit" name="submit_tambah_orangtua">Simpan Data Orang Tua</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>