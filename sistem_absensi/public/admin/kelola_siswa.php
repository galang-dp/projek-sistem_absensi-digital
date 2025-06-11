<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// ... (sisa logika PHP Anda untuk mengambil data siswa, filter, pesan, dll.) ...
$search_query = isset($_GET['search']) ? $_GET['search'] : '';
// ... (dan seterusnya)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Siswa - Admin</title>
    <link rel="stylesheet" href="../css/style.css"> </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Kelola Data Siswa</h1>
            <p><a href="dashboard_admin.php" class="admin-link" style="font-size: 0.9em; padding: 5px 10px;">&laquo; Kembali ke Dashboard</a></p>
        </div>

        <?php if (!empty($message)): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <div class="add-button-container">
            <a href="tambah_siswa.php" class="add-button">Tambah Siswa Baru</a>
        </div>

        <form method="GET" action="kelola_siswa.php" class="filter-form">
            <input type="text" name="search" placeholder="Cari NIS atau Nama Siswa..." value="<?php echo htmlspecialchars($search_query); ?>">
            <select name="kelas_id">
                <option value="">Semua Kelas</option>
                <?php foreach ($kelas_list as $kelas): ?>
                    <option value="<?php echo $kelas['kelas_id']; ?>" <?php echo ($filter_kelas_id == $kelas['kelas_id']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($kelas['nama_kelas']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Filter/Cari</button>
            <?php if (!empty($search_query) || !empty($filter_kelas_id)): ?>
                <a href="kelola_siswa.php" class="reset-button">Reset Filter</a> <?php endif; ?>
        </form>

        <?php if (!empty($students)): ?>
            <table class="table-data">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Kelas</th>
                        <th>Nama Orang Tua</th>
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
                            <td><?php echo htmlspecialchars($student['nama_orang_tua'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['telepon_orang_tua'] ?? 'N/A'); ?></td>
                            <td>
                                <?php if ($student['status_aktif']): ?>
                                    <span class="status-badge status-aktif">Aktif</span> <?php else: ?>
                                    <span class="status-badge status-nonaktif">Non-Aktif</span> <?php endif; ?>
                            </td>
                            <td class="action-links">
                                <a href="edit_siswa.php?id=<?php echo $student['siswa_id']; ?>" class="edit-link">Edit</a>
                                <a href="hapus_siswa.php?id=<?php echo $student['siswa_id']; ?>" class="delete-link" onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini? Data absensi terkait juga mungkin terpengaruh.');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif(empty($message)): ?>
            <p style="text-align:center;">Tidak ada data siswa yang cocok dengan filter atau belum ada data siswa.</p>
        <?php endif; ?>
        
        <a href="../logout.php" class="logout-link" style="margin-top: 30px;">Logout</a>
    </div>
</body>
</html>