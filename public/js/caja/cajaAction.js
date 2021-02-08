var permiso_editar_caja = false;
var permiso_eliminar_caja = false;
var id_select = null;
$(function () {
    $("#efectivo_inicial").keyup(function (event) {
        if(event.keyCode == 13){
            $("#btn-action-form-caja").click();
        }
    })

    $("#btn-action-form-caja").click(function () {
        DialogCargando("Iniciando caja ...");
        var url = $("#form-caja").attr('action');
        var params = new FormData(document.getElementById('form-caja'));

        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            var data = JSON.parse(data);
            window.location.reload(true);
        }).error(function (jqXHR,error) {
            CerrarDialogCargando();
            mostrarErrores("contenedor-errores-caja",JSON.parse(jqXHR.responseText));
        })
    });

    $("body").on("change","#estado",function(){
        var valor = $(this).val();

        if(valor == "otro"){
            $("#contenedor-razon-estado").removeClass("hide");
        }else{
            $("#contenedor-razon-estado").addClass("hide");
        }
    })
    
    $("#form-operacion-caja #tipo").change(function(){
        if($(this).val() == "Retiro"){
            $("#msj-retiro").removeClass("hide");
            $("#msj-consignacion").addClass("hide");
        }else if($(this).val() == "Consignación"){
            $("#msj-retiro").addClass("hide");
            $("#msj-consignacion").removeClass("hide");
        }else{
            $("#msj-retiro").addClass("hide");
            $("#msj-consignacion").addClass("hide");
        }
    })
    
    cargarTablaCajas();
})

