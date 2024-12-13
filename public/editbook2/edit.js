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
        const response = await fetch('http://localhost/library/public/readbook', {
            method: 'GET',
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
            <strong>${book.title}</strong>
            <p>Product Code: ${book.bookId}</p>
            <p>book Id: ${book.authorId}</p>
        `;

        const updateButton = document.createElement('button');
        updateButton.className = 'update';
        updateButton.textContent = 'Update';
        updateButton.addEventListener('click', () => openUpdateModal(book));

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteBook(book.bookId));  // Use book.bookId


        bookItem.appendChild(bookDetails);
        bookItem.appendChild(updateButton);
        bookItem.appendChild(deleteButton);

        booksList.appendChild(bookItem);
    });
}

// Function to open update modal and populate it
function openUpdateModal(book) {
    const modal = document.getElementById('update-modal');
    document.getElementById('book-title').value = book.title;  // Corrected property name

    document.getElementById('author-id').value = book.authorId;
    modal.style.display = 'block';

    // Add event listener to update form submission
    document.getElementById('update-form').onsubmit = (event) => {
        event.preventDefault();
        updateBook(book.bookId);
    };
}

// Function to close the modal
function closeModal() {
    const modal = document.getElementById('update-modal');
    modal.style.display = 'none';
}

// Function to update a book
async function updateBook(bookId) {
    const token = getCookie('authToken');
    const title = document.getElementById('book-title').value;
    const authorId = document.getElementById('author-id').value;

    console.log("Updating book with ID:", bookId);
    console.log("Payload:", { id: bookId, title, authorid: authorId });

    try {
        const response = await fetch('http://localhost/library/public/updatebook', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${token}`
            },
            body: JSON.stringify({
                id: bookId,
                title: title,
                authorid: authorId
            })
        });

        const responseText = await response.text();
        console.log("Raw Server Response:", responseText);

        // Attempt to parse JSON, but handle non-JSON responses
        try {
            const data = JSON.parse(responseText);
            if (data.status === 'success') {
                alert('Book updated successfully');
                closeModal();
                loadBooks();
            } else {
                alert(data.message || "Failed to update the book.");
            }
        } catch (err) {
            console.error("Non-JSON response:", responseText);
            alert("An unexpected error occurred. Check server logs for details.");
        }
    } catch (error) {
        console.error('Error updating book:', error);
        alert('Error updating book.');
    }
}



// Function to delete a book
async function deleteBook(bookId) {
    const token = getCookie('authToken');

    try {
        const response = await fetch('http://localhost/library/public/deletebooks', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${token}`
            },
            body: JSON.stringify({
                bookid: bookId,
                token: token
            })
        });

        const data = await response.json();
        if (data.status === 'success') {
            alert('Book deleted successfully');
            loadBooks();  // Reload the books list after deletion
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Error deleting book:', error);
        alert('Error deleting book.');
    }
}

// Load books on page load
document.addEventListener('DOMContentLoaded', loadBooks);
