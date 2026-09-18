<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Member - Perpustakaan Online</title>
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

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_member'])) {
        $member_id = (int)$_POST['member_id'];

        // Cek apakah member punya peminjaman
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM loans WHERE member_id = ?");
        $stmt->execute([$member_id]);
        $loan_count = $stmt->fetchColumn();

        if ($loan_count > 0) {
            // Hapus fines terkait loans terlebih dahulu
            $stmt = $pdo->prepare("DELETE FROM fines WHERE loan_id IN (SELECT id FROM loans WHERE member_id = ?)");
            $stmt->execute([$member_id]);

            // Hapus loans terkait member
            $stmt = $pdo->prepare("DELETE FROM loans WHERE member_id = ?");
            $stmt->execute([$member_id]);
        }

        // Hapus member
        $stmt = $pdo->prepare("DELETE FROM members WHERE id = ?");
        $stmt->execute([$member_id]);
        $_SESSION['success'] = 'Member berhasil dihapus bersama data terkait!';
        redirect('manage_members.php');
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_member'])) {
        $member_id = (int)$_POST['member_id'];
        $name = validateInput($_POST['name']);
        $email = validateInput($_POST['email']);
        $nis = validateInput($_POST['nis']);
        $jurusan = validateInput($_POST['jurusan']);
        $no_hp = validateInput($_POST['no_hp']);
        $kelas = validateInput($_POST['kelas']);

        // Cek duplikat email dan NIS (kecuali data lama member)
        $stmt = $pdo->prepare("SELECT email, nis FROM members WHERE (email = ? OR nis = ?) AND id != ?");
        $stmt->execute([$email, $nis, $member_id]);
        $existing = $stmt->fetch();
        if ($existing) {
            if ($existing['email'] === $email) {
                $_SESSION['error'] = 'Email sudah terdaftar oleh member lain! Coba email lain.';
            } elseif ($existing['nis'] === $nis) {
                $_SESSION['error'] = 'NIS sudah terdaftar oleh member lain! Coba NIS lain.';
            }
        } else {
            $stmt = $pdo->prepare("UPDATE members SET name = ?, email = ?, nis = ?, jurusan = ?, no_hp = ?, kelas = ? WHERE id = ?");
            $stmt->execute([$name, $email, $nis, $jurusan, $no_hp, $kelas, $member_id]);
            $_SESSION['success'] = 'Member berhasil diperbarui!';
            redirect('manage_members.php');
        }
    }

    $stmt = $pdo->query("SELECT * FROM members ORDER BY name");
    $members = $stmt->fetchAll();
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
            <h1 style="text-align: center;">Kelola Member</h1>
            <?php if (isset($_SESSION['success'])): ?>
                <p class="success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></p>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <p class="error"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></p>
            <?php endif; ?>
            <section class="dashboard-section">
                <table>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>NIS</th>
                        <th>Jurusan</th>
                        <th>No HP</th>
                        <th>Kelas</th>
                        <th>Aksi</th>
                    </tr>
                    <?php if (empty($members)): ?>
                        <tr><td colspan="7">Tidak ada member.</td></tr>
                    <?php else: ?>
                        <?php foreach ($members as $member): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($member['name']); ?></td>
                                <td><?php echo htmlspecialchars($member['email']); ?></td>
                                <td><?php echo htmlspecialchars($member['nis']); ?></td>
                                <td><?php echo htmlspecialchars($member['jurusan'] ?? 'Tidak ada'); ?></td>
                                <td><?php echo htmlspecialchars($member['no_hp'] ?? 'Tidak ada'); ?></td>
                                <td><?php echo htmlspecialchars($member['kelas'] ?? 'Tidak ada'); ?></td>
                                <td>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <button type="submit" name="delete_member" class="btn confirm-btn" onclick="return confirm('Hapus member ini? Semua data terkait akan dihapus!')">Hapus</button>
                                    </form>
                                    <button class="btn edit-btn" onclick="toggleEditForm(<?php echo $member['id']; ?>)">Edit</button>
                                </td>
                            </tr>
                            <tr id="edit-form-<?php echo $member['id']; ?>" class="edit-form">
                                <td colspan="7">
                                    <form method="POST">
                                        <input type="hidden" name="member_id" value="<?php echo $member['id']; ?>">
                                        <input type="text" name="name" value="<?php echo htmlspecialchars($member['name']); ?>" class="input-field" required>
                                        <input type="email" name="email" value="<?php echo htmlspecialchars($member['email']); ?>" class="input-field" required>
                                        <input type="text" name="nis" value="<?php echo htmlspecialchars($member['nis']); ?>" class="input-field" required>
                                        <input type="text" name="jurusan" value="<?php echo htmlspecialchars($member['jurusan'] ?? ''); ?>" class="input-field" required>
                                        <input type="text" name="no_hp" value="<?php echo htmlspecialchars($member['no_hp'] ?? ''); ?>" class="input-field" required>
                                        <input type="text" name="kelas" value="<?php echo htmlspecialchars($member['kelas'] ?? ''); ?>" class="input-field" required>
                                        <button type="submit" name="edit_member" class="btn">Simpan</button>
                                        <button type="button" class="btn cancel-btn" onclick="toggleEditForm(<?php echo $member['id']; ?>)">Batal</button>
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
        function toggleEditForm(memberId) {
            const form = document.getElementById('edit-form-' + memberId);
            form.classList.toggle('active');
        }
    </script>
</body>
</html>