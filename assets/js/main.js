// function openModal(modalId) {
//     document.getElementById(modalId).style.display = 'block';
// }

// function closeModal(modalId) {
//     document.getElementById(modalId).style.display = 'none';
// }

// function openEditBookModal(id, title, author, year, isbn, stock) {
//     document.getElementById('edit_book_id').value = id;
//     document.getElementById('edit_book_title').value = title;
//     document.getElementById('edit_book_author').value = author;
//     document.getElementById('edit_book_year').value = year;
//     document.getElementById('edit_book_isbn').value = isbn;
//     document.getElementById('edit_book_stock').value = stock;
//     openModal('editBookModal');
// }

// function openEditMemberModal(id, name, email, nis, card_expiry_date) {
//     document.getElementById('edit_member_id').value = id;
//     document.getElementById('edit_member_name').value = name;
//     document.getElementById('edit_member_email').value = email;
//     document.getElementById('edit_member_nis').value = nis;
//     document.getElementById('edit_member_card_expiry_date').value = card_expiry_date;
//     openModal('editMemberModal');
// }

// function openEditFineModal(fineId, currentFine) {
//     document.getElementById('edit_fine_id').value = fineId;
//     document.getElementById('edit_fine_amount').value = currentFine;
//     openModal('editFineModal');
// }

// function closeFineNotification() {
//     document.getElementById('fineNotification').style.display = 'none';
// }

// document.addEventListener('DOMContentLoaded', function() {
//     const editBookForm = document.getElementById('editBookForm');
//     if (editBookForm) {
//         editBookForm.addEventListener('submit', function(e) {
//             e.preventDefault();
//             fetch('update_book.php', {
//                 method: 'POST',
//                 body: new FormData(editBookForm)
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     alert('Buku berhasil diperbarui!');
//                     location.reload();
//                 } else {
//                     alert(data.message);
//                 }
//             });
//         });
//     }

//     const editMemberForm = document.getElementById('editMemberForm');
//     if (editMemberForm) {
//         editMemberForm.addEventListener('submit', function(e) {
//             e.preventDefault();
//             fetch('update_user.php', {
//                 method: 'POST',
//                 body: new FormData(editMemberForm)
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     alert('Member berhasil diperbarui!');
//                     location.reload();
//                 } else {
//                     alert(data.message);
//                 }
//             });
//         });
//     }

//     const editFineForm = document.getElementById('editFineForm');
//     if (editFineForm) {
//         editFineForm.addEventListener('submit', function(e) {
//             e.preventDefault();
//             fetch('update_fine.php', {
//                 method: 'POST',
//                 body: new FormData(editFineForm)
//             })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     alert('Denda berhasil diperbarui!');
//                     location.reload();
//                 } else {
//                     alert(data.message);
//                 }
//             });
//         });
//     }
// });

// function toggleChatbot() {
//     const chatbot = document.getElementById('chatbot');
//     chatbot.style.display = chatbot.style.display === 'block' ? 'none' : 'block';
// }

function openModal(modalId) {
    document.getElementById(modalId).style.display = 'block';
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}

function closeFineNotification() {
    document.getElementById('fineNotification').style.display = 'none';
}

function toggleChatbot() {
    const chatbot = document.getElementById('chatbot');
    chatbot.style.display = chatbot.style.display === 'block' ? 'none' : 'block';
}