<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Member - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    session_start();
    require_once '../config/db_connect.php';
    require_once '../includes/functions.php';
    if (!isAdmin()) {
        redirect('login.php');
    }

    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$id]);
    $member = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = validateInput($_POST['name']);
        $email = validateInput($_POST['email']);
        $nis = validateInput($_POST['nis']);
        $card_expiry_date = $_POST['card_expiry_date'];
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $member['password'];

        try {
            $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, nis = ?, password = ?, card_expiry_date = ? WHERE id = ?");
            $stmt->execute([$name, $email, $nis, $password, $card_expiry_date, $id]);
            $_SESSION['success'] = 'Member berhasil diperbarui!';
            redirect('manage_members.php');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
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
            <h1 style="text-align: center;">Edit Member</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <form method="POST">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" class="input-field" required>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" class="input-field" required>
                    <input type="text" name="nis" value="<?php echo htmlspecialchars($member['nis']); ?>" class="input-field" required>
                    <input type="password" name="password" placeholder="Password baru (kosongkan jika tidak diubah)" class="input-field">
                    <input type="date" name="card_expiry_date" value="<?php echo $member['card_expiry_date']; ?>" class="input-field" required>
                    <button type="submit" class="btn">Simpan</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>