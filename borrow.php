<?php
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
if (!isMember()) {
    redirect('../index.php');
}

$book_id = (int)$_GET['book_id'];
$member_id = $_SESSION['user_id'];
$loan_date = date('Y-m-d H:i:s');
$due_date = date('Y-m-d H:i:s', strtotime('+7 days'));

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare("SELECT stock FROM books WHERE id = ? AND stock > 0");
    $stmt->execute([$book_id]);
    if ($stmt->fetchColumn()) {
        $stmt = $pdo->prepare("INSERT INTO loans (member_id, book_id, loan_date, due_date, status) VALUES (?, ?, ?, ?, 'active')");
        $stmt->execute([$member_id, $book_id, $loan_date, $due_date]);
        $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1, status = CASE WHEN stock = 1 THEN 'borrowed' ELSE 'available' END WHERE id = ?");
        $stmt->execute([$book_id]);
        $pdo->commit();
        $_SESSION['success'] = 'Buku berhasil dipinjam!';
    } else {
        $_SESSION['error'] = 'Buku tidak tersedia!';
    }
} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}
redirect('dashboard.php');
?>