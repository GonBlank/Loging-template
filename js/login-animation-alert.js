// Leer cookie
document.addEventListener('DOMContentLoaded', function() {
    // Leer la cookie
    var cookieValue = getCookie('temp_message');
    if (cookieValue) {
        var cookieParams = JSON.parse(decodeURIComponent(cookieValue));
        showAlert(cookieParams.title, cookieParams.message, cookieParams.alertClass);
    }
});

// Función para obtener el valor de una cookie
function getCookie(name) {
    var value = "; " + document.cookie;
    var parts = value.split("; " + name + "=");
    if (parts.length === 2) {
        return parts.pop().split(";").shift();
    }
}

// Función para mostrar la alerta
function showAlert(cookie_title, cookie_mensaje, cookie_type) {
    let icon, alertClass;

    switch (cookie_type) {
        case 'success':
            icon = 'bi bi-patch-check-fill';
            alertClass = 'success';
            break;
        case 'warn':
            icon = 'bi bi-exclamation-triangle-fill';
            alertClass = 'warn';
            break;
        case 'error':
            icon = 'bi bi-exclamation-circle-fill';
            alertClass = 'error';
            break;
        default:
            icon = '';
            alertClass = '';
            break;
    }

    const alertHTML = `
        <div class="card ${alertClass} alert slide-in">
            <h2><i class="${icon}"></i> ${cookie_title}</h2>
            <p>${cookie_mensaje}</p>
        </div>
    `;

    document.getElementById('alertContainer').innerHTML = alertHTML;

    setTimeout(function() {
        var alertElement = document.querySelector('.alert');
        alertElement.classList.remove('slide-in');
        alertElement.classList.add('slide-out');

        setTimeout(function() {
            document.getElementById('alertContainer').innerHTML = '';
        }, 600); // Tiempo igual a la duración de la animación
    }, 4000); // Después de 4 segundos
}
