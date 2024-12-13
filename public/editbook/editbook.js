// Function to read cookies
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

// Function to fetch and display books
async function loadBooks() {
    const token = getCookie('authToken');
    if (!token) {
        alert('No token found. Please log in.');
        return;
    }

    try {
        const response = await fetch('http://localhost/library/public/books', {
            method: 'GET', // Explicitly specify GET
            headers: {
                'Authorization': `Token ${token}`
            }
        });

        const data = await response.json();
        if (data.status === 'success') {
            displayBooks(data.data);
        } else {
            alert(data.data.title);
        }
    } catch (error) {
        console.error('Error fetching books:', error);
        alert('Error fetching books.');
    }
}

// Function to display books
function displayBooks(books) {
    const booksList = document.getElementById('books-list');
    booksList.innerHTML = '';

    books.forEach(book => {
        const bookItem = document.createElement('div');
        bookItem.className = 'book-item';

        const bookDetails = document.createElement('div');
        bookDetails.className = 'book-details';
        bookDetails.innerHTML = `
            <strong>${book.books}</strong>
            <p>Author: ${book.author}</p>
            <p>Year: ${book.bookId}</p>
        `;

        const updateButton = document.createElement('button');
        updateButton.className = 'update';
        updateButton.textContent = 'Update';
        updateButton.addEventListener('click', () => updateBook(book.id));

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteBook(book.id));

        bookItem.appendChild(bookDetails);
        bookItem.appendChild(updateButton);
        bookItem.appendChild(deleteButton);

        booksList.appendChild(bookItem);
    });
}

// Placeholder functions for update and delete
function updateBook(id) {
    alert(`Update book with ID: ${id}`);
}

function deleteBook(id) {
    alert(`Delete book with ID: ${id}`);
}

// Load books on page load
document.addEventListener('DOMContentLoaded', loadBooks);
