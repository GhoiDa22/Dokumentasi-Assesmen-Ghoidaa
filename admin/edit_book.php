<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php
    session_start();
    require_once '../config/db_connect.php';
    require_once '../includes/functions.php';
    if (!isAdmin()) {
        redirect('login.php');
    }

    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$id]);
    $book = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = validateInput($_POST['title']);
        $author = validateInput($_POST['author']);
        $year = (int)$_POST['year'];
        $isbn = validateInput($_POST['isbn']);
        $stock = (int)$_POST['stock'];

        try {
            $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, year = ?, isbn = ?, stock = ? WHERE id = ?");
            $stmt->execute([$title, $author, $year, $isbn, $stock, $id]);
            $_SESSION['success'] = 'Buku berhasil diperbarui!';
            redirect('manage_books.php');
        } catch (PDOException $e) {
            $_SESSION['error'] = 'Error: ' . $e->getMessage();
        }
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
            <h1 style="text-align: center;">Edit Buku</h1>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <form method="POST">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" class="input-field" required>
                    <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" class="input-field" required>
                    <input type="number" name="year" value="<?php echo $book['year']; ?>" class="input-field" required>
                    <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" class="input-field" required>
                    <input type="number" name="stock" value="<?php echo $book['stock']; ?>" class="input-field" required>
                    <button type="submit" class="btn">Simpan</button>
                </form>
            </section>
        </main>
    </div>
</body>
</html>