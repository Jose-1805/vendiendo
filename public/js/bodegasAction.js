var id_select = 0;
var permiso_editar = false;
var permiso_eliminar = false;

function setPermisoEditar(valor){
    permiso_editar = valor;
}
function setPermisoEliminar(valor){
    permiso_eliminar = valor;
}

$(document).ready(function() {
    cargarTablaBodegas();
});

function crear() {
    var params = $("#modal-bodegas #form-bodega").serialize();
    var url = $("#base_url").val()+"/bodega/store";

    DialogCargando("Guardando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-crear-bodegas",JSON.parse(jqXHR.responseText));
    })
}

function editar() {
    var params = $("#modal-editar-bodegas #form-bodega").serialize();
    var url = $("#base_url").val()+"/bodega/update";

    DialogCargando("Guardando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-editar-bodegas",JSON.parse(jqXHR.responseText));
    })
}


function getEdicion(id){
    var url = $("#base_url").val()+"/bodega/editar";
    var params = {id:id,_token:$("#general-token").val()};
    DialogCargando("Cargando ...");
    $.post(url,params,function(data){
        $("#modal-editar-bodegas #contenido-editar-bodegas").html(data);
        $("#modal-editar-bodegas").openModal();
        CerrarDialogCargando();
    }).error(function(jqXHR,error,state){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-editar-bodegas",JSON.parse(jqXHR.responseText));
    })
}

function cargarTablaBodegas() {
    var url = $("#base_url").val()+"/bodega";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_bodegas = $('#tabla_bodegas').dataTable({ "destroy": true });
    tabla_bodegas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_bodegas').on('error.dt', function(e, settings, techNote, message) {
        console.log('An error has been reported by DataTables: ', message);
    })
    tabla_bodegas = $('#tabla_bodegas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/bodega/list-bodegas",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "direccion", 'className': "text-center" },
            { "data": "creador", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar ) {
                $('td', row).eq(3).html('<a onclick="getEdicion(' + data.id + ')"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            }else {
                $('td', row).eq(3).addClass('hide');
            }
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_bodegas.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [3] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}