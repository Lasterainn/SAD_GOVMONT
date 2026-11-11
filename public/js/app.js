document.addEventListener('DOMContentLoaded', () => {
    // 1. Fitur Tampilkan Password (Toggle Visibility)
    document.querySelectorAll('.toggle-password').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const targetIds = this.dataset.target.split(',');
            targetIds.forEach(id => {
                const input = document.getElementById(id.trim());
                if (input) {
                    input.type = this.checked ? 'text' : 'password';
                }
            });
        });
    });

    // 2. Validasi Client-side Form Register
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            const nikInput = document.getElementById('nik');
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');

            // Cek NIK 16 digit
            if (nikInput && !/^[0-9]{16}$/.test(nikInput.value.trim())) {
                alert('NIK wajib berupa 16 digit angka.');
                e.preventDefault();
                return;
            }

            // Cek Kecocokan Konfirmasi Password
            if (password && confirmPassword && password.value !== confirmPassword.value) {
                alert('Konfirmasi password tidak cocok dengan password yang dimasukkan!');
                e.preventDefault();
                return;
            }

            // Cek reCAPTCHA sudah dicentang
            if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
                alert('Silakan centang reCAPTCHA terlebih dahulu.');
                e.preventDefault();
                return;
            }
        });
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            if (typeof grecaptcha !== 'undefined' && grecaptcha.getResponse().length === 0) {
                alert('Silakan centang reCAPTCHA terlebih dahulu.');
                e.preventDefault();
            }
        });
    }
});