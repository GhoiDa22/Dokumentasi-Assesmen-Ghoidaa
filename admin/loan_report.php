<?php
ob_start();
session_start();
require_once '../config/db_connect.php';
require_once '../includes/functions.php';
require_once '../vendor/fpdf/fpdf.php';

if (!isAdmin()) {
    redirect('login.php');
}

class PDF extends FPDF {
    function Header() {
        $this->Image('../assets/images/smkn1-logo.png', 10, 6, 20);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(0, 10, 'Perpustakaan SMKN 1 Surakarta', 0, 1, 'C');
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(0, 8, 'Laporan Peminjaman Buku', 0, 1, 'C');
        $this->Ln(3);
        $this->SetFont('Arial', 'B', 8);
        $this->SetFillColor(41, 128, 185);
        $this->SetTextColor(255);
        $this->Cell(10, 6, 'No', 1, 0, 'C', true);
        $this->Cell(35, 6, 'Nama Member', 1, 0, 'C', true);
        $this->Cell(15, 6, 'Kelas', 1, 0, 'C', true);
        $this->Cell(30, 6, 'Jurusan', 1, 0, 'C', true);
        $this->Cell(50, 6, 'Judul Buku', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Tgl Pinjam', 1, 0, 'C', true);
        $this->Cell(25, 6, 'Jatuh Tempo', 1, 1, 'C', true);
        $this->SetTextColor(0);
    }

    function Footer() {
        $this->SetY(-15);
        $this->SetFont('Arial', 'I', 7);
        $this->Cell(0, 10, 'Halaman ' . $this->PageNo() . ' - Dicetak pada ' . date('d-m-Y H:i'), 0, 0, 'C');
    }

    function MultiCellRow($cells, $widths, $height = 4) {
        $nb = 0;
        foreach ($cells as $i => $cell) {
            $nb = max($nb, $this->NbLines($widths[$i], $cell));
        }
        $h = $height * $nb;
        $this->CheckPageBreak($h);
        $x = $this->GetX();
        $y = $this->GetY();
        foreach ($cells as $i => $cell) {
            $w = $widths[$i];
            $a = 'L';
            $this->Rect($x, $y, $w, $h);
            $this->MultiCell($w, $height, $cell, 0, $a);
            $x += $w;
            $this->SetXY($x, $y);
        }
        $this->Ln($h);
    }

    function CheckPageBreak($h) {
        if ($this->GetY() + $h > $this->PageBreakTrigger) {
            $this->AddPage($this->CurOrientation);
        }
    }

    function NbLines($w, $txt) {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0) $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', (string)$txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n") $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ') $sep = $i;
            $l += $cw[$c] ?? 0;
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }
}

$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
$report_type = isset($_POST['report_type']) ? $_POST['report_type'] : 'range';

$start_date_obj = $start_date ? DateTime::createFromFormat('d-m-Y', $start_date) : null;
$end_date_obj = $end_date ? DateTime::createFromFormat('d-m-Y', $end_date) : null;

if ($start_date && !$start_date_obj) {
    $_SESSION['error'] = 'Format tanggal mulai salah! Gunakan dd-mm-yyyy.';
} elseif ($end_date && !$end_date_obj && $report_type !== 'daily') {
    $_SESSION['error'] = 'Format tanggal selesai salah! Gunakan dd-mm-yyyy.';
}

$start_date = $start_date_obj ? $start_date_obj->format('Y-m-d') : null;
$end_date = $end_date_obj ? $end_date_obj->format('Y-m-d') : null;

