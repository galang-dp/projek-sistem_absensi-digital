<?php
// File: public/admin/pages/kelola_siswa_content.php
// Versi final yang sudah diperbaiki
global $conn; // Mengambil koneksi database dari index.php

// Logika untuk filter dan pencarian
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter_kelas_id = isset($_GET['kelas_id']) ? (int)$_GET['kelas_id'] : '';

// Pesan feedback dari aksi sebelumnya
$page_message = '';
if (isset($_SESSION['message'])) {
    $page_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Query untuk mengambil data siswa sesuai filter
$sql = "SELECT s.siswa_id, s.nis, s.nama_lengkap, s.status_aktif, 
               s.nama_ayah, s.telepon_ortu, 
               k.nama_kelas
        FROM Siswa s
        LEFT JOIN Kelas k ON s.kelas_id = k.kelas_id";

$conditions = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    $conditions[] = "(s.nis LIKE ? OR s.nama_lengkap LIKE ? OR s.nama_ayah LIKE ? OR s.telepon_ortu LIKE ?)";
    $search_param = "%" . $search_query . "%";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
    $types .= "ssss";
}

if (!empty($filter_kelas_id)) {
    $conditions[] = "s.kelas_id = ?";
    $params[] = $filter_kelas_id;
    $types .= "i";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY s.nama_lengkap ASC";

$stmt = $conn->prepare($sql);

if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
} elseif (!$stmt && $conn->error) {
     $page_message = "<div class='info-message error'>Gagal mempersiapkan query data siswa: " . htmlspecialchars($conn->error) . "</div>";
}

$students = [];
if ($stmt && empty($page_message)) {
    if (!$stmt->execute()) {
        $page_message = "<div class='info-message error'>Gagal mengeksekusi query: " . htmlspecialchars($stmt->error) . "</div>";
    } else {
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $students[] = $row;
            }
        }
    }
    $stmt->close();
}

// Ambil daftar kelas untuk dropdown filter
$kelas_list = [];
$result_kelas_list = $conn->query("SELECT kelas_id, nama_kelas, tahun_ajaran, semester FROM Kelas ORDER BY tahun_ajaran DESC, semester DESC, nama_kelas ASC");
if ($result_kelas_list) {
    while($row_kl = $result_kelas_list->fetch_assoc()){
        $kelas_list[] = $row_kl;
    }
}
?>

<?php if (!empty($page_message)): ?>
    <?php echo $page_message; ?>
<?php endif; ?>

<div class="add-button-container">
    <a href="index.php?page=tambah_siswa" class="add-button">Tambah Siswa Baru</a>
</div>

<form method="GET" action="index.php" class="filter-form">
    <input type="hidden" name="page" value="kelola_siswa">
    <input type="text" name="search" placeholder="Cari NIS, Nama Siswa, Nama Ayah, Telepon..." value="<?php echo htmlspecialchars($search_query); ?>">
    <select name="kelas_id">
        <option value="">Semua Kelas</option>
        <?php foreach ($kelas_list as $kelas): ?>
            <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo ($filter_kelas_id == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($kelas['nama_kelas'] . ' (' . $kelas['tahun_ajaran'] . ' - ' . $kelas['semester'] . ')'); ?>
            </option>
        <?php endforeach; ?>
    </select>
    <button type="submit">Filter/Cari</button>
    <?php if (!empty($search_query) || !empty($filter_kelas_id)): ?>
        <a href="index.php?page=kelola_siswa" class="reset-button">Reset Filter</a>
    <?php endif; ?>
</form>

<?php if (!empty($students)): ?>
    <div class="table-responsive">
        <table class="table-data">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIS</th>
                    <th>Nama Lengkap</th>
                    <th>Kelas</th>
                    <th>Nama Ayah</th>
                    <th>Telepon Ortu</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($students as $student): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($student['nis']); ?></td>
                        <td><?php echo htmlspecialchars($student['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($student['nama_kelas'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['nama_ayah'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($student['telepon_ortu'] ?? 'N/A'); ?></td>
                        <td>
                            <?php if ($student['status_aktif']): ?>
                                <span class="status-badge status-aktif">Aktif</span>
                            <?php else: ?>
                                <span class="status-badge status-nonaktif">Non-Aktif</span>
                            <?php endif; ?>
                        </td>
                        <td class="action-links">
                            <a href="index.php?page=edit_siswa&id=<?php echo $student['siswa_id']; ?>" class="edit-link">Edit</a>
                            
                            <?php if ($student['status_aktif']): ?>
                                <a href="hapus_siswa.php?id=<?php echo $student['siswa_id']; ?>" class="deactivate-link" onclick="return confirm('Apakah Anda yakin ingin menonaktifkan siswa ini?');">Nonaktifkan</a>
                            <?php endif; ?>

                            <a href="hapus_permanen_siswa.php?id=<?php echo $student['siswa_id']; ?>" class="delete-link-permanent" onclick="return confirm('PERINGATAN! Anda akan menghapus data siswa ini secara permanen. Aksi ini tidak dapat dibatalkan dan hanya akan berhasil jika siswa tidak memiliki riwayat absensi. Lanjutkan?');">Hapus Permanen</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif(empty($page_message)): ?>
    <p style="text-align:center;">Tidak ada data siswa yang cocok dengan filter atau belum ada data siswa.</p>
<?php endif; ?>