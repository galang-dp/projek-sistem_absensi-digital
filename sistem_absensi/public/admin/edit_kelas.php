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

// Inisialisasi variabel data kelas
$kelas_id_to_edit = null;
$nama_kelas_current = '';
$tingkat_current = '';
$wali_kelas_id_current = '';

// 2. Ambil ID kelas dari parameter GET
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $kelas_id_to_edit = (int)$_GET['id'];

    // 3. Ambil data kelas yang akan diedit dari database
    $stmt_kelas_data = $conn->prepare("SELECT kelas_id, nama_kelas, tingkat, wali_kelas_id FROM Kelas WHERE kelas_id = ?");
    if ($stmt_kelas_data) {
        $stmt_kelas_data->bind_param("i", $kelas_id_to_edit);
        $stmt_kelas_data->execute();
        $result_kelas_data = $stmt_kelas_data->get_result();

        if ($result_kelas_data->num_rows === 1) {
            $kelas = $result_kelas_data->fetch_assoc();
            $nama_kelas_current = htmlspecialchars($kelas['nama_kelas']);
            $tingkat_current = htmlspecialchars($kelas['tingkat'] ?? '');
            $wali_kelas_id_current = $kelas['wali_kelas_id']; // Bisa NULL
        } else {
            $_SESSION['message'] = "<div class='info-message error'>Data kelas tidak ditemukan.</div>";
            header("Location: kelola_kelas.php");
            exit();
        }
        $stmt_kelas_data->close();
    } else {
        $_SESSION['message'] = "<div class='info-message error'>Gagal mempersiapkan query data kelas: " . htmlspecialchars($conn->error) . "</div>";
        header("Location: kelola_kelas.php");
        exit();
    }
} else {
    $_SESSION['message'] = "<div class='info-message error'>ID Kelas tidak valid atau tidak disediakan.</div>";
    header("Location: kelola_kelas.php");
    exit();
}

// 4. Ambil daftar guru untuk dropdown wali kelas
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
    <title>Edit Data Kelas - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Edit Data Kelas</h1>
            <p><a href="kelola_kelas.php" class="admin-link">&laquo; Kembali ke Kelola Kelas</a></p>
        </div>

        <div class="form-container" style="max-width: 600px; margin-left:auto; margin-right:auto;">
            <?php if (!empty($error_message)): ?>
                <div class="info-message error"><?php echo $error_message; ?></div>
            <?php endif; ?>
            <?php if (!empty($success_message)): ?>
                <div class="info-message success"><?php echo $success_message; ?></div>
            <?php endif; ?>

            <form action="proses_edit_kelas.php" method="POST">
                <input type="hidden" name="kelas_id" value="<?php echo $kelas_id_to_edit; ?>">

                <div>
                    <label for="nama_kelas">Nama Kelas:</label>
                    <input type="text" id="nama_kelas" name="nama_kelas" value="<?php echo $nama_kelas_current; ?>" required placeholder="Contoh: Kelas 1A, Kelas Pagi">
                </div>
                <div>
                    <label for="tingkat">Tingkat:</label>
                    <input type="text" id="tingkat" name="tingkat" value="<?php echo $tingkat_current; ?>" placeholder="Contoh: 1, 2, Persiapan (Opsional)">
                </div>
                <div>
                    <label for="wali_kelas_id">Wali Kelas:</label>
                    <select id="wali_kelas_id" name="wali_kelas_id">
                        <option value="">-- Pilih Wali Kelas (Opsional) --</option>
                        <?php if (!empty($guru_list)): ?>
                            <?php foreach ($guru_list as $guru): ?>
                                <option value="<?php echo $guru['guru_id']; ?>" <?php echo ($wali_kelas_id_current == $guru['guru_id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($guru['nama_lengkap']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <option value="" disabled>Tidak ada data guru tersedia</option>
                        <?php endif; ?>
                    </select>
                </div>
                
                <div>
                    <button type="submit" name="submit_edit_kelas">Simpan Perubahan</button>
                </div>
            </form>
        </div>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 40px;">Logout</a>
    </div>
</body>
</html>