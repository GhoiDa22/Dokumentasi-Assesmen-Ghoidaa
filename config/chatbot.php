<?php
session_start();
require_once __DIR__ . '/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$input = isset($_POST['message']) ? validateInput($_POST['message']) : '';
$response = '';

if ($input === 'hai' || $input === 'Halo' || $input === 'halo') {
    $response = 'Halo! Kirim nomor untuk opsi berikut:<br>1. Jam buka<br>2. Cara pinjam<br>3. Cari buku<br>4. Status kartu<br>5. Cek denda';
} elseif ($input === '1') {
    $response = 'Jam buka perpustakaan: Senin-Jumat, 08:00-16:00 WIB.';
} elseif ($input === '2') {
    $response = 'Cara pinjam: Login, cari buku di menu "Cari Buku", klik "Pinjam" pada buku yang tersedia.';
} elseif ($input === '3') {
    $response = 'Silakan gunakan menu "Cari Buku" di dashboard untuk mencari judul atau penulis.';
} elseif ($input === '4' && isMember()) {
    $stmt = $pdo->prepare("SELECT card_expiry_date FROM members WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch();
    $status = strtotime($member['card_expiry_date']) >= time() ? 'Aktif' : 'Kadaluarsa';
    $response = 'Status kartu Anda: ' . $status . ' (berlaku hingga ' . date('d-m-Y', strtotime($member['card_expiry_date'])) . ').';
} elseif ($input === '5' && isMember()) {
    $stmt = $pdo->prepare("SELECT f.*, b.title AS book_title FROM fines f JOIN loans l ON f.loan_id = l.id JOIN books b ON l.book_id = b.id WHERE f.member_id = ? AND f.status = 'pending'");
    $stmt->execute([$_SESSION['user_id']]);
    $fines = $stmt->fetchAll();
    if (empty($fines)) {
        $response = 'Anda tidak memiliki denda aktif.';
    } else {
        $response = 'Denda Anda:<br>';
        foreach ($fines as $fine) {
            $response .= '- Buku "' . htmlspecialchars($fine['book_title']) . '": Rp ' . number_format($fine['amount'], 0, ',', '.') . '<br>';
        }
        $response .= 'Hubungi admin untuk pembayaran.';
    }
} else {
    $response = 'Maaf, saya tidak mengerti. Kirim "hai" untuk melihat opsi.';
}

echo json_encode(['response' => $response]);
?>