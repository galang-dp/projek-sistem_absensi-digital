<?php
// File: public/admin/pages/edit_kelas_content.php
global $conn; // Mengambil koneksi database dari index.php

// Pesan feedback
$error_message_content = isset($_SESSION['error_message_form']) ? $_SESSION['error_message_form'] : '';
$success_message_content = isset($_SESSION['success_message_form']) ? $_SESSION['success_message_form'] : '';
unset($_SESSION['error_message_form'], $_SESSION['success_message_form']);

// Inisialisasi variabel
$kelas_id_to_edit = null;
$kelas = []; // Array untuk menampung data kelas

// 1. Ambil ID kelas dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $kelas_id_to_edit = (int)$_GET['id'];

    // 2. Ambil data kelas yang akan diedit dari database
    $stmt_kelas_data = $conn->prepare("SELECT * FROM Kelas WHERE kelas_id = ?");
    if ($stmt_kelas_data) {
        $stmt_kelas_data->bind_param("i", $kelas_id_to_edit);
        $stmt_kelas_data->execute();
        $result_kelas_data = $stmt_kelas_data->get_result();

        if ($result_kelas_data->num_rows === 1) {
            $kelas = $result_kelas_data->fetch_assoc();
            
            // Logika untuk memisahkan nama kelas paralel dari nama kelas lengkap
            // Asumsi format nama kelas: "Kelas [Tingkat]-[NamaParalel]"
            $prefix = "Kelas " . $kelas['tingkat'] . "-";
            $nama_paralel_current = str_replace($prefix, '', $kelas['nama_kelas']);

        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data kelas tidak ditemukan.</div>";
            echo "<script>window.location.href='index.php?page=kelola_kelas';</script>";
            exit();
        }
        $stmt_kelas_data->close();
    } else {
        echo "<div class='info-message error'>Gagal mempersiapkan query: " . htmlspecialchars($conn->error) . "</div>";
        return;
    }
} else {
    echo "<div class='info-message error'>ID Kelas tidak valid atau tidak disediakan.</div>";
    return;
}

// 3. Ambil daftar guru untuk dropdown wali kelas
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
    <form action="proses_edit_kelas.php" method="POST">
        <input type="hidden" name="kelas_id" value="<?php echo $kelas['kelas_id']; ?>">

        <div>
            <label for="tingkat">Tingkat Kelas:</label>
            <select id="tingkat" name="tingkat" required>
                <option value="">-- Pilih Tingkat --</option>
                <option value="1" <?php echo (($kelas['tingkat'] ?? '') == '1') ? 'selected' : ''; ?>>Kelas 1</option>
                <option value="2" <?php echo (($kelas['tingkat'] ?? '') == '2') ? 'selected' : ''; ?>>Kelas 2</option>
                <option value="3" <?php echo (($kelas['tingkat'] ?? '') == '3') ? 'selected' : ''; ?>>Kelas 3</option>
                <option value="4" <?php echo (($kelas['tingkat'] ?? '') == '4') ? 'selected' : ''; ?>>Kelas 4</option>
                <option value="5" <?php echo (($kelas['tingkat'] ?? '') == '5') ? 'selected' : ''; ?>>Kelas 5</option>
                <option value="6" <?php echo (($kelas['tingkat'] ?? '') == '6') ? 'selected' : ''; ?>>Kelas 6</option>
            </select>
        </div>

        <div>
            <label for="nama_paralel">Nama Kelas Paralel:</label>
            <input type="text" id="nama_paralel" name="nama_paralel" value="<?php echo htmlspecialchars($nama_paralel_current ?? ''); ?>" required placeholder="Contoh: A, B, Pagi, Siang">
            <small>Nama kelas akhir akan menjadi gabungan dari tingkat dan nama paralel (Contoh: Kelas 1-A).</small>
        </div>

        <div>
            <label for="tahun_ajaran">Tahun Ajaran:</label>
            <input type="text" id="tahun_ajaran" name="tahun_ajaran" value="<?php echo htmlspecialchars($kelas['tahun_ajaran'] ?? ''); ?>" required placeholder="Contoh: 2024/2025">
        </div>

        <div>
            <label for="semester">Semester:</label>
            <select id="semester" name="semester" required>
                <option value="">-- Pilih Semester --</option>
                <option value="Ganjil" <?php echo (($kelas['semester'] ?? '') == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
                <option value="Genap" <?php echo (($kelas['semester'] ?? '') == 'Genap') ? 'selected' : ''; ?>>Genap</option>
            </select>
        </div>

        <div>
            <label for="wali_kelas_id">Wali Kelas:</label>
            <select id="wali_kelas_id" name="wali_kelas_id">
                <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                <?php foreach ($guru_list as $guru): ?>
                    <option value="<?php echo $guru['guru_id']; ?>" <?php echo (($kelas['wali_kelas_id'] ?? '') == $guru['guru_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($guru['nama_lengkap']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div style="margin-top: 20px;">
            <button type="submit" name="submit_edit_kelas">Simpan Perubahan</button>
        </div>
    </form>
</div>