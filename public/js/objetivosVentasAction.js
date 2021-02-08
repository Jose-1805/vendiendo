var bodegas = false;
$(function(){
    $("#btn-nuevo-objetivo-venta").click(function () {
        $("#load-info-action-objetivo-venta").removeClass("hide");
        $("#contenido-accion-objetivo-venta").html("");
        $("#modal-accion-objetivo-venta .modal-footer").addClass("hide");
        $("#modal-accion-objetivo-venta").openModal();

        var url = $("#base_url").val()+"/objetivos-ventas/show-store";
        var params = {_token:$("#general-token").val()};
        $.post(url,params,function (data) {
            $("#contenido-accion-objetivo-venta").html(data);

            $("#load-info-action-objetivo-venta").addClass("hide");
            $("#modal-accion-objetivo-venta .modal-footer").removeClass("hide");
            inicializarMaterialize();
        })
        nuevo = 1;
    })

    $("body,html").on("keyup","#form-action-objetivo-venta",function(event){
        if(event.keyCode == 13){
            nuevo = 1;
            action();
        }
    })

    $("#guardar-objetivo-venta").click(function () {
        nuevo = 1;
        action();
    })

    cargarTablaObjetivosVentas();
})

function action() {
    if(nuevo == 1) {
        var url = $("#form-action-objetivo-venta").attr("action");
        var params = $("#form-action-objetivo-venta").serialize();
        $("#contenedor-botones-accion-objetivo-venta").addClass("hide");
        $("#progres-accion-objetivo-venta").removeClass("hide");

        $.post(url, params, function (data) {
            if (data.success) {
                window.location.reload();
            }
        }).error(function (jqXHR, status, error) {
            $("#contenedor-botones-accion-objetivo-venta").removeClass("hide");
            $("#progres-accion-objetivo-venta").addClass("hide");
            mostrarErrores("contenedor-errores-action-objetivo-venta", JSON.parse(jqXHR.responseText));
        })

        nuevo = 2;
    }

}

function edit(id) {
    $("#load-info-action-objetivo-venta").removeClass("hide");
    $("#contenido-accion-objetivo-venta").html("");
    $("#modal-accion-objetivo-venta .modal-footer").addClass("hide");
    $("#modal-accion-objetivo-venta").openModal();

    var url = $("#base_url").val()+"/objetivos-ventas/show-update";
    var params = {_token:$("#general-token").val(),id:id};
    $.post(url,params,function (data) {
        $("#contenido-accion-objetivo-venta").html(data);

        $("#load-info-action-objetivo-venta").addClass("hide");
        $("#modal-accion-objetivo-venta .modal-footer").removeClass("hide");
        inicializarMaterialize();
    }).error(function (jqXHR,state,error) {
        $("#modal-accion-objetivo-venta").closeModal();
        mostrarErrores("contenedor-errores-objetivos-ventas",JSON.parse(jqXHR.responseText));
    })
}

function cargarTablaObjetivosVentas() {
    var url = $("#base_url").val()+"/objetivos-ventas";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_objetivos_ventas = $('#tabla_objetivos_ventas').dataTable({ "destroy": true });
    tabla_objetivos_ventas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_objetivos_ventas').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_objetivos_ventas = $('#tabla_objetivos_ventas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/objetivos-ventas/list-objetivos-ventas",
            "type": "GET"
        },
        "columns": [
            { "data": "valor", 'className': "text-center" },
            { "data": "fecha", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            var aux = 2;
            if(data.almacen){
                $('td', row).eq(aux).html(data.almacen);
                aux++;
            }else{
                $('td', row).eq(aux).addClass('hide');
            }
            if(data.editar == 1) {
                $('td', row).eq(aux).html('<a href="#" onclick="edit(' + data.id + ')"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
                aux++;
                $('td', row).eq(aux).html('<a href="#modal-eliminar-objetivo" class="modal-trigger" onclick="javascript: id_select = ' + data.id + '"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a>');
            }
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_objetivos_ventas.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function eliminarObjetivo() {
    if(id_select != null){
        var  parans = {id:id_select,_token:$("#general-token").val()};
        DialogCargando("Eliminando ...");
        var url = $("#base_url").val()+"/objetivos-ventas/destroy";
        $.post(url,parans,function (data) {
            if(data.success){
                window.location.reload();
            }
        }).error(function (jqXHR,state,error) {
            CerrarDialogCargando();
            mostrarErrores("contenedor-errores-eliminar-objetivo",JSON.parse(jqXHR.responseText));
        })
    }
}