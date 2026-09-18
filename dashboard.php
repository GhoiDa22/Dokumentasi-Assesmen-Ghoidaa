<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Perpustakaan Online</title>
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
        .profile-section img {
            max-width: 100px;
            height: auto;
            border-radius: 50%;
        }
        .loan-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .loan-table th, .loan-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <?php
    session_start();
    require_once 'config/db_connect.php';
    require_once 'includes/functions.php';

    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $name = validateInput($_POST['name']);
        $email = validateInput($_POST['email']);
        $nis = validateInput($_POST['nis']);
        $password = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : $user['password'];

        $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, nis = ?, password = ? WHERE id = ?");
        $stmt->execute([$name, $email, $nis, $password, $user_id]);
        $_SESSION['success'] = 'Profil berhasil diperbarui!';
        redirect('dashboard.php');
    }

    $stmt = $pdo->prepare("SELECT l.*, b.title AS book_title FROM loans l JOIN books b ON l.book_id = b.id WHERE l.member_id = ? AND l.status IN ('active', 'overdue') ORDER BY l.loan_date DESC");
    $stmt->execute([$user_id]);
    $loans = $stmt->fetchAll();
    ?>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                Perpustakaan SMKN 1 Surakarta
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="print_card.php">Cetak Kartu Member</a></li>
                    <li><a href="search.php">Cari Buku</a></li>
                    <li><a href="profile.php">Profil</a></li>
                    <li><a href="config/auth.php?logout=1">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1 style="text-align: center;">Dashboard Member</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <h2>Informasi Peminjaman</h2>
                <table class="loan-table" id="loan-table">
                    <tr>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Peminjaman</th>
                        <th>Status Pengambilan</th>
                    </tr>
                    <?php if (empty($loans)): ?>
                        <tr><td colspan="5">Tidak ada peminjaman aktif.</td></tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                            <tr data-loan-id="<?php echo $loan['id']; ?>">
                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['loan_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['due_date'])); ?></td>
                                <td><?php echo $loan['status'] === 'active' ? 'Aktif' : 'Terlambat'; ?></td>
                                <td id="pickup-<?php echo $loan['id']; ?>">
                                    <?php echo $loan['pickup_status'] === 'pending' ? 'Pending' : ($loan['pickup_status'] === 'instructed' ? 'Disuruh Ambil di Perpus' : 'Buku Sudah Dibawa'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </section>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        function updateLoanStatus() {
            $('#loan-table tr[data-loan-id]').each(function() {
                const loanId = $(this).data('loan-id');
                $.get('get_loan_status.php', { loan_id: loanId }, function(response) {
                    if (response && response.pickup_status) {
                        const statusText = response.pickup_status === 'pending' ? 'Pending' :
                            response.pickup_status === 'instructed' ? 'Disuruh Ambil di Perpus' : 'Buku Sudah Dibawa';
                        $('#pickup-' + loanId).text(statusText);
                    }
                }, 'json');
            });
        }

        // Panggil setiap 5 detik
        setInterval(updateLoanStatus, 5000);

        // Panggil sekali saat halaman dimuat
        $(document).ready(function() {
            updateLoanStatus();
        });
    </script>
</body>
</html>