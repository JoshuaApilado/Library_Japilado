document.addEventListener('DOMContentLoaded', function() {
    // Handle form submission
    document.getElementById('registerBookForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const title = document.getElementById('title').value;
        const token = getCookie('authToken'); // Read token from cookies
        const authorId = localStorage.getItem('authorId'); // Retrieve authorId from localStorage

        console.log('Book title:', title);  // Debugging
        console.log('Token:', token);  // Debugging
        console.log('Author ID:', authorId);  // Debugging

        // Check if title, token, or authorId is missing
        if (!title || !token || !authorId) {
            document.getElementById('error-message').textContent = 'Title, token, and author ID are required!';
            return;
        }

        // Prepare data to be sent in the request
        const bookData = {
            title: title,
            token: token,
            authorid: authorId
        };

        // Send POST request to register the book
        fetch('http://localhost/library/public/registerbook', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(bookData)
        })
        .then(response => {
            // Log the raw response for debugging
            console.log('Raw response:', response);

            // Attempt to parse the response as JSON
            return response.json(); // This will throw if the response isn't valid JSON
        })
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('error-message').textContent = '';
                alert('Book registered successfully!');
                document.getElementById('registerBookForm').reset();  // Clear the form

                setTimeout(() => {
                    window.location.href = '/library/public/userdashboard/userinfotest.html'; // Replace with your actual protected page URL
                }, 2000);

            } else {
                document.getElementById('error-message').textContent = 'Failed to register book. ' + (data.message || 'Unknown error');
            }
        })
        .catch(error => {
            // Handle any errors that occur during the fetch or JSON parsing
            console.error('Error:', error);
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
