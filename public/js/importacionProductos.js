var id_procesar = null;
var id_rechazar = null;
var item_select = null;
$(function(){
    $("#archivo").change(function () {
        var input = document.getElementById("archivo");
        if (input.files && input.files[0]) {
            var data_file = input.files[0].name.split(".");
            var ext = data_file[data_file.length - 1];

            if(ext != "xls" && ext != "xlsx") {
                $(this).val("");
                mostrarErrores("contenedor-errores-modal-importacion",["Seleccione únicamente archivos con extensión .xls o .xlsx"]);
            }else{
                ocultarErrores("contenedor-errores-modal-importacion");
            }
        }
    })

    $("#select-categoria").change(function(){
        $(".select-categoria").val($(this).val());
        $(".select-categoria select").val($(this).val())

    });
    $("#select-unidad").change(function(){
        $(".select-unidad").val($(this).val());
    });
    $("#select-proveedor").change(function(){
        $(".select-proveedor").val($(this).val());
    });
})

function openImportar(){
    $("#modal-importar-productos").openModal({ dismissible: false})
}

function importar(){
    var input = document.getElementById("archivo");
    if (input.files && input.files[0]) {
        DialogCargando("Importando datos ...");
        var url = $("#base_url").val() + "/productos/store-importacion";
        var params = new FormData(document.getElementById("form-importar"));

        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            data = JSON.parse(data);
            if (data.success) {
                $("html, body").animate({
                    scrollTop: 50
                }, 600);
                window.location.reload();
            }
            CerrarDialogCargando();
        }).error(function (jqXHR, error, estado) {
            CerrarDialogCargando();
            $("html, body").animate({scrollTop: "0px"}, 600);
            //alert('error');
            mostrarErrores("contenedor-errores-modal-importacion", JSON.parse(jqXHR.responseText));
        })
    }else{
        mostrarErrores("contenedor-errores-modal-importacion",["Seleccione un archivo"]);
    }
}

function verMas(id){
    $("#load-ver-mas").removeClass("hide");
    $("#contenido-ver-mas").addClass("hide");
    $("#contenido-ver-mas").html("");

    $("#modal-ver-mas").openModal();
    var url = $("#base_url").val()+"/productos/info-importacion";
    var params = {_token:$("#general-token").val(),id:id};
    $.post(url,params,function(data){
        $("#contenido-ver-mas").html(data);
        $("#load-ver-mas").addClass("hide");
        $("#contenido-ver-mas").removeClass("hide");
    })
}

function procesarProducto(){
    var mensajes = [];
    var error = false;
    var categoria = $("#select-categoria-"+id_procesar).val();
    var unidad = $("#select-unidad-"+id_procesar).val();
    var proveedor = $("#select-proveedor-"+id_procesar).val();
    if(!categoria){
        error = true;
        mensajes.push("Para procesar un producto seleccione una categoria");
    }

    if(!unidad){
        error = true;
        mensajes.push("Para procesar un producto seleccione una unidad");
    }

    if(!proveedor){
        error = true;
        mensajes.push("Para procesar un producto seleccione un proveedor");
    }

    if(error) {
        $("body,html").animate(
            {scrollTop:80}, '500'
        );
        $("#modal-confirmar-procesar").closeModal();
        mostrarErrores("contenedor-errores-importacion-productos", mensajes);
    }else{
        $("#modal-confirmar-procesar").closeModal();
        DialogCargando("Procesando ...");
        var params = {_token:$("#general-token").val(),id:id_procesar,categoria:categoria,unidad:unidad,proveedor:proveedor};
        var url = $("#base_url").val()+"/productos/procesar-importacion";
        id_procesar = null;
        $.post(url,params,function(data){
            if(data.success){
                $(item_select).parent().parent().parent().remove();
                mostrarConfirmacion("contenedor-confirmacion-importacion-productos",["El producto ha sido procesado con éxito"]);
                if($("#tabla-importacion-productos tbody tr").length == 0){
                    $("#tabla-importacion-productos thead tr").eq(0).remove();
                    $("#tabla-importacion-productos tbody").html('<tr><td colspan="9" class="center-align">No existen productos importados sin revisar.</td></tr>');
                }
                CerrarDialogCargando();
            }
        }).error(function(jqXHR,state,error){
            CerrarDialogCargando();
            mostrarErrores("contenedor-errores-importacion-productos", JSON.parse(jqXHR.responseText));
        });

    }
}

function procesarTodo(){
    var mensajes = [];
    var error = false;
    var categoria = $("#select-categoria").val();
    var unidad = $("#select-unidad").val();
    var proveedor = $("#select-proveedor").val();
    if(!categoria){
        error = true;
        mensajes.push("Para procesar todos los productos seleccione una categoria");
    }

    if(!unidad){
        error = true;
        mensajes.push("Para procesar todos los productos seleccione una unidad");
    }

    if(!proveedor){
        error = true;
        mensajes.push("Para procesar todos los productos seleccione un proveedor");
    }

    if(error) {
        $("body,html").animate(
            {scrollTop:80}, '500'
        );
        $("#modal-confirmar-procesar-todo").closeModal();
        mostrarErrores("contenedor-errores-importacion-productos", mensajes);
    }else{
        $("#modal-confirmar-procesar-todo").closeModal();
        DialogCargando("Procesando ...");
        var params = {_token:$("#general-token").val(),categoria:categoria,unidad:unidad,proveedor:proveedor};
        var url = $("#base_url").val()+"/productos/procesar-importacion-todo";
        $.post(url,params,function(data){
            if(data.success){
                window.location.reload();
            }
        }).error(function(jqXHR,state,error){
            CerrarDialogCargando();
            mostrarErrores("contenedor-errores-importacion-productos", JSON.parse(jqXHR.responseText));
        });

    }
}

function rechazarProducto() {
    $("#modal-confirmar-rechazar").closeModal();
    DialogCargando("Rechazando ...");
    var params = {_token:$("#general-token").val(),id:id_rechazar};
    var url = $("#base_url").val()+"/productos/rechazar-importacion";
    id_rechazar = null;
    $.post(url,params,function(data){
        if(data.success){
            $(item_select).parent().parent().parent().remove();
            mostrarConfirmacion("contenedor-confirmacion-importacion-productos",["El producto ha sido rechazado con éxito"]);
            if($("#tabla-importacion-productos tbody tr").length == 0){
                $("#tabla-importacion-productos thead tr").eq(0).remove();
                $("#tabla-importacion-productos tbody").html('<tr><td colspan="9" class="center-align">No existen productos importados sin revisar.</td></tr>');
            }
            CerrarDialogCargando();
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-importacion-productos", JSON.parse(jqXHR.responseText));
    });
}
