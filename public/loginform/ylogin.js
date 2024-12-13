document.getElementById('loginForm').addEventListener('submit', function(event) {
    event.preventDefault(); // Prevent default form submission

    // Get the username and password values
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    // Simple validation (optional)
    if (!username || !password) {
        document.getElementById('error-message').textContent = 'Both fields are required!';
        return;
    }

    // Prepare the data for the POST request
    const loginData = {
        username: username,
        password: password
    };

    // Send the POST request to the server
    fetch('http://localhost/library/public/verify', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(loginData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            localStorage.setItem('username', data.data.username);  // Corrected this line
            setTimeout(() => {
                window.location.href = '/library/public/userdashboard/userinfotest.html'; // Replace with your actual protected page URL
            }, 2000);
        } else {
            document.getElementById('error-message').textContent = 'Authentication Failed. Please check your credentials.';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('error-message').textContent = 'An error occurred. Please try again later.';
    });
});
