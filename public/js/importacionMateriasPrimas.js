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
    $("#select-unidad").change(function(){
        $(".select-unidad").val($(this).val());
    });
    $("#select-proveedor").change(function(){
        $(".select-proveedor").val($(this).val());
    });
})

function openImportar(){
    $("#modal-importar-materias-primas").openModal({ dismissible: false})
}

function importar(){
    var input = document.getElementById("archivo");
    if (input.files && input.files[0]) {
        DialogCargando("Importando datos ...");
        var url = $("#base_url").val() + "/materia-prima/store-importacion";
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

function procesarMateriaPrima(){
    var mensajes = [];
    var error = false;
    var unidad = $("#select-unidad-"+id_procesar).val();
    var proveedor = $("#select-proveedor-"+id_procesar).val();

    if(!unidad){
        error = true;
        mensajes.push("Para procesar una materia prima seleccione una unidad");
    }

    if(!proveedor){
        error = true;
        mensajes.push("Para procesar una materia prima seleccione un proveedor");
    }

    if(error) {
        $("body,html").animate(
            {scrollTop:80}, '500'
        );
        $("#modal-confirmar-procesar").closeModal();
        mostrarErrores("contenedor-errores-importacion-materias-primas", mensajes);
    }else{
        $("#modal-confirmar-procesar").closeModal();
        DialogCargando("Procesando ...");
        var params = {_token:$("#general-token").val(),id:id_procesar,unidad:unidad,proveedor:proveedor};
        var url = $("#base_url").val()+"/materia-prima/procesar-importacion";
        id_procesar = null;
        $.post(url,params,function(data){
            if(data.success){
                $(item_select).parent().parent().parent().remove();
                mostrarConfirmacion("contenedor-confirmacion-importacion-materias-primas",["La materia prima ha sido procesada con éxito"]);
                if($("#tabla-importacion-materias-primas tbody tr").length == 0){
                    $("#tabla-importacion-materias-primas thead tr").eq(0).remove();
                    $("#tabla-importacion-materias-primas tbody").html('<tr><td colspan="9" class="center-align">No existen materias primas importados sin revisar.</td></tr>');
                }
                CerrarDialogCargando();
            }
        }).error(function(jqXHR,state,error){
            CerrarDialogCargando();
            mostrarErrores("contenedor-errores-importacion-materias-primas", JSON.parse(jqXHR.responseText));
        });

    }
}

function procesarTodo(){
    var mensajes = [];
    var error = false;
    var unidad = $("#select-unidad").val();
    var proveedor = $("#select-proveedor").val();

    if(!unidad){
        error = true;
        mensajes.push("Para procesar todas las materias primas seleccione una unidad");
    }

    if(!proveedor){
        error = true;
        mensajes.push("Para procesar todas las materias primas seleccione un proveedor");
    }

    if(error) {
        $("body,html").animate(
            {scrollTop:80}, '500'
        );
        $("#modal-confirmar-procesar-todo").closeModal();
        mostrarErrores("contenedor-errores-importacion-materias-primas", mensajes);
    }else{
        $("#modal-confirmar-procesar-todo").closeModal();
        DialogCargando("Procesando ...");
        var params = {_token:$("#general-token").val(),unidad:unidad,proveedor:proveedor};
        var url = $("#base_url").val()+"/materia-prima/procesar-importacion-todo";
        $.post(url,params,function(data){
            if(data.success){
                window.location.reload();
            }
        }).error(function(jqXHR,state,error){
            CerrarDialogCargando();
            mostrarErrores("contenedor-errores-importacion-materias-primas", JSON.parse(jqXHR.responseText));
        });

    }
}

function rechazarMateriaPrima() {
    $("#modal-confirmar-rechazar").closeModal();
    DialogCargando("Rechazando ...");
    var params = {_token:$("#general-token").val(),id:id_rechazar};
    var url = $("#base_url").val()+"/materia-prima/rechazar-importacion";
    id_rechazar = null;
    $.post(url,params,function(data){
        if(data.success){
            $(item_select).parent().parent().parent().remove();
            mostrarConfirmacion("contenedor-confirmacion-importacion-materias-primas",["La materia prima ha sido rechazada con éxito"]);
            if($("#tabla-importacion-materias-primas tbody tr").length == 0){
                $("#tabla-importacion-materias-primas thead tr").eq(0).remove();
                $("#tabla-importacion-materias-primas tbody").html('<tr><td colspan="9" class="center-align">No existen materias primas importadas sin revisar.</td></tr>');
            }
            CerrarDialogCargando();
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-importacion-materias-primas", JSON.parse(jqXHR.responseText));
    });
}
