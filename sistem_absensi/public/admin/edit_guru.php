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

// Inisialisasi variabel data guru
$guru_id_to_edit = null;
$nip = '';
$nama_lengkap = '';
$email = '';
$nomor_telepon = '';
$alamat = '';
$username = '';
$user_id_guru = null; // user_id terkait dengan guru ini

// 2. Ambil ID guru dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $guru_id_to_edit = (int)$_GET['id'];

    // 3. Ambil data guru yang akan diedit dari database
    $stmt = $conn->prepare("SELECT g.guru_id, g.user_id, g.nip, g.nama_lengkap, g.email, g.nomor_telepon, g.alamat, u.username 
                            FROM Guru g
                            JOIN Users u ON g.user_id = u.user_id
                            WHERE g.guru_id = ?");
    if ($stmt) {
        $stmt->bind_param("i", $guru_id_to_edit);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $guru = $result->fetch_assoc();
            $user_id_guru = $guru['user_id'];
            $nip = htmlspecialchars($guru['nip'] ?? '');
            $nama_lengkap = htmlspecialchars($guru['nama_lengkap']);
            $email = htmlspecialchars($guru['email'] ?? '');
            $nomor_telepon = htmlspecialchars($guru['nomor_telepon'] ?? '');
            $alamat = htmlspecialchars($guru['alamat'] ?? '');
            $username = htmlspecialchars($guru['username']);
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data guru tidak ditemukan.</div>";
            header("Location: kelola_guru.php");
            exit();
        }
        $stmt->close();
    } else {
        // Handle prepare error
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_guru.php");
        exit();
    }
} else {
    $_SESSION['message'] = "<div class='info-message error'>ID Guru tidak valid atau tidak disediakan.</div>";
    header("Location: kelola_guru.php");
    exit();
}

// Ambil input lama jika ada (setelah redirect karena error validasi dari proses_edit_guru.php)
// Biasanya ini tidak terlalu diperlukan di halaman edit jika error langsung ditangani di proses
// dan redirect kembali dengan ID guru yang sama. Namun, bisa berguna jika ada validasi di sisi klien
// atau jika ingin mempertahankan perubahan yang belum disimpan.
if (isset($_SESSION['old_input_edit'])) {
    $old_input = $_SESSION['old_input_edit'];
    // Isi kembali variabel dengan old input jika ada dan sesuai
    // Contoh: $nama_lengkap = htmlspecialchars($old_input['nama_lengkap'] ?? $nama_lengkap);
    // ... (lakukan untuk semua field yang relevan) ...
    unset($_SESSION['old_input_edit']);
}


$conn->close();
header("index.php?page=kelola_guru");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Guru - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Edit Data Guru</h1>
            <p><a href="kelola_guru.php" class="admin-link">&laquo; Kembali ke Kelola Guru</a></p>
        </div>

        <div class="form-container" style="max-width: 600px; margin-left:auto; margin-right:auto;">
            <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_edit_guru.php" method="POST">
                <input type="hidden" name="guru_id" value="<?php echo $guru_id_to_edit; ?>">
                <input type="hidden" name="user_id" value="<?php echo $user_id_guru; ?>">

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
                    <label for="password_baru">Password Baru (Opsional):</label>
                    <input type="password" id="password_baru" name="password_baru" placeholder="Isi jika ingin mengubah password">
                    <small>Biarkan kosong jika tidak ingin mengubah password.</small>
                </div>
                <div>
                    <label for="konfirmasi_password_baru">Konfirmasi Password Baru:</label>
                    <input type="password" id="konfirmasi_password_baru" name="konfirmasi_password_baru" placeholder="Ulangi password baru">
                </div>

                <div>
                    <button type="submit" name="submit_edit_guru">Simpan Perubahan</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>