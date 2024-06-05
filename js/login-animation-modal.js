//--signup modal--//

var sign_up_modal = document.getElementById("sign-up-modal");
var sign_up_btn = document.getElementById("sign-up-btn");
var sign_up_span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
sign_up_btn.onclick = function () {
    sign_up_modal.style.display = "flex";
}

// When the user clicks on <span> (x), close the modal
sign_up_span.onclick = function () {
    sign_up_modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target == sign_up_modal) {
        sign_up_modal.style.display = "none";
    }
}