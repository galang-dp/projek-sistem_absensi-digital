<?php
// File: public/admin/pages/edit_guru_content.php
global $conn; // Mengambil koneksi database dari index.php

// Pesan feedback
$error_message_content = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : '';
$success_message_content = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : '';
unset($_SESSION['error_message_form'], $_SESSION['success_message_form']);

// Inisialisasi variabel
$guru_id_to_edit = null;
$guru = []; // Array untuk menampung data guru

// 1. Ambil ID guru dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $guru_id_to_edit = (int)$_GET['id'];

    // 2. Ambil data guru yang akan diedit
    $stmt_guru_data = $conn->prepare("SELECT g.guru_id, g.user_id, g.nip, g.nama_lengkap, g.email, g.nomor_telepon, g.alamat, u.username 
                                      FROM Guru g
                                      JOIN Users u ON g.user_id = u.user_id
                                      WHERE g.guru_id = ?");
    if ($stmt_guru_data) {
        $stmt_guru_data->bind_param("i", $guru_id_to_edit);
        $stmt_guru_data->execute();
        $result_guru_data = $stmt_guru_data->get_result();

        if ($result_guru_data->num_rows === 1) {
            $guru = $result_guru_data->fetch_assoc();
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data guru tidak ditemukan.</div>";
            echo "<script>window.location.href='index.php?page=kelola_guru';</script>";
            exit();
        }
        $stmt_guru_data->close();
    } else {
        echo "<div class='info-message error'>Gagal mempersiapkan query data guru: " . htmlspecialchars($conn->error) . "</div>";
        return;
    }
} else {
    echo "<div class='info-message error'>ID Guru tidak valid atau tidak disediakan.</div>";
    return;
}
?>

<?php if (!empty($error_message_content)): ?>
    <div class="info-message error"><?php echo $error_message_content; ?></div>
<?php endif; ?>
<?php if (!empty($success_message_content)): ?>
    <div class="info-message success"><?php echo $success_message_content; ?></div>
<?php endif; ?>

<div class="form-container">
    <form action="proses_edit_guru.php" method="POST">
        <input type="hidden" name="guru_id" value="<?php echo $guru['guru_id']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $guru['user_id']; ?>">

        <h3>Informasi Detail Guru</h3>
        <div>
            <label for="nip">NIP (Nomor Induk Pegawai):</label>
            <input type="text" id="nip" name="nip" value="<?php echo htmlspecialchars($guru['nip'] ?? ''); ?>" placeholder="Opsional">
        </div>
        <div>
            <label for="nama_lengkap">Nama Lengkap Guru:</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($guru['nama_lengkap'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($guru['email'] ?? ''); ?>" placeholder="Opsional, contoh: guru@sekolah.com">
        </div>
        <div>
            <label for="nomor_telepon">Nomor Telepon:</label>
            <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?php echo htmlspecialchars($guru['nomor_telepon'] ?? ''); ?>" placeholder="Opsional, contoh: 08123456789">
        </div>
        <div>
            <label for="alamat">Alamat:</label>
            <textarea id="alamat" name="alamat" placeholder="Opsional"><?php echo htmlspecialchars($guru['alamat'] ?? ''); ?></textarea>
        </div>

        <h3 style="margin-top: 30px; border-top: 1px solid #eee; padding-top:20px;">Informasi Akun Login Guru</h3>
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($guru['username'] ?? ''); ?>" required placeholder="Untuk login guru">
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

        <div style="margin-top: 20px;">
            <button type="submit" name="submit_edit_guru">Simpan Perubahan</button>
        </div>
    </form>
</div>