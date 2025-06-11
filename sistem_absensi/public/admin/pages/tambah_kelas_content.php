<?php
// File: public/admin/pages/tambah_kelas_content.php
global $conn; // Mengambil koneksi database dari index.php

// Inisialisasi variabel untuk 'sticky form'
$old_input = isset($_SESSION['old_input_kelas']) ? $_SESSION['old_input_kelas'] : [];
$tingkat_input = htmlspecialchars($old_input['tingkat'] ?? '');
$nama_paralel_input = htmlspecialchars($old_input['nama_paralel'] ?? '');
$tahun_ajaran_input = htmlspecialchars($old_input['tahun_ajaran'] ?? '');
$semester_input = htmlspecialchars($old_input['semester'] ?? '');
$wali_kelas_id_input = htmlspecialchars($old_input['wali_kelas_id'] ?? '');
unset($_SESSION['old_input_kelas']);

// Pesan feedback
$error_message_content = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : '';
$success_message_content = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : '';
unset($_SESSION['error_message_form'], $_SESSION['success_message_form']);

// Ambil daftar guru untuk dropdown wali kelas
$guru_list = [];
$sql_guru = "SELECT guru_id, nama_lengkap FROM Guru ORDER BY nama_lengkap ASC";
$result_guru = $conn->query($sql_guru);
if ($result_guru && $result_guru->num_rows > 0) {
    while ($row = $result_guru->fetch_assoc()) {
        $guru_list[] = $row;
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
    <form action="proses_tambah_kelas.php" method="POST">
        
        <div>
            <label for="tingkat">Tingkat Kelas:</label>
            <select id="tingkat" name="tingkat" required>
                <option value="">-- Pilih Tingkat --</option>
                <option value="1" <?php echo ($tingkat_input == '1') ? 'selected' : ''; ?>>Kelas 1</option>
                <option value="2" <?php echo ($tingkat_input == '2') ? 'selected' : ''; ?>>Kelas 2</option>
                <option value="3" <?php echo ($tingkat_input == '3') ? 'selected' : ''; ?>>Kelas 3</option>
                <option value="4" <?php echo ($tingkat_input == '4') ? 'selected' : ''; ?>>Kelas 4</option>
                <option value="5" <?php echo ($tingkat_input == '5') ? 'selected' : ''; ?>>Kelas 5</option>
                <option value="6" <?php echo ($tingkat_input == '6') ? 'selected' : ''; ?>>Kelas 6</option>
            </select>
        </div>

        <div>
            <label for="nama_paralel">Nama Kelas Paralel:</label>
            <input type="text" id="nama_paralel" name="nama_paralel" value="<?php echo $nama_paralel_input; ?>" required placeholder="Contoh: A, B, Pagi, Siang">
            <small>Nama kelas akhir akan menjadi gabungan dari tingkat dan nama paralel (Contoh: Kelas 1-A).</small>
        </div>

        <div>
            <label for="tahun_ajaran">Tahun Ajaran:</label>
            <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo $tahun_ajaran_input; ?>" required placeholder="Contoh: 2024/2025">
        </div>

        <div>
            <label for="semester">Semester:</label>
            <select id="semester" name="semester" required>
                <option value="">-- Pilih Semester --</option>
                <option value="Ganjil" <?php echo ($semester_input == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                <option value="Genap" <?php echo ($semester_input == 'Genap') ? 'selected' : ''; ?>>Genap</option>
            </select>
        </div>

        <div>
            <label for="wali_kelas_id">Wali Kelas:</label>
            <select id="wali_kelas_id" name="wali_kelas_id">
                <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                <?php foreach ($guru_list as $guru): ?>
                    <option value="<?php echo $guru['guru_id']; ?>" <?php echo ($wali_kelas_id_input == $guru['guru_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($guru['nama_lengkap']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" name="submit_tambah_kelas">Simpan Data Kelas</button>
        </div>
    </form>
</div>