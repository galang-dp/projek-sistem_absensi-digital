<?php
session_start();
require_once '../config/db_connect.php'; // Sesuaikan path jika berbeda

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password_input = $_POST['password'];

    if (empty($username) || empty($password_input)) {
        header("Location: login.php?error=Username dan password tidak boleh kosong");
        exit();
    }

    // Ambil data user dari database
    $sql = "SELECT user_id, username, password_hash, role FROM Users WHERE username = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        header("Location: login.php?error=Kesalahan database: " . $conn->error);
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        // Verifikasi password
        // GANTILAH INI DENGAN password_verify($password_input, $user['password_hash']) JIKA SUDAH MENGGUNAKAN HASHING
        // if ($password_input === $users['password_hash']) { // INI HANYA UNTUK CONTOH TANPA HASH, SANGAT TIDAK AMAN
        if (password_verify($password_input, $user['password_hash'])) { // Gunakan ini untuk produksi
            // Login sukses
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            // Redirect berdasarkan role
            if ($user['role'] == 'Admin') {
                header("Location: admin/index.php");
                exit();
            } elseif ($user['role'] == 'Guru') {
                header("Location: guru/index.php");
                exit();
            } else {
                header("Location: login.php?error=Role tidak dikenal");
                exit();
            }
        } else {
            header("Location: login.php?error=Username atau password salah");
            exit();
        }
    } else {
        header("Location: login.php?error=Username atau password salah");
        exit();
    }
    $stmt->close();
} else {
    header("Location: login.php");
    exit();
}
$conn->close();
?>