$(function () {
    $('body').on('change',' #almacen_global',function () {
        $('.select_almacen_usuario').val($(this).val());
        inicializarMaterialize();
    })

    $('#btn-guardar').click(function () {
        guardar();
    })
    cargarTablaUsuarios();
})

function cargarTablaUsuarios() {
    var url = $("#base_url").val()+"/migracion-ab/list-usuarios";

    var checked = "";
    var i=1;
    var tabla_usuarios = $('#tabla_usuarios').dataTable({ "destroy": true });
    tabla_usuarios.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_usuarios').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_usuarios = $('#tabla_usuarios').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/migracion-ab/list-usuarios",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "perfil", 'className': "text-center" },
            { "data": "alias", 'className': "text-center" },
            { "data": "correo", 'className': "text-center" },
            { "data": "telefono", 'className': "text-center" },
            { "data": "almacen", "className": "text-center" },
            { "data": "administrador", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_usuarios.data().length){
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
    var url = $("#base_url").val()+"/migracion-ab/configurar-usuarios";
    var params = $('#form-lista-usuarios').serialize();

    DialogCargando('Configurando ...');
    $.post(url,params,function(data){
        if(data.success){
            if(data.reload){
                CerrarDialogCargando();
                cargarTablaUsuarios();
            }else{
                window.location.href = $("#base_url").val();
            }
        }
    })
}