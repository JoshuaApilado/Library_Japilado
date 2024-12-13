// JavaScript to handle login action
document.getElementById('loginButton').addEventListener('click', function() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    if (!username || !password) {
        alert('Please enter both username and password.');
        return;
    }

    const userData = {
        username: username,
        password: password
    };

    // Send a POST request to your PHP server
    fetch('http://localhost/library/public/verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(userData),
        credentials: 'same-origin' // Make sure cookies are sent with the request
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Login successful!');
            // Optionally redirect the user to another page, e.g.:
            // window.location.href = '/dashboard';
        } else {
            alert(data.data.title); // Authentication failed
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again later.');
    });
});
