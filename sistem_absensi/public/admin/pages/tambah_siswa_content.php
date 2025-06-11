<?php
// File: public/admin/pages/tambah_siswa_content.php
// Variabel $conn sudah tersedia dari file index.php yang memuat file ini.

// Inisialisasi variabel untuk 'sticky form' (mempertahankan input jika ada error)
$old_input = isset($_SESSION['old_input_siswa']) ? $_SESSION['old_input_siswa'] : [];
$nis_input = htmlspecialchars($old_input['nis'] ?? '');
$nama_lengkap_input = htmlspecialchars($old_input['nama_lengkap'] ?? '');
$tanggal_lahir_input = htmlspecialchars($old_input['tanggal_lahir'] ?? '');
$jenis_kelamin_input = htmlspecialchars($old_input['jenis_kelamin'] ?? '');
$alamat_input = htmlspecialchars($old_input['alamat'] ?? '');
$kelas_id_input = htmlspecialchars($old_input['kelas_id'] ?? '');
$fingerprint_template_1_input = htmlspecialchars($old_input['fingerprint_template_1'] ?? '');
$nama_ayah_input = htmlspecialchars($old_input['nama_ayah'] ?? '');
$pekerjaan_ayah_input = htmlspecialchars($old_input['pekerjaan_ayah'] ?? '');
$nama_ibu_input = htmlspecialchars($old_input['nama_ibu'] ?? '');
$pekerjaan_ibu_input = htmlspecialchars($old_input['pekerjaan_ibu'] ?? '');
$telepon_ortu_input = htmlspecialchars($old_input['telepon_ortu'] ?? '');
unset($_SESSION['old_input_siswa']); // Hapus setelah digunakan agar tidak muncul lagi di form kosong

// Pesan feedback dari proses sebelumnya
$error_message_content = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : '';
$success_message_content = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : '';
unset($_SESSION['error_message_form'], $_SESSION['success_message_form']);

// Ambil daftar kelas untuk dropdown
$kelas_list = [];
$sql_kelas = "SELECT kelas_id, nama_kelas FROM Kelas ORDER BY nama_kelas ASC";
$result_kelas = $conn->query($sql_kelas);
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
    <form action="proses_tambah_siswa.php" method="POST" enctype="multipart/form-data">
        
        <h3>Informasi Siswa</h3>
        <div>
            <label for="nis">NIS:</label>
            <input type="text" id="nis" name="nis" value="<?php echo $nis_input; ?>" required>
        </div>
        <div>
            <label for="nama_lengkap">Nama Lengkap Siswa:</label>
            <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $nama_lengkap_input; ?>" required>
        </div>
        <div>
            <label for="tanggal_lahir">Tanggal Lahir:</label>
            <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $tanggal_lahir_input; ?>">
        </div>
        <div>
            <label for="jenis_kelamin">Jenis Kelamin:</label>
            <select id="jenis_kelamin" name="jenis_kelamin">
                <option value="">-- Pilih Jenis Kelamin --</option>
                <option value="Laki-laki" <?php echo ($jenis_kelamin_input == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                <option value="Perempuan" <?php echo ($jenis_kelamin_input == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
            </select>
        </div>
        <div>
            <label for="alamat">Alamat Tinggal:</label>
            <textarea id="alamat" name="alamat" placeholder="Alamat siswa dan orang tua"><?php echo $alamat_input; ?></textarea>
        </div>
        <div>
            <label for="kelas_id">Kelas:</label>
            <select id="kelas_id" name="kelas_id" required>
                <option value="">-- Pilih Kelas --</option>
                <?php foreach ($kelas_list as $kelas): ?>
                    <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo ($kelas_id_input == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label for="fingerprint_template_1">Data Fingerprint (Template/ID):</label>
            <input type="text" id="fingerprint_template_1" name="fingerprint_template_1" value="<?php echo $fingerprint_template_1_input; ?>" placeholder="Input ID/Template dari alat scan">
            <small>Diinput manual setelah pendaftaran di alat fingerprint terpisah.</small>
        </div>
        <div>
            <label for="foto_siswa">Foto Siswa (Opsional):</label>
            <input type="file" id="foto_siswa" name="foto_siswa" accept="image/*">
        </div>

        <h3 style="margin-top: 30px; border-top: 1px solid #eee; padding-top:20px;">Informasi Orang Tua/Wali</h3>
        <div>
            <label for="nama_ayah">Nama Ayah:</label>
            <input type="text" id="nama_ayah" name="nama_ayah" value="<?php echo $nama_ayah_input; ?>">
        </div>
        <div>
            <label for="pekerjaan_ayah">Pekerjaan Ayah:</label>
            <input type="text" id="pekerjaan_ayah" name="pekerjaan_ayah" value="<?php echo $pekerjaan_ayah_input; ?>">
        </div>
        <div>
            <label for="nama_ibu">Nama Ibu:</label>
            <input type="text" id="nama_ibu" name="nama_ibu" value="<?php echo $nama_ibu_input; ?>">
        </div>
        <div>
            <label for="pekerjaan_ibu">Pekerjaan Ibu:</label>
            <input type="text" id="pekerjaan_ibu" name="pekerjaan_ibu" value="<?php echo $pekerjaan_ibu_input; ?>">
        </div>
        <div>
            <label for="telepon_ortu">Nomor Telepon (untuk Notifikasi):</label>
            <input type="text" id="telepon_ortu" name="telepon_ortu" value="<?php echo $telepon_ortu_input; ?>" placeholder="Wajib diisi jika ingin ada notifikasi">
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" name="submit_tambah_siswa">Tambah Siswa</button>
        </div>
    </form>
</div>