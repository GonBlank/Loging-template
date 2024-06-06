//--signup modal--//

var sign_up_modal = document.getElementById("sign-up-modal");
var sign_up_btn = document.getElementById("show-sign-up-modal");
var sign_up_span = document.getElementsByClassName("sign-up-close")[0];

// When the user clicks the button, open the modal 
sign_up_btn.onclick = function () {
    sign_up_modal.style.display = "flex";
}

// When the user clicks on <span> (x), close the modal
sign_up_span.onclick = function () {
    closeModalWithAnimation();
    //sign_up_modal.style.display = "none";
    
}

//--recovery password modal--//

var recovery_password_modal = document.getElementById("password-recovery-modal");
var recovery_password_btn = document.getElementById("show-password-recovery-modal");
var recovery_password_span = document.getElementsByClassName("password-recovery-close")[0];

// When the user clicks the button, open the modal 
recovery_password_btn.onclick = function () {
    recovery_password_modal.style.display = "flex";
}

// When the user clicks on <span> (x), close the modal
recovery_password_span.onclick = function () {
    recovery_password_modal.style.display = "none";
}


// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target == sign_up_modal) {
        closeModalWithAnimation()
        //sign_up_modal.style.display = "none";
    }
    if (event.target == recovery_password_modal) {
        recovery_password_modal.style.display = "none";
    }
    
}



function closeModalWithAnimation() {
    var modalContent = document.querySelector(".modal-content");
    modalContent.classList.add("disappearing");
    setTimeout(function() {
        sign_up_modal.style.display = "none";
        modalContent.classList.remove("disappearing");
    }, 200); // Duración de la animación en milisegundos
}