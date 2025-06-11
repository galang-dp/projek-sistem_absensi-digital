<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// Pesan feedback
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message'], $_SESSION['success_message']);

// Inisialisasi variabel data orang tua
$orang_tua_id_to_edit = null;
$nama_orang_tua_current = '';
$nomor_telepon_notifikasi_current = '';
$email_notifikasi_current = '';
$alamat_current = '';

// 2. Ambil ID orang tua dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $orang_tua_id_to_edit = (int)$_GET['id'];

    // 3. Ambil data orang tua yang akan diedit dari database
    $stmt_ortu_data = $conn->prepare("SELECT orang_tua_id, nama_orang_tua, nomor_telepon_notifikasi, email_notifikasi, alamat FROM OrangTua WHERE orang_tua_id = ?");
    if ($stmt_ortu_data) {
        $stmt_ortu_data->bind_param("i", $orang_tua_id_to_edit);
        $stmt_ortu_data->execute();
        $result_ortu_data = $stmt_ortu_data->get_result();

        if ($result_ortu_data->num_rows === 1) {
            $ortu = $result_ortu_data->fetch_assoc();
            $nama_orang_tua_current = htmlspecialchars($ortu['nama_orang_tua']);
            $nomor_telepon_notifikasi_current = htmlspecialchars($ortu['nomor_telepon_notifikasi']);
            $email_notifikasi_current = htmlspecialchars($ortu['email_notifikasi'] ?? '');
            $alamat_current = htmlspecialchars($ortu['alamat'] ?? '');
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data orang tua tidak ditemukan.</div>";
            header("Location: kelola_orangtua.php");
            exit();
        }
        $stmt_ortu_data->close();
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query data orang tua: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_orangtua.php");
        exit();
    }
} else {
    $_SESSION['message'] = "<div class='info-message error'>ID Orang Tua tidak valid atau tidak disediakan.</div>";
    header("Location: kelola_orangtua.php");
    exit();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Orang Tua - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Edit Data Orang Tua/Wali</h1>
            <p><a href="kelola_orangtua.php" class="admin-link">&laquo; Kembali ke Kelola Orang Tua</a></p>
        </div>

        <div class="form-container" style="max-width: 600px; margin-left:auto; margin-right:auto;">
            <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_edit_orangtua.php" method="POST">
                <input type="hidden" name="orang_tua_id" value="<?php echo $orang_tua_id_to_edit; ?>">

                <div>
                    <label for="nama_orang_tua">Nama Orang Tua/Wali:</label>
                    <input type="text" id="nama_orang_tua" name="nama_orang_tua" value="<?php echo $nama_orang_tua_current; ?>" required placeholder="Nama lengkap orang tua atau wali">
                </div>
                <div>
                    <label for="nomor_telepon_notifikasi">Nomor Telepon (untuk Notifikasi):</label>
                    <input type="text" id="nomor_telepon_notifikasi" name="nomor_telepon_notifikasi" value="<?php echo $nomor_telepon_notifikasi_current; ?>" required placeholder="Contoh: 081234567890">
                </div>
                <div>
                    <label for="email_notifikasi">Email (untuk Notifikasi):</label>
                    <input type="email" id="email_notifikasi" name="email_notifikasi" value="<?php echo $email_notifikasi_current; ?>" placeholder="Opsional, contoh: ortu@email.com">
                </div>
                <div>
                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat" placeholder="Opsional"><?php echo $alamat_current; ?></textarea>
                </div>
                
                <div>
                    <button type="submit" name="submit_edit_orangtua">Simpan Perubahan</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>