<?php
// File: public/admin/pages/kelola_guru_content.php (Versi Final Diperbaiki)
global $conn; // Mengambil koneksi database dari index.php

// Pesan feedback dari aksi sebelumnya
$page_message = '';
if (isset($_SESSION['message'])) {
    $page_message = $_SESSION['message'];
    unset($_SESSION['message']);
}

// Logika untuk pencarian
$search_query = isset($_GET['search']) ? trim($_GET['search']) : '';

// Ambil semua data guru dari database dengan pencarian
$sql = "SELECT g.guru_id, g.nip, g.nama_lengkap, g.email, g.nomor_telepon, u.username
        FROM Guru g
        LEFT JOIN Users u ON g.user_id = u.user_id";

$params = [];
$types = "";

if (!empty($search_query)) {
    $sql .= " WHERE (g.nip LIKE ? OR g.nama_lengkap LIKE ? OR g.email LIKE ? OR u.username LIKE ?)";
    $search_param = "%" . $search_query . "%";
    array_push($params, $search_param, $search_param, $search_param, $search_param);
    $types .= "ssss";
}

$sql .= " ORDER BY g.nama_lengkap ASC";

$stmt = $conn->prepare($sql);

if ($stmt && !empty($params)) {
    $stmt->bind_param($types, ...$params);
} elseif (!$stmt && $conn->error) {
     $page_message = "<div class='info-message error'>Gagal mempersiapkan query data guru: " . htmlspecialchars($conn->error) . "</div>";
}

$gurus = [];
if ($stmt && empty($page_message)) {
    if (!$stmt->execute()) {
        $page_message = "<div class='info-message error'>Gagal mengeksekusi query: " . htmlspecialchars($stmt->error) . "</div>";
    } else {
        $result = $stmt->get_result();
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $gurus[] = $row;
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
    <a href="index.php?page=tambah_guru" class="add-button">Tambah Guru Baru</a>
</div>

<form method="GET" action="index.php" class="filter-form">
    <input type="hidden" name="page" value="kelola_guru">
    <div style="flex-grow: 2;">
        <input type="text" name="search" placeholder="Cari NIP, Nama, Email, atau Username..." value="<?php echo htmlspecialchars($search_query); ?>">
    </div>
    <div>
        <button type="submit">Cari</button>
    </div>
    <?php if (!empty($search_query)): ?>
        <div>
            <a href="index.php?page=kelola_guru" class="reset-button">Reset</a>
        </div>
    <?php endif; ?>
</form>

<?php if (!empty($gurus)): ?>
    <div class="table-responsive">
        <table class="table-data">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>NIP</th>
                    <th>Nama Lengkap</th>
                    <th>Email</th>
                    <th>No. Telepon</th>
                    <th>Username Login</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php $no = 1; foreach ($gurus as $guru): ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($guru['nip'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($guru['nama_lengkap']); ?></td>
                        <td><?php echo htmlspecialchars($guru['email'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($guru['nomor_telepon'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($guru['username'] ?? 'N/A'); ?></td>
                        <td class="action-links">
                            <a href="index.php?page=edit_guru&id=<?php echo $guru['guru_id']; ?>" class="edit-link">Edit</a>
                            <a href="hapus_guru.php?id=<?php echo $guru['guru_id']; ?>" class="delete-link-permanent" onclick="return confirm('PERINGATAN! Anda akan menghapus data guru ini dan akun loginnya secara permanen. Lanjutkan?');">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php elseif(empty($page_message)): ?>
    <p style="text-align:center; margin-top: 20px;">Belum ada data guru yang ditambahkan atau cocok dengan pencarian.</p>
<?php endif; ?>