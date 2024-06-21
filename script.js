document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('togglePasswordIcon').addEventListener('click', function() {
        var passwordInput = document.getElementById('password');
        var icon = document.getElementById('togglePassword');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            passwordInput.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    });
});


