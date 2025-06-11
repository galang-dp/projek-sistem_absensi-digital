<?php
// File: public/guru/pages/edit_absensi_content.php
global $conn; // Mengambil koneksi database dari index.php

// Ambil user_id dari session untuk mencari data guru terkait
$user_id_session = $_SESSION['user_id']; 
$guru_id = null;
$kelas_id = null;
$nama_kelas = "Belum ada kelas yang diampu";
$students = [];
$tanggal_hari_ini = date("Y-m-d"); // Kita akan mengedit absensi untuk hari ini
$pesan_error = '';

// Ambil pesan status dari URL jika ada (setelah update)
$pesan_sukses = '';
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'sukses') {
        $pesan_sukses = "Data absensi berhasil diperbarui!";
    } elseif ($_GET['status'] == 'gagal') {
        $pesan_error = "Gagal memperbarui data absensi: " . (isset($_GET['pesan']) ? htmlspecialchars($_GET['pesan']) : '');
    }
}

// 1. Dapatkan guru_id dari user_id
$stmt_guru = $conn->prepare("SELECT guru_id FROM Guru WHERE user_id = ?");
if ($stmt_guru) {
    $stmt_guru->bind_param("i", $user_id_session);
    $stmt_guru->execute();
    $result_guru = $stmt_guru->get_result();
    if ($result_guru->num_rows > 0) {
        $guru_data = $result_guru->fetch_assoc();
        $guru_id = $guru_data['guru_id'];
    } else {
        $pesan_error = "Data guru tidak ditemukan untuk user ini.";
    }
    $stmt_guru->close();
} else {
    $pesan_error = "Gagal mempersiapkan query data guru: " . $conn->error;
}

// 2. Jika guru_id ditemukan, dapatkan kelas yang diampu
if ($guru_id && empty($pesan_error)) {
    $stmt_kelas = $conn->prepare("SELECT kelas_id, nama_kelas FROM Kelas WHERE wali_kelas_id = ?");
    if ($stmt_kelas) {
        $stmt_kelas->bind_param("i", $guru_id);
        $stmt_kelas->execute();
        $result_kelas = $stmt_kelas->get_result();
        if ($result_kelas->num_rows > 0) {
            $kelas_data = $result_kelas->fetch_assoc();
            $kelas_id = $kelas_data['kelas_id'];
            $nama_kelas = $kelas_data['nama_kelas'];
        } else {
            $pesan_error = "Anda belum ditugaskan sebagai wali kelas untuk kelas manapun.";
        }
        $stmt_kelas->close();
    } else {
        $pesan_error = "Gagal mempersiapkan query data kelas: " . $conn->error;
    }
}

// 3. Jika kelas_id ditemukan, dapatkan daftar siswa dan data absensi hari ini
if ($kelas_id && empty($pesan_error)) {
    $sql_siswa = "SELECT s.siswa_id, s.nis, s.nama_lengkap, 
                         a.absensi_id, a.status_kehadiran, a.keterangan
                  FROM Siswa s
                  LEFT JOIN Absensi a ON s.siswa_id = a.siswa_id AND a.tanggal_absensi = ?
                  WHERE s.kelas_id = ? AND s.status_aktif = TRUE
                  ORDER BY s.nama_lengkap ASC";
    
    $stmt_siswa = $conn->prepare($sql_siswa);
    if ($stmt_siswa) {
        $stmt_siswa->bind_param("si", $tanggal_hari_ini, $kelas_id);
        $stmt_siswa->execute();
        $result_siswa = $stmt_siswa->get_result();
        while ($row = $result_siswa->fetch_assoc()) {
            $students[] = $row;
        }
        $stmt_siswa->close();
    } else {
        $pesan_error = "Gagal mempersiapkan query data siswa: " . $conn->error;
    }
}
?>

<div class="form-container" style="max-width: 800px; margin: 0 auto;">
    <div style="text-align:center; margin-bottom: 20px;">
        <h3>Kelas: <?php echo htmlspecialchars($nama_kelas); ?></h3>
        <p><strong>Tanggal: <?php echo date("d F Y", strtotime($tanggal_hari_ini)); ?></strong></p>
    </div>

    <?php if (!empty($pesan_error)): ?>
        <p class="info-message error"><?php echo $pesan_error; ?></p>
    <?php endif; ?>
    <?php if (!empty($pesan_sukses)): ?>
        <p class="info-message success"><?php echo $pesan_sukses; ?></p>
    <?php endif; ?>

    <?php if (empty($pesan_error) || !empty($students)): // Tampilkan form hanya jika tidak ada error awal yang fatal ?>
    <form action="proses_update_absensi.php" method="POST">
        <input type="hidden" name="tanggal_absensi" value="<?php echo $tanggal_hari_ini; ?>">
        <input type="hidden" name="guru_id" value="<?php echo $guru_id; ?>">
        
        <div class="table-responsive">
            <table class="table-absensi">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Status Kehadiran</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($students)): ?>
                        <?php $no = 1; foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($student['nis']); ?></td>
                                <td><?php echo htmlspecialchars($student['nama_lengkap']); ?></td>
                                <td>
                                    <input type="hidden" name="siswa_id[]" value="<?php echo $student['siswa_id']; ?>">
                                    <input type="hidden" name="absensi_id[<?php echo $student['siswa_id']; ?>]" value="<?php echo $student['absensi_id'] ?? ''; ?>">
                                    <select name="status_kehadiran[<?php echo $student['siswa_id']; ?>]" required>
                                        <option value="Belum Absen" <?php echo (($student['status_kehadiran'] ?? 'Belum Absen') == 'Belum Absen') ? 'selected' : ''; ?>>Belum Absen</option>
                                        <option value="Hadir" <?php echo (($student['status_kehadiran'] ?? '') == 'Hadir') ? 'selected' : ''; ?>>Hadir</option>
                                        <option value="Izin" <?php echo (($student['status_kehadiran'] ?? '') == 'Izin') ? 'selected' : ''; ?>>Izin</option>
                                        <option value="Sakit" <?php echo (($student['status_kehadiran'] ?? '') == 'Sakit') ? 'selected' : ''; ?>>Sakit</option>
                                        <option value="Tidak Hadir" <?php echo (($student['status_kehadiran'] ?? '') == 'Tidak Hadir') ? 'selected' : ''; ?>>Tidak Hadir</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="keterangan[<?php echo $student['siswa_id']; ?>]" value="<?php echo htmlspecialchars($student['keterangan'] ?? ''); ?>" placeholder="Keterangan jika Izin/Sakit">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align:center;">Tidak ada siswa aktif di kelas ini.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($students)): ?>
            <button type="submit" name="submit_absensi" class="btn-submit-absensi" style="width:auto; float:right; margin-top:20px;">Simpan Perubahan Absensi</button>
        <?php endif; ?>
    </form>
    <?php endif; ?>
</div>