<?php
header('Content-Type: application/json');
require_once 'config/db_connect.php';

$query = isset($_GET['q']) ? trim($_GET['q']) : '';
$response = ['found' => false];

if ($query) {
    $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? LIMIT 1");
    $search_term = "%$query%";
    $stmt->execute([$search_term]);
    $book = $stmt->fetch();
    if ($book && $book['stock'] > 0) {
        $response = ['found' => true, 'book' => ['title' => $book['title']]];
    }
}

echo json_encode($response);
?>