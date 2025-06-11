<?php
// =========================================================================
// File: public/record_attendance.php
// Tujuan: Menerima data dari alat ESP8266, memvalidasi, dan mencatat absensi ke database.
// =========================================================================

// Atur zona waktu ke Waktu Indonesia Barat agar waktu server sesuai
date_default_timezone_set('Asia/Jakarta');

// 1. Memanggil file koneksi database
// Path ini relatif dari lokasi file ini (public/) ke folder config/
require_once '../config/db_connect.php';

// 2. Hanya proses jika request datang dengan metode POST
// Ini adalah pengaman agar file tidak bisa diakses langsung dari browser.
file_put_contents("log_post.txt", date('Y-m-d H:i:s') . " - " . json_encode($_POST) . "\n", FILE_APPEND);
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. Ambil 'fingerprint_id' yang dikirim oleh alat ESP8266
    // Kita pastikan data yang diterima adalah angka (integer).
    $fingerprint_id_input = isset($_POST['fingerprint_id']) ? (int)$_POST['fingerprint_id'] : 0;
    
    // Siapkan pesan yang akan dikirim kembali ke alat
    $response_message = "";

    // 4. Proses hanya jika fingerprint_id valid (bukan 0)
    if ($fingerprint_id_input > 0) {
        
        // 5. Cari siswa yang cocok di database
        // Query mencari siswa yang statusnya AKTIF dan memiliki ID fingerprint yang sesuai.
        $stmt_find_siswa = $conn->prepare(
            "SELECT siswa_id, nama_lengkap, telepon_ortu FROM Siswa 
             WHERE (fingerprint_template_1 = ? OR fingerprint_template_2 = ?) AND status_aktif = TRUE"
        );
        
        if (!$stmt_find_siswa) {
            http_response_code(500); // Kode error untuk server
            exit("Error DB: Gagal mempersiapkan statement cari siswa.");
        }
        
        $fingerprint_id_str = (string)$fingerprint_id_input;
        $stmt_find_siswa->bind_param("ss", $fingerprint_id_str, $fingerprint_id_str);
        $stmt_find_siswa->execute();
        $result_siswa = $stmt_find_siswa->get_result();

        // 6. Jika siswa ditemukan...
        if ($result_siswa->num_rows > 0) {
            $siswa_data = $result_siswa->fetch_assoc();
            $siswa_id = $siswa_data['siswa_id'];
            $nama_siswa_absen = $siswa_data['nama_lengkap'];

            // 7. Cek apakah siswa sudah tercatat absen hari ini (untuk mencegah data ganda)
            $tanggal_hari_ini = date("Y-m-d");
            $stmt_check_absensi = $conn->prepare("SELECT absensi_id FROM Absensi WHERE siswa_id = ? AND tanggal_absensi = ?");
            $stmt_check_absensi->bind_param("is", $siswa_id, $tanggal_hari_ini);
            $stmt_check_absensi->execute();

            if ($stmt_check_absensi->get_result()->num_rows == 0) {
                // Jika BELUM ADA absensi hari ini, catat sebagai 'Hadir'
                $waktu_scan = date("Y-m-d H:i:s");
                $stmt_insert = $conn->prepare(
                    "INSERT INTO Absensi (siswa_id, tanggal_absensi, waktu_scan_masuk, status_kehadiran, keterangan) 
                     VALUES (?, ?, ?, 'Hadir', 'Absensi via Fingerprint')"
                );
                
                if ($stmt_insert) {
                    $stmt_insert->bind_param("iss", $siswa_id, $tanggal_hari_ini, $waktu_scan);
                    if ($stmt_insert->execute()) {
                        // Jika berhasil disimpan, siapkan pesan sukses
                        $response_message = "Absen SUKSES: " . $nama_siswa_absen;
                    } else {
                        // Jika gagal menyimpan
                        $response_message = "Error: Gagal mencatat absensi.";
                        http_response_code(500);
                    }
                    $stmt_insert->close();
                }
            } else {
                // Jika SUDAH ADA absensi hari ini, siapkan pesan info
                $response_message = "INFO: " . $nama_siswa_absen . " sudah absen.";
            }
            $stmt_check_absensi->close();
        } else {
            // Jika ID sidik jari tidak cocok dengan siswa manapun
            $response_message = "Error: ID " . $fingerprint_id_input . " tidak terdaftar.";
            http_response_code(404); // Not Found
        }
        $stmt_find_siswa->close();
    } else {
        // Jika data fingerprint_id yang dikirim tidak valid (misal: 0)
        $response_message = "Error: Data ID tidak valid.";
        http_response_code(400); // Bad Request
    }
    
    // 8. Kirim respons teks kembali ke ESP8266
    echo $response_message;

} else {
    // Jika skrip diakses bukan dengan metode POST
    http_response_code(405); // Method Not Allowed
    echo "Error: Metode request tidak valid.";
}

// 9. Tutup koneksi database
$conn->close();
?>
```

### **Langkah Selanjutnya: Pengujian**

Sekarang Anda memiliki skrip `record_attendance.php` yang bersih dan terstruktur. Langkah Anda selanjutnya tetap sama seperti sebelumnya, yaitu **melakukan pengujian sistematis** untuk menemukan di mana letak masalah koneksi.

1.  **Tempatkan File:** Pastikan file di atas disimpan dengan nama `record_attendance.php` di dalam folder `public/`.
2.  **Konfigurasi Alat:** Pastikan `serverUrl` di dalam program ESP8266 Anda sudah benar menunjuk ke file ini menggunakan alamat IP lokal Anda (contoh: `http://192.168.9.174/sistem_absensi/public/record_attendance.php`).
3.  **Tes Paling Penting (Gunakan Smartphone):**
    * Buka browser di smartphone Anda (yang terhubung ke WiFi yang sama).
    * Akses URL `http://192.168.9.174/sistem_absensi/public/record_attendance.php`.
    * **Jika Anda melihat pesan "Error: Metode request tidak valid."**, ini adalah kabar baik! Berarti server Anda bisa diakses. Masalahnya mungkin ada pada alat atau kabel USB Anda.
    * **Jika Anda melihat error browser ("This site can't be reached")**, berarti masalahnya ada pada jaringan atau konfigurasi XAMPP/Firewall Anda, dan Anda perlu mengikuti kembali panduan diagnosa yang telah saya berikan.

Lakukan tes dengan smartphone ini dan beri tahu saya hasilnya. Ini akan sangat membantu kita menyelesaikan masalah koneksi An