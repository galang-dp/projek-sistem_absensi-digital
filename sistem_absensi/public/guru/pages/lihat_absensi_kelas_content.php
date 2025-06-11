<?php
// File: public/guru/pages/lihat_absensi_kelas_content.php
global $conn; // Mengambil koneksi database dari index.php

// Ambil user_id dari session untuk mencari data guru terkait
$user_id_session = $_SESSION['user_id'];
$guru_id = null;
$kelas_id = null;
$nama_kelas = "Belum ada kelas yang diampu";
$attendance_data = [];
$pesan_error = '';

// Tentukan tanggal yang akan ditampilkan
$selected_date = isset($_GET['tanggal']) ? $_GET['tanggal'] : date("Y-m-d");

// Validasi format tanggal (sederhana)
if (!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/", $selected_date)) {
    $selected_date = date("Y-m-d");
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

// 3. Jika kelas_id ditemukan, dapatkan data absensi siswa untuk tanggal yang dipilih
if ($kelas_id && empty($pesan_error)) {
    $sql_absensi = "SELECT s.nis, s.nama_lengkap, 
                           COALESCE(a.status_kehadiran, 'Belum Absen') as status_kehadiran, 
                           a.keterangan, a.waktu_scan_masuk
                    FROM Siswa s
                    LEFT JOIN Absensi a ON s.siswa_id = a.siswa_id AND a.tanggal_absensi = ?
                    WHERE s.kelas_id = ? AND s.status_aktif = TRUE
                    ORDER BY s.nama_lengkap ASC";
    
    $stmt_absensi = $conn->prepare($sql_absensi);
    if ($stmt_absensi) {
        $stmt_absensi->bind_param("si", $selected_date, $kelas_id);
        $stmt_absensi->execute();
        $result_absensi = $stmt_absensi->get_result();
        while ($row = $result_absensi->fetch_assoc()) {
            $attendance_data[] = $row;
        }
        if (empty($attendance_data) && empty($pesan_error)) {
            $pesan_error = "Tidak ada data absensi untuk ditampilkan pada tanggal ini di kelas Anda.";
        }
        $stmt_absensi->close();
    } else {
        $pesan_error = "Gagal mempersiapkan query data absensi: " . $conn->error;
    }
}

// Hitung rekapitulasi
$summary = ['Hadir' => 0, 'Izin' => 0, 'Sakit' => 0, 'Tidak Hadir' => 0, 'Belum Absen' => 0, 'Total' => count($attendance_data)];
if (!empty($attendance_data)) {
    foreach ($attendance_data as $data) {
        if (isset($summary[$data['status_kehadiran']])) {
            $summary[$data['status_kehadiran']]++;
        }
    }
}
?>

<div class="form-container" style="max-width: 1000px; margin: 0 auto;">
    <div style="text-align:center; margin-bottom: 20px;">
        <h3>Rekap Absensi Kelas: <?php echo htmlspecialchars($nama_kelas); ?></h3>
    </div>

    <form method="GET" action="index.php" class="filter-form">
        <input type="hidden" name="page" value="lihat_absensi_kelas"> <label for="tanggal">Pilih Tanggal:</label>
        <input type="date" id="tanggal" name="tanggal" value="<?php echo htmlspecialchars($selected_date); ?>" required>
        <button type="submit">Tampilkan</button>
    </form>

    <?php if (!empty($pesan_error)): ?>
        <p class="info-message error"><?php echo $pesan_error; ?></p>
    <?php endif; ?>

    <?php if (empty($pesan_error) || !empty($attendance_data)): ?>
        <p>Menampilkan absensi untuk tanggal: <strong><?php echo date("d F Y", strtotime($selected_date)); ?></strong></p>

        <div class="summary-box">
            <div class="summary-item"><strong><?php echo $summary['Total']; ?></strong> <span>Total Siswa</span></div>
            <div class="summary-item"><strong><?php echo $summary['Hadir']; ?></strong> <span style="color: green;">Hadir</span></div>
            <div class="summary-item"><strong><?php echo $summary['Izin']; ?></strong> <span style="color: blue;">Izin</span></div>
            <div class="summary-item"><strong><?php echo $summary['Sakit']; ?></strong> <span style="color: orange;">Sakit</span></div>
            <div class="summary-item"><strong><?php echo $summary['Tidak Hadir']; ?></strong> <span style="color: red;">Tidak Hadir</span></div>
            <div class="summary-item"><strong><?php echo $summary['Belum Absen']; ?></strong> <span style="color: grey;">Belum Absen</span></div>
        </div>

        <div class="table-responsive">
            <table class="table-data"> <thead>
                    <tr>
                        <th>No.</th>
                        <th>NIS</th>
                        <th>Nama Lengkap</th>
                        <th>Status Kehadiran</th>
                        <th>Waktu Scan Masuk</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($attendance_data)): ?>
                        <?php $no = 1; foreach ($attendance_data as $data): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo htmlspecialchars($data['nis']); ?></td>
                                <td><?php echo htmlspecialchars($data['nama_lengkap']); ?></td>
                                <td><?php echo htmlspecialchars($data['status_kehadiran']); ?></td>
                                <td><?php echo $data['waktu_scan_masuk'] ? date("H:i:s", strtotime($data['waktu_scan_masuk'])) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($data['keterangan'] ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <?php if(empty($pesan_error)): ?>
                        <tr>
                            <td colspan="6" style="text-align:center;">Tidak ada data absensi untuk tanggal dan kelas ini.</td>
                        </tr>
                        <?php endif; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>