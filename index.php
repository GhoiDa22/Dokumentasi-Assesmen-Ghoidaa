<?php
session_start();
require_once 'config/db_connect.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}

$stmt = $pdo->query("SELECT * FROM books ORDER BY title");
$books = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Perpustakaan Online</title>
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
        }
        .book-card img {
            max-width: 100px;
            height: auto;
        }
        .chatbot {
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 300px;
            height: 400px;
            background: #fff;
            border: 2px solid #5620c9ff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(86, 32, 201, 0.3);
            display: none;
            flex-direction: column;
            overflow: hidden;
        }
        .chatbot-header {
            background: #5620c9ff;
            color: #fff;
            padding: 10px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
        }
        .chatbot-messages {
            flex-grow: 1;
            padding: 10px;
            overflow-y: auto;
            background: #f1f1f1;
        }
        .chatbot-messages .bot-message {
            background: #e9ecef;
            color: #333;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 10px;
            max-width: 70%;
            animation: slideIn 0.3s ease-out;
        }
        .chatbot-messages .user-message {
            background: #5620c9ff;
            color: #fff;
            padding: 8px 12px;
            margin: 5px 0;
            border-radius: 10px;
            max-width: 70%;
            margin-left: auto;
            animation: slideIn 0.3s ease-out;
        }
        @keyframes slideIn {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        .chatbot-input {
            padding: 10px;
            border-top: 2px solid #5620c9ff;
            background: #fff;
        }
        .chatbot-input input {
            width: 75%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            outline: none;
        }
        .chatbot-input input:focus {
            border-color: #5620c9ff;
            box-shadow: 0 0 5px rgba(86, 32, 201, 0.5);
        }
        .chatbot-input button {
            padding: 8px 15px;
            background: #5620c9ff;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .chatbot-input button:hover {
            background: #3f17a0ff;
        }
        #chatbot-toggle {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: #5620c9ff;
            color: #fff;
            border: none;
            padding: 10px;
            border-radius: 10px;
            cursor: pointer;
            font-size: 20px;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #chatbot-toggle::before {
            content: "\1F916"; /* Emoji robot */
            font-size: 24px;
        }
    </style>
</head>
<body>
    <div class="container">
        <aside class="sidebar">
            <div class="logo">
                <img src="assets/images/smkn1-logo.png" alt="SMKN 1 Surakarta">
                Perpustakaan SMKN 1 Surakarta
            </div>
            <nav>
                <ul>
                    <li><a href="index.php">Beranda</a></li>
                    <li class="dropdown">
                        <a href="#">Member</a>
                        <div class="dropdown-content">
                            <a href="register.php">Daftar</a>
                            <a href="login.php">Login</a>
                             <a href="forgot_password.php">Lupa Password</a>
                        </div>
                    </li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <h1 style="text-align: center;">Selamat Datang di Perpustakaan</h1>
            <section class="dashboard-section">
                <h2>Daftar Buku</h2>
                <div class="book-list">
                    <?php if (empty($books)): ?>
                        <p>Tidak ada buku tersedia.</p>
                    <?php else: ?>
                        <?php foreach ($books as $book): ?>
                            <div class="book-card">
                                <?php if ($book['cover']): ?><img src="<?php echo htmlspecialchars($book['cover']); ?>" alt="<?php echo htmlspecialchars($book['title']); ?>"><?php endif; ?>
                                <h3><?php echo htmlspecialchars($book['title']); ?></h3>
                                <p>Penulis: <?php echo htmlspecialchars($book['author']); ?></p>
                                <p>Stok: <?php echo $book['stock']; ?></p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>
    <button id="chatbot-toggle"></button>
    <div id="chatbot" class="chatbot">
        <div class="chatbot-header">Chatbot Perpustakaan</div>
        <div class="chatbot-messages" id="chatbot-messages"></div>
        <div class="chatbot-input">
            <input type="text" id="chatbot-input" placeholder="Ketik pesan...">
            <button onclick="sendMessage()">Kirim</button>
        </div>
    </div>
    <script src="assets/js/main.js"></script>
    <script>
        const chatbotToggle = document.getElementById('chatbot-toggle');
        const chatbot = document.getElementById('chatbot');
        const messages = document.getElementById('chatbot-messages');
        const input = document.getElementById('chatbot-input');

        chatbotToggle.addEventListener('click', () => {
            chatbot.style.display = chatbot.style.display === 'flex' ? 'none' : 'flex';
        });

        function sendMessage() {
            const text = input.value.trim();
            if (text) {
                addMessage('You: ' + text, 'user');
                input.value = '';

                if (text.toLowerCase().startsWith('cari buku')) {
                    const query = text.replace('cari buku', '').trim();
                    fetch('search_books.php?q=' + encodeURIComponent(query))
                        .then(response => response.json())
                        .then(data => {
                            if (data.found && data.book) {
                                addMessage('Bot: Buku ditemukan! Judul: ' + data.book.title + '. Stok tersedia, Anda bisa meminjamnya via halaman Cari Buku.', 'bot');
                            } else {
                                addMessage('Bot: Maaf, buku dengan judul "' + query + '" tidak ditemukan.', 'bot');
                            }
                        })
                        .catch(error => {
                            addMessage('Bot: Error saat mencari buku.', 'bot');
                            console.error('Error:', error);
                        });
                } else if (text.toLowerCase() === 'cara pinjam') {
                    addMessage('Bot: Cara Pinjam:\n1. Login atau daftar sebagai member.\n2. Pilih buku di Beranda.\n3. Ajukan peminjaman via Dashboard.', 'bot');
                } else if (text.toLowerCase() === 'jam buka tutup') {
                    addMessage('Bot: Jam Buka-Tutup:\nSenin-Jumat: 08:00 - 16:00\nSabtu: 08:00 - 12:00\nMinggu: Tutup', 'bot');
                } else {
                    addMessage('Bot: Pilih opsi: "cari buku [judul]", "cara pinjam", atau "jam buka tutup".', 'bot');
                }
            }
        }

        function addMessage(text, sender) {
            const div = document.createElement('div');
            div.textContent = text;
            div.className = sender === 'user' ? 'user-message' : 'bot-message';
            messages.appendChild(div);
            messages.scrollTop = messages.scrollHeight;
        }

        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
</body>
</html>