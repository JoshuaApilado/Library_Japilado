// Function to read cookies
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) return parts.pop().split(';').shift();
}

// Function to fetch and display authors
async function loadAuthors() {
    const token = getCookie('authToken');
    if (!token) {
        alert('No token found. Please log in.');
        return;
    }

    try {
        const response = await fetch('http://localhost/library/public/readauthor', {
            method: 'GET',
            headers: {
                'Authorization': `Token ${token}`
            }
        });

        const data = await response.json();
        if (data.status === 'success') {
            displayAuthors(data.data);
        } else {
            alert(data.data.title || 'Failed to fetch authors.');
        }
    } catch (error) {
        console.error('Error fetching authors:', error);
        alert('Error fetching authors.');
    }
}

// Function to display authors
function displayAuthors(authors) {
    const authorList = document.getElementById('author-list');
    authorList.innerHTML = '';

    authors.forEach(author => {
        const authorItem = document.createElement('div');
        authorItem.className = 'author-item';

        const authorDetails = document.createElement('div');
        authorDetails.className = 'author-details';
        authorDetails.innerHTML = `
            <strong>${author.name}</strong>
            <p>Author ID: ${author.authorId}</p>
        `;

        const updateButton = document.createElement('button');
        updateButton.className = 'update';
        updateButton.textContent = 'Update';
        updateButton.addEventListener('click', () => openUpdateModal(author));

        const deleteButton = document.createElement('button');
        deleteButton.className = 'delete';
        deleteButton.textContent = 'Delete';
        deleteButton.addEventListener('click', () => deleteAuthor(author.authorId));

        authorItem.appendChild(authorDetails);
        authorItem.appendChild(updateButton);
        authorItem.appendChild(deleteButton);

        authorList.appendChild(authorItem);
    });
}

// Function to open the update modal
function openUpdateModal(author) {
    const modal = document.getElementById('update-modal');
    document.getElementById('author-title').value = author.name;
    document.getElementById('author-id').value = author.authorId;

    modal.style.display = 'block';

    document.getElementById('update-form').onsubmit = (event) => {
        event.preventDefault();
        updateAuthor(author.authorId);
    };
}

// Function to close the modal
function closeModal() {
    const modal = document.getElementById('update-modal');
    modal.style.display = 'none';
}

// Function to update an author
async function updateAuthor(authorId) {
    const token = getCookie('authToken');
    const name = document.getElementById('author-title').value;

    try {
        const response = await fetch('http://localhost/library/public/updateauthor', {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${token}`
            },
            body: JSON.stringify({
                authorid: authorId,
                name: name
            }),
        });

        const rawResponse = await response.text(); // Handle unexpected responses
        console.log('Raw Response:', rawResponse);

        const data = JSON.parse(rawResponse); // Parse JSON manually
        if (data.status === 'success') {
            alert('Author updated successfully.');
            document.cookie = `authToken=${data.token}; path=/;`; // Update token
            closeModal();
            loadAuthors();
        } else {
            alert(data.data.title || 'Failed to update author.');
        }
    } catch (error) {
        console.error('Error updating author:', error);
        alert('Error updating author.');
    }
}


// Function to delete an author
async function deleteAuthor(authorId) {
    const token = getCookie('authToken');

    try {
        const response = await fetch('http://localhost/library/public/deleteauthor', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Token ${token}`
            },
            body: JSON.stringify({ authorid: authorId })
        });

        const data = await response.json();
        if (data.status === 'success') {
            alert('Author deleted successfully.');
            loadAuthors();
        } else {
            alert(data.data.title || 'Failed to delete author.');
        }
    } catch (error) {
        console.error('Error deleting author:', error);
        alert('Error deleting author.');
    }
}

// Load authors on page load
document.addEventListener('DOMContentLoaded', loadAuthors);
