$(function () {
    $('body').on('change',' #almacen_global',function () {
        $('.select_almacen_costo_fijo').val($(this).val());
        $('.check_bodega').prop('checked',false);
        inicializarMaterialize();
    })

    $('body').on('change','.select_almacen_costo_fijo',function () {
        $(this).parent().parent().parent().find('input[type="checkbox"]').prop('checked',false);
        inicializarMaterialize();
    })

    $('body').on('change','.check_bodega',function () {
        $(this).parent().parent().parent().find('select').val(null);
        inicializarMaterialize();
    })

    $('#btn-guardar').click(function () {
        guardar();
    })
    cargarTablaCostosFijos();
})

function cargarTablaCostosFijos() {
    var url = $("#base_url").val()+"/migracion-ab/list-costos-fijos";

    var checked = "";
    var i=1;
    var tabla_costos_fijos = $('#tabla_costos_fijos').dataTable({ "destroy": true });
    tabla_costos_fijos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_costos_fijos').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_costos_fijos = $('#tabla_costos_fijos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/migracion-ab/list-costos-fijos",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "estado", 'className': "text-center" },
            { "data": "almacen", "className": "text-center" },
            { "data": "bodega", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_costos_fijos.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function guardar() {
    var url = $("#base_url").val()+"/migracion-ab/configurar-costos-fijos";
    var params = $('#form-lista-costos-fijos').serialize();

    DialogCargando('Configurando ...');
    $.post(url,params,function(data){
        if(data.success){
            if(data.reload){
                CerrarDialogCargando();
                cargarTablaCostosFijos();
            }else{
                window.location.href = $("#base_url").val();
            }
        }
    })
}