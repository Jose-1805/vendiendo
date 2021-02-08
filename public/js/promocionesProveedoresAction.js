$(function(){
    $(".check-estado-promocion").change(function(event){
        var estado = "";
        var elemento = $(this);
        var id = $(this).parent().parent().parent().parent().data("promocion");
        if($(elemento).prop("checked")){
            $(elemento).prop("checked",!$(elemento).prop("checked"));
            estado = "activo";
        }else{
            $(elemento).prop("checked",!$(elemento).prop("checked"));
            estado = "inactivo";
        }

        if(confirm("¿Esta seguro de cambiar el estado de esta promoción?")){
            var url = $("#base_url").val()+"/promociones-proveedor/update-estado";
            var params = {_token:$("#general-token").val(),estado:estado,id:id};

            DialogCargando("Cambiando estado ...");
            $.post(url,params,function (data) {
                if(data.success){
                    mostrarConfirmacion("contenedor-confirmacion-promociones",["El estado de la promoción ha sido cambiado con exito"]);
                    $(elemento).prop("checked",!$(elemento).prop("checked"));
                }
                CerrarDialogCargando();
            }).error(function(jqXHR,state,error){
                mostrarErrores("contenedor-errores-promociones",JSON.parse(jqXHR.responseText));
                CerrarDialogCargando();
            })
        }
    })

    $("#producto").change(function(){
        $("#valor_actual").val(productosAllPrecios[$(this).val()]);
        $("#valor_con_descuento").val(productosAllPrecios[$(this).val()]);
    })
})

function guardarPromocion(){
    var url = $("#base_url").val()+"/promociones-proveedor/store";
    var params = $("#modal-store-promocion #form-promocion").serialize();

    DialogCargando("Guardando ...");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }
    }).error(function(jqXHR,state,error){
        mostrarErrores("contenedor-errores-store-promocion",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}