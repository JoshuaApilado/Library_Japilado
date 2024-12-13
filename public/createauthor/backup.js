document.addEventListener('DOMContentLoaded', function() {
    // Display logged-in username from localStorage
    const loggedInUser = localStorage.getItem('username');
    console.log('Logged in user from localStorage:', loggedInUser);  // Debugging
    if (loggedInUser) {
        document.getElementById('logged-in-user').textContent = loggedInUser;
    } else {
        document.getElementById('logged-in-user').textContent = 'Unknown User';
    }

    // Handle form submission
    document.getElementById('authorForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const authorName = document.getElementById('name').value;
        const token = getCookie('authToken'); // Read token from cookies

        console.log('Author name:', authorName);  // Debugging
        console.log('Token:', token);  // Debugging

        if (!authorName || !token) {
            document.getElementById('error-message').textContent = 'Author name is required, and token must be present!';
            return;
        }

        // Prepare data to be sent in the request
        const authorData = {
            name: authorName,
            token: token // Automatically sent from cookies, no need for user input
        };

        // Send POST request to register the author
        fetch('http://localhost/library/public/create', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(authorData)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('error-message').textContent = '';
                alert('Author registered successfully!');
                // Optionally, clear the form or do any other UI updates
                document.getElementById('authorForm').reset();  // Clear the form after successful submission
            } else {
                document.getElementById('error-message').textContent = 'Failed to register author. ' + (data.data.title || 'Unknown error');
            }
        })
        .catch(error => {
            document.getElementById('error-message').textContent = 'Error: ' + error.message;
        });
    });
});

// Utility function to get cookie by name
function getCookie(name) {
    const value = `; ${document.cookie}`;
    const parts = value.split(`; ${name}=`);
    if (parts.length === 2) {
        return parts.pop().split(';').shift();
    }
    return null;
}
