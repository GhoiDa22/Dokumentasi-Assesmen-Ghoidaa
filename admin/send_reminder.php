<?php
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';

// Cek apakah PHPMailer ada
if (!file_exists('../vendor/PHPMailer/src/PHPMailer.php')) {
    die('Error: PHPMailer tidak ditemukan. Silakan install PHPMailer di vendor/.');
}

require_once '../vendor/PHPMailer/src/PHPMailer.php';
require_once '../vendor/PHPMailer/src/SMTP.php';
require_once '../vendor/PHPMailer/src/Exception.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isAdmin() || !isset($_GET['loan_id'])) {
    redirect('login.php');
}

$loan_id = (int)$_GET['loan_id'];
$stmt = $pdo->prepare("SELECT l.*, m.email, m.name, b.title AS book_title 
                     FROM loans l 
                     JOIN members m ON l.member_id = m.id 
                     JOIN books b ON l.book_id = b.id 
                     WHERE l.id = ?");
$stmt->execute([$loan_id]);
$loan = $stmt->fetch();

if ($loan) {
    $due_date = new DateTime($loan['due_date']);
    $today = new DateTime();
    $days_left = $today->diff($due_date)->days;
    $is_overdue = $today > $due_date;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = '23.rizkia.ahsan@poltekindonusa.ac.id';
        $mail->Password = 'vajpnpceesgyrybi';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;
        $mail->SMTPDebug = 2;
        $mail->setFrom('23.rizkia.ahsan@poltekindonusa.ac.id', 'Perpustakaan SMKN 1 Surakarta');
        $mail->addAddress($loan['email']);
        $mail->isHTML(true);
        $mail->Subject = $is_overdue ? 'Peringatan Denda Peminjaman' : 'Pengingat Jatuh Tempo Peminjaman';
        $mail->Body = "Halo {$loan['name']},<br><br>";
        if ($is_overdue) {
            $mail->Body .= "Buku '{$loan['book_title']}' telah melewati batas waktu pengembalian ({$loan['due_date']}). Silakan kembalikan segera untuk menghindari denda.<br><br>";
        } else {
            $mail->Body .= "Buku '{$loan['book_title']}' akan jatuh tempo pada {$loan['due_date']} ({$days_left} hari lagi). Silakan rencanakan pengembalian.<br><br>";
        }
        $mail->Body .= "Terima kasih,<br>Perpustakaan SMKN 1 Surakarta";
        $mail->send();
        $_SESSION['success'] = 'Notifikasi email berhasil dikirim!';
    } catch (Exception $e) {
        $_SESSION['error'] = 'Gagal mengirim email: ' . $mail->ErrorInfo;
    }
}
redirect('manage_loans.php');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kirim Notifikasi - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #ecf0f1; /* Warna abu-abu muda */
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
    ?>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                Pustakawan SMKN 1 Surakarta
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
            <h1 style="text-align: center;">Kirim Notifikasi</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <p>Proses pengiriman notifikasi sedang dilakukan. Anda akan diarahkan kembali.</p>
            </section>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>