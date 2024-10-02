// ===== nuevos metodos estandar, reutilizables y no puntuales para cada caso =====

// === LEEME
// este es el color hexadecima de los textos de los alert ( #d1d1d1 ), si utilizas 
// un alert y sale muy oscuro el texto modificas sweetalert1_p.css y le pones ese color

// context => info, warning, error, success
// positions => bottom-right, bottom-left, top-left, top-right, top-full-width, bottom-full-width

function toastRight(tipo, mensaje){
    toastr.options.timeOut = "false";
    toastr.options.closeButton = true;

    toastr.options = {
        "timeOut": "4000",
    };

    toastr.remove();
    toastr[tipo](mensaje, '', {
        positionClass: 'toast-top-right'
    });
}

function toastRightBottom(tipo, mensaje){
    toastr.options.timeOut = "false";
    toastr.options.closeButton = true;

    toastr.options = {
        "timeOut": "3000",
    };

    toastr.remove();
    toastr[tipo](mensaje, '', {
        positionClass: 'toast-bottom-right'
    });
}

// confirm, despliega un callback
function alertClickCallback(titulo, mensaje, tipo, text_confir = 'Confirmar', text_cancel = 'Cancelar', callback){
    if( text_cancel == 'no_cancel' ){
        var mostrar_cancel = false;
    }else{
        var mostrar_cancel = true;
    }

    swal.fire({
        title: titulo,
        text: mensaje,
        type: tipo,
        showCancelButton: mostrar_cancel,
        cancelButtonText: text_cancel,
        confirmButtonText: text_confir
    }).then(function(result) {
        if (result.value) {
            callback();
        }
    });
}

//alert Message
function alertMessage(titulo, mensaje, tipo, confirm = true){
    swal.fire({
        title: titulo,
        text: mensaje,
        type: tipo,
        confirmButtonText: 'Ok',
        showCancelButton: false,
        showConfirmButton: confirm,
    });
}

function cerrarModal(){
    $('.close').click();
}

// ===== end funciones finales =====
