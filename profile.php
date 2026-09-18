<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Perpustakaan Online</title>
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
        .profile-section {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 5px;
        }
        .profile-section img {
            max-width: 100px;
            height: auto;
            border-radius: 50%;
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

    $user_id = $_SESSION['user_id'];
    $stmt = $pdo->prepare("SELECT * FROM members WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
        $name = validateInput($_POST['name']);
        $email = validateInput($_POST['email']);
        $nis = validateInput($_POST['nis']);
        $jurusan = validateInput($_POST['jurusan']);
        $no_hp = validateInput($_POST['no_hp']);
        $password = !empty($_POST['password']) ? $_POST['password'] : $user['password']; // Tanpa hashing

        // Cek duplikat email (kecuali email lama user)
        $stmt = $pdo->prepare("SELECT email FROM members WHERE email = ? AND id != ?");
        $stmt->execute([$email, $user_id]);
        if ($stmt->fetch()) {
            $_SESSION['error'] = 'Email sudah terdaftar oleh member lain! Coba email lain.';
        } else {
            // Handle upload foto
            $photo = $user['photo'];
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                $photo = 'assets/images/members/' . uniqid() . '_' . basename($_FILES['photo']['name']);
                move_uploaded_file($_FILES['photo']['tmp_name'], $photo);
            }

            $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, nis = ?, jurusan = ?, no_hp = ?, password = ?, photo = ? WHERE id = ?");
            $stmt->execute([$name, $email, $nis, $jurusan, $no_hp, $password, $photo, $user_id]);
            $_SESSION['success'] = 'Profil berhasil diperbarui!';
            redirect('profile.php');
        }
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
            <div class="profile-section">
                <h1 style="text-align: center;">Profil Member</h1>
                <?php if (isset($_SESSION['error'])): ?>
                    <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
                <?php endif; ?>
                <?php if (isset($_SESSION['success'])): ?>
                    <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
                <?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <img src="<?php echo $user['photo'] ? $user['photo'] : 'assets/images/default-avatar.png'; ?>" alt="Foto Profil" id="profile-photo">
                    <input type="file" name="photo" class="input-field" accept="image/*" onchange="previewImage(event)">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" class="input-field" required>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" class="input-field" required>
                    <input type="text" name="nis" value="<?php echo htmlspecialchars($user['nis']); ?>" class="input-field" required>
                    <input type="text" name="jurusan" value="<?php echo htmlspecialchars($user['jurusan'] ?? ''); ?>" class="input-field" required>
                    <input type="text" name="no_hp" value="<?php echo htmlspecialchars($user['no_hp'] ?? ''); ?>" class="input-field" required>
                    <input type="password" name="password" class="input-field" placeholder="Ganti Password (kosongkan jika tidak diubah)">
                    <button type="submit" name="update_profile" class="btn">Simpan</button>
                </form>
                <h2>Data Peminjaman</h2>
                <?php
                $stmt = $pdo->prepare("SELECT l.*, b.title AS book_title FROM loans l JOIN books b ON l.book_id = b.id WHERE l.member_id = ? ORDER BY l.loan_date DESC");
                $stmt->execute([$user_id]);
                $loans = $stmt->fetchAll();
                ?>
                <table class="loan-table">
                    <tr>
                        <th>Judul Buku</th>
                        <th>Tanggal Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Status</th>
                    </tr>
                    <?php if (empty($loans)): ?>
                        <tr><td colspan="4">Tidak ada riwayat peminjaman.</td></tr>
                    <?php else: ?>
                        <?php foreach ($loans as $loan): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($loan['book_title']); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['loan_date'])); ?></td>
                                <td><?php echo date('d-m-Y', strtotime($loan['due_date'])); ?></td>
                                <td><?php echo $loan['status'] === 'active' ? 'Aktif' : ($loan['status'] === 'overdue' ? 'Terlambat' : 'Selesai'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </div>
        </main>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById('profile-photo').src = e.target.result;
            }
            reader.readAsDataURL(file);
        }
    </script>
</body>
</html>