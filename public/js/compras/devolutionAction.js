var id_devolucion_global = 0;
$(document).ready(function(){
    $('#modal-forma-pago').on('hidden.bs.modal', function () {
        //alert("pailas");
    });
    $("#btn-ver-devoluciones-lista").click(function () {
        $("#div-lista-cc").each(function() {
            displaying = $(this).css("display");
            if(displaying == "block") {
                $(this).fadeOut('slow',function() {
                    $(this).css("display","none");
                });
            } else {
                $(this).fadeIn('slow',function() {
                    $(this).css("display","block");
                });
            }
        });
    });
})
function estadoCuentaXCobrar(id_cuentaXpagar,estado) {

        $("#modal-forma-pago").openModal({
            complete: function() {window.location.reload(true); }}
        );
        var campos_hidden = "<input type='hidden' id='id_cuentaXpagar' value='"+id_cuentaXpagar+"'>" +
            "<input type='hidden' id='estado' value='"+estado+"'>";
        $("#modal-forma-pago").append(campos_hidden);

    id_devolucion_global = id_cuentaXpagar;
}
function PagarCC() {
    var forma_pago = $('input:radio[name=forma_pago]:checked').val();
    //var id_cuentaXpagar = $("#id_cuentaXpagar").val();
    var id_cuentaXpagar = id_devolucion_global;
    var estado = $("#estado").val();
    var url = $("#base_url").val()+"/compra/cobrar/"+id_cuentaXpagar+"/"+estado+"/"+forma_pago;
    var token = $("#general-token").val();
    if (window.confirm("Realmente quiere ejecutar la cobranza de la cuenta?, si ejecuta ejecuta el cobro no podra volver al estado anterior")) {
    $.ajax({
        url: url,
        headers: {'X-CSRF-TOKEN': token},
        type: 'GET',
        dataType: 'json',
        data:{id_cuentaXpagar: id_cuentaXpagar},

        success: function (data) {
            $("#"+id_cuentaXpagar).prop( "disabled", true );
            mostrarConfirmacion("contenedor-confirmacion-comprasIndex",{"dato":[data.response]})
            inicializarMaterialize();
            window.location.reload(true);
        },
        error: function (data,jqXHR) {
            console.log('Error:', data);
            //mostrarErrores("contenedor-errores-productoIndex",JSON.parse(jqXHR.responseText));
            //alert(data.response);
            //window.location.reload(true);
        }

    });
        $("#modal-forma-pago").closeModal();
    }else {
        $("#modal-forma-pago").closeModal();
        if (!$("#" + id_cuentaXpagar).is(':checked')) {
            //alert("Está activado");
            $("#" + id_cuentaXpagar).prop("checked", "checked");
        } else {
            //alert("No está activado");
            $("#" + id_cuentaXpagar).prop("checked", "");
        }
    }
}