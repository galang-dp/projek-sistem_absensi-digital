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

// Inisialisasi variabel data siswa
$siswa_id_to_edit = null;
$nis_current = '';
$nama_lengkap_current = '';
$tanggal_lahir_current = '';
$jenis_kelamin_current = '';
$alamat_current = '';
$kelas_id_current = '';
$orang_tua_id_current = '';
$fingerprint_template_1_current = '';
$fingerprint_template_2_current = '';
$status_aktif_current = 1; // Default aktif
$foto_siswa_current = '';


// 2. Ambil ID siswa dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $siswa_id_to_edit = (int)$_GET['id'];

    // 3. Ambil data siswa yang akan diedit dari database
    $stmt_siswa_data = $conn->prepare("SELECT * FROM Siswa WHERE siswa_id = ?");
    if ($stmt_siswa_data) {
        $stmt_siswa_data->bind_param("i", $siswa_id_to_edit);
        $stmt_siswa_data->execute();
        $result_siswa_data = $stmt_siswa_data->get_result();

        if ($result_siswa_data->num_rows === 1) {
            $siswa = $result_siswa_data->fetch_assoc();
            $nis_current = htmlspecialchars($siswa['nis']);
            $nama_lengkap_current = htmlspecialchars($siswa['nama_lengkap']);
            $tanggal_lahir_current = htmlspecialchars($siswa['tanggal_lahir'] ?? '');
            $jenis_kelamin_current = htmlspecialchars($siswa['jenis_kelamin'] ?? '');
            $alamat_current = htmlspecialchars($siswa['alamat'] ?? '');
            $kelas_id_current = $siswa['kelas_id'];
            $orang_tua_id_current = $siswa['orang_tua_id'];
            $fingerprint_template_1_current = htmlspecialchars($siswa['fingerprint_template_1'] ?? '');
            $fingerprint_template_2_current = htmlspecialchars($siswa['fingerprint_template_2'] ?? '');
            $status_aktif_current = $siswa['status_aktif'];
            $foto_siswa_current = $siswa['foto_siswa']; // Path foto
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data siswa tidak ditemukan.</div>";
            header("Location: kelola_siswa.php");
            exit();
        }
        $stmt_siswa_data->close();
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query data siswa: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_siswa.php");
        exit();
    }
} else {
    $_SESSION['message'] = "<div class='info-message error'>ID Siswa tidak valid atau tidak disediakan.</div>";
    header("Location: kelola_siswa.php");
    exit();
}

// 4. Ambil daftar kelas untuk dropdown
$kelas_list = [];
$sql_kelas = "SELECT kelas_id, nama_kelas FROM Kelas ORDER BY nama_kelas ASC";
$result_kelas = $conn->query($sql_kelas);
if ($result_kelas && $result_kelas->num_rows > 0) {
    while ($row = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row;
    }
}

