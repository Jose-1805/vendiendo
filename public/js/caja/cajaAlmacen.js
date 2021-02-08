var permiso_editar_caja = false;
var permiso_eliminar_caja = false;
var id_select = null;
var id_almacen_destinatario = null;
$(function () {
    $("#efectivo_inicial").keyup(function (event) {
        if(event.keyCode == 13){
            $("#btn-action-form-caja").click();
        }
    })
    
    $('body').on('click','.iniciar-caja-almacen',function () {
        id_select = $(this).data('almacen');
        $("#modal-iniciar-caja-almacen").openModal();
    })

    $('body').on('click','.movimiento-caja-maestra',function () {
        id_almacen_destinatario = $(this).data('almacen');
        $("#modal-movimiento-caja-maestra").openModal();
    })

    $('body').on('click','.lista-movimientos-cajas-maestras',function () {
        cargarHistorialMovimientoCaja($(this).data('caja'));
    })
})


function cargarTablaAlmacen() {
    var url = $("#base_url").val()+"/caja";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_almacen = $('#tabla_almacen').dataTable({ "destroy": true });
    tabla_almacen.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_almacen').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_almacen = $('#tabla_almacen').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/list-caja-almacenes",
            "type": "GET"
        },
        "columns": [
            { "data": "almacen", 'className': "text-center" },
            { "data": "fecha", 'className': "text-center" },
            { "data": "efectivo_inicial", 'className': "text-center" },
            { "data": "efectivo_final", 'className': "text-center" },
            { "data": "opciones", 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_almacen.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [4] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function cerrarCajaMaestra(mensaje = true){
    if(mensaje){
        $("#modal-cerrar-caja-master").openModal();
    }else{
        var url = $("#base_url").val()+"/caja/cerrar-caja-maestra";
        var params = {_token:$("#general-token").val()};

        DialogCargando("Cerrando caja maestra ...");
        $.post(url,params,function (data) {
            if(data.success){
                window.location.reload();
            }
        }).error(function (jqXHR,state,error) {
            CerrarDialogCargando();
            $("#modal-cerrar-caja-master").closeModal();
            mostrarErrores("contenedor-errores-lista-cajas",JSON.parse(jqXHR.responseText));
        })
    }
}


function iniciarCajaAlmacen(){
    var params = $("#form-iniciar-caja-almacen").serialize()+'&almacen='+id_select;
    var url = $("#base_url").val()+"/caja/iniciar-caja-almacen";
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        moveTop($("#modal-operacion-caja .modal-content"),1000,0);
        mostrarErrores("contenedor-errores-iniciar-caja-almacen",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function realizarMovimientoCajaMaestra(){
    var params = $("#form-modal-movimiento-caja-maestra").serialize()+'&almacen_destinatario='+id_almacen_destinatario;
    var url = $("#base_url").val()+"/caja/realizar-movimiento-caja-maestra";
    DialogCargando("Enviando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        moveTop($("#modal-movimiento-caja-maestra .modal-content"),1000,0);
        mostrarErrores("contenedor-errores-movimiento-caja-maestra",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function cargarHistorialMovimientoCaja(caja){
    var params = {caja:caja,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/historial-movimientos-caja-maestra";
    DialogCargando("Cargando historial ...");
    $.post(url,params,function (data) {
        CerrarDialogCargando();
        $("#contenedor-lista-movimientos-caja-maestra").html(data);
        $("#modal-lista-movimientos-caja-maestra").openModal();
        listaHistorialMovimientosCaja(caja);
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-lista-almacenes",JSON.parse(jqXHR.responseText));
        moveTop();
    })
}

function listaHistorialMovimientosCaja(caja) {
    var tabla_lista_movimientos_caja_maestra = $('#tabla_lista_movimientos_caja_maestra').dataTable({ "destroy": true });
    tabla_lista_movimientos_caja_maestra.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_movimientos_caja_maestra').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_lista_movimientos_caja_maestra = $('#tabla_lista_movimientos_caja_maestra').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/lista-historial-movimientos-caja-maestra?caja="+caja,
            "type": "GET",
        },
        "columns": [
            { "data": "fecha", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "observacion", 'className': "text-center" }
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            i = 1;
            if(i === tabla_lista_movimientos_caja_maestra.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [ ] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}