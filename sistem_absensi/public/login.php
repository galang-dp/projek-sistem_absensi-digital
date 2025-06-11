<?php
session_start();
// Jika sudah login, redirect ke halaman index.php yang baru
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'Admin') {
        header("Location: admin/index.php"); // Arahkan ke index.php admin
        exit();
    } elseif ($_SESSION['role'] == 'Guru') {
        header("Location: guru/index.php"); // Arahkan ke index.php guru
        exit();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Absensi SDI Al-Hasanah</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="body-centered"> 
    <div class="login-container">
        <h2>Login Sistem Absensi</h2>
        <?php
        if (isset($_GET['error'])) {
            echo "<p class='error-message'>" . htmlspecialchars($_GET['error']) . "</p>";
        }
        ?>
        <form action="process_login.php" method="POST">
            <div>
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div>
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div>
                <button type="submit">Login</button>
            </div>
        </form>
    </div>
</body>
</html>