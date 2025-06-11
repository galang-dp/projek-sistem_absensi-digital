<?php
// File: public/admin/pages/tambah_guru_content.php
global $conn; // Mengambil koneksi database dari index.php

// Inisialisasi variabel untuk 'sticky form'
$old_input = isset($_SESSION['old_input_guru']) ? $_SESSION['old_input_guru'] : [];
$nip_input = htmlspecialchars($old_input['nip'] ?? '');
$nama_lengkap_input = htmlspecialchars($old_input['nama_lengkap'] ?? '');
$email_input = htmlspecialchars($old_input['email'] ?? '');
$nomor_telepon_input = htmlspecialchars($old_input['nomor_telepon'] ?? '');
$alamat_input = htmlspecialchars($old_input['alamat'] ?? '');
$username_input = htmlspecialchars($old_input['username'] ?? '');
unset($_SESSION['old_input_guru']); // Hapus setelah digunakan

// Pesan feedback
$error_message_content = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : '';
$success_message_content = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : '';
unset($_SESSION['error_message_form'], $_SESSION['success_message_form']);
?>

<?php if (!empty($error_message_content)): ?>
    <div class="info-message error"><?php echo $error_message_content; ?></div>
<?php endif; ?>
<?php if (!empty($success_message_content)): ?>
    <div class="info-message success"><?php echo $success_message_content; ?></div>
<?php endif; ?>

<div class="form-container">
    <form action="proses_tambah_guru.php" method="POST">
        <h3>Informasi Detail Guru</h3>
        <div>
            <label for="nip">NIP (Nomor Induk Pegawai):</label>
            <input type="text" id="nip" name="nip" value="<?php echo $nip_input; ?>" placeholder="Opsional">
        </div>
        <div>
            <label for="nama_lengkap">Nama Lengkap Guru:</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $nama_lengkap_input; ?>" required>
        </div>
        <div>
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo $email_input; ?>" placeholder="Opsional, contoh: guru@sekolah.com">
        </div>
        <div>
            <label for="nomor_telepon">Nomor Telepon:</label>
            <input type="text" id="nomor_telepon" name="nomor_telepon" value="<?php echo $nomor_telepon_input; ?>" placeholder="Opsional, contoh: 08123456789">
        </div>
        <div>
            <label for="alamat">Alamat:</label>
            <textarea id="alamat" name="alamat" placeholder="Opsional"><?php echo $alamat_input; ?></textarea>
        </div>

        <h3 style="margin-top: 30px; border-top: 1px solid #eee; padding-top:20px;">Informasi Akun Login Guru</h3>
        <div>
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" value="<?php echo $username_input; ?>" required placeholder="Untuk login guru">
        </div>
        <div>
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required placeholder="Min. 6 karakter">
        </div>
        <div>
            <label for="konfirmasi_password">Konfirmasi Password:</label>
            <input type="password" id="konfirmasi_password" name="konfirmasi_password" required placeholder="Ulangi password">
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" name="submit_tambah_guru">Simpan Data Guru</button>
        </div>
    </form>
</div>