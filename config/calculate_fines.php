<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$fine_rate = file_get_contents(__DIR__ . '/fine_rate.txt') ?: 1000;

$stmt = $pdo->prepare("SELECT l.*, m.email, m.name, b.title AS book_title 
                     FROM loans l 
                     JOIN members m ON l.member_id = m.id 
                     JOIN books b ON l.book_id = b.id 
                     WHERE l.status = 'active' AND l.due_date < NOW()");
$stmt->execute();
$overdue_loans = $stmt->fetchAll();

foreach ($overdue_loans as $loan) {
    $due_date = new DateTime($loan['due_date']);
    $today = new DateTime();
    $days_overdue = $today->diff($due_date)->days;
    $fine_amount = $days_overdue * $fine_rate;

    if ($fine_amount > 0) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM fines WHERE loan_id = ?");
        $stmt->execute([$loan['id']]);
        if ($stmt->fetchColumn() == 0) {
            $stmt = $pdo->prepare("INSERT INTO fines (loan_id, member_id, amount, status) VALUES (?, ?, ?, 'pending')");
            $stmt->execute([$loan['id'], $loan['member_id'], $fine_amount]);
        } else {
            $stmt = $pdo->prepare("UPDATE fines SET amount = ? WHERE loan_id = ?");
            $stmt->execute([$fine_amount, $loan['id']]);
        }

        $stmt = $pdo->prepare("UPDATE loans SET fine = ?, status = 'overdue' WHERE id = ?");
        $stmt->execute([$fine_amount, $loan['id']]);

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'your_email@gmail.com';
            $mail->Password = 'your_app_password';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;

            $mail->setFrom('your_email@gmail.com', 'Perpustakaan SMKN 1 Surakarta');
            $mail->addAddress($loan['email']);
            $mail->isHTML(true);
            $mail->Subject = 'Pemberitahuan Denda Peminjaman';
            $mail->Body = "Halo {$loan['name']},<br><br>Buku '{$loan['book_title']}' yang Anda pinjam telah melewati batas waktu pengembalian ({$loan['due_date']}). Denda sebesar Rp " . number_format($fine_amount, 0, ',', '.') . " telah dikenakan. Silakan hubungi admin untuk pembayaran.<br><br>Terima kasih,<br>Perpustakaan SMKN 1 Surakarta";
            $mail->send();
        } catch (Exception $e) {
            error_log("Gagal mengirim email: {$mail->ErrorInfo}");
        }
    }
}
?>