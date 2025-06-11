<?php
// File: public/admin/pages/edit_siswa_content.php
global $conn; // Mengambil koneksi database dari index.php

// Pesan feedback
$error_message_content = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : '';
$success_message_content = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : '';
unset($_SESSION['error_message_form'], $_SESSION['success_message_form']);

// Inisialisasi variabel
$siswa_id_to_edit = null;
$siswa = []; // Array untuk menampung semua data siswa

// 1. Ambil ID siswa dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $siswa_id_to_edit = (int)$_GET['id'];

    // 2. Ambil data siswa lengkap (termasuk data ortu) dari tabel Siswa
    $stmt_siswa_data = $conn->prepare("SELECT * FROM Siswa WHERE siswa_id = ?");
    if ($stmt_siswa_data) {
        $stmt_siswa_data->bind_param("i", $siswa_id_to_edit);
        $stmt_siswa_data->execute();
        $result_siswa_data = $stmt_siswa_data->get_result();

        if ($result_siswa_data->num_rows === 1) {
            $siswa = $result_siswa_data->fetch_assoc();
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data siswa tidak ditemukan.</div>";
            echo "<script>window.location.href='index.php?page=kelola_siswa';</script>"; // Redirect via JS jika header sudah dikirim
            exit();
        }
        $stmt_siswa_data->close();
    } else {
        echo "<div class='info-message error'>Gagal mempersiapkan query: " . htmlspecialchars($conn->error) . "</div>";
        return; // Hentikan eksekusi file ini jika ada error DB
    }
} else {
    echo "<div class='info-message error'>ID Siswa tidak valid.</div>";
    return;
}

// 3. Ambil daftar kelas untuk dropdown
$kelas_list = [];
$result_kelas = $conn->query("SELECT kelas_id, nama_kelas FROM Kelas ORDER BY nama_kelas ASC");
if ($result_kelas && $result_kelas->num_rows > 0) {
    while ($row = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row;
    }
}
?>

<?php if (!empty($error_message_content)): ?>
    <div class="info-message error"><?php echo $error_message_content; ?></div>
<?php endif; ?>
<?php if (!empty($success_message_content)): ?>
    <div class="info-message success"><?php echo $success_message_content; ?></div>
<?php endif; ?>

<div class="form-container">
    <form action="proses_edit_siswa.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="siswa_id" value="<?php echo $siswa['siswa_id']; ?>">
        <input type="hidden" name="foto_siswa_lama" value="<?php echo htmlspecialchars($siswa['foto_siswa'] ?? ''); ?>">

        <h3>Informasi Siswa</h3>
        <div>
            <label for="nis">NIS:</label>
            <input type="text" id="nis" name="nis" value="<?php echo htmlspecialchars($siswa['nis'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="nama_lengkap">Nama Lengkap Siswa:</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo htmlspecialchars($siswa['nama_lengkap'] ?? ''); ?>" required>
        </div>
        <div>
            <label for="tanggal_lahir">Tanggal Lahir:</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo htmlspecialchars($siswa['tanggal_lahir'] ?? ''); ?>">
        </div>
        <div>
            <label for="jenis_kelamin">Jenis Kelamin:</label>
            <select id="jenis_kelamin" name="jenis_kelamin">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="Laki-laki" <?php echo (($siswa['jenis_kelamin'] ?? '') == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="Perempuan" <?php echo (($siswa['jenis_kelamin'] ?? '') == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
            </select>
        </div>
        <div>
            <label for="alamat">Alamat Tinggal:</label>
            <textarea id="alamat" name="alamat"><?php echo htmlspecialchars($siswa['alamat'] ?? ''); ?></textarea>
        </div>
        <div>
            <label for="kelas_id">Kelas:</label>
            <select id="kelas_id" name="kelas_id" required>
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas_list as $kelas): ?>
                    <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo (($siswa['kelas_id'] ?? '') == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="fingerprint_template_1">Data Fingerprint (Template/ID):</label>
            <input type="text" id="fingerprint_template_1" name="fingerprint_template_1" value="<?php echo htmlspecialchars($siswa['fingerprint_template_1'] ?? ''); ?>" placeholder="Input ID/Template dari alat scan">
        </div>
         <div>
            <label for="status_aktif">Status Siswa:</label>
            <select id="status_aktif" name="status_aktif" required>
                <option value="1" <?php echo (($siswa['status_aktif'] ?? 1) == 1) ? 'selected' : ''; ?>>Aktif</option>
                <option value="0" <?php echo (($siswa['status_aktif'] ?? 1) == 0) ? 'selected' : ''; ?>>Non-Aktif</option>
            </select>
        </div>
        <div>
            <label for="foto_siswa">Ganti Foto Siswa (Opsional):</label>
            <input type="file" id="foto_siswa" name="foto_siswa" accept="image/*">
            <?php 
                $path_ke_root = '../../'; // 2 level up dari /public/admin/pages/ ke root
                if (!empty($siswa['foto_siswa']) && file_exists($path_ke_root . $siswa['foto_siswa'])): 
            ?>
                <p style="margin-top:10px;">
                    Foto saat ini: <br>
                    <img src="<?php echo $path_ke_root . htmlspecialchars($siswa['foto_siswa']); ?>" alt="Foto Siswa" style="max-width: 100px; max-height: 100px; border:1px solid #ddd; padding: 5px;">
                </p>
            <?php endif; ?>
        </div>

        <h3 style="margin-top: 30px; border-top: 1px solid #eee; padding-top:20px;">Informasi Orang Tua/Wali</h3>
        <div>
            <label for="nama_ayah">Nama Ayah:</label>
            <input type="text" id="nama_ayah" name="nama_ayah" value="<?php echo htmlspecialchars($siswa['nama_ayah'] ?? ''); ?>">
        </div>
        <div>
            <label for="pekerjaan_ayah">Pekerjaan Ayah:</label>
            <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah" value="<?php echo htmlspecialchars($siswa['pekerjaan_ayah'] ?? ''); ?>">
        </div>
        <div>
            <label for="nama_ibu">Nama Ibu:</label>
            <input type="text" id="nama_ibu" name="nama_ibu" value="<?php echo htmlspecialchars($siswa['nama_ibu'] ?? ''); ?>">
        </div>
        <div>
            <label for="pekerjaan_ibu">Pekerjaan Ibu:</label>
            <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu" value="<?php echo htmlspecialchars($siswa['pekerjaan_ibu'] ?? ''); ?>">
        </div>
        <div>
            <label for="telepon_ortu">Nomor Telepon (untuk Notifikasi):</label>
            <input type="text" id="telepon_ortu" name="telepon_ortu" value="<?php echo htmlspecialchars($siswa['telepon_ortu'] ?? ''); ?>">
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" name="submit_edit_siswa">Simpan Perubahan</button>
        </div>
    </form>
</div>