function cargarTablaCajas() {
    var url = $("#base_url").val()+"/caja";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_cajas = $('#tabla_cajas').dataTable({ "destroy": true });
    tabla_cajas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_cajas').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_cajas = $('#tabla_cajas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/list-cajas",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "prefijo", 'className': "text-center" },
            { "data": "estado", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "valor_inicial", 'className': "text-center" },
            { "data": "valor_final", 'className': "text-center" },
            { "data": null, 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
            var opciones = data.historial;
            if(permiso_editar_caja)opciones += data.editar;

            if(permiso_eliminar_caja)opciones += data.eliminar;
            opciones += data.historial_estados;

            opciones += data.transacciones;
            $('td', row).eq(6).html(opciones);
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_cajas.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [6] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function setPermisoEditarCaja(permiso){
    permiso_editar_caja = permiso;
}

function setPermisoEliminarCaja(permiso){
    permiso_eliminar_caja = permiso;
}

function guardar(){
    var params = $("#modal-crear-caja #form-caja").serialize();
    var url = $("#base_url").val()+"/caja/store-caja";
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
                window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        mostrarErrores("contenedor-errores-crear-caja",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function getEditar(caja){
    var params = {caja:caja,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/form-editar";
    DialogCargando("Cargando ...");
    $.post(url,params,function(data){
        $("#contenido-editar-caja").html(data);
        CerrarDialogCargando();
        $("#modal-editar-caja").openModal();
        inicializarMaterialize();
    }).error(function (jqXHR,status,error) {
        mostrarErrores("contenedor-errores-editar-caja",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function editar(){
    var params = $("#modal-editar-caja #form-caja").serialize();
    var url = $("#base_url").val()+"/caja/update-caja";
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        mostrarErrores("contenedor-errores-editar-caja",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function eliminar(id,modal = true){
    if(modal){
        $("#modal-eliminar-caja").openModal();
        id_select = id;
    }else{
        var params = {caja:id_select,_token:$("#general-token").val()};
        var url = $("#base_url").val()+"/caja/destroy";

        DialogCargando("Eliminando ...");
        $.post(url,params,function(data){
            if(data.success){
                window.location.reload(true);
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-eliminar-caja",JSON.parse(jqXHR.responseText));
            CerrarDialogCargando();
        })
    }
}

function cargarHistorial(caja){
    var params = {caja:caja,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/historial";
    DialogCargando("Cargando historial ...");
    $.post(url,params,function (data) {
        CerrarDialogCargando();
        $("#contenedor-historial").html(data);
        $("#modal-historial").openModal();
        listaHistorial(caja);
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-cajas",JSON.parse(jqXHR.responseText));
        moveTop();
    })
}

function listaHistorial(caja) {
    var tabla_lista_historial = $('#tabla_lista_historial').dataTable({ "destroy": true });
    tabla_lista_historial.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_historial').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_lista_historial = $('#tabla_lista_historial').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/lista-historial?caja="+caja,
            "type": "GET",
        },
        "columns": [
            { "data": "id", 'className': "hide text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "valor_inicial", 'className': "text-center" },
            { "data": "valor_final", 'className': "text-center" },
            { "data": "valor_final_real", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "updated_at", 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            i = 1;
            if(i === tabla_lista_historial.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
            inicializarMaterialize();
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [ ] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}

function cargarHistorialEstados(caja){
    var params = {caja:caja,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/historial-estados";
    DialogCargando("Cargando historial ...");
    $.post(url,params,function (data) {
        CerrarDialogCargando();
        $("#contenedor-historial-estados").html(data);
        $("#modal-historial-estados").openModal();
        listaHistorialEstados(caja);
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-cajas",JSON.parse(jqXHR.responseText));
        moveTop();
    })
}

function listaHistorialEstados(caja) {
    var tabla_lista_historial_estados = $('#tabla_lista_historial_estados').dataTable({ "destroy": true });
    tabla_lista_historial_estados.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_historial_estados').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_lista_historial_estados = $('#tabla_lista_historial_estados').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/lista-historial-estados?caja="+caja,
            "type": "GET",
        },
        "columns": [
            { "data": "id", 'className': "hide text-center" },
            { "data": "estado_anterior", 'className': "text-center" },
            { "data": "estado_nuevo", 'className': "text-center" },
            { "data": "razon_estado", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            i = 1;
            if(i === tabla_lista_historial_estados.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
            inicializarMaterialize();
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}


function cargarTransacciones(caja){
    var params = {caja:caja,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/transacciones";
    DialogCargando("Cargando transacciones ...");
    $.post(url,params,function (data) {
        CerrarDialogCargando();
        $("#contenedor-transacciones").html(data);
        $("#modal-transacciones").openModal();
        listaTransacciones(caja);
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-cajas",JSON.parse(jqXHR.responseText));
        moveTop();
    })
}


function listaTransacciones(caja) {
    var tabla_lista_transacciones = $('#tabla_lista_transacciones').dataTable({ "destroy": true });
    tabla_lista_transacciones.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_transacciones').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_lista_transacciones = $('#tabla_lista_transacciones').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/lista-transacciones?caja="+caja,
            "type": "GET",
        },
        "columns": [
            { "data": "id", 'className': "hide text-center" },
            { "data": "tipo", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "comentario", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "creador", 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            i = 1;
            if(i === tabla_lista_transacciones.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [6] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}


function cargarTablaCajaMayor() {
    var url = $("#base_url").val()+"/caja";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_cajas = $('#tabla_caja_mayor').dataTable({ "destroy": true });
    tabla_cajas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_caja_mayor').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_cajas = $('#tabla_caja_mayor').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/list-caja-mayor",
            "type": "GET"
        },
        "columns": [
            { "data": "fecha", 'className': "text-center" },
            { "data": "estado", 'className': "text-center" },
            { "data": "efectivo_inicial", 'className': "text-center" },
            { "data": "efectivo_final", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "opciones", 'className': "text-center" },
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_cajas.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [5] }],
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


function guardarOperacionCajaMaestra(){
    var params = $("#form-operacion-caja").serialize();
    var url = $("#base_url").val()+"/caja/operacion-caja-maestra";
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,status,error) {
        moveTop($("#modal-operacion-caja .modal-content"),1000,0);
        mostrarErrores("contenedor-errores-operacion-caja",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function cargarHistorialOperacionesCaja(caja){
    var params = {caja:caja,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/historial-operaciones-caja";
    DialogCargando("Cargando historial ...");
    $.post(url,params,function (data) {
        CerrarDialogCargando();
        $("#contenedor-historial-operaciones-caja").html(data);
        $("#modal-historial-operaciones-caja").openModal();
        listaHistorialOperacionesCaja(caja);
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-lista-cajas",JSON.parse(jqXHR.responseText));
        moveTop();
    })
}

function listaHistorialOperacionesCaja(caja) {
    var tabla_lista_operaciones_caja = $('#tabla_lista_operaciones_caja').dataTable({ "destroy": true });
    tabla_lista_operaciones_caja.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_operaciones_caja').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_lista_operaciones_caja = $('#tabla_lista_operaciones_caja').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/caja/lista-historial-operaciones-caja?caja="+caja,
            "type": "GET",
        },
        "columns": [
            { "data": "banco", 'className': "text-center" },
            { "data": "numero", 'className': "text-center" },
            { "data": "tipo", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "observacion", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "usuario", 'className': "text-center" }
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            i = 1;
            if(i === tabla_lista_operaciones_caja.data().length){
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