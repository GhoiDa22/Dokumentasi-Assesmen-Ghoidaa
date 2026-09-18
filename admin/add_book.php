<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
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
    </style>
</head>
<body>
    <?php
    session_start();
    require_once '../config/db_connect.php';
    require_once '../includes/functions.php';
    if (!isAdmin()) {
        redirect('login.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = validateInput($_POST['title']);
        $author = validateInput($_POST['author']);
        $year = (int)$_POST['year'];
        $isbn = validateInput($_POST['isbn']);
        $stock = (int)$_POST['stock'];
        $status = 'available';
        $description = validateInput($_POST['description']);

        $cover = '';
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../assets/images/books/';
            if (!file_exists($upload_dir)) mkdir($upload_dir, 0777, true);
            $cover_name = uniqid() . '.' . pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $cover_path = $upload_dir . $cover_name;
            move_uploaded_file($_FILES['cover']['tmp_name'], $cover_path);
            $cover = 'assets/images/books/' . $cover_name;
        }

        $stmt = $pdo->prepare("INSERT INTO books (title, author, year, isbn, stock, status, cover, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$title, $author, $year, $isbn, $stock, $status, $cover, $description]);
        $_SESSION['success'] = 'Buku berhasil ditambahkan!';
        redirect('add_book.php');
    }
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
            <h1 style="text-align: center;">Tambah Buku</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="auth-section">
                <form method="POST" enctype="multipart/form-data">
                    <input type="text" name="title" placeholder="Judul Buku" class="input-field" required>
                    <input type="text" name="author" placeholder="Penulis" class="input-field" required>
                    <input type="number" name="year" placeholder="Tahun Terbit" class="input-field" required>
                    <input type="text" name="isbn" placeholder="ISBN" class="input-field" required>
                    <input type="number" name="stock" placeholder="Stok" class="input-field" required>
                    <textarea name="description" placeholder="Deskripsi Buku" class="input-field" rows="4" required></textarea>
                    <input type="file" name="cover" class="input-field" accept="image/*">
                    <button type="submit" class="btn">Tambah Buku</button>
                </form>
            </section>
        </main>
    </div>
    <script src="../assets/js/main.js"></script>
</body>
</html>