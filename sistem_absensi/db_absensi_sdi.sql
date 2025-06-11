-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 11, 2025 at 10:48 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_absensi_sdi`
--

-- --------------------------------------------------------

--
-- Table structure for table `absensi`
--

CREATE TABLE `absensi` (
  `absensi_id` int(11) NOT NULL,
  `siswa_id` int(11) NOT NULL,
  `tanggal_absensi` date NOT NULL,
  `waktu_scan_masuk` datetime DEFAULT NULL,
  `status_kehadiran` enum('Hadir','Izin','Sakit','Tidak Hadir','Belum Absen') NOT NULL DEFAULT 'Belum Absen',
  `keterangan` text DEFAULT NULL,
  `diedit_oleh_guru_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `absensi`
--

INSERT INTO `absensi` (`absensi_id`, `siswa_id`, `tanggal_absensi`, `waktu_scan_masuk`, `status_kehadiran`, `keterangan`, `diedit_oleh_guru_id`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-06-10', NULL, 'Belum Absen', '', 4, '2025-06-10 10:01:11', '2025-06-10 13:40:20');

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `user_id`, `nama_lengkap`, `email`, `nomor_telepon`, `created_at`, `updated_at`) VALUES
(1, 1, 'Administrator Utama', 'admin@sdialhasanah.sch.id', '081234567890', '2025-06-04 07:09:53', '2025-06-04 07:09:53');

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE `guru` (
  `guru_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nomor_telepon` varchar(20) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`guru_id`, `user_id`, `nip`, `nama_lengkap`, `email`, `nomor_telepon`, `alamat`, `created_at`, `updated_at`) VALUES
