function cambiarEstado(estado,pedido){
    if(confirm("¿Esta seguro de cambiar el estado de este producto?")) {
        var url = $("#base_url").val() + "/pedidos-proveedor/update-estado";
        var params = {_token: $("#general-token").val(), id: pedido, estado: estado};
        DialogCargando("Cambiando estado ...");
        $.post(url, params, function (data) {
            if (data.success) {
                moveTop();
                window.location.reload();
            }
        }).error(function (jqXHR, state, error) {
            mostrarErrores("contenedor-errores-pedidos", JSON.parse(jqXHR.responseText));
            moveTop();
            CerrarDialogCargando();
        })
    }
}

function showDetallePedido(id){
    var url = $("#base_url").val()+"/pedidos-proveedor/detalle";
    var params = {_token:$("#general-token").val(),"id":id};

    DialogCargando("Cargando ...");
    $.post(url,params,function (data) {
        $("#contenido-detalle").html(data);
        $("#modal-detalle").openModal();
        CerrarDialogCargando();
    });
}