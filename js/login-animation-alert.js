function showAlert(type) {
    let icon, title, message, alertClass;

    switch (type) {
        case 'success':
            icon = 'bi bi-patch-check-fill';
            title = 'Success';
            message = 'Success mensaje: todo salió bien';
            alertClass = 'success';
            break;
        case 'warn':
            icon = 'bi bi-exclamation-triangle-fill';
            title = 'Warning';
            message = 'Warn mensaje: parece que algo falló';
            alertClass = 'warn';
            break;
        case 'error':
            icon = 'bi bi-exclamation-circle-fill';
            title = 'Error';
            message = 'Error mensaje: algo salió mal';
            alertClass = 'error';
            break;
        default:
            icon = '';
            title = '';
            message = '';
            alertClass = '';
            break;
    }

    const alertHTML = `
				<div class="card ${alertClass} alert" style="animation-name: slideInFromTop;">
					<h4><i class="${icon}"></i> ${title}</h4>
					<p>${message}</p>
				</div>
			`;

    document.getElementById('alertContainer').innerHTML = alertHTML;

    setTimeout(function () {
        document.querySelector('.alert').style.animationName = 'slideOutToTop';
        setTimeout(function () {
            document.getElementById('alertContainer').innerHTML = '';
        }, 500); // Tiempo igual a la duración de la animación
    }, 2000); // Después de 2 segundos
}