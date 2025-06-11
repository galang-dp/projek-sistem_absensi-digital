<?php
// File: public/admin/pages/laporan_absensi_content.php
global $conn; // Mengambil koneksi database dari index.php

// Inisialisasi filter dengan nilai default
// Default rentang tanggal: 7 hari terakhir hingga hari ini
$tanggal_mulai_filter = isset($_GET['tanggal_mulai']) && !empty($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date('Y-m-d', strtotime('-6 days'));
$tanggal_selesai_filter = isset($_GET['tanggal_selesai']) && !empty($_GET['tanggal_selesai']) ? $_GET['tanggal_selesai'] : date('Y-m-d');
$kelas_id_filter = isset($_GET['kelas_id']) && !empty($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : '';
$tahun_ajaran_filter = isset($_GET['tahun_ajaran']) && !empty($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$semester_filter = isset($_GET['semester']) && !empty($_GET['semester']) ? $_GET['semester'] : '';

$message = '';

// Ambil daftar tahun ajaran dan kelas untuk dropdown filter
$tahun_ajaran_list = [];
$sql_ta = "SELECT DISTINCT tahun_ajaran FROM Kelas ORDER BY tahun_ajaran DESC";
$result_ta = $conn->query($sql_ta);
if ($result_ta) {
    while ($row_ta = $result_ta->fetch_assoc()) {
        $tahun_ajaran_list[] = $row_ta['tahun_ajaran'];
    }
}

$kelas_list = [];
$sql_kelas = "SELECT kelas_id, nama_kelas, tahun_ajaran, semester FROM Kelas ORDER BY tahun_ajaran DESC, semester DESC, nama_kelas ASC";
$result_kelas = $conn->query($sql_kelas);
if ($result_kelas) {
    while ($row_kl = $result_kelas->fetch_assoc()) {
        $kelas_list[] = $row_kl;
    }
}

// Ambil data absensi berdasarkan filter
$laporan_absensi = [];
$sql_laporan = "SELECT a.tanggal_absensi, s.nis, s.nama_lengkap AS nama_siswa, 
                       k.nama_kelas, k.tahun_ajaran, k.semester, 
                       a.status_kehadiran, a.waktu_scan_masuk, a.keterangan
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
if (!empty($tahun_ajaran_filter)) {
    $sql_laporan .= " AND k.tahun_ajaran = ?";
    $params[] = $tahun_ajaran_filter;
    $types .= "s";
}
if (!empty($semester_filter)) {
    $sql_laporan .= " AND k.semester = ?";
    $params[] = $semester_filter;
    $types .= "s";
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
        $message = "<div class='info-message error'>Gagal mengambil data laporan absensi: " . htmlspecialchars($stmt_laporan->error) . "</div>";
    }
    $stmt_laporan->close();
} else {
    $message = "<div class='info-message error'>Gagal mempersiapkan query laporan: " . htmlspecialchars($conn->error) . "</div>";
}
?>

<?php if (!empty($message)): ?>
    <?php echo $message; ?>
<?php endif; ?>

<form method="GET" action="index.php" class="filter-form">
    <input type="hidden" name="page" value="laporan_absensi">
    <div>
        <label for="tanggal_mulai">Dari Tanggal:</label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai_filter); ?>" required>
    </div>
    <div>
        <label for="tanggal_selesai">Sampai Tanggal:</label>
        <input type="date" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($tanggal_selesai_filter); ?>" required>
    </div>
    <div>
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select id="tahun_ajaran" name="tahun_ajaran">
            <option value="">Semua</option>
            <?php foreach ($tahun_ajaran_list as $ta): ?>
                <option value="<?php echo htmlspecialchars($ta); ?>" <?php echo ($tahun_ajaran_filter == $ta) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ta); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="semester">Semester:</label>
        <select id="semester" name="semester">
            <option value="">Semua</option>
            <option value="Ganjil" <?php echo ($semester_filter == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
            <option value="Genap" <?php echo ($semester_filter == 'Genap') ? 'selected' : ''; ?>>Genap</option>
        </select>
    </div>
    <div>
        <label for="kelas_id">Kelas:</label>
        <select id="kelas_id" name="kelas_id">
            <option value="">Semua Kelas</option>
            <?php foreach ($kelas_list as $kelas): ?>
                <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo ($kelas_id_filter == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($kelas['nama_kelas']) . " (" . htmlspecialchars($kelas['tahun_ajaran']) . " - " . htmlspecialchars($kelas['semester']) . ")"; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="align-self: flex-end;">
        <button type="submit">Tampilkan Laporan</button>
        <a href="index.php?page=laporan_absensi" class="reset-button" style="margin-left: 5px;">Reset</a>
    </div>
</form>

<?php if (!empty($laporan_absensi)): ?>
    <h3 style="text-align:center; margin-top:30px;">Menampilkan <?php echo count($laporan_absensi); ?> Data Absensi</h3>
    <div class="table-responsive">
        <table class="table-data">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>NIS</th>
                    <th>Nama Siswa</th>
                    <th>Kelas</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Status Kehadiran</th>
                    <th>Waktu Scan</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($laporan_absensi as $laporan): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo date("d-m-Y", strtotime($laporan['tanggal_absensi'])); ?></td>
                        <td><?php echo htmlspecialchars($laporan['nis']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['nama_siswa']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['nama_kelas']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['tahun_ajaran']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['semester']); ?></td>
                        <td><?php echo htmlspecialchars($laporan['status_kehadiran']); ?></td>
                        <td><?php echo $laporan['waktu_scan_masuk'] ? date("H:i:s", strtotime($laporan['waktu_scan_masuk'])) : '-'; ?></td>
                        <td><?php echo htmlspecialchars($laporan['keterangan'] ?? '-'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif(empty($message)): ?>
    <p style="text-align:center; margin-top:20px;">Tidak ada data absensi yang ditemukan untuk filter yang dipilih.</p>
<?php endif; ?>