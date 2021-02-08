$(function(){
    $("#no_acepta").click(function () {
        noAceptar();
    })
    $("#acepta").click(function () {
        aceptar();
    })
})

function noAceptar(){
    DialogCargando("Cargando ...");
    window.location.href = $("#base_url").val()+"/auth/logout";
}

function aceptar() {
    var url = $("#base_url").val()+"/home/aceptar-terminos-condiciones";
    DialogCargando("Cargando ...");
    $.post(url,{_tonen:$("#general-token").val()},function (data) {
        if(data.success){
            window.location.reload(true);
        }else if(data.mensaje){
            CerrarDialogCargando();
            lanzarToast(data.mensaje,"Mensaje",8000);
        }
    })
}