<?php
// File: public/admin/pages/kelola_kelas_content.php
global $conn; // Mengambil koneksi database dari index.php

// Pesan feedback dari aksi sebelumnya
$page_message = '';
if (isset($_SESSION['message'])) {
    $page_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Logika untuk filter
$filter_tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '';
$filter_semester = isset($_GET['semester']) ? $_GET['semester'] : '';

// Ambil daftar tahun ajaran yang unik untuk dropdown filter
$tahun_ajaran_list = [];
$sql_ta = "SELECT DISTINCT tahun_ajaran FROM Kelas ORDER BY tahun_ajaran DESC";
$result_ta = $conn->query($sql_ta);
if ($result_ta) {
    while ($row_ta = $result_ta->fetch_assoc()) {
        $tahun_ajaran_list[] = $row_ta['tahun_ajaran'];
    }
}

// Ambil semua data kelas dari database, dengan filter
$sql = "SELECT k.kelas_id, k.nama_kelas, k.tingkat, k.tahun_ajaran, k.semester, g.nama_lengkap AS nama_wali_kelas
        FROM Kelas k
        LEFT JOIN Guru g ON k.wali_kelas_id = g.guru_id";

$conditions = [];
$params = [];
$types = "";

if (!empty($filter_tahun_ajaran)) {
    $conditions[] = "k.tahun_ajaran = ?";
    $params[] = $filter_tahun_ajaran;
    $types .= "s";
}
if (!empty($filter_semester)) {
    $conditions[] = "k.semester = ?";
    $params[] = $filter_semester;
    $types .= "s";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

// Urutkan berdasarkan periode terbaru, lalu nama kelas
$sql .= " ORDER BY k.tahun_ajaran DESC, k.semester DESC, k.nama_kelas ASC";

$stmt = $conn->prepare($sql);

if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
} elseif (!$stmt && $conn->error) {
     $page_message = "<div class='info-message error'>Gagal mempersiapkan query data kelas: " . htmlspecialchars($conn->error) . "</div>";
}

$kelasList = [];
if ($stmt && empty($page_message)) {
    if (!$stmt->execute()) {
        $page_message = "<div class='info-message error'>Gagal mengeksekusi query: " . htmlspecialchars($stmt->error) . "</div>";
    } else {
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $kelasList[] = $row;
            }
        }
    }
    $stmt->close();
}

?>

<?php if (!empty($page_message)): ?>
    <?php echo $page_message; ?>
<?php endif; ?>

<div class="add-button-container">
    <a href="index.php?page=tambah_kelas" class="add-button">Tambah Kelas Baru</a>
</div>

<form method="GET" action="index.php" class="filter-form">
    <input type="hidden" name="page" value="kelola_kelas">
    <div style="flex-grow:1;">
        <label for="tahun_ajaran">Tahun Ajaran:</label>
        <select id="tahun_ajaran" name="tahun_ajaran">
            <option value="">Semua Tahun Ajaran</option>
            <?php foreach ($tahun_ajaran_list as $ta): ?>
                <option value="<?php echo htmlspecialchars($ta); ?>" <?php echo ($filter_tahun_ajaran == $ta) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($ta); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div style="flex-grow:1;">
        <label for="semester">Semester:</label>
        <select id="semester" name="semester">
            <option value="">Semua Semester</option>
            <option value="Ganjil" <?php echo ($filter_semester == 'Ganjil') ? 'selected' : ''; ?>>Ganjil</option>
            <option value="Genap" <?php echo ($filter_semester == 'Genap') ? 'selected' : ''; ?>>Genap</option>
        </select>
    </div>
    <div style="align-self: flex-end;">
        <button type="submit">Filter</button>
        <?php if (!empty($filter_tahun_ajaran) || !empty($filter_semester)): ?>
            <a href="index.php?page=kelola_kelas" class="reset-button" style="margin-left: 5px;">Reset</a>
        <?php endif; ?>
    </div>
</form>

<?php if (!empty($kelasList)): ?>
    <div class="table-responsive">
        <table class="table-data">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Kelas</th>
                    <th>Tingkat</th>
                    <th>Tahun Ajaran</th>
                    <th>Semester</th>
                    <th>Wali Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($kelasList as $kelas): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($kelas['nama_kelas']); ?></td>
                        <td><?php echo htmlspecialchars($kelas['tingkat'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($kelas['tahun_ajaran']); ?></td>
                        <td><?php echo htmlspecialchars($kelas['semester']); ?></td>
                        <td><?php echo htmlspecialchars($kelas['nama_wali_kelas'] ?? 'Belum Ditentukan'); ?></td>
                        <td class="action-links">
                            <a href="index.php?page=edit_kelas&id=<?php echo $kelas['kelas_id']; ?>" class="edit-link">Edit</a>
                            <a href="hapus_kelas.php?id=<?php echo $kelas['kelas_id']; ?>" class="delete-link-permanent" onclick="return confirm('Apakah Anda yakin ingin menghapus data kelas ini?');">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif(empty($page_message)): ?>
    <p style="text-align:center;">Tidak ada data kelas yang cocok dengan filter atau belum ada data kelas.</p>
<?php endif; ?>