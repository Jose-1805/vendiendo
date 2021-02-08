$(function () {
    $('body').on('change',' #almacen_global',function () {
        $('.select_almacen_remision').val($(this).val());
        inicializarMaterialize();
    });

    $('#btn-guardar').click(function () {
        guardar();
    })
    cargarTablaFacturas();
})

function cargarTablaFacturas() {
    var url = $("#base_url").val()+"/migracion-ab/list-remisiones";

    var checked = "";
    var i=1;
    var tabla_remisiones = $('#tabla_remisiones').dataTable({ "destroy": true });
    tabla_remisiones.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_remisiones').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_remisiones = $('#tabla_remisiones').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/migracion-ab/list-remisiones",
            "type": "GET"
        },
        "columns": [
            { "data": "numero", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "fecha", 'className': "text-center" },
            { "data": "cliente", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "estado", 'className': "text-center" },
            { "data": "almacen", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_remisiones.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function guardar() {
    var url = $("#base_url").val()+"/migracion-ab/configurar-remisiones";
    var params = $('#form-lista-remisiones').serialize();

    DialogCargando('Configurando ...');
    $.post(url,params,function(data){
        if(data.success){
            if(data.reload){
                CerrarDialogCargando();
                cargarTablaFacturas();
            }else{
                window.location.href = $("#base_url").val();
            }
        }
    })
}