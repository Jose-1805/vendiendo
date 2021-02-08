var id_select = 0;
var permiso_editar = false;
var permiso_eliminar = false;

function setPermisoEditar(valor){
    permiso_editar = valor;
}
function setPermisoEliminar(valor){
    permiso_eliminar = valor;
}

$(document).ready(function(){
    cargarTablaGastos();
    
    $(".fecha").change(function(){
        cargarTablaGastos();
    })

    $("#btn-agregar-a-caja").click(function(){
        var url = $("#base_url").val()+"/gastos/form-agregar-efectivo-caja";
        var params = {_token:$("#general-token").val()};

        DialogCargando("Cargando ...");

        $.post(url,params,function(data){
            $("#agregar_efectivo_caja").html(data);
            $("#modal-agregar-a-caja").openModal();
            CerrarDialogCargando();
        })
    })
});

function crear() {
    var params = $("#modal-gastos-diario #form-gasto").serialize();
    var url = $("#base_url").val()+"/gastos/store";

    DialogCargando("Guardando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-crear-gastos-diarios",JSON.parse(jqXHR.responseText));
    })
}

function editar() {
    var params = $("#modal-editar-gastos-diarios #form-gasto").serialize();
    var url = $("#base_url").val()+"/gastos/update";

    DialogCargando("Guardando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-editar-gastos-diarios",JSON.parse(jqXHR.responseText));
    })
}


function eliminar() {
    var params = {id:id_select,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/gastos/destroy";

    DialogCargando("Eliminando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        $("#modal-eliminar-gasto").closeModal();
        mostrarErrores("contenedor-errores-gastos-diarios",JSON.parse(jqXHR.responseText));
    })
}

function getEdicion(id){
    var url = $("#base_url").val()+"/gastos/edicion";
    var params = {id:id,_token:$("#general-token").val()};
    DialogCargando("Cargando ...");
    $.post(url,params,function(data){
        $("#modal-editar-gastos-diarios #contenido-editar-gastos-diarios").html(data);
        $("#modal-editar-gastos-diarios").openModal();
        CerrarDialogCargando();
    }).error(function(jqXHR,error,state){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-editar-gastos-diarios",JSON.parse(jqXHR.responseText));
    })
}

function cargarTablaGastos() {
    var url = $("#base_url").val()+"/gastos";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var params = "fecha_inicio="+$("#fecha_inicio").val()+"&fecha_fin="+$("#fecha_fin").val();
    var i=1;
    var tabla_gastos = $('#tabla_gastos').dataTable({ "destroy": true });
    tabla_gastos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_gastos').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_gastos = $('#tabla_gastos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/gastos/list-gastos?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": "valor", 'className': "text-center" },
            { "data": "descripcion", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar ) {
                if(data.editar)
                $('td', row).eq(3).html('<a onclick="getEdicion(' + data.id + ')"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            }else {
                $('td', row).eq(3).addClass('hide');
            }

            if(permiso_eliminar)
                $('td', row).eq(4).html('<a href="#modal-eliminar-gasto" class="modal-trigger" onclick="javascript: id_select = '+data.id+'"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(4).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_gastos.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [3,4] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function agregarEfectivoCaja(){
    var params = {_token:$("#general-token").val(),cantidad:$("#cantidad").val()}
    var url = $("#base_url").val()+"/gastos/agregar-efectivo-caja";

    DialogCargando("Guardando ...");

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-agregar-a-caja",JSON.parse(jqXHR.responseText));
    })
}