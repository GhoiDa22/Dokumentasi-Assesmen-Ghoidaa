<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Peminjaman - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #5620c9ff;
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 100%;
            color: #fff;
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .search-section {
            margin-bottom: 20px;
            position: relative;
        }
        .suggestion-list {
            position: absolute;
            background: #fff;
            border: 1px solid #ccc;
            max-height: 200px;
            overflow-y: auto;
            width: 100%;
            z-index: 1000;
            display: none;
            border-radius: 5px;
        }
        .suggestion-list div {
            padding: 10px;
            cursor: pointer;
            border-bottom: 1px solid #eee;
        }
        .suggestion-list div:hover {
            background: #f0f0f0;
        }
        .loan-form {
            margin-bottom: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .input-field {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .btn {
            padding: 10px 20px;
            background: #5620c9ff;
            color: #fff;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .btn:hover {
            background: #4517a0ff;
        }
        .status-select {
            padding: 5px;
            margin: 5px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once '../config/db_connect.php';
    require_once '../includes/functions.php';

    // Cek dan load PHPMailer
    if (!file_exists('../vendor/autoload.php')) {
        $_SESSION['error'] = 'Error: PHPMailer tidak ditemukan. Jalankan "composer require phpmailer/phpmailer" di terminal.';
        redirect('manage_loans.php');
    }
    require_once '../vendor/autoload.php';
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    if (!isAdmin()) {
        redirect('login.php');
    }

    // Ambil data member
    $stmt = $pdo->query("SELECT id, name, nis FROM members ORDER BY name");
    $members = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Ambil data buku
    $stmt = $pdo->query("SELECT id, title, author FROM books WHERE stock > 0 ORDER BY title");
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tambah peminjaman
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_loan'])) {
        $member_id = (int)$_POST['member_id'];
        $book_id = (int)$_POST['book_id'];
        $loan_date = date('Y-m-d H:i:s');
        $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));
        $status = 'active';
        $fine = 0;
        $pickup_status = 'pending';

        // Validasi member_id
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        if ($stmt->fetchColumn() == 0) {
            $_SESSION['error'] = 'Member ID tidak valid!';
            redirect('manage_loans.php');
        }

        // Validasi book_id
        $stmt = $pdo->prepare("SELECT stock FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $stock = $stmt->fetchColumn();
        if ($stock > 0) {
            $stmt = $pdo->prepare("INSERT INTO loans (member_id, book_id, loan_date, due_date, status, fine, extension_count, pickup_status) VALUES (?, ?, ?, ?, ?, ?, 0, ?)");
            $stmt->execute([$member_id, $book_id, $loan_date, $due_date, $status, $fine, $pickup_status]);
            $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
            $stmt->execute([$book_id]);
            $_SESSION['success'] = 'Peminjaman berhasil ditambahkan!';
        } else {
            $_SESSION['error'] = 'Stok buku habis!';
        }
        redirect('manage_loans.php');
    }

    // Kirim pengingat
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_reminder'])) {
        $loan_id = (int)$_POST['loan_id'];
        $member_name = htmlspecialchars($_POST['member_name']);
        $book_title = htmlspecialchars($_POST['book_title']);
        $due_date = $_POST['due_date'];
        $email = htmlspecialchars($_POST['email']);

        $due_date_obj = new DateTime($due_date);
        $today = new DateTime();
        $days_left = $today->diff($due_date_obj)->days;
        $is_overdue = $today > $due_date_obj;

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'manahhati0@gmail.com';
            $mail->Password = 'sndfadeavagszjjg';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->setFrom('manahhati0@gmail.com', 'Perpustakaan SMKN 1 Surakarta');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $is_overdue ? 'Peringatan Denda Peminjaman' : 'Pengingat Jatuh Tempo Peminjaman';
            $mail->Body = "Halo {$member_name},<br><br>";
            if ($is_overdue) {
                $mail->Body .= "Buku '{$book_title}' telah melewati batas waktu pengembalian ({$due_date}). Silakan kembalikan segera untuk menghindari denda.<br><br>";
            } else {
                $mail->Body .= "Buku '{$book_title}' akan jatuh tempo pada {$due_date} ({$days_left} hari lagi). Silakan rencanakan pengembalian.<br><br>";
            }
            $mail->Body .= "Terima kasih,<br>Perpustakaan SMKN 1 Surakarta";
            $mail->send();
            $_SESSION['success'] = 'Notifikasi email berhasil dikirim!';
        } catch (Exception $e) {
            $_SESSION['error'] = 'Gagal mengirim email: ' . $mail->ErrorInfo;
        }
        redirect('manage_loans.php');
    }

    // Perpanjang peminjaman
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['extend_loan'])) {
        $loan_id = (int)$_POST['loan_id'];
        $stmt = $pdo->prepare("SELECT due_date, status, extension_count FROM loans WHERE id = ?");
        $stmt->execute([$loan_id]);
        $loan = $stmt->fetch();
        if ($loan && $loan['status'] === 'active' && $loan['extension_count'] < 1) {
            $new_due_date = date('Y-m-d H:i:s', strtotime($loan['due_date'] . ' +7 days'));
            $stmt = $pdo->prepare("UPDATE loans SET due_date = ?, extension_count = extension_count + 1 WHERE id = ?");
            $stmt->execute([$new_due_date, $loan_id]);

            $stmt = $pdo->prepare("SELECT m.name AS member_name, m.email, b.title AS book_title FROM loans l 
                                 JOIN members m ON l.member_id = m.id 
                                 JOIN books b ON l.book_id = b.id 
                                 WHERE l.id = ?");
            $stmt->execute([$loan_id]);
            $loan_details = $stmt->fetch();

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = '23.rizkia.ahsan@poltekindonusa.ac.id';
                $mail->Password = 'vajpnpceesgyrybi';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port = 465;
                $mail->setFrom('23.rizkia.ahsan@poltekindonusa.ac.id', 'Perpustakaan SMKN 1 Surakarta');
                $mail->addAddress($loan_details['email']);
                $mail->isHTML(true);
                $mail->Subject = 'Pemberitahuan Perpanjangan Peminjaman';
                $mail->Body = "Halo {$loan_details['member_name']},<br><br>" .
                              "Peminjaman buku '{$loan_details['book_title']}' telah diperpanjang hingga " . date('d-m-Y', strtotime($new_due_date)) . ".<br><br>" .
                              "Terima kasih,<br>Perpustakaan SMKN 1 Surakarta";
                $mail->send();
                $_SESSION['success'] = 'Peminjaman diperpanjang dan notifikasi email berhasil dikirim!';
            } catch (Exception $e) {
                $_SESSION['error'] = 'Peminjaman diperpanjang, tapi gagal kirim email: ' . $mail->ErrorInfo;
            }
        } else {
            $_SESSION['error'] = 'Peminjaman tidak dapat diperpanjang: Status salah atau sudah diperpanjang sebelumnya!';
        }
        redirect('manage_loans.php');
    }

    // Update pickup status
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_pickup'])) {
        $loan_id = (int)$_POST['loan_id'];
        $pickup_status = $_POST['pickup_status'];
        $stmt = $pdo->prepare("UPDATE loans SET pickup_status = ? WHERE id = ?");
        $stmt->execute([$pickup_status, $loan_id]);
        $_SESSION['success'] = 'Status pengambilan berhasil diperbarui!';
        redirect('manage_loans.php');
    }

    $stmt = $pdo->prepare("SELECT l.*, m.name AS member_name, m.nis, m.email, b.title AS book_title 
                         FROM loans l 
                         JOIN members m ON l.member_id = m.id 
                         JOIN books b ON l.book_id = b.id 
                         WHERE l.status IN ('active', 'overdue') 
                         ORDER BY l.loan_date DESC");
    $stmt->execute();
    $loans = $stmt->fetchAll();
    ?>
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
            <h1 style="text-align: center;">Kelola Peminjaman</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <h2>Tambah Peminjaman</h2>
                <form method="POST" class="loan-form">
                    <div class="search-section">
                        <input type="text" id="member-search" class="input-field" placeholder="Cari Member (Nama/NIS)" onkeyup="searchMember()">
                        <div id="member-suggestions" class="suggestion-list"></div>
                        <input type="hidden" id="member-id" name="member_id">
                    </div>
                    <div class="search-section">
                        <input type="text" id="book-search" class="input-field" placeholder="Cari Buku (Judul/Penulis)" onkeyup="searchBook()">
                        <div id="book-suggestions" class="suggestion-list"></div>
                        <input type="hidden" id="book-id" name="book_id">
                    </div>
                    <button type="submit" name="add_loan" class="btn">Tambah Peminjaman</button>
                </form>
                <table>
                    <tr>
                        <th>Nama Member</th>
                        <th>NIS</th>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status Peminjaman</th>
                        <th>Status Pengambilan</th>
                        <th>Aksi</th>
                    </tr>
                    <?php if (empty($loans)): ?>
                        <tr><td colspan="8">Belum ada peminjaman aktif.</td></tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['member_name']); ?></td>
                                <td><?php echo isset($loan['nis']) ? htmlspecialchars($loan['nis']) : '-'; ?></td>
                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['loan_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['due_date'])); ?></td>
                                <td><?php echo $loan['status'] === 'active' ? 'Aktif' : 'Terlambat'; ?></td>
                                <td>
                                    <?php echo $loan['pickup_status'] === 'pending' ? 'Pending' : ($loan['pickup_status'] === 'instructed' ? 'Disuruh Ambil di Perpus' : 'Buku Sudah Dibawa'); ?>
                                </td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                        <select name="pickup_status" class="status-select" onchange="this.form.submit()">
                                            <option value="pending" <?php echo $loan['pickup_status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="instructed" <?php echo $loan['pickup_status'] === 'instructed' ? 'selected' : ''; ?>>Disuruh Ambil di Perpus</option>
                                            <option value="taken" <?php echo $loan['pickup_status'] === 'taken' ? 'selected' : ''; ?>>Buku Sudah Dibawa</option>
                                        </select>
                                        <input type="hidden" name="update_pickup" value="1">
                                    </form>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="loan_id" value="<?php echo $loan['id']; ?>">
                                        <input type="hidden" name="member_name" value="<?php echo htmlspecialchars($loan['member_name']); ?>">
                                        <input type="hidden" name="book_title" value="<?php echo htmlspecialchars($loan['book_title']); ?>">
                                        <input type="hidden" name="due_date" value="<?php echo $loan['due_date']; ?>">
                                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($loan['email']); ?>">
                                        <button type="submit" name="send_reminder" class="btn confirm-btn" onclick="return confirm('Kirim notifikasi email ke member?')">Kirim Notif</button>
                                        <button type="submit" name="extend_loan" class="btn" style="background: #28a745;" onclick="return confirm('Perpanjang peminjaman ini?')">Perpanjang</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </section>
        </main>
    </div>
    <script>
        let members = <?php echo json_encode($members); ?>;
        let books = <?php echo json_encode($books); ?>;

        function searchMember() {
            let input = document.getElementById('member-search').value.toLowerCase();
            let suggestions = document.getElementById('member-suggestions');
            suggestions.innerHTML = '';
            if (input.length > 0) {
                let filtered = members.filter(m => 
                    m.name.toLowerCase().includes(input) || 
                    (m.nis && m.nis.toLowerCase().includes(input))
                );
                filtered.forEach(m => {
                    let div = document.createElement('div');
                    div.innerHTML = `${m.name} (NIS: ${m.nis || '-'})`;
                    div.onclick = () => {
                        document.getElementById('member-search').value = `${m.name} (NIS: ${m.nis || '-'})`;
                        document.getElementById('member-id').value = m.id;
                        suggestions.style.display = 'none';
                    };
                    suggestions.appendChild(div);
                });
                suggestions.style.display = filtered.length ? 'block' : 'none';
            } else {
                suggestions.style.display = 'none';
            }
        }

        function searchBook() {
            let input = document.getElementById('book-search').value.toLowerCase();
            let suggestions = document.getElementById('book-suggestions');
            suggestions.innerHTML = '';
            if (input.length > 0) {
                let filtered = books.filter(b => 
                    b.title.toLowerCase().includes(input) || 
                    (b.author && b.author.toLowerCase().includes(input))
                );
                filtered.forEach(b => {
                    let div = document.createElement('div');
                    div.innerHTML = `${b.title} (Penulis: ${b.author || '-'})`;
                    div.onclick = () => {
                        document.getElementById('book-search').value = `${b.title} (Penulis: ${b.author || '-'})`;
                        document.getElementById('book-id').value = b.id;
                        suggestions.style.display = 'none';
                    };
                    suggestions.appendChild(div);
                });
                suggestions.style.display = filtered.length ? 'block' : 'none';
            } else {
                suggestions.style.display = 'none';
            }
        }

        // Sembunyikan saran saat klik di luar
        document.addEventListener('click', function(e) {
            if (!e.target.closest('.search-section')) {
                document.getElementById('member-suggestions').style.display = 'none';
                document.getElementById('book-suggestions').style.display = 'none';
            }
        });
    </script>
</body>
</html>