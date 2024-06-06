//--signup modal--//

var sign_up_modal = document.getElementById("sign-up-modal");
var sign_up_btn = document.getElementById("show-sign-up-modal");
var sign_up_span = document.getElementsByClassName("sign-up-close")[0];

// When the user clicks the button, open the modal 
sign_up_btn.onclick = function () {
    sign_up_modal.style.display = "flex";
    sign_up_modal.classList.remove("deblurring");
    sign_up_modal.classList.add("blurring");
}

// When the user clicks on <span> (x), close the modal
sign_up_span.onclick = function () {
    closeModalWithAnimation(sign_up_modal);
}

//--recovery password modal--//

var recovery_password_modal = document.getElementById("password-recovery-modal");
var recovery_password_btn = document.getElementById("show-password-recovery-modal");
var recovery_password_span = document.getElementsByClassName("password-recovery-close")[0];

// When the user clicks the button, open the modal 
recovery_password_btn.onclick = function () {
    recovery_password_modal.style.display = "flex";
    recovery_password_modal.classList.remove("deblurring");
    recovery_password_modal.classList.add("blurring");
}

// When the user clicks on <span> (x), close the modal
recovery_password_span.onclick = function () {
    closeModalWithAnimation(recovery_password_modal);
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target == sign_up_modal) {
        closeModalWithAnimation(sign_up_modal);
    }
    if (event.target == recovery_password_modal) {
        closeModalWithAnimation(recovery_password_modal);
    }
}

function closeModalWithAnimation(modal) {
    var modalContent = modal.querySelector(".modal-content");
    modalContent.classList.add("disappearing");
    modal.classList.remove("blurring");
    modal.classList.add("deblurring");

    setTimeout(function() {
        modal.style.display = "none";
        modalContent.classList.remove("disappearing");
        modal.classList.remove("deblurring");
    }, 200); // Duración de la animación en milisegundos
}
