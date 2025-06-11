<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// Inisialisasi filter
// Default rentang tanggal: 7 hari terakhir hingga hari ini
$tanggal_mulai_filter = isset($_GET['tanggal_mulai']) && !empty($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d', strtotime('-6 days'));
$tanggal_selesai_filter = isset($_GET['tanggal_selesai']) && !empty($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : date('Y-m-d');
$kelas_id_filter = isset($_GET['kelas_id']) && !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : '';

// Validasi sederhana untuk tanggal
if (strtotime($tanggal_mulai_filter) > strtotime($tanggal_selesai_filter)) {
    // Jika tanggal mulai lebih besar dari tanggal selesai, set default atau beri pesan error
    // Di sini kita set default saja untuk bulan ini agar tidak error.
    $tanggal_mulai_filter = date('Y-m-01');
    $tanggal_selesai_filter = date('Y-m-d');
    $_SESSION['message_laporan'] = "<div class='info-message error'>Rentang tanggal tidak valid. Menampilkan data untuk bulan ini.</div>";
}


// Pesan feedback
$message = '';
if (isset($_SESSION['message_laporan'])) {
    $message = $_SESSION['message_laporan'];
    unset($_SESSION['message_laporan']);
}

// Ambil daftar kelas untuk dropdown filter
$kelas_list = [];
$sql_kelas = "SELECT kelas_id, nama_kelas FROM Kelas ORDER BY nama_kelas ASC";
$result_kelas = $conn->query($sql_kelas);
if ($result_kelas && $result_kelas->num_rows > 0) {
    while ($row = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row;
    }
}

// 2. Ambil data absensi berdasarkan filter
$laporan_absensi = [];
$sql_laporan = "SELECT a.tanggal_absensi, s.nis, s.nama_lengkap AS nama_siswa, 
                       k.nama_kelas, a.status_kehadiran, a.waktu_scan_masuk, a.keterangan
                FROM Absensi a
                JOIN Siswa s ON a.siswa_id = s.siswa_id
                JOIN Kelas k ON s.kelas_id = k.kelas_id
                WHERE a.tanggal_absensi BETWEEN ? AND ?";

$params = [$tanggal_mulai_filter, $tanggal_selesai_filter];
$types = "ss";

if (!empty($kelas_id_filter)) {
    $sql_laporan .= " AND k.kelas_id = ?";
    $params[] = $kelas_id_filter;
    $types .= "i";
}

$sql_laporan .= " ORDER BY a.tanggal_absensi DESC, k.nama_kelas ASC, s.nama_lengkap ASC";

$stmt_laporan = $conn->prepare($sql_laporan);

if ($stmt_laporan) {
    $stmt_laporan->bind_param($types, ...$params);
    $stmt_laporan->execute();
    $result_laporan = $stmt_laporan->get_result();
    if ($result_laporan) {
        while ($row = $result_laporan->fetch_assoc()) {
            $laporan_absensi[] = $row;
        }
    } else {
        $message .= "<div class='info-message error'>Gagal mengambil data laporan absensi: " . htmlspecialchars($stmt_laporan->error) . "</div>";
    }
    $stmt_laporan->close();
} else {
    $message .= "<div class='info-message error'>Gagal mempersiapkan query laporan absensi: " . htmlspecialchars($conn->error) . "</div>";
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Absensi - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Laporan Absensi Siswa</h1>
            <p><a href="dashboard_admin.php" class="admin-link">&laquo; Kembali ke Dashboard</a></p>
        </div>

        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form method="GET" action="laporan_absensi.php" class="filter-form">
            <div>
                <label for="tanggal_mulai">Tanggal Mulai:</label>
                <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai_filter); ?>" required>
            </div>
            <div>
                <label for="tanggal_selesai">Tanggal Selesai:</label>
                <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($tanggal_selesai_filter); ?>" required>
            </div>
            <div>
                <label for="kelas_id">Pilih Kelas:</label>
                <select id="kelas_id" name="kelas_id">
                    <option value="">-- Semua Kelas --</option>
                    <?php foreach ($kelas_list as $kelas): ?>
                        <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo ($kelas_id_filter == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit">Tampilkan Laporan</button>
            <?php if (!empty($_GET['tanggal_mulai']) || !empty($_GET['kelas_id'])): // Tampilkan tombol reset jika ada filter aktif ?>
                <a href="laporan_absensi.php" class="reset-button">Reset Filter</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($laporan_absensi)): ?>
            <h3 style="text-align:center; margin-top:30px;">Laporan Absensi dari <?php echo date("d M Y", strtotime($tanggal_mulai_filter)); ?> sampai <?php echo date("d M Y", strtotime($tanggal_selesai_filter)); ?>
                <?php if(!empty($kelas_id_filter) && !empty($kelas_list)) {
                    foreach($kelas_list as $k) { if($k['kelas_id'] == $kelas_id_filter) { echo " - Kelas: " . htmlspecialchars($k['nama_kelas']); break;}}}?>
            </h3>
            <table class="table-data">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>NIS</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Status Kehadiran</th>
                        <th>Waktu Scan Masuk</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($laporan_absensi as $laporan): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo date("d M Y", strtotime($laporan['tanggal_absensi'])); ?></td>
                            <td><?php echo htmlspecialchars($laporan['nis']); ?></td>
                            <td><?php echo htmlspecialchars($laporan['nama_siswa']); ?></td>
                            <td><?php echo htmlspecialchars($laporan['nama_kelas']); ?></td>
                            <td><?php echo htmlspecialchars($laporan['status_kehadiran']); ?></td>
                            <td><?php echo $laporan['waktu_scan_masuk'] ? date("H:i:s", strtotime($laporan['waktu_scan_masuk'])) : '-'; ?></td>
                            <td><?php echo htmlspecialchars($laporan['keterangan'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif(empty($message)): ?>
            <p style="text-align:center; margin-top:20px;">Tidak ada data absensi yang ditemukan untuk filter yang dipilih.</p>
        <?php endif; ?>
        
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>