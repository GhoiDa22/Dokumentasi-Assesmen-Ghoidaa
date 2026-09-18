function toggleChatbot() {
    const chatbot = document.getElementById('chatbot');
    chatbot.style.display = chatbot.style.display === 'none' ? 'block' : 'none';
}

function addMessage(message, isUser = false) {
    const chatbotBody = document.getElementById('chatbot-body');
    const messageDiv = document.createElement('div');
    messageDiv.style.marginBottom = '10px';
    messageDiv.style.padding = '5px';
    messageDiv.style.background = isUser ? '#e67e22' : '#ecf0f1';
    messageDiv.style.color = isUser ? 'white' : '#333';
    messageDiv.style.borderRadius = '5px';
    messageDiv.innerHTML = message;
    chatbotBody.appendChild(messageDiv);
    chatbotBody.scrollTop = chatbotBody.scrollHeight;
}

document.getElementById('chatbot-input').addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && this.value.trim()) {
        const userInput = this.value.trim();
        addMessage(userInput, true);
        fetch('../config/chatbot.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'message=' + encodeURIComponent(userInput)
        })
        .then(response => response.json())
        .then(data => {
            addMessage(data.response);
        })
        .catch(error => {
            addMessage('Error: Tidak bisa terhubung ke server.');
        });
        this.value = '';
    }
});

addMessage('Halo! Kirim nomor untuk opsi berikut:<br>1. Jam buka<br>2. Cara pinjam<br>3. Cari buku<br>4. Status kartu<br>5. Cek denda');