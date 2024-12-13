document.getElementById('login-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    // Get form data
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const messageDiv = document.getElementById('message');
    messageDiv.style.display = 'none'; // Reset message display

    try {
        // Call the API
        const response = await fetch('/library/public/verify', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        const result = await response.json();

        // Display the message
        messageDiv.style.display = 'block';
        if (result.status === 'success') {
            localStorage.setItem('username', result.data.username);  // Store username
            messageDiv.className = 'message success';
            messageDiv.textContent = 'Login successful!';

            // Redirect to a protected page after login
            setTimeout(() => {
                window.location.href = '/library/public/userdashboard/userinfo.html'; // Replace with your actual protected page URL
            }, 2000);
        } else {
            messageDiv.className = 'message fail';
            messageDiv.textContent = result.data.title || 'Authentication failed.';
        }
    } catch (error) {
        // Handle any errors
        messageDiv.style.display = 'block';
        messageDiv.className = 'message fail';
        messageDiv.textContent = 'An error occurred. Please try again.';
    }
});
