$(function(){
    $('#btn-realizar-consignaciones-pagos').click(function () {
        realizarConsignacionesPagos();
    })
})

function cambiarContrasena(){
    DialogCargando("Guardando ...");

    var params = $("#form-cambiar-contrasena").serialize();

    var url = $("#base_url").val() + "/configuracion/cambiar-contrasena";
    $.post(url, params, function (data) {
        if (data.success) {
            window.location.reload();
            /*mostrarConfirmacion("contenedor-confirmacion-cambiar-contrasena",["La contraseña ha sido cambiada con éxito"]);
            $("#password-new").val("");
            $("#password-old").val("");
            $("#password-check").val("");*/
        }
        //$("#contenedor-botones-cambiar-contrasena").removeClass("hide");
        //$("#progress-cambiar-contrasena").addClass("hide");
    }).error(function (jqXHR, error, state) {
        mostrarErrores("contenedor-errores-cambiar-contrasena", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function cerrarApliaccion(){
    var url = $("#base_url").val()+"/configuracion/reiniciar-api-key";
    var params = {_token:$("#general-token").val()};
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        $("#modal-cerrar-aplicacion").closeModal();
        if(data.success){
            alert("La sesión ha sido cerrada en la aplicación móvil.");
        }
        CerrarDialogCargando();
    }).error(function (jqXHR,state,error) {
        $("#modal-cerrar-aplicacion").closeModal();
        alert("Ocurrio un error al cerrar la sesión la aplicación móvil.");
        CerrarDialogCargando();
    })
}

function updatedFacturaAbierta(){
    var url = $("#base_url").val()+"/configuracion/updated-factura-abierta";
    var params = {_token:$("#general-token").val()};
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload();
        }
    }).error(function (jqXHR,state,error) {
        alert("Ocurrio un error al cambiar la funcionalidad de factura abierta.");
        CerrarDialogCargando();
    })
}

function establecerSistemaPuntos(){
    var url = $("#base_url").val()+"/configuracion/establecer-sistema-puntos";
    var params = $("#form-sistema-puntos").serialize();
    DialogCargando("Estableciendo sistema de puntos ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload();
        }
    }).error(function (jqXHR,state,error) {
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-sistema-puntos",JSON.parse(jqXHR.responseText));
    })
}

function cambiarInformacionNegocio(){
    DialogCargando("Guardando ...");

    var params = $("#form-informacion-negocio").serialize();

    var url = $("#base_url").val() + "/configuracion/cambiar-informacion-negocio";
    $.post(url, params, function (data) {
        if (data.success) {
            window.location.reload();
            /*mostrarConfirmacion("contenedor-confirmacion-cambiar-contrasena",["La contraseña ha sido cambiada con éxito"]);
             $("#password-new").val("");
             $("#password-old").val("");
             $("#password-check").val("");*/
        }
        //$("#contenedor-botones-cambiar-contrasena").removeClass("hide");
        //$("#progress-cambiar-contrasena").addClass("hide");
    }).error(function (jqXHR, error, state) {
        mostrarErrores("contenedor-errores-informacion-negocio", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function administrarPanelGraficas(){
    DialogCargando("Guardando ...");
    var params = $("#form-panel-graficas").serialize();

    var url = $("#base_url").val() + "/configuracion/administrar-panel-graficas";
    $.post(url, params, function (data) {
        if (data.success) {
            window.location.reload();
            /*mostrarConfirmacion("contenedor-confirmacion-cambiar-contrasena",["La contraseña ha sido cambiada con éxito"]);
             $("#password-new").val("");
             $("#password-old").val("");
             $("#password-check").val("");*/
        }
        //$("#contenedor-botones-cambiar-contrasena").removeClass("hide");
        //$("#progress-cambiar-contrasena").addClass("hide");
    }).error(function (jqXHR, error, state) {
        mostrarErrores("contenedor-errores-panel-graficas", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function administrarFormasPago(){
    DialogCargando("Guardando ...");
    var params = $("#form-formas-pago").serialize();

    var url = $("#base_url").val() + "/configuracion/administrar-formas-pago";
    $.post(url, params, function (data) {
        if (data.success) {
            window.location.reload();
            /*mostrarConfirmacion("contenedor-confirmacion-cambiar-contrasena",["La contraseña ha sido cambiada con éxito"]);
             $("#password-new").val("");
             $("#password-old").val("");
             $("#password-check").val("");*/
        }
        //$("#contenedor-botones-cambiar-contrasena").removeClass("hide");
        //$("#progress-cambiar-contrasena").addClass("hide");
    }).error(function (jqXHR, error, state) {
        mostrarErrores("contenedor-errores-formas-pago", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function realizarConsignacionesPagos() {
    DialogCargando("Realizando consignaciones ...");
    var params = {_token:$('#general-token').val()};

    var url = $("#base_url").val() + "/configuracion/realizar-consignaciones";
    $.post(url, params, function (data) {
        if (data.success) {
             mostrarConfirmacion("contenedor-confirmacion-formas-pago",["Las consignaciones requeridas han sido realizadas con éxito"]);
             $("#text-consignaciones-pago").remove();
        }
        CerrarDialogCargando();
    }).error(function (jqXHR, error, state) {
        mostrarErrores("contenedor-errores-formas-pago", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}