// 5. Ambil daftar orang tua untuk dropdown
$orangtua_list = [];
$sql_orangtua = "SELECT orang_tua_id, nama_orang_tua, nomor_telepon_notifikasi FROM OrangTua ORDER BY nama_orang_tua ASC";
$result_orangtua = $conn->query($sql_orangtua);
if ($result_orangtua && $result_orangtua->num_rows > 0) {
    while ($row = $result_orangtua->fetch_assoc()) {
        $orangtua_list[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data Siswa - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Edit Data Siswa</h1>
            <p><a href="kelola_siswa.php" class="admin-link">&laquo; Kembali ke Kelola Siswa</a></p>
        </div>

        <div class="form-container" style="max-width: 700px; margin-left:auto; margin-right:auto;"> <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_edit_siswa.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="siswa_id" value="<?php echo $siswa_id_to_edit; ?>">
                <input type="hidden" name="foto_siswa_lama" value="<?php echo htmlspecialchars($foto_siswa_current ?? ''); ?>">


                <div>
                    <label for="nis">NIS:</label>
                    <input type="text" id="nis" name="nis" value="<?php echo $nis_current; ?>" required>
                </div>
                <div>
                    <label for="nama_lengkap">Nama Lengkap:</label>
                    <input type="text" id="nama_lengkap" name="nama_lengkap" value="<?php echo $nama_lengkap_current; ?>" required>
                </div>
                <div>
                    <label for="tanggal_lahir">Tanggal Lahir:</label>
                    <input type="date" id="tanggal_lahir" name="tanggal_lahir" value="<?php echo $tanggal_lahir_current; ?>">
                </div>
                <div>
                    <label for="jenis_kelamin">Jenis Kelamin:</label>
                    <select id="jenis_kelamin" name="jenis_kelamin">
                        <option value="">-- Pilih Jenis Kelamin --</option>
                        <option value="Laki-laki" <?php echo ($jenis_kelamin_current == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                        <option value="Perempuan" <?php echo ($jenis_kelamin_current == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                    </select>
                </div>
                <div>
                    <label for="alamat">Alamat:</label>
                    <textarea id="alamat" name="alamat"><?php echo $alamat_current; ?></textarea>
                </div>
                <div>
                    <label for="kelas_id">Kelas:</label>
                    <select id="kelas_id" name="kelas_id">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas_list as $kelas): ?>
                            <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo ($kelas_id_current == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                 <div>
                    <label for="orang_tua_id">Orang Tua/Wali:</label>
                    <select id="orang_tua_id" name="orang_tua_id">
                        <option value="">-- Pilih Orang Tua/Wali --</option>
                        <?php foreach ($orangtua_list as $ortu): ?>
                            <option value="<?php echo $ortu['orang_tua_id']; ?>" <?php echo ($orang_tua_id_current == $ortu['orang_tua_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ortu['nama_orang_tua']) . " (" . htmlspecialchars($ortu['nomor_telepon_notifikasi']) .")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label for="fingerprint_template_1">Data Fingerprint 1 (Template):</label>
                    <input type="text" id="fingerprint_template_1" name="fingerprint_template_1" value="<?php echo $fingerprint_template_1_current; ?>" placeholder="ID/Template dari mesin fingerprint">
                </div>
                <div>
                    <label for="fingerprint_template_2">Data Fingerprint 2 (Template Opsional):</label>
                    <input type="text" id="fingerprint_template_2" name="fingerprint_template_2" value="<?php echo $fingerprint_template_2_current; ?>" placeholder="ID/Template cadangan">
                </div>
                <div>
                    <label for="status_aktif">Status Siswa:</label>
                    <select id="status_aktif" name="status_aktif" required>
                        <option value="1" <?php echo ($status_aktif_current == 1) ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?php echo ($status_aktif_current == 0) ? 'selected' : ''; ?>>Non-Aktif</option>
                    </select>
                </div>

                <div>
                    <label for="foto_siswa">Ganti Foto Siswa (Opsional):</label>
                    <input type="file" id="foto_siswa" name="foto_siswa" accept="image/*">
                    <?php if (!empty($foto_siswa_current) && file_exists('../../' . $foto_siswa_current)): // Path relatif dari file ini ke root lalu ke uploads ?>
                        <p style="margin-top:10px;">
                            Foto saat ini: <br>
                            <img src="<?php echo '../../' . htmlspecialchars($foto_siswa_current); ?>" alt="Foto Siswa" style="max-width: 100px; max-height: 100px; border:1px solid #ddd; padding: 5px;">
                        </p>
                        <small>Unggah file baru untuk mengganti foto ini. Biarkan kosong jika tidak ingin mengganti.</small>
                    <?php elseif (!empty($foto_siswa_current)): ?>
                        <p style="margin-top:10px; color:red;">Foto saat ini (<?php echo htmlspecialchars($foto_siswa_current); ?>) tidak ditemukan.</p>
                    <?php endif; ?>
                </div>
                
                <div style="margin-top: 30px;">
                    <button type="submit" name="submit_edit_siswa">Simpan Perubahan</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>