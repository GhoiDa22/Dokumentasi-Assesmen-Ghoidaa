<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Online</title>
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
        .login-container {
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $identifier = validateInput($_POST['identifier']); // Bisa email atau NIS
        $password = $_POST['password'];

        $stmt = $pdo->prepare("SELECT * FROM members WHERE email = ? OR nis = ?");
        $stmt->execute([$identifier, $identifier]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            redirect('dashboard.php');
        } else {
            $_SESSION['error'] = 'Email/NIS atau password salah!';
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
            <div class="login-container">
                <h1 style="text-align: center;">Login</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                <?php endif; ?>
                <form method="POST">
                    <input type="text" name="identifier" class="input-field" placeholder="Email atau NIS" required>
                    <div style="position: relative;">
                        <input type="password" name="password" id="login-password" class="input-field" placeholder="Password" required>
                        <span class="password-toggle" onclick="togglePassword('login-password')">&#128065;</span>
                    </div>
                    <button type="submit" class="btn">Login</button>
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