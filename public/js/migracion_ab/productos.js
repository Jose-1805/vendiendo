var columnas = null
$(function () {
    $('body').on('change',' #check_bodega_global',function () {
        $('.check_bodega').prop('checked',$(this).prop('checked'));
        if($(this).prop('checked'))$('.params-producto').val('');
        inicializarMaterialize();
    })

    $('body').on('change','.check_bodega',function () {
        if($(this).prop('checked'))
            $($(this).parent().parent().parent().find('.params-producto').val(''));
        inicializarMaterialize();
    })

    $('body').on('keyup','.params-producto',function () {
        $(this).parent().parent().parent().parent().find('.check_bodega').prop('checked',false);
        inicializarMaterialize();
    })


    $('body').on('change','.check_bodega',function () {
        $(this).parent().parent().parent().find('select').val(null);
        inicializarMaterialize();
    })

    $('#btn-guardar').click(function () {
        guardar();
    })
})

function cargarTablaProductos(columns) {
    columnas = columns;
    var url = $("#base_url").val()+"/migracion-ab/list-productos";

    var checked = "";
    var i=1;
    var tabla_productos = $('#tabla_productos').dataTable({ "destroy": true });
    tabla_productos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_productos').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_productos = $('#tabla_productos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/migracion-ab/list-productos",
            "type": "GET"
        },
        "columns": columnas,
        "createdRow": function (row, data, index) {

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_productos.data().length){
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
    var url = $("#base_url").val()+"/migracion-ab/configurar-productos";
    var params = $('#form-lista-productos').serialize();

    DialogCargando('Configurando ...');
    $.post(url,params,function(data){
        if(data.success){
            if(data.reload){
                CerrarDialogCargando();
                cargarTablaProductos(columnas);
            }else{
                window.location.href = $("#base_url").val();
            }
        }
    }).error(function (jqXHR,error,state) {
        mostrarErrores("contenedor-errores-productos", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}