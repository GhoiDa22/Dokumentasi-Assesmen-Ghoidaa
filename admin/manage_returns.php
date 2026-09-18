<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengembalian - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #5620c9ff; /* Ungu tua transparan */
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 100%;
            color: #fff; /* Teks putih biar kontras */
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_loan'])) {
        $loan_id = (int)$_POST['loan_id'];
        // Update status dan return_date
        $stmt = $pdo->prepare("UPDATE loans SET status = 'returned', return_date = NOW() WHERE id = ? AND status IN ('active', 'overdue')");
        $stmt->execute([$loan_id]);

        // Tambah stok buku
        $stmt = $pdo->prepare("UPDATE books SET stock = stock + 1 WHERE id = (SELECT book_id FROM loans WHERE id = ?)");
        $stmt->execute([$loan_id]);
        $_SESSION['success'] = 'Pengembalian berhasil diproses!';
        redirect('manage_returns.php');
    }

    $stmt = $pdo->prepare("SELECT l.*, m.name AS member_name, m.nis, b.title AS book_title 
                         FROM loans l 
                         JOIN members m ON l.member_id = m.id 
                         JOIN books b ON l.book_id = b.id 
                         WHERE l.status IN ('active', 'overdue') 
                         ORDER BY l.due_date ASC");
    $stmt->execute();
    $loans = $stmt->fetchAll();
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
            <h1 style="text-align: center;">Kelola Pengembalian</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <table>
                    <tr>
                        <th>Nama Member</th>
                        <th>NIS</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    <?php if (empty($loans)): ?>
                        <tr><td colspan="7">Tidak ada peminjaman untuk dikembalikan.</td></tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['member_name']); ?></td>
                                <td><?php echo htmlspecialchars($loan['nis']); ?></td>
                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['loan_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['due_date'])); ?></td>
                                <td><?php echo $loan['status'] === 'active' ? 'Aktif' : 'Terlambat'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                        <button type="submit" name="return_loan" class="btn confirm-btn" onclick="return confirm('Proses pengembalian buku ini?')">Kembalikan</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </section>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>