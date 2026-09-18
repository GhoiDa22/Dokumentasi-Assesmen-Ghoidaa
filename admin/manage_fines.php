<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Denda - Perpustakaan Online</title>
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
        .fine-form {
            margin-bottom: 20px;
        }
        .btn-email {
            padding: 10px 20px;
            background: #007bff; /* Biru */
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 5px;
        }
        .btn-email:hover {
            background: #0056b3; /* Biru gelap */
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

    // Default tarif denda per hari (dari input atau 500 kalau belum diatur)
    $fine_rate_per_day = isset($_SESSION['fine_rate_per_day']) ? (int)$_SESSION['fine_rate_per_day'] : 500;

    // Proses input tarif denda
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_fine_rate'])) {
        $new_rate = (int)$_POST['fine_rate_per_day'];
        if ($new_rate > 0) {
            $_SESSION['fine_rate_per_day'] = $new_rate;
            $fine_rate_per_day = $new_rate;
            $_SESSION['success'] = 'Tarif denda berhasil diatur sebesar Rp ' . number_format($new_rate, 0, ',', '.') . ' per hari!';
        } else {
            $_SESSION['error'] = 'Tarif denda harus lebih dari 0!';
        }
        redirect('manage_fines.php');
    }

    // Update atau hitung denda otomatis
    $stmt = $pdo->prepare("SELECT l.id AS loan_id, l.member_id, l.due_date, f.id AS fine_id, f.amount, f.status, m.name AS member_name, m.nis, b.title AS book_title, m.email 
                         FROM loans l 
                         LEFT JOIN fines f ON l.id = f.loan_id 
                         JOIN members m ON l.member_id = m.id 
                         JOIN books b ON l.book_id = b.id 
                         WHERE l.status = 'overdue' AND (f.id IS NULL OR f.status = 'pending')");
    $stmt->execute();
    $overdue_loans = $stmt->fetchAll();
    foreach ($overdue_loans as $loan) {
        $due_date = new DateTime($loan['due_date']);
        $now = new DateTime();
        $days_late = max(0, $due_date->diff($now)->days); // Hanya hitung hari positif
        $fine_amount = $days_late * $fine_rate_per_day;

        if ($loan['fine_id']) {
            $stmt = $pdo->prepare("UPDATE fines SET amount = ?, updated_at = NOW() WHERE id = ?");
            $stmt->execute([$fine_amount, $loan['fine_id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO fines (loan_id, member_id, amount, status, created_at) VALUES (?, ?, ?, 'pending', NOW())");
            $stmt->execute([$loan['loan_id'], $loan['member_id'], $fine_amount]);
        }

        // Tulis ke file log
        $log_entry = "Tanggal: " . date('Y-m-d H:i:s') . "\n"
                   . "Nama Member: " . htmlspecialchars($loan['member_name']) . "\n"
                   . "Judul Buku: " . htmlspecialchars($loan['book_title']) . "\n"
                   . "Jumlah Denda: Rp " . number_format($fine_amount, 0, ',', '.') . "\n"
                   . "Hari Telat: " . $days_late . " hari\n"
                   . "------------------------\n";
        file_put_contents('denda_log.txt', $log_entry, FILE_APPEND | LOCK_EX);
    }

    // Proses update status denda
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_fine'])) {
        $fine_id = (int)$_POST['fine_id'];
        $status = validateInput($_POST['status']);
        $stmt = $pdo->prepare("UPDATE fines SET status = ? WHERE id = ?");
        $stmt->execute([$status, $fine_id]);
        $_SESSION['success'] = 'Status denda berhasil diperbarui!';
        redirect('manage_fines.php');
    }

    // Proses kirim email denda
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_fine_email'])) {
        $fine_id = (int)$_POST['fine_id'];
        $stmt = $pdo->prepare("SELECT f.amount, f.status, m.name AS member_name, m.email, b.title AS book_title, l.due_date 
                             FROM fines f 
                             JOIN loans l ON f.loan_id = l.id 
                             JOIN members m ON f.member_id = m.id 
                             JOIN books b ON l.book_id = b.id 
                             WHERE f.id = ?");
        $stmt->execute([$fine_id]);
        $fine = $stmt->fetch();
        if ($fine && $fine['status'] === 'pending') {
            $due_date = new DateTime($fine['due_date']);
            $now = new DateTime();
            $days_late = max(0, $due_date->diff($now)->days);

            require_once '../vendor/autoload.php';
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '23.rizkia.ahsan@poltekindonusa.ac.id';
                $mail->Password = 'vajpnpceesgyrybi';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                $mail->setFrom('23.rizkia.ahsan@poltekindonusa.ac.id', 'Perpustakaan SMKN 1 Surakarta');
                $mail->addAddress($fine['email']);
                $mail->isHTML(true);
                $mail->Subject = 'Pemberitahuan Denda Peminjaman';
                $mail->Body = "Halo {$fine['member_name']},<br><br>" .
                              "Anda terkena denda karena keterlambatan pengembalian buku '{$fine['book_title']}'.<br><br>" .
                              "Detail:<br>" .
                              "- Tanggal Jatuh Tempo: " . date('d-m-Y', strtotime($fine['due_date'])) . "<br>" .
                              "- Hari Telat: {$days_late} hari<br>" .
                              "- Jumlah Denda: Rp " . number_format($fine['amount'], 0, ',', '.') . "<br><br>" .
                              "Silakan segera bayar denda ini di perpustakaan.<br><br>" .
                              "Terima kasih,<br>Perpustakaan SMKN 1 Surakarta";
                $mail->send();
                $_SESSION['success'] = 'Notifikasi email denda berhasil dikirim!';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Gagal mengirim email: ' . $mail->ErrorInfo;
            }
        } else {
            $_SESSION['error'] = 'Email tidak dapat dikirim: Denda sudah dibayar atau tidak valid!';
        }
        redirect('manage_fines.php');
    }

    $stmt = $pdo->prepare("SELECT f.*, m.name AS member_name, m.nis, b.title AS book_title, l.due_date, m.email 
                         FROM fines f 
                         JOIN loans l ON f.loan_id = l.id 
                         JOIN members m ON f.member_id = m.id 
                         JOIN books b ON l.book_id = b.id 
                         ORDER BY f.created_at DESC");
    $stmt->execute();
    $fines = $stmt->fetchAll();

    // Hitung hari telat untuk setiap denda
    $now = new DateTime();
    foreach ($fines as &$fine) {
        $due_date = new DateTime($fine['due_date']);
        $fine['days_late'] = max(0, $due_date->diff($now)->days);
    }
    unset($fine); // Hapus referensi terakhir
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
            <h1 style="text-align: center;">Kelola Denda</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <div class="fine-form">
                    <h2>Atur Tarif Denda per Hari</h2>
                    <form method="POST">
                        <input type="number" name="fine_rate_per_day" class="input-field" value="<?php echo $fine_rate_per_day; ?>" min="1" required>
                        <button type="submit" name="set_fine_rate" class="btn">Simpan Tarif</button>
                    </form>
                    <p>Tarif saat ini: Rp <?php echo number_format($fine_rate_per_day, 0, ',', '.'); ?> per hari</p>
                </div>
                <table>
                    <tr>
                        <th>Nama Member</th>
                        <th>NIS</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Jatuh Tempo</th>
                        <th>Hari Telat</th>
                        <th>Jumlah Denda</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    <?php if (empty($fines)): ?>
                        <tr><td colspan="8">Tidak ada denda.</td></tr>
                    <?php else: ?>
                        <?php foreach ($fines as $fine): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fine['member_name']); ?></td>
                                <td><?php echo isset($fine['nis']) ? htmlspecialchars($fine['nis']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($fine['book_title']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($fine['due_date'])); ?></td>
                                <td><?php echo $fine['days_late']; ?> hari</td>
                                <td>Rp <?php echo number_format($fine['amount'], 0, ',', '.'); ?></td>
                                <td><?php echo $fine['status'] === 'pending' ? 'Belum Dibayar' : 'Dibayar'; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="fine_id" value="<?php echo $fine['id']; ?>">
                                        <select name="status" class="input-field" required>
                                            <option value="pending" <?php echo $fine['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="paid" <?php echo $fine['status'] === 'paid' ? 'selected' : ''; ?>>Paid</option>
                                        </select>
                                        <button type="submit" name="update_fine" class="btn">Update</button>
                                        <?php if ($fine['status'] === 'pending'): ?>
                                            <button type="submit" name="send_fine_email" class="btn-email" onclick="return confirm('Kirim notifikasi email denda ke member?')">Kirim Email</button>
                                        <?php endif; ?>
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