<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #5620c9ff;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 100%;
            color: #fff;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .forgot-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config/db_connect.php';
    require_once 'includes/functions.php';

    if (isset($_SESSION['user_id'])) {
        redirect('dashboard.php');
    }

    $success_message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $identifier = validateInput($_POST['identifier']); // Bisa email atau NIS
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];

        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? OR nis = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user) {
            if ($old_password === $user['password']) {
                $stmt = $pdo->prepare("UPDATE members SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $user['id']]);
                $success_message = 'Password berhasil diperbarui! Anda akan diarahkan ke halaman login dalam 2 detik...';
                header("Refresh:2; url=login.php");
            } else {
                $_SESSION['error'] = 'Password lama salah!';
            }
        } else {
            $_SESSION['error'] = 'Email atau NIS tidak ditemukan!';
        }
    }
    ?>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                Perpustakaan SMKN 1 Surakarta
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li class="dropdown">
                        <a href="#">Member</a>
                        <div class="dropdown-content">
                            <a href="register.php">Daftar</a>
                            <a href="login.php">Login</a>
                            <a href="forgot_password.php">Lupa Password</a>
                        </div>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <div class="forgot-container">
                <h1 style="text-align: center;">Lupa Password</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="identifier" class="input-field" placeholder="Email atau NIS" required>
                    <div style="position: relative;">
                        <input type="password" name="old_password" id="old-password" class="input-field" placeholder="Password Lama" required>
                        <span class="password-toggle" onclick="togglePassword('old-password')">&#128065;</span>
                    </div>
                    <div style="position: relative;">
                        <input type="password" name="new_password" id="new-password" class="input-field" placeholder="Password Baru" required>
                        <span class="password-toggle" onclick="togglePassword('new-password')">&#128065;</span>
                    </div>
                    <button type="submit" class="btn">Ubah Password</button>
                </form>
            </div>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        function togglePassword(id) {
            const password = document.getElementById(id);
            const toggle = password.nextElementSibling;
            if (password.type === "password") {
                password.type = "text";
                toggle.textContent = "🙈"; // Mata tertutup
            } else {
                password.type = "password";
                toggle.textContent = "🙉"; // Mata terbuka
            }
        }
    </script>
</body>
</html>