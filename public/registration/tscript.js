document.getElementById('register-form').addEventListener('submit', async function (e) {
    e.preventDefault();

    // Get form data
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;

    const messageDiv = document.getElementById('message');
    messageDiv.style.display = 'none'; // Reset message display

    try {
        // Call the API
        const response = await fetch('http://localhost/library/public/register', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });

        const result = await response.json();

        // Display the message
        messageDiv.style.display = 'block';
        if (result.status === 'success') {
            messageDiv.className = 'message success';
            messageDiv.textContent = 'Registration successful!!';

            // Redirect to the login page after registration
            setTimeout(() => {
                window.location.href = '/library/public/loginform/loginform.html'; // Corrected the redirect URL
            }, 2000);
        } else {
            messageDiv.className = 'message fail';
            messageDiv.textContent = result.data.title || 'Registration failed.';
        }
    } catch (error) {
        // Handle any errors
        messageDiv.style.display = 'block';
        messageDiv.className = 'message fail';
        messageDiv.textContent = 'An error occurred. Please try again.';
    }
});
