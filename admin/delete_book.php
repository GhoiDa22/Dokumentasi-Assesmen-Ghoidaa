<?php
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
if (!isAdmin()) {
    redirect('login.php');
}

$id = (int)$_GET['id'];
try {
    $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $_SESSION['success'] = 'Buku berhasil dihapus!';
} catch (PDOException $e) {
    $_SESSION['error'] = 'Error: ' . $e->getMessage();
}
redirect('manage_books.php');
?>