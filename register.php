<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Perpustakaan Online</title>
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
        .register-container {
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
        $name = validateInput($_POST['name']);
        $email = validateInput($_POST['email']);
        $nis = validateInput($_POST['nis']);
        $jurusan = validateInput($_POST['jurusan']);
        $no_hp = validateInput($_POST['no_hp']);
        $kelas = validateInput($_POST['kelas']);
        $password = $_POST['password']; // Tanpa hashing

        $stmt = $pdo->prepare("SELECT email, nis FROM members WHERE email = ? OR nis = ?");
        $stmt->execute([$email, $nis]);
        $existing = $stmt->fetch();
        if ($existing) {
            if ($existing['email'] === $email) {
                $_SESSION['error'] = 'Email sudah terdaftar! Coba email lain.';
            } elseif ($existing['nis'] === $nis) {
                $_SESSION['error'] = 'NIS sudah terdaftar! Coba NIS lain.';
            }
        } else {
            $card_expiry_date = date('Y-m-d', strtotime('+1 year'));
            $stmt = $pdo->prepare("INSERT INTO members (name, email, nis, jurusan, no_hp, kelas, password, card_expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $nis, $jurusan, $no_hp, $kelas, $password, $card_expiry_date]);
            $success_message = 'Member berhasil mendaftar! Anda akan diarahkan ke halaman login dalam 2 detik...';
            header("Refresh:2; url=login.php");
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
            <div class="register-container">
                <h1 style="text-align: center;">Daftar</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                <?php endif; ?>
                <?php if ($success_message): ?>
                    <p class="success"><?php echo htmlspecialchars($success_message); ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="name" class="input-field" placeholder="Nama" required>
                    <input type="email" name="email" class="input-field" placeholder="Email" required>
                    <input type="text" name="nis" class="input-field" placeholder="NIS" required>
                    <select name="jurusan" class="input-field" required>
                        <option value="">Pilih Jurusan</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Manajemen Perkantoran dan Layanan Bisnis">Manajemen Perkantoran dan Layanan Bisnis</option>
                        <option value="Pemasaran">Pemasaran</option>
                        <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                    </select>
                    <input type="text" name="no_hp" class="input-field" placeholder="No HP" required>
                    <input type="text" name="kelas" class="input-field" placeholder="Kelas" required>
                    <div style="position: relative;">
                        <input type="password" name="password" id="register-password" class="input-field" placeholder="Password" required>
                        <span class="password-toggle" onclick="togglePassword('register-password')">&#128065;</span>
                    </div>
                    <button type="submit" class="btn">Daftar</button>
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