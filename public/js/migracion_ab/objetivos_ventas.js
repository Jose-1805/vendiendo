$(function () {
    $('body').on('change',' #almacen_global',function () {
        $('.select_almacen_objetivo_venta').val($(this).val());
        inicializarMaterialize();
    });

    $('#btn-guardar').click(function () {
        guardar();
    })
    cargarTablaObjetivosVentas();
})

function cargarTablaObjetivosVentas() {
    var url = $("#base_url").val()+"/migracion-ab/list-objetivos-ventas";

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
            "url": $("#base_url").val()+"/migracion-ab/list-objetivos-ventas",
            "type": "GET"
        },
        "columns": [
            { "data": "valor", 'className': "text-center" },
            { "data": "fecha", 'className': "text-center" },
            { "data": "almacen", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {

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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function guardar() {
    var url = $("#base_url").val()+"/migracion-ab/configurar-objetivos-ventas";
    var params = $('#form-lista-objetivos-ventas').serialize();

    DialogCargando('Configurando ...');
    $.post(url,params,function(data){
        if(data.success){
            if(data.reload){
                CerrarDialogCargando();
                cargarTablaObjetivosVentas();
            }else{
                window.location.href = $("#base_url").val();
            }
        }
    })
}