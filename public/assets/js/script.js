document.addEventListener('DOMContentLoaded', function () {
    // Function to validate forms
    function validateForm(form) {
        const inputs = form.querySelectorAll('input[required]');
        let isValid = true;

        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        return isValid;
    }

    // Handle login form submission
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function (event) {
            if (!validateForm(this)) {
                event.preventDefault(); // Prevent form submission if validation fails
                alert('Please fill in all required fields.');
            }
        });
    }

    // Handle profile update form submission
    const profileForm = document.getElementById('profileForm');
    if (profileForm) {
        profileForm.addEventListener('submit', function (event) {
            if (!validateForm(this)) {
                event.preventDefault(); // Prevent form submission if validation fails
                alert('Please fill in all required fields and ensure passwords match.');
            }
        });
    }

    // Function to check if password and confirm password match
    function checkPasswords() {
        const newPassword = document.getElementById('newPassword');
        const confirmNewPassword = document.getElementById('confirmNewPassword');
        if (newPassword && confirmNewPassword) {
            if (newPassword.value !== confirmNewPassword.value) {
                confirmNewPassword.setCustomValidity('Passwords do not match.');
                confirmNewPassword.classList.add('is-invalid');
                return false;
            } else {
                confirmNewPassword.setCustomValidity('');
                confirmNewPassword.classList.remove('is-invalid');
            }
        }
        return true;
    }

    if (profileForm) {
        const newPassword = document.getElementById('newPassword');
        const confirmNewPassword = document.getElementById('confirmNewPassword');
        newPassword && newPassword.addEventListener('change', checkPasswords);
        confirmNewPassword && confirmNewPassword.addEventListener('change', checkPasswords);
    }
});
