<?php
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
if (!isAdmin()) {
    redirect('login.php');
}

$fine_id = (int)$_POST['fine_id'];
$amount = (int)$_POST['amount'];

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE fines SET amount = ? WHERE id = ?");
    $stmt->execute([$amount, $fine_id]);
    $stmt = $pdo->prepare("UPDATE loans SET fine = ? WHERE id = (SELECT loan_id FROM fines WHERE id = ?)");
    $stmt->execute([$amount, $fine_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Denda berhasil diperbarui!';
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}
redirect('manage_fines.php');
?>