<?php
session_start();
require_once '../../config/db_connect.php'; // Sesuaikan path jika perlu

// 1. Cek jika user belum login atau bukan admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'Admin') {
    header("Location: ../login.php?error=Anda harus login sebagai Admin.");
    exit();
}

// Pesan feedback
$message = '';
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Hapus pesan setelah ditampilkan
}

$search_query = isset($_GET['search']) ? $_GET['search'] : '';

// 2. Ambil semua data orang tua dari database
$sql = "SELECT orang_tua_id, nama_orang_tua, nomor_telepon_notifikasi, email_notifikasi, alamat
        FROM OrangTua";

$conditions = [];
$params = [];
$types = "";

if (!empty($search_query)) {
    // Pencarian bisa berdasarkan nama orang tua, nomor telepon, atau email
    $conditions[] = "(nama_orang_tua LIKE ? OR nomor_telepon_notifikasi LIKE ? OR email_notifikasi LIKE ?)";
    $search_param = "%" . $search_query . "%";
    array_push($params, $search_param, $search_param, $search_param);
    $types .= "sss";
}

if (!empty($conditions)) {
    $sql .= " WHERE " . implode(" AND ", $conditions);
}

$sql .= " ORDER BY nama_orang_tua ASC";

$stmt = $conn->prepare($sql);

if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
} elseif (!$stmt && $conn->error) {
     $message = "<div class='info-message error'>Gagal mempersiapkan query data orang tua: " . htmlspecialchars($conn->error) . "</div>";
}

$orangTuaList = [];
if ($stmt) {
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $orangTuaList[] = $row;
            }
        } else {
             $message = "<div class='info-message error'>Gagal mengambil data orang tua: " . htmlspecialchars($stmt->error) . "</div>";
        }
    } else {
        $message = "<div class='info-message error'>Gagal mengeksekusi query: " . htmlspecialchars($stmt->error) . "</div>";
    }
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Orang Tua - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    </head>
<body>
    <div class="dashboard-container">
        <div class="dashboard-header">
            <h1>Kelola Data Orang Tua</h1>
            <p><a href="dashboard_admin.php" class="admin-link">&laquo; Kembali ke Dashboard</a></p>
        </div>

        <?php if (!empty($message)): ?>
            <?php echo $message; /* Pesan sudah termasuk div dengan kelas .info-message .error/.success */ ?>
        <?php endif; ?>

        <div class="add-button-container">
            <a href="tambah_orangtua.php" class="add-button">Tambah Data Orang Tua Baru</a>
        </div>

        <form method="GET" action="kelola_orangtua.php" class="filter-form">
            <input type="text" name="search" placeholder="Cari Nama, No. Telepon, atau Email Ortu..." value="<?php echo htmlspecialchars($search_query); ?>">
            <button type="submit">Cari</button>
            <?php if (!empty($search_query)): ?>
                <a href="kelola_orangtua.php" class="reset-button">Reset Pencarian</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($orangTuaList)): ?>
            <table class="table-data">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama Orang Tua/Wali</th>
                        <th>No. Telepon Notifikasi</th>
                        <th>Email Notifikasi</th>
                        <th>Alamat</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($orangTuaList as $ortu): ?>
                        <tr>
                            <td><?php echo $no++; ?></td>
                            <td><?php echo htmlspecialchars($ortu['nama_orang_tua']); ?></td>
                            <td><?php echo htmlspecialchars($ortu['nomor_telepon_notifikasi']); ?></td>
                            <td><?php echo htmlspecialchars($ortu['email_notifikasi'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($ortu['alamat'] ?? 'N/A'); ?></td>
                            <td class="action-links">
                                <a href="edit_orangtua.php?id=<?php echo $ortu['orang_tua_id']; ?>" class="edit-link">Edit</a>
                                <a href="hapus_orangtua.php?id=<?php echo $ortu['orang_tua_id']; ?>" class="delete-link" onclick="return confirm('Apakah Anda yakin ingin menghapus data orang tua ini? Pastikan tidak ada siswa yang terkait dengan data ini.');">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php elseif(empty($message)): ?>
            <p style="text-align:center;">Belum ada data orang tua yang ditambahkan atau cocok dengan pencarian.</p>
        <?php endif; ?>
        
        <a href="../logout.php" class="logout-link">Logout</a>
    </div>
</body>
</html>