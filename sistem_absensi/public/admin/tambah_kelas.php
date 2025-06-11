<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// Inisialisasi variabel untuk form (jika ada old input atau error)
$nama_kelas_input = '';
$tingkat_input = '';
$wali_kelas_id_input = '';

// Pesan feedback
$error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
unset($_SESSION['error_message'], $_SESSION['success_message']);

// Ambil input lama jika ada (setelah redirect karena error)
if (isset($_SESSION['old_input_kelas'])) {
    $old_input = $_SESSION['old_input_kelas'];
    $nama_kelas_input = htmlspecialchars($old_input['nama_kelas'] ?? '');
    $tingkat_input = htmlspecialchars($old_input['tingkat'] ?? '');
    $wali_kelas_id_input = htmlspecialchars($old_input['wali_kelas_id'] ?? '');
    unset($_SESSION['old_input_kelas']);
}

// 2. Ambil daftar guru untuk dropdown wali kelas
$guru_list = [];
$sql_guru = "SELECT guru_id, nama_lengkap FROM Guru ORDER BY nama_lengkap ASC";
$result_guru = $conn->query($sql_guru);
if ($result_guru && $result_guru->num_rows > 0) {
    while ($row = $result_guru->fetch_assoc()) {
        $guru_list[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kelas Baru - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Tambah Kelas Baru</h1>
            <p><a href="kelola_kelas.php" class="admin-link">&laquo; Kembali ke Kelola Kelas</a></p>
        </div>

        <div class="form-container" style="max-width: 600px; margin-left:auto; margin-right:auto;">
            <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_tambah_kelas.php" method="POST">
                <div>
                    <label for="nama_kelas">Nama Kelas:</label>
                    <input type="text" id="nama_kelas" name="nama_kelas" value="<?php echo $nama_kelas_input; ?>" required placeholder="Contoh: Kelas 1A, Kelas Pagi">
                </div>
                <div>
                    <label for="tingkat">Tingkat:</label>
                    <input type="text" id="tingkat" name="tingkat" value="<?php echo $tingkat_input; ?>" placeholder="Contoh: 1, 2, Persiapan (Opsional)">
                </div>
                <div>
                    <label for="wali_kelas_id">Wali Kelas:</label>
                    <select id="wali_kelas_id" name="wali_kelas_id">
                        <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                        <?php if (!empty($guru_list)): ?>
                            <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['guru_id']; ?>" <?php echo ($wali_kelas_id_input == $guru['guru_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($guru['nama_lengkap']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Tidak ada data guru tersedia</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" name="submit_tambah_kelas">Simpan Data Kelas</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>