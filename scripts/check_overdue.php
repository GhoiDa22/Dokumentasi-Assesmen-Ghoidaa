<?php
require_once '../config/db_connect.php';
require_once '../config/email_config.php';
require_once '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mailer = new PHPMailer(true);
try {
    // Server settings
    $mailer->SMTPDebug = 0; // Set ke 2 untuk debug, 0 untuk produksi
    $mailer->isSMTP();
    $mailer->Host = SMTP_HOST;
    $mailer->SMTPAuth = true;
    $mailer->Username = SMTP_USERNAME;
    $mailer->Password = SMTP_PASSWORD;
    $mailer->SMTPSecure = SMTP_SECURE;
    $mailer->Port = SMTP_PORT;

    // Ambil data peminjaman terlambat
    $stmt = $pdo->query("SELECT l.*, u.email, u.name, b.title 
                         FROM loans l 
                         JOIN users u ON l.user_id = u.id 
                         JOIN books b ON l.book_id = b.id 
                         WHERE l.status = 'active' AND l.due_date < NOW() AND l.notification_sent = FALSE");
    $overdue_loans = $stmt->fetchAll();

    if (empty($overdue_loans)) {
        echo "Tidak ada peminjaman terlambat.";
        exit;
    }

    foreach ($overdue_loans as $loan) {
        $mailer->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mailer->addAddress($loan['email'], $loan['name']);
        $mailer->isHTML(true);
        $mailer->Subject = 'Peringatan Keterlambatan Pengembalian Buku';
        $mailer->Body = '
            <html>
            <head>
                <style>
                    body { font-family: Poppins, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f4f7fb; border-radius: 10px; }
                    h2 { color: #3498db; }
                    p { line-height: 1.6; }
                    .btn { background-color: #3498db; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; }
                </style>
            </head>
            <body>
                <div class="container">
                    <h2>Peringatan Keterlambatan</h2>
                    <p>Halo, ' . htmlspecialchars($loan['name']) . ',</p>
                    <p>Buku "<strong>' . htmlspecialchars($loan['title']) . '</strong>" yang Anda pinjam telah melewati batas waktu pengembalian pada tanggal ' . date('d-m-Y', strtotime($loan['due_date'])) . '.</p>
                    <p>Harap kembalikan buku ke perpustakaan secepatnya untuk menghindari denda.</p>
                    <p><a href="http://localhost/library_system/user/dashboard.php" class="btn">Lihat Peminjaman</a></p>
                    <p>Terima kasih,<br>Tim Perpustakaan Online</p>
                </div>
            </body>
            </html>';
        $mailer->AltBody = 'Halo ' . htmlspecialchars($loan['name']) . ', Buku "' . htmlspecialchars($loan['title']) . '" yang Anda pinjam telah melewati batas waktu pengembalian pada tanggal ' . date('d-m-Y', strtotime($loan['due_date'])) . '. Harap kembalikan secepatnya. Terima kasih, Tim Perpustakaan Online';

        $mailer->send();
        $stmt = $pdo->prepare("UPDATE loans SET status = 'overdue', notification_sent = TRUE WHERE id = ?");
        $stmt->execute([$loan['id']]);
    }
    echo "Notifikasi terlambat berhasil dikirim ke " . count($overdue_loans) . " anggota.";
} catch (Exception $e) {
    echo "Error mengirim email: {$mailer->ErrorInfo}";
}
?>