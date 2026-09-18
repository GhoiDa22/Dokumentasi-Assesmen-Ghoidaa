<?php
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
if (!isAdmin()) {
    redirect('login.php');
}

$fine_id = (int)$_GET['id'];
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE fines SET status = 'paid', paid_at = NOW() WHERE id = ?");
    $stmt->execute([$fine_id]);
    $stmt = $pdo->prepare("UPDATE loans SET fine = 0 WHERE id = (SELECT loan_id FROM fines WHERE id = ?)");
    $stmt->execute([$fine_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Pembayaran denda berhasil dikonfirmasi!';
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}
redirect('manage_fines.php');
?>