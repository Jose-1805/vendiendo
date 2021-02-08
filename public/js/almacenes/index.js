var permiso_editar = false;
$(document).ready(function(){
    cargarTablaBodegas();
});

function setPermisoEditar(permiso) {
    permiso_editar = permiso;
}

function cargarTablaBodegas() {
    var url = $("#base_url").val()+"/almacen";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_almacenes = $('#tabla_almacenes').dataTable({ "destroy": true });
    tabla_almacenes.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_almacenes').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_almacenes = $('#tabla_almacenes').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/almacen/list-almacenes",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "direccion", 'className': "text-center" },
            { "data": "telefono", 'className': "text-center" },
            { "data": "latitud", 'className': "text-center" },
            { "data": "longitud", 'className': "text-center" },
            { "data": "administrador", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar ) {
                $('td', row).eq(6).html('<a href="'+$('#base_url').val()+'/almacen/editar/'+data.id+'"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            }else {
                $('td', row).eq(6).addClass('hide');
            }

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_almacenes.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [6] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}