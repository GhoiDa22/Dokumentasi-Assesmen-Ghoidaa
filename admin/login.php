<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            background: #ffffff;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .login-container {
            text-align: center;
            padding: 20px;
        }
        .login-container img {
            width: 100px;
            height: 100px;
            margin-bottom: 20px;
        }
        .login-form {
            background: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 350px;
        }
        .input-field {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border: 2px solid #3498db;
            border-radius: 5px;
            font-size: 16px;
        }
        .btn {
            background: #3498db;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }
        .btn:hover {
            background: #8e44ad;
            transform: scale(1.05);
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once '../config/db_connect.php';
    require_once '../includes/functions.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = validateInput($_POST['email']);
        $password = $_POST['password'];
        $stmt = $pdo->prepare("SELECT * FROM admins WHERE email = ?");
        $stmt->execute([$email]);
        $admin = $stmt->fetch();
        if ($admin && $admin['password'] === $password) {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_type'] = 'admin';
            redirect('dashboard.php');
        } else {
            $_SESSION['error'] = 'Email atau password salah!';
        }
    }
    ?>
    <div class="login-container">
        <img src="../assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
        <div class="login-form">
            <h1>Login Admin</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <p style="color: #e74c3c;"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <form method="POST">
                <input type="email" name="email" placeholder="Email" class="input-field" required>
                <input type="password" name="password" placeholder="Password" class="input-field" required>
                <button type="submit" class="btn">Login</button>
            </form>
        </div>
    </div>
</body>
</html>