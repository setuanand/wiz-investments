// Authentication form validation

document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('register-form');
    const loginForm = document.getElementById('login-form');
    const resetForm = document.getElementById('reset-form');

    // Register form validation
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const email = this.querySelector('#email').value.trim();
            const password = this.querySelector('#password').value;
            const passwordConfirm = this.querySelector('#password_confirm').value;

            if (!email || !password || !passwordConfirm) {
                e.preventDefault();
                alert('All fields are required.');
                return;
            }

            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }

            if (password.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters.');
                return;
            }
        });
    }

    // Login form validation
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = this.querySelector('#email').value.trim();
            const password = this.querySelector('#password').value;

            if (!email || !password) {
                e.preventDefault();
                alert('Email and password are required.');
                return;
            }
        });
    }

    // Reset form validation
    if (resetForm) {
        resetForm.addEventListener('submit', function(e) {
            const newPassword = this.querySelector('#new_password').value;
            const confirmPassword = this.querySelector('#confirm_password').value;

            if (!newPassword || !confirmPassword) {
                e.preventDefault();
                alert('Both password fields are required.');
                return;
            }

            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Passwords do not match.');
                return;
            }

            if (newPassword.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters.');
                return;
            }
        });
    }
});
