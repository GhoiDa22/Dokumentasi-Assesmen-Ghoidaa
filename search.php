<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Buku - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
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
        .book-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        .book-card {
            background: #f9f9f9;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            position: relative;
        }
        .book-card img {
            max-width: 100px;
            height: auto;
        }
        .borrow-btn {
            background: #5620c9ff;
            color: #fff;
            border: none;
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        .borrow-btn:hover {
            background: #3f17a0ff;
        }
        .message {
            color: #28a745;
            font-weight: bold;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php
    session_start();
    require_once 'config/db_connect.php';
    require_once 'includes/functions.php';

    if (!isset($_SESSION['user_id'])) {
        redirect('login.php');
    }

    $search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
    $books = [];
    $message = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow'])) {
        $book_id = (int)$_POST['book_id'];
        $user_id = $_SESSION['user_id'];

        $stmt = $pdo->prepare("SELECT stock FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $stock = $stmt->fetchColumn();

        if ($stock > 0) {
            $loan_date = date('Y-m-d H:i:s');
            $due_date = date('Y-m-d H:i:s', strtotime('+7 days'));
            $status = 'active';
            $fine = 0;

            $stmt = $pdo->prepare("INSERT INTO loans (member_id, book_id, loan_date, due_date, status, fine) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$user_id, $book_id, $loan_date, $due_date, $status, $fine]);

            $stmt = $pdo->prepare("UPDATE books SET stock = stock - 1 WHERE id = ?");
            $stmt->execute([$book_id]);

            $message = 'Peminjaman berhasil! Silakan datang ke perpustakaan untuk mengambil buku.';
        } else {
            $message = 'Maaf, stok buku habis!';
        }
    }

    if ($search_query) {
        $stmt = $pdo->prepare("SELECT * FROM books WHERE title LIKE ? OR author LIKE ? ORDER BY title");
        $search_term = "%$search_query%";
        $stmt->execute([$search_term, $search_term]);
        $books = $stmt->fetchAll();
    }
    ?>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                Perpustakaan SMKN 1 Surakarta
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="print_card.php">Cetak Kartu Member</a></li>
                    <li><a href="search.php">Cari Buku</a></li>
                    <li><a href="profile.php">Profil</a></li>
                    <li><a href="config/auth.php?logout=1">Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1 style="text-align: center;">Cari Buku</h1>
            <form method="GET" style="margin-bottom: 20px; text-align: center;">
                <input type="text" name="q" class="input-field" placeholder="Cari judul atau penulis..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                <button type="submit" class="btn">Cari</button>
            </form>
            <section class="dashboard-section">
                <?php if ($message): ?>
                    <p class="message"><?php echo $message; ?></p>
                <?php endif; ?>
                <div class="book-list">
                    <?php if (empty($books)): ?>
                        <p>Tidak ada buku yang sesuai dengan pencarian "<?php echo htmlspecialchars($search_query); ?>".</p>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-card">
                                <?php if ($book['cover']): ?><img src="<?php echo htmlspecialchars($book['cover']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>"><?php endif; ?>
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p>Penulis: <?php echo htmlspecialchars($book['author']); ?></p>
                                <p>Stok: <?php echo $book['stock']; ?></p>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                    <button type="submit" name="borrow" class="borrow-btn" <?php echo $book['stock'] <= 0 ? 'disabled' : ''; ?>>Pinjam</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
</body>
</html>