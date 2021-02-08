var id_select = 0;
var permiso_consignar = false;
var permiso_eliminar = false;
$(function(){

    cargarTablaCuentasBancarias();
})


function limpiarForm(contenedor){
    $(contenedor+" #form-cuenta-bancaria #titular").val("");
    $(contenedor+" #form-cuenta-bancaria #saldo").val("");
    $(contenedor+" #form-cuenta-bancaria #banco option:eq(0)").attr("selected","selected");
}

function cargarTablaCuentasBancarias() {
    var url = $("#base_url").val()+"/cuenta-bancaria";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_cuentas_bancarias = $('#tabla_cuentas_bancarias').dataTable({ "destroy": true });
    tabla_cuentas_bancarias.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_cuentas_bancarias').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_cuentas_bancarias = $('#tabla_cuentas_bancarias').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/cuenta-bancaria/list-cuenta-bancaria",
            "type": "GET"
        },
        "columns": [
            { "data": "banco", 'className': "text-center" },
            { "data": "titular", 'className': "text-center" },
            { "data": "numero", 'className': "text-center" },
            { "data": "saldo", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
            if(permiso_consignar) {
                $('td', row).eq(5).html('<a onclick="modalConsignar(' + data.id + ')"><i class="fa fa-dollar" style="cursor: pointer;"></i></a>');
                $('td', row).eq(6).html('<a onclick="historialConsignaciones(' + data.id + ')"><i class="fa fa-list-alt" style="cursor: pointer;"></i></a>');
            }else {
                $('td', row).eq(5).addClass('hide');
                $('td', row).eq(6).addClass('hide');
            }
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_cuentas_bancarias.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function crear(){
    DialogCargando("Guardando ...");
    var params = $("#form-cuenta-bancaria").serialize();
    var url = $("#base_url").val()+"/cuenta-bancaria/store";

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        mostrarErrores("contenedor-errores-form-cuenta-bancaria",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function setPermisoConsignar(permiso){
    permiso_consignar = permiso;
}

function modalConsignar(cuenta){
    id_select = cuenta;
    $("#valor").val("0");
    $("#modal-consignar").openModal();
}


function consignar(){
    DialogCargando("Realizando consignación ...");
    var params = {_token:$("#general-token").val(),cuenta:id_select,valor:$("#valor").val()};
    var url = $("#base_url").val()+"/cuenta-bancaria/consignar";

    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        mostrarErrores("contenedor-errores-consignar",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function historialConsignaciones(cuenta){
    $("#modal-historial-consignaciones").openModal();

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_historial_consignaciones = $('#tabla_historial_consignaciones').dataTable({ "destroy": true });
    tabla_historial_consignaciones.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_historial_consignaciones').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_historial_consignaciones = $('#tabla_historial_consignaciones').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/cuenta-bancaria/list-consignaciones/"+cuenta,
            "type": "GET"
        },
        "columns": [
            { "data": "created_at", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_historial_consignaciones.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}