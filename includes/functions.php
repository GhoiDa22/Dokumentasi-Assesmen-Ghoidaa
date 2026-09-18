<?php
function validateInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function isAdmin() {
    global $pdo;
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') {
        $stmt = $pdo->prepare("SELECT id FROM admins WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->rowCount() > 0;
    }
    return false;
}

function isMember() {
    global $pdo;
    if (isset($_SESSION['user_id']) && !isset($_SESSION['user_type'])) {
        $stmt = $pdo->prepare("SELECT id FROM members WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->rowCount() > 0;
    }
    return false;
}
?>