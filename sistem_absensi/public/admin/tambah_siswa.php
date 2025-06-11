<?php
session_start();
// Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin");
    exit();
}
require_once '../../config/db_connect.php';

// Ambil data kelas untuk dropdown
$kelas_result = $conn->query("SELECT kelas_id, nama_kelas FROM Kelas ORDER BY nama_kelas ASC");

// Ambil data orang tua untuk dropdown
$ortu_result = $conn->query("SELECT orang_tua_id, nama_orang_tua, nomor_telepon_notifikasi FROM OrangTua ORDER BY nama_orang_tua ASC");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Siswa - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav>
        <a href="dashboard_admin.php">Dashboard Admin</a>
        <a href="tambah_siswa.php">Tambah Siswa</a>
        <a href="../logout.php">Logout</a>
    </nav>
    <div class="form-container">
        <h2>Tambah Data Siswa Baru</h2>
        <?php
        if (isset($_GET['status'])) {
            if ($_GET['status'] == 'success') {
                echo "<p class='success-message'>Data siswa berhasil ditambahkan!</p>";
            } elseif ($_GET['status'] == 'error') {
                echo "<p class='error-message'>Gagal menambahkan data siswa: " . htmlspecialchars($_GET['message']) . "</p>";
            }
        }
        ?>
        <form action="proses_tambah_siswa.php" method="POST" enctype="multipart/form-data">
            <div>
                <label for="nis">NIS:</label>
                <input type="text" id="nis" name="nis" required>
            </div>
            <div>
                <label for="nama_lengkap">Nama Lengkap:</label>
                <input type="text" id="nama_lengkap" name="nama_lengkap" required>
            </div>
            <div>
                <label for="tanggal_lahir">Tanggal Lahir:</label>
                <input type="date" id="tanggal_lahir" name="tanggal_lahir">
            </div>
            <div>
                <label for="jenis_kelamin">Jenis Kelamin:</label>
                <select id="jenis_kelamin" name="jenis_kelamin">
                    <option value="Laki-laki">Laki-laki</option>
                    <option value="Perempuan">Perempuan</option>
                </select>
            </div>
            <div>
                <label for="alamat">Alamat:</label>
                <textarea id="alamat" name="alamat"></textarea>
            </div>
            <div>
                <label for="kelas_id">Kelas:</label>
                <select id="kelas_id" name="kelas_id">
                    <option value="">Pilih Kelas</option>
                    <?php while($kelas = $kelas_result->fetch_assoc()): ?>
                        <option value="<?php echo $kelas['kelas_id']; ?>"><?php echo htmlspecialchars($kelas['nama_kelas']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
             <div>
                <label for="orang_tua_id">Orang Tua/Wali (untuk Notifikasi):</label>
                <select id="orang_tua_id" name="orang_tua_id">
                    <option value="">Pilih Orang Tua</option>
                    <?php while($ortu = $ortu_result->fetch_assoc()): ?>
                        <option value="<?php echo $ortu['orang_tua_id']; ?>">
                            <?php echo htmlspecialchars($ortu['nama_orang_tua']) . " (" . htmlspecialchars($ortu['nomor_telepon_notifikasi']) .")"; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <small>Jika data orang tua belum ada, tambahkan melalui menu Kelola Data Orang Tua.</small>
            </div>
            <div>
                <label for="fingerprint_template_1">Data Fingerprint 1 (Template):</label>
                <input type="text" id="fingerprint_template_1" name="fingerprint_template_1">
                <small>Input manual template fingerprint. Idealnya ini dari SDK alat.</small>
            </div>
            <div>
                <label for="foto_siswa">Foto Siswa (Opsional):</label>
                <input type="file" id="foto_siswa" name="foto_siswa" accept="image/*">
            </div>
            <div>
                <button type="submit">Tambah Siswa</button>
            </div>
        </form>
    </div>
</body>
</html>