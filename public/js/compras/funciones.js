
$(document).ready(function () {
    $('body').on('keyup','.cantidad-numerico',function (){
        this.value = (this.value + '').replace(/[^0-9]/g, '');
    });
});

function openFormDevolution(id_producto,producto_historial_id,cantidad,unidad,nombre_producto,id_compra,tipo_compra,valor_proveedor,proveedor_id,id_producto_historial) {

    if (tipo_compra=="MateriaPrima")tipo_compra = "Materia Prima";
    $("#titulo-devolucion").html("Devolución "+tipo_compra);
    if (tipo_compra=="Materia Prima")tipo_compra = "MateriaPrima";

    $("#btn-action-form-devolucion").removeClass('hide');

    var html ="<p id='mensaje-info-devolucion' class='punteado' style='background-color: #e8eaf6; color: #1a237e;font: 9pt Verdana, Geneva, Arial, Helvetica, sans-serif;'>Actualmente tienes "+cantidad+" "+ unidad+" de "+nombre_producto+"; a continuación ingresa la cantidad que deseas devolver y el motivo de la devolución</p>" +
        " <div class='input-field col s6 '><input type='hidden' name='producto_id' id='producto_id' value='"+id_producto+"'><input type='hidden' name='producto_historial_id' id='producto_historial_id' value='"+producto_historial_id+"'><input type='hidden' name='compra_id' id='compra_id' value='"+id_compra+"'>" +
        "<input type='hidden' name='tipo_compra' id='tipo_compra' value='"+tipo_compra+"'><input type='hidden' name='valor_proveedor' id='valor_proveedor' value='"+valor_proveedor+"'>" +
        "<input name='cantidad' type='text' id='cantidad' class='cantidad-numerico' onblur='comparaCantidades(this.value,"+cantidad+")' required/><label for='cantidad_devolucion'>Cantidad a devolver</label>" +
        "</div><div class='input-field col s6'>"+
        "<input name='motivo' type='text' id='motivo' maxlength='100' required/><label for='motivo'>Motivo de la devolucion</label>" +
        "</div><div id='mensaje-confirmacion-devolucion'><input type='hidden' name='proveedor_id' id='proveedor_id' value='"+proveedor_id+"'><input type='hidden' name='producto_historial_id' id='producto_historial_id' value='"+id_producto_historial+"'></div>";

    $("#contenido-detalle-compra").html(html);
}
function comparaCantidades(cantidad_new,cantidad_old) {
    if (cantidad_new > cantidad_old){
        //$("#contenido-detalle-compra").append("<p class='contenedor-errores'>La cantidad a devolver no puede superar a la cantidad de la compra</p>");
        $("#cantidad").val("");

        $("#mensaje-confirmacion-devolucion").show(1000,function() {
            $("#mensaje-confirmacion-devolucion").addClass('contenedor-errores');
            $("#mensaje-confirmacion-devolucion").html("La cantidad a devolver no puede superar a la cantidad de la compra");
        });
        setTimeout(function() {
            $("#mensaje-confirmacion-devolucion").fadeOut(1500);
            $("#mensaje-confirmacion-devolucion").html("");
        },3000);
    }
}
function ejecutarDevolucion() {
    if (window.confirm("Si realiza la devolucion de este elemento, la existencia del mismo se verá afectada en la base de datos")) {

        inicializarMaterialize();

        $("#devolucion-form").addClass("hide");
        $("#progress-action-form-devolucion").removeClass("hide");
        $("#btn-action-form-devolucion").addClass('hide');

        var url = $('#devolucion-form').attr("action");
        var parametros = new FormData(document.getElementById("devolucion-form"));
        $.ajax({
            url: url,
            type: "POST",
            dataType: "html",
            data: parametros,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            if (data == "Compra-eliminada") {
                window.location.href = $("#base_url").val() + "/compra";
            } else {
                $("#contenedor-detalle-compra").html(data);
                $("#devolucion-form").removeClass("hide");
                $("#progress-action-form-devolucion").addClass("hide");
                $("#modal-detalle-compra").closeModal();
                lanzarToast("La devolución se ejecutó manera exitosa","Confirmación",8000);
                inicializarMaterialize();
            }
        }).error(function (jqXHR) {
            var html = "";
            $.each(JSON.parse(jqXHR.responseText), function (key, value) {
                html += "<p>" + value + "</p>";
            })

            $("#devolucion-form").removeClass("hide");
            $("#progress-action-form-devolucion").addClass("hide");
            $("#btn-action-form-devolucion").removeClass("hide");

            $("#mensaje-confirmacion-devolucion").show(1000, function () {
                $("#mensaje-confirmacion-devolucion").addClass('contenedor-errores');
                $("#mensaje-confirmacion-devolucion").html(html);
                //html="";
                //$("#mensaje-confirmacion-devolucion").html("D' oh!. Acaba de ocurir un problema");

            });
            setTimeout(function () {
                $("#mensaje-confirmacion-devolucion").fadeOut(1500);
                $("#mensaje-confirmacion-devolucion").html("");
                inicializarMaterialize();
            }, 3000);
        })
    }
}
function listarDevolucionesCompra(id_compra) {
    $("#modal-lista-devoluciones-compra").openModal(
        {
            complete: function() { window.location.reload(); }
        }
    );
    var url = $("#base_url").val()+"/compra/detalle-devolucion/"+id_compra;

    $.get(url,function (response) {
        $("#listado-devoluciones").html(response);
    });
}
function pagarDevolucion() {
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
                $("#" + id_cuentaXpagar).attr('disabled',true);

                setTimeout(function() {
                    $("#contenedor-confirmacion-comprasIndex").fadeOut(1500);
                },3000);
            }
        });

        $("#modal-forma-pago-compra").closeModal();
    }else {
        $("#modal-forma-pago-compra").closeModal();
        if (!$("#" + id_cuentaXpagar).is(':checked')) {
            //alert("Está activado");
            $("#" + id_cuentaXpagar).prop("checked", "checked");
        } else {
            //alert("No está activado");
            $("#" + id_cuentaXpagar).prop("checked", "");
        }
    }
}
function cambioEstadoCuentaXCobrar(id_cuentaXpagar,estado) {

    $("#modal-forma-pago-compra").openModal({
        complete: function() {window.location.reload(true); }}
    );
    var campos_hidden = "<input type='hidden' id='id_cuentaXpagar' value='"+id_cuentaXpagar+"'>" +
        "<input type='hidden' id='estado' value='"+estado+"'>";
    $("#modal-forma-pago-compra").append(campos_hidden);

    id_devolucion_global = id_cuentaXpagar;
}
function cancelarPagoDevolucion() {
    var id_cuentaXpagar = id_devolucion_global;
    $("#modal-forma-pago-compra").closeModal();
    if (!$("#" + id_cuentaXpagar).is(':checked')) {
        //alert("Está activado");
        $("#" + id_cuentaXpagar).prop("checked", "checked");
    } else {
        //alert("No está activado");
        $("#" + id_cuentaXpagar).prop("checked", "");
    }
}



