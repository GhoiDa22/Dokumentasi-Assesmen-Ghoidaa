<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';
require_once 'vendor/fpdf/fpdf.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    die("Data member tidak ditemukan.");
}

// Cek apakah foto ada, kalau tidak suruh ke profil
$photo_path = isset($user['photo']) && file_exists($user['photo']) ? $user['photo'] : 'assets/images/default-avatar.png';
if (!file_exists($photo_path) || $photo_path === 'assets/images/default-avatar.png') {
    die("Harap unggah foto di halaman profil terlebih dahulu sebelum mencetak kartu!");
}

class PDF extends FPDF {
    function Header() {
        global $user;
        // Background
        $this->Image('assets/images/bg.jpg', 0, 0, 85, 54);

        // Foto member di kanan bawah
        $photo_path = isset($user['photo']) && file_exists($user['photo']) ? $user['photo'] : 'assets/images/default-avatar.png';
        if (file_exists($photo_path)) {
            $this->Image($photo_path, 64, 30, 18, 18);
        }
    }

    function Footer() {}
}

$pdf = new PDF('L', 'mm', array(85, 54)); // Ukuran 1 halaman saja
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetTextColor(86, 32, 201);
$pdf->SetXY(5, 5);
$pdf->Cell(60, 5, 'KARTU MEMBER PERPUSTAKAAN', 0, 1, 'L');

$pdf->SetFont('Arial', '', 6);
$pdf->SetTextColor(51, 51, 51);
$pdf->Ln(0.5);
$pdf->SetX(5);
$pdf->Cell(60, 3.5, 'Nama       : ' . $user['name'], 0, 1, 'L');
$pdf->SetX(5);
$pdf->Cell(60, 3.5, 'Email      : ' . $user['email'], 0, 1, 'L');
$pdf->SetX(5);
$pdf->Cell(60, 3.5, 'NIS        : ' . $user['nis'], 0, 1, 'L');
$pdf->SetX(5);
$pdf->Cell(60, 3.5, 'Kadaluarsa : ' . date('d-m-Y', strtotime($user['card_expiry_date'])), 0, 1, 'L');
$pdf->SetX(5);
$pdf->Cell(60, 3.5, 'Jurusan    : ' . ($user['jurusan'] ?? 'Tidak ada'), 0, 1, 'L');
$pdf->SetX(5);
$pdf->Cell(60, 3.5, 'No HP      : ' . ($user['no_hp'] ?? 'Tidak ada'), 0, 1, 'L');

$pdf->Output('D', 'Kartu_Member_' . $user['name'] . '.pdf');
?>
