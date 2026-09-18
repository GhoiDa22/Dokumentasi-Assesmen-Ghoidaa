<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #5620c9ff; /* Warna abu-abu muda */
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 100%;
        }
        .dropdown:hover .dropdown-content {
            display: block;
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

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM books");
    $total_books = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM members");
    $total_members = $stmt->fetch()['count'];

    $stmt = $pdo->query("SELECT COUNT(*) as count FROM loans WHERE status IN ('active', 'overdue')");
    $total_loans = $stmt->fetch()['count'];
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
            <h1 style="text-align: center;">Dashboard Admin</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <div class="book-list">
                    <div class="book-card">
                        <h3>Total Buku</h3>
                        <p style="color: #3498db; font-size: 24px;"><?php echo $total_books; ?></p>
                    </div>
                    <div class="book-card">
                        <h3>Total Member</h3>
                        <p style="color: #8e44ad; font-size: 24px;"><?php echo $total_members; ?></p>
                    </div>
                    <div class="book-card">
                        <h3>Peminjaman Aktif</h3>
                        <p style="color: #e67e22; font-size: 24px;"><?php echo $total_loans; ?></p>
                    </div>
                </div>
            </section>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>