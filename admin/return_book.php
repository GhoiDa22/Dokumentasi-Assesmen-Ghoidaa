<?php
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
if (!isAdmin()) {
    redirect('login.php');
}

$loan_id = (int)$_GET['loan_id'];
try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("UPDATE loans SET status = 'returned', return_date = NOW() WHERE id = ?");
    $stmt->execute([$loan_id]);
    $stmt = $pdo->prepare("UPDATE books SET stock = stock + 1, status = 'available' WHERE id = (SELECT book_id FROM loans WHERE id = ?)");
    $stmt->execute([$loan_id]);
    $pdo->commit();
    $_SESSION['success'] = 'Buku berhasil dikembalikan!';
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}
redirect('manage_returns.php');
?>