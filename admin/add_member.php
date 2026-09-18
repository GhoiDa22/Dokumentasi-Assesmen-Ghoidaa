<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Member - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
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
        .auth-section form {
            display: flex;
            flex-direction: column;
            gap: 10px;
            max-width: 400px;
            margin: 0 auto;
        }
        .input-field {
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn {
            padding: 10px;
            background: #5620c9ff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #4517a0ff;
        }
        select.input-field {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24"><path d="M7 10l5 5 5-5z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once '../config/db_connect.php';
    require_once '../includes/functions.php';
    if (!isAdmin()) {
        redirect('login.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = validateInput($_POST['name']);
        $email = validateInput($_POST['email']);
        $nis = validateInput($_POST['nis']);
        $jurusan = validateInput($_POST['jurusan']);
        $kelas = validateInput($_POST['kelas']);
        $no_hp = validateInput($_POST['no_hp']);
        $password = $_POST['password']; // Ga di-hash, sesuai request
        $card_expiry_date = date('Y-m-d', strtotime('+1 year'));

        // Cek duplikat email dan NIS
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
            $stmt = $pdo->prepare("INSERT INTO members (name, email, nis, jurusan, kelas, no_hp, password, card_expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $nis, $jurusan, $kelas, $no_hp, $password, $card_expiry_date]);
            $_SESSION['success'] = 'Member berhasil ditambahkan!';
            redirect('add_member.php');
        }
    }
    ?>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                <br> SMKN 1 Surakarta
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="dropdown">
                        <a href="#">Kelola Data</a>
                        <div class="dropdown-content">
                            <a href="add_book.php">Tambah Buku</a>
                            <a href="manage_books.php">Kelola Buku</a>
                            <a href="add_member.php">Tambah Member</a>
                            <a href="manage_members.php">Kelola Member</a>
                        </div>
                    </li>
                    <li><a href="manage_loans.php">Kelola Peminjaman</a></li>
                    <li><a href="manage_returns.php">Kelola Pengembalian</a></li>
                    <li><a href="manage_fines.php">Kelola Denda</a></li>
                    <li><a href="loan_report.php">Laporan Peminjaman</a></li>
                    <li><a href="../config/auth.php?logout=1">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1 style="text-align: center;">Tambah Member</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="auth-section">
                <form method="POST">
                    <input type="text" name="name" placeholder="Nama" class="input-field" required>
                    <input type="email" name="email" placeholder="Email" class="input-field" required>
                    <input type="text" name="nis" placeholder="NIS" class="input-field" required>
                    <select name="jurusan" class="input-field" required>
                        <option value="">Pilih Jurusan</option>
                        <option value="Akuntansi">Akuntansi</option>
                        <option value="Manajemen Perkantoran dan Layanan Bisnis">Manajemen Perkantoran dan Layanan Bisnis</option>
                        <option value="Pemasaran">Pemasaran</option>
                        <option value="Desain Komunikasi Visual">Desain Komunikasi Visual</option>
                    </select>
                    <input type="text" name="kelas" placeholder="Kelas (contoh: 12)" class="input-field" required>
                    <input type="text" name="no_hp" placeholder="No HP" class="input-field" required>
                    <input type="password" name="password" placeholder="Password" class="input-field" required>
                    <button type="submit" class="btn">Tambah Member</button>
                </form>
            </section>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>