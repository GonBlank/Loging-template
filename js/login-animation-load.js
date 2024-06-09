document.getElementById('login-form').addEventListener('submit', function () {
    // Ocultar el botón de submit
    document.getElementById('submit-login-btn').classList.add('d-none');
    // Mostrar el botón de carga
    document.getElementById('loading-login-btn').classList.remove('d-none');
});

document.getElementById('signup-form').addEventListener('submit', function () {
    // Ocultar el botón de submit
    document.getElementById('submit-signup-btn').classList.add('d-none');
    // Mostrar el botón de carga
    document.getElementById('loading-signup-btn').classList.remove('d-none');
});



document.getElementById('password-recovery-form').addEventListener('submit', function () {
    // Ocultar el botón de submit
    document.getElementById('submit-password-recovery-btn').classList.add('d-none');
    // Mostrar el botón de carga
    document.getElementById('loading-password-recovery-btn').classList.remove('d-none');
});