// Function to get cookie by name
function getCookie(name) {
    let nameEQ = name + "=";
    let ca = document.cookie.split(';');
    for (let i = 0; i < ca.length; i++) {
        let c = ca[i].trim();
        if (c.indexOf(nameEQ) === 0) {
            console.log('Cookie found:', c);  // Debugging
            return c.substring(nameEQ.length, c.length);
        }
    }
    console.log('Cookie not found');  // Debugging
    return null;
}

// Function to fetch books data from API
function fetchBooksData() {
    const token = getCookie('authToken');  // Replace 'auth_token' with the actual cookie name
    console.log('Token from cookie:', token); // Debugging

    if (!token) {
        document.getElementById('error-message').textContent = 'Token not found. Please log in.';
        return;
    }

    fetch('http://localhost/library/public/books', {
        method: 'GET',
        headers: {
            'Authorization': `Token ${token}`
        },
        credentials: 'include'  // Ensure cookies are sent with cross-origin requests
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'fail') {
            document.getElementById('error-message').textContent = data.data.title;
        } else {
            displayBooksData(data.data);
        }
    })
    .catch(error => {
        console.error('Error fetching data:', error);
        document.getElementById('error-message').textContent = 'An error occurred while fetching data.';
    });
}

// Function to display books data in table
function displayBooksData(authorsData) {
    const tableBody = document.getElementById('table-body');
    tableBody.innerHTML = '';  // Clear previous data

    authorsData.forEach(item => {
        // Skip this item if either `author` or `books` is null/undefined/empty
        if (!item.author || !item.books || item.books.length === 0) {
            return;
        }

        const row = document.createElement('tr');
        const authorCell = document.createElement('td');
        const booksCell = document.createElement('td');

        authorCell.textContent = item.author;
        booksCell.textContent = item.books.join(', ');

        row.appendChild(authorCell);
        row.appendChild(booksCell);

        tableBody.appendChild(row);
    });
}

// Fetch data when the page loads
document.addEventListener('DOMContentLoaded', fetchBooksData);