if (isset($_POST['generate_report']) && !isset($_SESSION['error'])) {
    $query = "SELECT l.*, m.name AS member_name, m.kelas, m.jurusan, b.title AS book_title 
              FROM loans l 
              JOIN members m ON l.member_id = m.id 
              JOIN books b ON l.book_id = b.id";
    $params = [];

    if ($report_type === 'daily' && $start_date) {
        $query .= " WHERE DATE(l.loan_date) = ?";
        $params[] = $start_date;
    } elseif ($report_type === 'range' && $start_date && $end_date) {
        $query .= " WHERE l.loan_date >= ? AND l.loan_date <= ?";
        $params[] = $start_date . ' 00:00:00';
        $params[] = $end_date . ' 23:59:59';
    }

    $query .= " ORDER BY l.loan_date DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $loans = $stmt->fetchAll();

    $pdf = new PDF('P', 'mm', 'A4');
    $pdf->AddPage();
    $pdf->SetFont('Arial', '', 7);
    $counter = 1;

    foreach ($loans as $loan) {
        $pdf->MultiCellRow([
            $counter++,
            $loan['member_name'] ?? '-',
            $loan['kelas'] ?? '-',
            $loan['jurusan'] ?? '-',
            $loan['book_title'] ?? '-',
            date('d-m-Y', strtotime($loan['loan_date'])),
            date('d-m-Y', strtotime($loan['due_date']))
        ], [10, 35, 15, 30, 50, 25, 25]);
    }

    ob_end_clean();
    $pdf->Output('D', 'Laporan_Peminjaman.pdf');
    exit;
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Peminjaman - Perpustakaan SMKN 1 Surakarta</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <style>
        body {
            background-color: #f4f6f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            display: flex;
            min-height: 100vh;
        }
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #5620c9ff; /* Warna abu-abu muda */
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 100%;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .main-content {
            flex: 1;
            padding: 20px;
        }
        h1 {
            color: #297AB9;
            text-align: center;
        }
        .filter-form {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .input-field {
            padding: 8px;
            margin: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn {
            background-color: #297AB9;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background-color: #1f5e8a;
        }
        .error {
            color: red;
            text-align: center;
        }
        .success {
            color: green;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="../assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                <br> SMKN 1 Surakarta
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="dropdown">
                        <a href="#">Kelola Data</a>
                        <div class="dropdown-content">
                            <a href="add_book.php">Tambah Buku</a>
                            <a href="manage_books.php">Kelola Buku</a>
                            <a href="add_member.php">Tambah Member</a>
                            <a href="manage_members.php">Kelola Member</a>
                        </div>
                    </li>
                    <li><a href="manage_loans.php">Kelola Peminjaman</a></li>
                    <li><a href="manage_returns.php">Kelola Pengembalian</a></li>
                    <li><a href="manage_fines.php">Kelola Denda</a></li>
                    <li><a href="loan_report.php">Laporan Peminjaman</a></li>
                    <li><a href="../config/auth.php?logout=1">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1>Laporan Peminjaman</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <div class="filter-form">
                    <h2>Filter Laporan</h2>
                    <form method="POST">
                        <select name="report_type" class="input-field" onchange="this.form.submit()">
                            <option value="range" <?php echo $report_type === 'range' ? 'selected' : ''; ?>>Rentang Tanggal</option>
                            <option value="daily" <?php echo $report_type === 'daily' ? 'selected' : ''; ?>>Harian</option>
                        </select>
                        <label for="start_date">Tanggal Mulai:</label>
                        <input type="text" name="start_date" id="start_date" class="input-field" placeholder="Tanggal (dd-mm-yyyy)" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>" required>
                        <!-- <input type="text" name="start_date" class="input-field" placeholder="Tanggal (dd-mm-yyyy)" value="<?php echo isset($_POST['start_date']) ? htmlspecialchars($_POST['start_date']) : ''; ?>" required> -->

                        <label for="end_date" id="end-label">Tanggal Selesai:</label>
<input type="text" name="end_date" id="end_date" class="input-field" placeholder="Tanggal Selesai (dd-mm-yyyy)" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>">
                        <!-- <input type="text" name="end_date" class="input-field" placeholder="Tanggal Selesai (dd-mm-yyyy)" value="<?php echo isset($_POST['end_date']) ? htmlspecialchars($_POST['end_date']) : ''; ?>" <?php echo $report_type === 'daily' ? 'style="display:none;"' : ''; ?>> -->
                        <button type="submit" name="generate_report" class="btn">Generate Laporan</button>
                    </form>
                </div>
                <p>Klik tombol di atas untuk mengunduh laporan PDF berdasarkan filter.</p>
            </section>
        </main>
    </div>
    <script>
       document.addEventListener('DOMContentLoaded', function () {
    flatpickr("input[name='start_date']", {
        dateFormat: "d-m-Y"
    });
    flatpickr("input[name='end_date']", {
        dateFormat: "d-m-Y"
    });

    // Tampilkan/hilangkan label tanggal selesai
    const reportType = document.querySelector("select[name='report_type']");
    const endDateField = document.querySelector("input[name='end_date']");
    const endLabel = document.getElementById("end-label");

    function toggleEndDate() {
        if (reportType.value === "daily") {
            endDateField.style.display = 'none';
            if (endLabel) endLabel.style.display = 'none';
        } else {
            endDateField.style.display = 'inline-block';
            if (endLabel) endLabel.style.display = 'inline-block';
        }
    }

    reportType.addEventListener('change', toggleEndDate);
    toggleEndDate();
});
    </script>
</body>
</html>
