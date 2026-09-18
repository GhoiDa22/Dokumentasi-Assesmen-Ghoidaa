<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Perpustakaan Online</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .dropdown-content {
            display: none;
            position: absolute;
            background-color: #5620c9ff; /* Ungu tua transparan */
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            z-index: 1;
            left: 0;
            top: 100%;
            color: #fff; /* Teks putih biar kontras */
        }
        .dropdown:hover .dropdown-content {
            display: block;
        }
        .book-card img {
            max-width: 100px;
            height: auto;
        }
        .edit-form {
            display: none;
            margin-top: 20px;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .edit-form.active {
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_book'])) {
        $book_id = (int)$_POST['book_id'];

        // Cek apakah buku sedang dipinjam
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE book_id = ? AND status IN ('active', 'overdue')");
        $stmt->execute([$book_id]);
        $is_borrowed = $stmt->fetchColumn() > 0;

        if ($is_borrowed) {
            $_SESSION['error'] = 'Buku sedang dipinjam, tidak dapat dihapus!';
        } else {
            $stmt = $pdo->prepare("DELETE FROM books WHERE id = ?");
            $stmt->execute([$book_id]);
            $_SESSION['success'] = 'Buku berhasil dihapus!';
        }
        redirect('manage_books.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_book'])) {
        $book_id = (int)$_POST['book_id'];
        $title = validateInput($_POST['title']);
        $author = validateInput($_POST['author']);
        $year = (int)$_POST['year'];
        $isbn = validateInput($_POST['isbn']);
        $stock = (int)$_POST['stock'];
        $status = validateInput($_POST['status']);

        $stmt = $pdo->prepare("UPDATE books SET title = ?, author = ?, year = ?, isbn = ?, stock = ?, status = ? WHERE id = ?");
        $stmt->execute([$title, $author, $year, $isbn, $stock, $status, $book_id]);
        $_SESSION['success'] = 'Buku berhasil diperbarui!';
        redirect('manage_books.php');
    }

    $stmt = $pdo->query("SELECT * FROM books ORDER BY title");
    $books = $stmt->fetchAll();
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
            <h1 style="text-align: center;">Kelola Buku</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <table>
                    <tr>
                        <th>Cover</th>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>Tahun</th>
                        <th>ISBN</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                    <?php if (empty($books)): ?>
                        <tr><td colspan="8">Tidak ada buku.</td></tr>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <tr>
                                <td><?php if ($book['cover']): ?><img src="<?php echo htmlspecialchars($book['cover']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>"><?php else: ?>-<?php endif; ?></td>
                                <td><?php echo htmlspecialchars($book['title']); ?></td>
                                <td><?php echo htmlspecialchars($book['author']); ?></td>
                                <td><?php echo $book['year']; ?></td>
                                <td><?php echo htmlspecialchars($book['isbn']); ?></td>
                                <td><?php echo $book['stock']; ?></td>
                                <td><?php echo $book['status']; ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <button type="submit" name="delete_book" class="btn confirm-btn" onclick="return confirm('Hapus buku ini? Jika buku sedang dipinjam, penghapusan akan gagal.')">Hapus</button>
                                    </form>
                                    <button class="btn edit-btn" onclick="toggleEditForm(<?php echo $book['id']; ?>)">Edit</button>
                                </td>
                            </tr>
                            <tr id="edit-form-<?php echo $book['id']; ?>" class="edit-form">
                                <td colspan="8">
                                    <form method="POST">
                                        <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                        <input type="text" name="title" value="<?php echo htmlspecialchars($book['title']); ?>" class="input-field" required>
                                        <input type="text" name="author" value="<?php echo htmlspecialchars($book['author']); ?>" class="input-field" required>
                                        <input type="number" name="year" value="<?php echo $book['year']; ?>" class="input-field" required>
                                        <input type="text" name="isbn" value="<?php echo htmlspecialchars($book['isbn']); ?>" class="input-field" required>
                                        <input type="number" name="stock" value="<?php echo $book['stock']; ?>" class="input-field" required>
                                        <select name="status" class="input-field" required>
                                            <option value="available" <?php echo $book['status'] === 'available' ? 'selected' : ''; ?>>Tersedia</option>
                                            <option value="borrowed" <?php echo $book['status'] === 'borrowed' ? 'selected' : ''; ?>>Dipinjam</option>
                                        </select>
                                        <button type="submit" name="edit_book" class="btn">Simpan</button>
                                        <button type="button" class="btn cancel-btn" onclick="toggleEditForm(<?php echo $book['id']; ?>)">Batal</button>
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
        function toggleEditForm(bookId) {
            const form = document.getElementById('edit-form-' + bookId);
            form.classList.toggle('active');
        }
    </script>
</body>
</html>