(1, 2, '198503102010012001', 'Aji Pengajar', 'siti.pengajar@sdialhasanah.sch.id', '081298765432', 'Jl. Pendidikan No. 10', '2025-06-04 07:56:13', '2025-06-04 07:56:13'),
(3, 4, '321654651515131212', 'paiz970', 'paiz20@gmail.com', '085153556159', 'jl.hjdfcgnn', '2025-06-04 16:18:58', '2025-06-04 16:18:58'),
(4, 6, '3216546515151312123', 'alam', 'alam123@gmail.com', '085153556154', 'asdasadsasa', '2025-06-05 10:41:28', '2025-06-05 10:41:28'),
(5, 7, '3216546515151312122', 'nilam', 'nilam@gmail.com', '0851535561565', 'jbkgukbj,gkug,kb', '2025-06-10 13:58:33', '2025-06-10 13:58:33');

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `kelas_id` int(11) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL,
  `tingkat` varchar(10) DEFAULT NULL,
  `tahun_ajaran` varchar(10) DEFAULT NULL,
  `semester` enum('Ganjil','Genap') DEFAULT NULL,
  `wali_kelas_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`kelas_id`, `nama_kelas`, `tingkat`, `tahun_ajaran`, `semester`, `wali_kelas_id`, `created_at`, `updated_at`) VALUES
(4, 'Kelas 1-A', '1', '2024/2025', 'Ganjil', 4, '2025-06-09 08:18:31', '2025-06-09 08:18:31'),
(5, 'Kelas 4-A', '4', '2024/2025', 'Ganjil', 4, '2025-06-09 12:12:02', '2025-06-09 12:33:52'),
(8, 'Kelas 1-B', '1', '2021/2022', 'Ganjil', 1, '2025-06-09 12:17:25', '2025-06-09 12:17:25'),
(9, 'Kelas 3-A', '3', '2024/2025', 'Genap', 5, '2025-06-10 13:59:02', '2025-06-10 13:59:02');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `siswa_id` int(11) NOT NULL,
  `nis` varchar(30) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `nama_ayah` varchar(100) DEFAULT NULL,
  `pekerjaan_ayah` varchar(100) DEFAULT NULL,
  `nama_ibu` varchar(100) DEFAULT NULL,
  `pekerjaan_ibu` varchar(100) DEFAULT NULL,
  `telepon_ortu` varchar(20) DEFAULT NULL,
  `kelas_id` int(11) DEFAULT NULL,
  `fingerprint_template_1` text DEFAULT NULL,
  `fingerprint_template_2` text DEFAULT NULL,
  `status_aktif` tinyint(1) DEFAULT 1,
  `foto_siswa` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`siswa_id`, `nis`, `nama_lengkap`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `nama_ayah`, `pekerjaan_ayah`, `nama_ibu`, `pekerjaan_ibu`, `telepon_ortu`, `kelas_id`, `fingerprint_template_1`, `fingerprint_template_2`, `status_aktif`, `foto_siswa`, `created_at`, `updated_at`) VALUES
(2, '132123131231', 'paiz', '2024-07-17', 'Laki-laki', 'weewWDWderwfae', 'efaaefa', 'adsasd', 'asdasddsa', 'asdasda', '2131312312313212', 4, '1', NULL, 1, 'public/uploads/foto_siswa/siswa_132123131231_1749471278.jpeg', '2025-06-09 12:14:38', '2025-06-10 16:37:33'),
(3, '1321231312312', 'siti', '2021-11-10', 'Perempuan', 'klnljnbjkbnjbjkbjkbk', 'zcxz', 'zxcxzcxzc', 'adasd', 'xczcsa', '085511112222', 9, '15', NULL, 1, '', '2025-06-10 13:43:26', '2025-06-10 13:59:28');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin','Guru') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'admin_sdi', '$2y$10$O.t9OXR2oFowFXikfWgwFeCu5DrHNol1cuvAKSf9yC0yumOGeApdu', 'Admin', '2025-06-04 07:03:29', '2025-06-04 07:03:29'),
(2, 'guru_sdi', '$2y$10$qR5z16NHKMJb2NlXn1fjjOPAH/SJ3M5WUo3Mu9QvYakE3SiAObG4', 'Guru', '2025-06-04 07:55:05', '2025-06-04 07:55:05'),
(4, 'paiz12', 'pais456', 'Guru', '2025-06-04 16:18:58', '2025-06-04 16:18:58'),
(5, 'adminsaja1', '$2y$10$fUm.UQecQiF9xMY.t0vRfOefrw6lFx4dw4XJ4cAHFAloKbGUCps82', 'Admin', '2025-06-05 16:21:02', '2025-06-04 16:43:02'),
(6, 'alam', '$2y$10$widdEv/TmLVBRf/v2nEoh.rW89Om3KzC60NmUmfUpjdElK/NfMl4O', 'Guru', '2025-06-05 10:41:28', '2025-06-05 10:41:28'),
(7, 'nilam', '$2y$10$o5Y3QgHoF07drDfyJ5Er7e8hHXM79vBmWt28BkxdolEr3qAJjb/Ty', 'Guru', '2025-06-10 13:58:33', '2025-06-10 13:58:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `absensi`
--
ALTER TABLE `absensi`
  ADD PRIMARY KEY (`absensi_id`),
  ADD UNIQUE KEY `unique_absensi_siswa_tanggal` (`siswa_id`,`tanggal_absensi`),
  ADD KEY `diedit_oleh_guru_id` (`diedit_oleh_guru_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `guru`
--
ALTER TABLE `guru`
  ADD PRIMARY KEY (`guru_id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `nip` (`nip`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`kelas_id`),
  ADD UNIQUE KEY `nama_kelas` (`nama_kelas`),
  ADD KEY `wali_kelas_id` (`wali_kelas_id`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`siswa_id`),
  ADD UNIQUE KEY `nis` (`nis`),
  ADD KEY `kelas_id` (`kelas_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `absensi`
--
ALTER TABLE `absensi`
  MODIFY `absensi_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `guru`
--
ALTER TABLE `guru`
  MODIFY `guru_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `kelas`
--
ALTER TABLE `kelas`
  MODIFY `kelas_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `siswa`
--
ALTER TABLE `siswa`
  MODIFY `siswa_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `absensi`
--
ALTER TABLE `absensi`
  ADD CONSTRAINT `absensi_ibfk_1` FOREIGN KEY (`siswa_id`) REFERENCES `siswa` (`siswa_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `absensi_ibfk_2` FOREIGN KEY (`diedit_oleh_guru_id`) REFERENCES `guru` (`guru_id`) ON DELETE SET NULL;

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `guru`
--
ALTER TABLE `guru`
  ADD CONSTRAINT `guru_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `kelas_ibfk_1` FOREIGN KEY (`wali_kelas_id`) REFERENCES `guru` (`guru_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
