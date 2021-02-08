var ConfirmacionRecarga = false;    //Variable que se encarga de evitar pedir confirmacion de abandonar pagina
var permiso_editar = 0;
var id_select = 0;
var input_nombre = null;
var productos = [];
var clientes = null;
var fila = 1;
var numero_fila_actual_producto = "";
//Estado factura pedida
var numero_cuotas = null;
var dias_credito = null;
var tipo_periodicidad_notificacion = null;
var periodicidad_notificacion = null;
var fecha_primera_notificacion = null;
var estado_factura = null;
var clienteSeleccionado = null;
var token_puntos = null;
var valor_puntos_redimidos = 0;
var valor_medios_pago = 0;          total_factura = 0 ;
var columnDefs = [{},{}];
var aplica_descuentos = false;

function setAplicaDescuentos(permiso){
    aplica_descuentos = permiso;
}

function setPermisoEditar(valor){
    permiso_editar = valor;
}

$(document).ready(function(){
    if(!permiso_editar) {
        columnDefs[0] = { "targets": [7], "visible": false, "searchable": false };
    }

    $(".div_flotate").draggable();

    cargarTablaFacturas()
});

function cargarTablaFacturas() {
    var url = $("#base_url").val()+"/factura";
    var checked = "";
    var i=1;
    var FacturasTabla = $('#FacturasTabla').dataTable({ "destroy": true });
    FacturasTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#FacturasTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    FacturasTabla = $('#FacturasTabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/factura/list-facturas",
            "type": "GET"
        },
        "columns": [
            { "data": "id", 'className': "text-center hide" },
            { "data": "numero", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "cliente", 'className': "text-center" },
            { "data": "usuario", "className": "text-center"},
            { "data": "caja", "className": "text-center"},
            { "data": "estado", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, 'defaultContent': "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {

            if(data.estado != "cerrada"){
                if(data.estado == "Pedida" && data.abonos_count == 0){
                    var valor_fac = data.valor.replace('$','');
                    while(valor_fac.indexOf('.') != -1)
                     valor_fac = valor_fac.replace('.','');
                    //$('td', row).eq(6).html("<div class='switch'><label>Pedida<input type='checkbox' id='"+data.id+"' checked onclick=\"estadoFactura('" + data.id +"','"+ data.estado +"','"+ valor_fac.replace(',','.') +"')\"></label><span class='lever'></span></div>");
                    $('td', row).eq(7).html("<div class='switch'><label><label id='pedida' style='color: #039be5;font-weight: bold;'>Pedida</label><br><input type='checkbox' id='"+data.id+"' onclick=\"estadoFactura('" + data.id +"','"+ data.estado +"','"+ valor_fac.replace(',','.') +"')\"><span class='lever'></span><br>Pagada</label></div>");
                }else if (data.estado == 'Pagada'){
                    $('td', row).eq(7).html("<i class='material-icons prefix green-text tooltipped' data-tooltip='Pagada'>check_circle</i>");
                }else if (data.estado == 'Pendiente por pagar'){
                    $('td', row).eq(7).html("<div class='tooltipped' data-tooltip='Pendiente por pagar'><img src='img/sistema/PendientePago2.png' style='max-height: 30px;'></div>");
                }
            }else{
                $('td', row).eq(7).html(data.estado);
            }
            var posicion = 8;
            if(permiso_editar) {

                if (data.dias_credito > 0 /*|| data.estado == 'Pedida'*/) {
                    $('td', row).eq(posicion).html("<a href='#' onclick=\"abrirModalAbonos('" + data.id + "', '" + data.estado + "')\"><i class='fa fa-dollar fa-1x'></i></a>");

                }
                posicion ++;
            }
            if(data.estado != "cerrada" && data.estado != "abierta"){
                $('td', row).eq(posicion).html("<a href='" + url + '/detalle/' + data.id +"'><i class='fa fa-chevron-right'></i></a>")
            }else
                $('td', row).eq(posicion).html("<a href='" + url +'/detalle-factura-abierta/' + data.id +"'><i class='fa fa-chevron-right'></i></a>")

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === FacturasTabla.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        "columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [2,4,7,8,9] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

$(function(){
    /**
     *  FILTRO
     */
    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarFactura(input);
    });

    $("#busqueda, #busqueda2").keyup(function(event){
        if(event.keyCode == 13)
            buscarFactura($(this));
    });

    $("#busqueda-cliente, #busqueda-cliente-2").keyup(function(event){
        if(event.keyCode == 13)
            buscarCliente($(this));
    });

    $("#busqueda-producto").keyup(function(event){
        if(event.keyCode == 13) {
            var load = $(this).parent().children("button").eq(0).children(".icono-load-buscar").eq(0);
            var icon = $(this).parent().children("button").eq(0).children(".icono-buscar").eq(0);
            buscarProductos_(load,icon);
        }
    });

    $("#btn-buscar-productos").click(function(){
        var load = $(this).parent().children(".icono-load-buscar").eq(0);
        var icon = $(this).parent().children(".icono-buscar").eq(0);
        buscarProductos_(load,icon);
    });

    $("#categoria").change(function(){       
        /*var load = $(this).parent().children(".icono-load-buscar").eq(0);
         var icon = $(this).parent().children(".icono-buscar").eq(0);*/
        buscarProductos_();
    });

    $(".btn-buscar-cliente").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarCliente(input);
    });

    $("#lista-facturas").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        if($("#busqueda").val()){
            $("#busqueda2").val($("#busqueda").val());
            url += "&filtro="+$("#busqueda").val();
        }else if($("#busqueda2").val()){
            $("#busqueda").val($("#busqueda2").val());
            url += "&filtro="+$("#busqueda2").val();
        }
        window.location.href = url;
    })

    $('#btn-factura-abierta').click(function(event){
       $('input:enabled.barcode').first().focus();
    });
    
    $("#tabla-detalle-producto tbody").on("click","tr td .fa-trash",function(){
        eliminarFila($(this));
    })

    $("#tabla-detalle-producto tbody").on("keydown","tr td .nombre",function(){
        $(this).blur();
        $(this).click();
    })

    $("#tabla-detalle-producto tbody").on("click","tr td .nombre",function(){
        input_nombre = $(this);
        $(input_nombre).blur();
        var fila_spin = $(this).parent().parent().attr('id');
        numero_fila_actual_producto = fila_spin;
        var load = $(input_nombre).parent().children(".fa-spin").eq(0);
        buscarProductos_(load, null, null, numero_fila_actual_producto);
    })

    $("body").on("keyup",".cantidad",function(){
        valoresFactura();
    })

    $("#efectivo-modal").keyup(function(){
        var efectivo = parseInt($(this).val());
        var total = valoresFactura();
        total = total - valor_puntos_redimidos - valor_medios_pago;
        if(total < efectivo){
            $("#regreso-modal").text("$ "+formato_numero((efectivo-total),0,',','.'));
            $("#descuento-modal").text("$ 0");
        }else{
            $("#regreso-modal").text("$ 0");
            $("#descuento-modal").text("$ "+formato_numero((total-efectivo),0,',','.'));
        }
    })

    $(".valor-medio-pago").keyup(function(){
        var total = valoresFactura();
        total = total - valor_puntos_redimidos;

        valor_medios_pago = 0;

        $('.valor-medio-pago').each(function (i,el) {
            if($(el).val()) {
                valor_medios_pago += parseInt($(el).val());
            }
        })

        if(Math.round(total)<valor_medios_pago){
            $(this).val('');
            mostrarErrores('contenedor-errores-medios-pago',{'error':['El valor máximo permitido en los medios de pago es $ '+number_format(total,2)]});
        }
    })


    $('.valor-total-tipo-pago').click(function () {
        $(this).parent().children('.valor-medio-pago').val('0');
        var total = valoresFactura();
        total = total - valor_puntos_redimidos;

        valor_medios_pago = 0;

        $('.valor-medio-pago').each(function (i,el) {
            if($(el).val()) {
                valor_medios_pago += parseInt($(el).val());
            }
        })

        $('#descuento-modal').text('$ 0');
        $('#efectivo-modal').val('0');

        $(this).parent().children('.valor-medio-pago').val(Math.round(total)-valor_medios_pago);
        $(this).parent().children('.valor-medio-pago').focus();
    })

    $("#contenedor-productos-modal").on("click",".content-table-slide .pagination li a",function(e){
        e.preventDefault();
        var page = $(this).attr("href").split("=")[1];
        buscarProductos_(null,null,page, numero_fila_actual_producto);
    })

    $("#tipo_periodicidad_notificacion").change(function(){
        if($(this).val() == "quincenal" || $(this).val() == "mensual"){
            $("#periodicidad").removeClass("hide");
        }else{
            $("#periodicidad").addClass("hide");
        }
    })

    $(".btn-toggle-datos-cliente").click(function(){
        $("#info-cliente").slideToggle(500);
        if($(this).hasClass("fa-angle-double-up")){
            $(this).removeClass("fa-angle-double-up");
            $(this).addClass("fa-angle-double-down");

            $("#contenedor-botones-cliente-up").removeClass("hide");
            $("#contenedor-botones-cliente").addClass("hide");
        }else{
            $("#contenedor-botones-cliente-up").addClass("hide");
            $("#contenedor-botones-cliente").removeClass("hide");
            $(this).removeClass("fa-angle-double-down");
            $(this).addClass("fa-angle-double-up");
        }

    })
    
    $(".btn-toggle-estados-factura").click(function(){
        $("#estados-factura").slideToggle(500);
        if($(this).hasClass("fa-angle-double-up")){
            $(this).removeClass("fa-angle-double-up");
            $(this).addClass("fa-angle-double-down");
        }else{
            $(this).removeClass("fa-angle-double-down");
            $(this).addClass("fa-angle-double-up");
        }

    })

    $("body").on("keypress", function(e) {

        if((e.keyCode == 10 || e.keyCode == 13) && e.ctrlKey){

            var cantidadFilas = $('#tabla-detalle-producto tbody').find('tr').length;
            var valorUltimaFila = ($('#tabla-detalle-producto tbody tr:last-child').children().children().val())

            if(cantidadFilas > 1 && valorUltimaFila == ''){
                $('table tr:last-child').remove();
            }
            pagar();
        }
    });

    $("body").on("keyup", ".barcode", function(e) {
        var y = $(this).closest('tr').index();

        if(e.keyCode == 38 && $(this).hasClass('barcode')){

            $('#tabla-detalle-producto tr').eq(y).find('input.cantidad').focus();
        }
    });

    $("body").on("keyup", ".cantidad", function(e) {
        var y = $(this).closest('tr').index();
        if(e.keyCode == 40){
            y =   parseInt(y) +2;

        }
        if(e.keyCode == 40 || e.keyCode == 38){
            $('#tabla-detalle-producto tr').eq(y).find('input.cantidad').focus();
        }

        if (e.keyCode == 37){
            $('table tr:last-child').find('input.barcode').focus()
        }

    });

    $("#redimir").change(function(){
        if(clienteSeleccionado != null && clienteSeleccionado.predeterminado != "si") {
            if ($(this).val() == 1) {
                if (clienteSeleccionado.valor_puntos < (total_factura-valor_medios_pago))
                    $("#modal-puntos #valor").eq(0).val(parseFloat(clienteSeleccionado.valor_puntos).toFixed(2));
                else
                    $("#modal-puntos #valor").eq(0).val(parseFloat(total_factura).toFixed(2) -  valor_medios_pago);

                $("#modal-puntos #valor").eq(0).attr("readonly", "readonly");
            } else {
                if (clienteSeleccionado.valor_puntos < (total_factura-valor_medios_pago))
                    $("#modal-puntos #valor").eq(0).val(parseFloat(clienteSeleccionado.valor_puntos).toFixed(2));
                else
                    $("#modal-puntos #valor").eq(0).val(parseFloat(total_factura).toFixed(2)-valor_medios_pago);

                $("#modal-puntos #valor").eq(0).prop("readonly", false);
            }
        }
    });

    $("#modal-puntos #valor").keyup(function(){
        if(clienteSeleccionado != null && clienteSeleccionado.predeterminado != "si") {
            if($(this).val() > parseInt(clienteSeleccionado.valor_puntos) || $(this).val() > parseInt(total_factura-valor_medios_pago)){
                alert("El valor a redimir no es correcto");
                if (clienteSeleccionado.valor_puntos < total_factura)
                    $(this).val(parseFloat(clienteSeleccionado.valor_puntos));
                else
                    $(this).val(parseFloat(total_factura-valor_medios_pago));
            }

        }
    });

    $("#btn-limpiar-lista-productos").click(function () {
        if($(".id-pr").eq(0).val()){
            //var nFilas = $("#tabla-detalle-producto >tbody >tr").length;
            $("#tabla-detalle-producto tbody tr").remove();

            if (sessionStorage.lista_factura_abierta){
                sessionStorage.removeItem('lista_factura_abierta');
                //loadPedidoFacturaAbierta();
            }
            agregarElementoFactura();
            //loadPedidoFacturaAbierta();
            valoresFactura();

        }else{
            alert("No existe productos en la lista");
        }
    });

    $("#efectivo-modal").keyup(function(e){
        if(e.keyCode == 13){
            validPagar();
        }
    });

    if($("#btn-imprimir-pos")){
        $("#btn-imprimir-pos").focus();
    }
})

//POPUP FACTURA
function buscarProductos_(load = null,icon = null,page=null,numero_fila_actual_producto){
    if(load != null)
        $(load).removeClass("hide");
    if(icon != null)
        $(icon).addClass("hide");
    var url = $("#base_url").val()+"/productos/filtro";
    var params = {_token:$("#general-token").val(),vista:"factura.lista_productos"};//,filtro:$("#busqueda-producto").val(),categoria:$("#categoria").val(),page:page};
    $.post(url,params,function(data){
        $("#contenedor-productos-modal").html(data);
        $("#footer-modal-detalles-factura").removeClass("modal-fixed-footer");
        //$("#footer-modal-detalles-factura").css("display","none");   
        //$("#filtros-detalles-factura").html($(".dataTables_filter").html());
        $("#modal-detalles-factura .modal-footer #contenedor-botones-detalles-factura-modal .green-text").addClass('hide');
        $("#modal-detalles-factura").openModal();
        $(".dataTables_filter label input").focus();
        //$("#btnSeleccionProducto").attr('onclick', "seleccionProducto(null,'"+ numero_fila_actual_producto +"',null)")
        if(load != null)
            $(load).addClass("hide");
        if(icon != null)
            $(icon).removeClass("hide");
    })
}

function cargatablaListaProductos(){
    var i= 0;
    var params = "";
    var tabla_lista_productos = $('#tabla_lista_productos').dataTable({ "destroy": true });
    tabla_lista_productos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_productos').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })

    tabla_lista_productos = $('#tabla_lista_productos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/productos/list-factura-productos?"+params,
            "type": "GET"
        },"tabIndex": 1,
        "columns": [
            { "data": null, 'className': "text-center" , "width": "2%"},
            { "data": "nombre", 'className': "text-center" , "width": "23%"},
            { "data": "valor", 'className': "text-center" , "width": "15%"},
            { "data": "stock", 'className': "text-center" , "width": "15%"},
            { "data": "umbral", 'className': "text-center" , "width": "15%"},
            { "data": "unidad", 'className': "text-center" , "width": "15%"},
            { "data": "categoria", 'className': "text-center" , "width": "15%"}
        ],
        "fnRowCallback": function (row, data, index) {       
            var classStrock="";
            if(data.stock <= 0){
                var classStock = "grey-text";
            }
            $(row).addClass(classStock);
            $(row).attr("id-data",data.id);
            if(data.stock > 0){
                $(row).attr('onClick', "checkradioButton('radio-producto-"+data.id+"')");
                $('td', row).eq(0).html("<p style='margin: 0px !important;padding: 0px !important;'' class='radio_producto'>"+
                    "<input type='radio' id='radio-producto-"+data.id+"' name='radio-producto' value='"+data.id+"' onclick=\"seleccionProducto(null,'"+ numero_fila_actual_producto +"',null,event);\" >"+
                    "<label for='radio-producto-"+data.id+"'></label>"+
                    "</p>");
            }else{
                $('td', row).eq(0).html('');
                classStock = "red-text";
            }
            $('td',row).eq(3).addClass(classStock);
            $("#radio-producto-"+data.id+"").attr('onclick', "seleccionProducto(null,'"+ numero_fila_actual_producto +"',null,event)")

                if(i === 0){
                    setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');  
                    inicializarMaterialize();
                        $('table#tabla_lista_productos thead tr th').each(function() {
                            $(this).attr('tabindex',-1);
                        });
                    },700)
                    i=1;
                }else{
                    i++;
                }
        },"tabIndex": -1,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });

  
}



function buscarFactura(input){
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());

    var filtro = $(input).val();
    var url = $("#base_url").val()+"/factura/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#lista-facturas").html(data);
        //inicializarMaterialize();
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function buscarCliente(element) {
    if($(element).attr("id") == "busqueda-cliente")
        $("#busqueda-cliente-2").val($("#busqueda-cliente").val());
    else
        $("#busqueda-cliente").val($("#busqueda-cliente-2").val());

    var filtro = $(element).val();
    var url = $("#base_url").val()+"/factura/buscar-cliente";
    var params = {filtro:filtro,_token:$("#general-token").val()};
    $("#datos-cliente .buscar-cliente .fa-search").addClass("hide");
    $("#datos-cliente .buscar-cliente .fa-spin").removeClass("hide");
    $.post(url,params,function(data){

        $("#modal-clientes .modal-footer #contenedor-botones-clientes .green-text").addClass('hide');
        //console.log($("#modal-clientes .modal-footer #contenedor-botones-clientes .green-text").html());
        $("#modal-clientes .modal-content #contenedor-clientes").html(data);
        $("#modal-clientes").openModal({dismissible: false,complete: function() {
            clientes = null;
            if($("#cliente").val() == ""){
                $("#contenedor-detalles-compra").addClass("hide");
                $("#contenedor-estados-compra").addClass("hide");
            }else{
                $("#contenedor-detalles-compra").removeClass("hide");
                $("#contenedor-estados-compra").removeClass("hide");
            }
        }});

        $(".dataTables_filter label input").focus();
        $.post($("#base_url").val()+"/factura/total-cliente",params,function(data){
            if(data.success){
                if(data.clientes.length) {
                    clientes = data.clientes;
                }
            }
        });
        //if(data.success){
        //    if(data.clientes.length) {
        //        clientes = data.clientes;
        //        var tabla = "<table class='table'>" +
        //            "<thead>" +
        //            "<th></th>" +
        //            "<th>Identificación</th>" +
        //            "<th>Nombre</th>" +
        //            "<th>Correo</th>" +
        //            "</thead>" +
        //            "<tbody>";
        //        $.each(data.clientes, function (i, cliente) {
        //            if(cliente.predeterminado != "si") {
        //                tabla += "<tr>" +
        //                    "<td style='min-width: 30px;'><p><input name='cliente' type='radio' id='cliente" + cliente.id + "' value='" + cliente.id + "'/><label for='cliente" + cliente.id + "'></label></p></td>" +
        //                    "<td>(" + cliente.tipo_identificacion + ") " + cliente.identificacion + "</td>" +
        //                    "<td>" + cliente.nombre + "</td>" +
        //                    "<td>" + cliente.correo + "</td>" +
        //                    "</tr>";
        //            }else{
        //                tabla += "<tr>" +
        //                    "<td style='min-width: 30px;'><p><input name='cliente' type='radio' id='cliente" + cliente.id + "' value='" + cliente.id + "'/><label for='cliente" + cliente.id + "'></label></p></td>" +
        //                    "<td colspan='3'>" + cliente.nombre + "</td>" +
        //                    "</tr>";
        //            }
        //        })
        //        tabla += "</tbody></table>";
        //    }else{
        //        tabla = "<p>No se han encontrado clientes con el filtro ingresado</p>";
        //    }
        //    $("#modal-clientes .modal-content #contenedor-clientes").html(tabla);
        //    $("#modal-clientes").openModal({dismissible: false,complete: function() {
        //        clientes = null;
        //        if($("#cliente").val() == ""){
        //            $("#contenedor-detalles-compra").addClass("hide");
        //            $("#contenedor-estados-compra").addClass("hide");
        //        }else{
        //            $("#contenedor-detalles-compra").removeClass("hide");
        //            $("#contenedor-estados-compra").removeClass("hide");
        //        }
        //    }});
        //}else{
        //    $(".dato-cliente").text("No establecido");
        //    $("#cliente").val("");
        //    $(".btn-editar-cliente").addClass("hide");
        //    $(".btn-crear-cliente").removeClass("hide");
        //    if($(".btn-toggle-datos-cliente").eq(0).hasClass("fa-angle-double-up")){
        //        $("#contenedor-botones-cliente-up").addClass("hide");
        //        $("#contenedor-botones-cliente").removeClass("hide");
        //    }else{
        //        $("#contenedor-botones-cliente-up").removeClass("hide");
        //        $("#contenedor-botones-cliente").addClass("hide");
        //    }
        //}
        $("#datos-cliente .buscar-cliente .fa-search").removeClass("hide");
        $("#datos-cliente .buscar-cliente .fa-spin").addClass("hide");

    }).error(function(jqXHR,error,state){
        $(".dato-cliente").text("No establecido");
        $("#cliente").val("");
        $(".btn-editar-cliente").addClass("hide");
        $(".btn-crear-cliente").removeClass("hide");
        $("#datos-cliente .buscar-cliente .fa-search").removeClass("hide");
        $("#datos-cliente .buscar-cliente .fa-spin").addClass("hide");
    })
}

function cargatablaListaClientes(){
     var i= 0;
    var params = "";
    var tabla_lista_clientes = $('#tabla_lista_clientes').dataTable({ "destroy": true });
    tabla_lista_clientes.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_lista_clientes').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })

    tabla_lista_clientes = $('#tabla_lista_clientes').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/factura/list-factura-cliente?"+params, //GET
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" , "width": "10%"},
            { "data": "identificacion", 'className': "text-center" , "width": "20%"},
            { "data": "nombre", 'className': "text-center" , "width": "30%"},
            { "data": "correo", 'className': "text-center" , "width": "40%"},
        ],
        "fnRowCallback": function (row, data, index) {
           // console.log(data);

            $(row).attr('onClick', "checkradioButton('cliente"+data.id+"')");
            $('td', row).eq(0).html("<p style='margin: 0px !important;padding: 0px !important;'' class='radio_producto'>"+
                "<input type='radio' id='cliente"+data.id+"' name='cliente' value='"+data.id+"' onclick=\"javascript: seleccionCliente(null,event);\">"+
                "<label for='cliente"+data.id+"'></label>"+
                "</p>");
                if(i === 0){
                    setTimeout(function () {  
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                        $('table#tabla_lista_clientes thead tr th').each(function() {
                            $(this).attr('tabindex',-1);
                        });
                    },700)
                    i=1;
                }else{
                    i++;
                }
        },
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}


function seleccionCliente( seleccion = null, event = null) {
    //console.log(event)
    if(event != null){
        if(typeof event.which !== "undefined"){
            if(event.x === 0)
                return false;
        }
    }
    
    if(clientes != null) {
        if(seleccion == null)
            seleccion = $('input:radio[name=cliente]:checked').val();
        if(typeof(seleccion) != "undefined"){
            $.each(clientes,function (i,cliente) {
                if(cliente.id == seleccion){
                    clienteSeleccionado = cliente;

                    if(clienteSeleccionado.predeterminado == 'si') {
                        $('#estado option').each(function (i,el) {
                            if($(el).val() == 'Pagada'){
                                $(el).prop('selected','selectd');
                            }
                        })
                        $('#estado').prop('disabled', true);
                    }else {
                        $('#estado').prop('disabled', false);
                    }

                    $("#txt-nombre").text(cliente.nombre);
                    $("#txt-tipo-identificacion").text(cliente.tipo_identificacion);
                    $("#txt-identificacion").text(cliente.identificacion);
                    $("#txt-correo").text(cliente.correo);
                    $("#txt-telefono").text(cliente.telefono);
                    $("#txt-direccion").text(cliente.direccion);
                    $("#valor-puntos").val(cliente.valor_puntos);
                    $("#tipo-cliente").val(cliente.predeterminado);
                    $("#cliente").val(cliente.id);
                    $(".btn-editar-cliente").removeClass("hide");
                    //$(".btn-crear-cliente").addClass("hide");
                    $("#modal-clientes").closeModal();
                    if($(".btn-toggle-datos-cliente").eq(0).hasClass("fa-angle-double-up")){
                        $(".btn-toggle-datos-cliente").eq(0).click();
                    }else{
                        $("#contenedor-botones-cliente-up").removeClass("hide");
                        $("#contenedor-botones-cliente").addClass("hide");
                    }
                    inicializarMaterialize();
                }
            })
        }else{
            alert("Seleccione un cliente");
        }
    }
}

function agregarElementoFactura(){
    var agregar = true;
    $(".id-pr").each(function (i,el) {
        if(i == ($(".id-pr").length - 1)){
            if($(el).val() == "" || typeof $(el).val() == "undefined") {
                $(el).parent().parent().children("td").children(".barcode").eq(0).focus();
                agregar = false;
            }
        }
    })
    if(agregar) {
        var fi = parseInt(fila);
        var html = "<tr id='filaProd_" + fila + "'>" +
            "<td style='text-align: center'>" +
            "<input type='text' class='barcode barCodeProducto' id='barCodeProducto_" + fila + "'  placeholder='Codigo de barras' onchange=\"seleccionProducto('barCode', 'filaProd_" + fila + "', this.id,event); obtenerMaximoTotalStockProductos('', 'filaProd_" + fi + "', 'barCode' );\">" +
            "</td>" +
            "<td style='text-align: center'>" +
            "<input type='text' class='nombre' placeholder='Click aquí'>" +
            "<i class='fa fa-spin fa-spinner hide' style='margin-top: -40px;'></i>" +
            "<input type='hidden' class='id-pr'>" +
            "</td>" +

            "<td>" +
            "<p class='unidad'></p>" +
            "</td>" +

            "<td>" +
            "<input type='text' value='1' min='1' class='excepcion num-real center-align cantidad' onblur=\"obtenerMaximoTotalStockProductos(this.id, '', 'cantidad')\">" +
            "</td>" +

            "<td>" +
            "<p style='padding-top: 15px;white-space: nowrap;' class='vlr-unitario'>$ 0</p>" +
            "</td>" +

            "<td>" +
            "<p style='padding-top: 15px;white-space: nowrap;' class='vlr-total'>$ 0</p>" +
            "</td>" +

            "<td>" +
            "<i class='fa fa-trash red-text text-darken-1 waves-effect waves-light' id='eliminar_" + fila + "' title='Eliminar elemento' style='cursor: pointer;'></i>" +
            "</td>" +
            "</tr>";

        $("#tabla-detalle-producto tbody").append(html);
        setFocus('barCodeProducto_' + fila);
        fila++;
    }
}
function seleccionProducto(accion, fila_ID, barCodeID,event){
    if(event != null){
        if(typeof event.which !== "undefined"){
            if(event.x === 0)
                return false;
        }
    }
    
    //if(typeof event.which !== "undefined"){return false;}
  
    /*var barCodeProducto = $('#' + barCodeID).val();

     if (accion == undefined){
     var id = $('input:radio[name=radio-producto]:checked').val();
     }

     if(typeof id == "undefined" && accion == undefined){
     alert("Seleccione un producto");
     }else{
     var continuar = true;

     if(continuar) {
     var url = $("#base_url").val() + "/productos/datos-producto";
     var params = {_token: $("#general-token").val(), id: id, barCodeProducto:barCodeProducto};
     $("#progress-detalles-factura-modal").removeClass("hide");
     $("#contenedor-botones-detalles-factura-modal").addClass("hide");


     $.post(url, params, function (data) {
     //console.log(data)
     if (data.success) {
     if($(input_nombre).val() != ""){
     valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
     for(i in productos){
     if(valor == productos[i].id){
     productos.splice(i,1);
     }
     }
     }
     productos.push(data.producto);

     if(localStorage.loadPedido){
     var aux_pr = {};
     for(i in productos){
     var productoObj={
     'IdProducto':productos[i].id,
     'Nombre':productos[i].nombre,
     'Stock':productos[i].stock,
     'Cantidad':"1",
     'Precio': parseFloat(productos[i].precio_costo),
     'Sigla':productos[i].sigla,
     'Iva':productos[i].iva,
     'utilidad':productos[i].utilidad
     };
     aux_pr[productos[i].id] = productoObj;
     }
     localStorage.productosC = JSON.stringify(aux_pr);
     }

     if(accion == 'barCode'){
     $('#' + fila_ID).each(function(){

     $(this).children().next().children().val(data.producto.nombre);//producto
     $(this).children().next().children(".id-pr").val(data.producto.id);//unidad
     $(this).children().next().next().children(".unidad").text(data.producto.sigla);//unidad
     $(this).children().next().next().next().children(".cantidad").val("1").attr("data-max",data.producto.stock);//cantidad
     var valor_unitario = parseFloat(data.producto.precio_costo)+((parseFloat(data.producto.precio_costo) * parseFloat(data.producto.utilidad))/100);
     $(this).children().next().next().next().children(".vlr-unitario").text("$ "+formato_numero(valor_unitario,2,',','.'));
     $(this).children().next().next().next().next().children(".iva").text(formato_numero(data.producto.iva,2,',','.')+"%");
     $(this).children().next().next().next().next().children(".vlr-total").text("$ "+formato_numero(valor_unitario,2,',','.'));
     });
     }else{
     $(input_nombre).parent().parent().children("td").children(".barcode").eq(0).val(data.producto.barcode);
     $(input_nombre).val(data.producto.nombre);
     $(input_nombre).parent().children(".id-pr").eq(0).val(data.producto.id);
     $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
     $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).attr("data-max",data.producto.stock);
     $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
     var valor_unitario = parseFloat(data.producto.precio_costo)+((parseFloat(data.producto.precio_costo) * parseFloat(data.producto.utilidad))/100);
     $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ "+formato_numero(valor_unitario,2,',','.'));
     $(input_nombre).parent().parent().children("td").children(".iva").eq(0).text(formato_numero(data.producto.iva,2,',','.')+"%");
     $(input_nombre).parent().parent().children("td").children(".vlr-total").eq(0).text("$ "+formato_numero(valor_unitario,2,',','.'));

     }

     valoresFactura();
     input_nombre = null;
     $("#modal-detalles-factura").closeModal();
     } else {
     //alert(data.mensaje)
     Materialize.toast(data.mensaje, 4000,'red');
     }

     $("#progress-detalles-factura-modal").addClass("hide");
     $("#contenedor-botones-detalles-factura-modal").removeClass("hide");
     })
     }
     }*/
    numero_fila_actual_producto = fila_ID;
    DialogCargando();
    var continuar = false;
    var barCodeProducto = $('#' + barCodeID).val();

    if (accion == undefined){
        var id = $('input:radio[name=radio-producto]:checked').val();
    }
    if(typeof id == "undefined" && accion == undefined){
        alert("Seleccione un producto");
        CerrarDialogCargando();
    }else{
        continuar = barCodeProducto == '' ? false : true; /*nuevo*/
        if(continuar) {

            var url = $("#base_url").val() + "/productos/datos-producto";
            var params = {_token: $("#general-token").val(), id: id, barCodeProducto:barCodeProducto};
            $("#progress-detalles-factura-modal").removeClass("hide");
            $("#contenedor-botones-detalles-factura-modal").addClass("hide");

            $.post(url, params, function (data) {
                //console.log(data)
                if (data.success) {
                    if($(input_nombre).val() != ""){
                        valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
                        var repeticiones = 0;
                        $(".id-pr").each(function (i,el) {
                            if($(el).val() == valor)repeticiones++;
                        })

                        if(repeticiones == 1) {
                            for (i in productos) {
                                if (valor == productos[i].id) {
                                    productos.splice(i, 1);
                                }
                            }
                        }
                    }
                    $.extend(data.producto,{
                        filaProd:numero_fila_actual_producto
                    });

                    for (i in productos) {
                        if (data.producto.id == productos[i].id) {
                            productos.splice(i, 1);
                        }
                    }
                    productos.push(data.producto);

                    //console.log(productos);


                    if(localStorage.loadPedido){
                        var aux_pr = {};
                        for(i in productos){
                            var productoObj={
                                'barcode':productos[i].barcode,
                                'IdProducto':productos[i].id,
                                'Nombre':productos[i].nombre,
                                'Stock':productos[i].stock,
                                'Cantidad':"1",
                                'Precio': parseFloat(productos[i].precio_costo),
                                'Sigla':productos[i].sigla,
                                'Iva':productos[i].iva,
                                'utilidad':productos[i].utilidad
                            };
                            aux_pr[productos[i].id] = productoObj;
                        }
                        localStorage.productosC = JSON.stringify(aux_pr);
                    }
                    if (sessionStorage.lista_factura_abierta){

                    }

                    if(accion == 'barCode'){
                        $('#' + fila_ID).each(function(){
                            $(this).children().children('.barcode').prop('disabled', true);
                            $(this).children().next().children().val(data.producto.nombre);//producto
                            $(this).children().next().children(".id-pr").val(data.producto.id);//unidad
                            $(this).children().next().next().children(".unidad").text(data.producto.sigla);//unidad
                            $(this).children().next().next().next().children(".cantidad").val("1").data("max",data.producto.stock).attr("id", data.producto.id).addClass('producto_' + data.producto.id);//cantidad
                            var valor_unitario = parseFloat(data.producto.precio_costo)+((parseFloat(data.producto.precio_costo) * parseFloat(data.producto.utilidad))/100);
                            var valor_con_iva = valor_unitario + ((valor_unitario * data.producto.iva)/100);
                            $(this).children().next().next().next().children(".vlr-unitario").text("$ "+formato_numero(valor_con_iva,2,',','.'));
                            $(this).children().next().next().next().next().children(".vlr-total").text("$ "+formato_numero(valor_con_iva,2,',','.'));
                        });
                    }else{
                        fila_ID = $(input_nombre).parent().parent().attr('id');
                        $(input_nombre).parent().parent().children("td").children(".barcode").eq(0).val(data.producto.barcode).prop( "disabled", true );
                        $(input_nombre).val(data.producto.nombre);
                        $(input_nombre).parent().children(".id-pr").eq(0).val(data.producto.id);
                        $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
                        $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).data("max",data.producto.stock).attr("id", data.producto.id).addClass('producto_' + data.producto.id);
                        $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
                        var valor_unitario = parseFloat(data.producto.precio_costo)+((parseFloat(data.producto.precio_costo) * parseFloat(data.producto.utilidad))/100);
                        var valor_con_iva = valor_unitario + ((valor_unitario * data.producto.iva)/100);
                        $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ "+formato_numero(valor_con_iva,2,',','.'));
                        $(input_nombre).parent().parent().children("td").children(".vlr-total").eq(0).text("$ "+formato_numero(valor_con_iva,2,',','.'));

                    }
                    if($('#'+ fila_ID).closest("tr").is(":last-child")){

                        agregarElementoFactura();
                    }else{
                        $('input:enabled.barcode').first().focus();
                    }

                    valoresFactura();
                    input_nombre = null;
                    $("#modal-detalles-factura").closeModal();
                    /*nuevo*/
                    //if (accion != 'barCode'){
                    obtenerMaximoTotalStockProductos(data.producto.id, fila_ID, 'cantidad');
                    //}
                } else {
                    Materialize.toast(data.mensaje, 4000,'red');
                }

                $("#progress-detalles-factura-modal").addClass("hide");
                $("#contenedor-botones-detalles-factura-modal").removeClass("hide");
            })
        }
    }
}
/*function seleccionProducto(){
 var id = $('input:radio[name=radio-producto]:checked').val();
 if(typeof id == "undefined"){
 alert("Seleccione un producto");
 }else{
 var continuar = true;
 for(i in productos){
 if(productos[i].id == id){
 alert("El producto seleccionado ya ha sido agregado a la factura");
 continuar = false;
 }
 }

 if(continuar) {
 var url = $("#base_url").val() + "/productos/datos-producto";
 var params = {_token: $("#general-token").val(), id: id};
 $("#progress-detalles-factura-modal").removeClass("hide");
 $("#contenedor-botones-detalles-factura-modal").addClass("hide");
 $.post(url, params, function (data) {
 if (data.success) {
 //console.log($(input_nombre).val())
 if($(input_nombre).val() != ""){
 valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
 for(i in productos){
 if(valor == productos[i].id){
 productos.splice(i,1);
 }
 }
 }

 productos.push(data.producto);
 if(localStorage.loadPedido){
 var aux_pr = {};
 for(i in productos){
 var productoObj={
 'IdProducto':productos[i].id,
 'Nombre':productos[i].nombre,
 'Stock':productos[i].stock,
 'Cantidad':"1",
 'Precio': parseFloat(productos[i].precio_costo),
 'Sigla':productos[i].sigla,
 'Iva':productos[i].iva,
 'utilidad':productos[i].utilidad
 };
 aux_pr[productos[i].id] = productoObj;
 }
 localStorage.productosC = JSON.stringify(aux_pr);
 }
 $(input_nombre).val(data.producto.nombre);
 $(input_nombre).parent().children(".id-pr").eq(0).val(data.producto.id);
 $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
 $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).attr("data-max",data.producto.stock);
 $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
 var valor_unitario = parseFloat(data.producto.precio_costo)+((parseFloat(data.producto.precio_costo) * parseFloat(data.producto.utilidad))/100);
 $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ "+formato_numero(valor_unitario,2,',','.'));
 $(input_nombre).parent().parent().children("td").children(".iva").eq(0).text(formato_numero(data.producto.iva,2,',','.')+"%");
 $(input_nombre).parent().parent().children("td").children(".vlr-total").eq(0).text("$ "+formato_numero(valor_unitario,2,',','.'));

 valoresFactura();
 input_nombre = null;
 $("#modal-detalles-factura").closeModal();
 } else {
 alert("Ocurrió un error al seleccionar el producto, por favor intente nuevamente");
 }

 $("#progress-detalles-factura-modal").addClass("hide");
 $("#contenedor-botones-detalles-factura-modal").removeClass("hide");
 })
 }
 }
 }*/

function valoresFactura() {
    //return true;
    var subtotal = 0;
    var total = 0;
    var iva = 0;
    var productos_analizados = [];
    for(i in productos){
        if($.inArray(productos[i].id, productos_analizados) == -1) {
            productos_analizados.push(productos[i].id);
            var precio_venta_sin_iva = parseFloat(productos[i].precio_costo) + parseFloat(((productos[i].precio_costo * productos[i].utilidad) / 100));
            subtotal += parseFloat(valorTotalProducto(productos[i].id, precio_venta_sin_iva,productos[i].iva));
            var iva_data = 0;
            if (productos[i].iva != "" && productos[i].iva != null) {
                iva_data = parseFloat(productos[i].iva);
            }
            iva += parseFloat(getIvaProducto(productos[i].id, precio_venta_sin_iva, iva_data));
        }
    }

    total = subtotal + iva;
    //iva = total - subtotal;
    var subtotalStr = formato_numero(subtotal,2,',','.');
    var totalStr = formato_numero(total,2,',','.');
    var ivaStr = formato_numero(iva,2,',','.');
    $("#txt-subtotal").text("$ "+subtotalStr);
    $("#txt-iva").text("$ "+ivaStr);
    $("#txt-total-pagar").text("$ "+totalStr);
    $("#txt-total-pagar-flotante").text("$ "+totalStr);

    if(localStorage.loadPedido){
        var aux_pr = {};
        var storage_productos = JSON.parse(localStorage.productosC);
        for(i in storage_productos){
            var input_n = null;
            $("input.id-pr").each(function(indice){
                if($("input.id-pr").eq(indice).val() == storage_productos[i].IdProducto){
                    input_n = $("input.id-pr").eq(indice).parent().children(".nombre").eq(0);
                }
            })
            var productoObj={
                'IdProducto':storage_productos[i].IdProducto,
                'Nombre':storage_productos[i].Nombre,
                'Stock':storage_productos[i].Stock,
                'Cantidad':$(input_n).parent().parent().children("td").children(".cantidad").eq(0).val(),
                'Precio':storage_productos[i].Precio,
                'Sigla':storage_productos[i].Sigla,
                'Iva':storage_productos[i].Iva,
                'utilidad':storage_productos[i].utilidad
            };

            aux_pr[storage_productos[i].IdProducto] = productoObj;
        }
        localStorage.productosC = JSON.stringify(aux_pr);
    }
    return total;
}

function valorTotalProducto(id_pr,valor,iva){
    var elementos = [];
    var totalProducto = 0;
    $(".id-pr").each(function(indice){
        if($(".id-pr").eq(indice).val() == id_pr){
            elementos.push($(".id-pr").eq(indice));
        }
    })

    $.each(elementos,function(i,el){
        var fila = $(el).parent().parent();
        var cantidad = parseFloat($(fila).children("td").children(".cantidad").eq(0).val());
        var total = cantidad * valor;
        var total_iva = ((total * iva)/100);
        totalProducto += total;
        var totalStr = formato_numero(total + total_iva,2,',','.');
        $(fila).children("td").children(".vlr-total").text("$ "+totalStr);
    })


    return totalProducto;

}

function getIvaProducto(id_pr,valor,iva){
    var elementos = [];
    var total = 0;
    $(".id-pr").each(function(indice){
        if($(".id-pr").eq(indice).val() == id_pr){
            elementos.push($(".id-pr").eq(indice));
        }
    })

    if(iva == "" || iva == null){
        iva = 0;
    }

    $.each(elementos,function(i,el){
        var fila = $(el).parent().parent();
        var cantidad = parseFloat($(fila).children("td").children(".cantidad").eq(0).val());
        var vlrIva = (valor * iva)/100;
        total += (cantidad * vlrIva);
    })

    return total;
}

function pagar(){
    if(validFactura()){
        ConfirmacionRecarga = true;
        if($("#estado").val() == "Pagada"){
            $("#total-pagar-modal").text("$ "+formato_numero(valoresFactura(),2,',','.'));

            total_factura = valoresFactura();
            if (valor_puntos_redimidos > 0){
                $("#puntos-redimidos").removeClass('hide');
                $("#total-puntos-modal").text("$ "+formato_numero(valor_puntos_redimidos,2,',','.'));
            }

            valor_medios_pago = 0;

            $('.valor-medio-pago').each(function (i,el) {
                if($(el).val()) {
                    valor_medios_pago += parseInt($(el).val());
                }
            })
            if(valor_medios_pago >= 0){
                $("#medios-pago").removeClass('hide');
                $("#medios-pago #total-medios-pago").text("$ "+formato_numero(valor_medios_pago,2,',','.'));
            }

            $("#total-pagar-neto").text("$ "+formato_numero(Math.abs(valoresFactura() - valor_puntos_redimidos - valor_medios_pago),2,',','.'));


            $("#efectivo-modal").val("0");
            if ($("#tipo-cliente").val() == 'si')
                $("#btn-redimir-puntos").addClass('hide');

            $("#regreso-modal").text("$ 0");
            $("#modal-pagar").openModal();
            $("#efectivo-modal").focus();
            setTimeout(function(){
                $("#efectivo-modal").focus();
            },500)
        }else if($("#estado").val() == "Pendiente por pagar"){
            $("#modal-pendiente-pagar").openModal();
            //facturar();
        }else if($("#estado").val() == "Pedida"){
            facturar();
        }
    }
}

function validPagar(){
    var efectivo = parseInt($("#efectivo-modal").val());
    var total = valoresFactura();
    total = total - valor_puntos_redimidos;
    if($.isNumeric(efectivo) && efectivo >= 0){
        if(!aplica_descuentos){
            if(parseInt(total) > parseInt(efectivo)){
                if(confirm("El valor ingresado en el campo efectivo debe ser mayor o igual al valor total de la factura. ¿Desea omitir el calculo y facturar?")){
                    $("#modal-pagar").closeModal();
                    facturar();
                }else{
                    $("#efectivo-modal").focus();
                }
            }else{
                $("#modal-pagar").closeModal();
                facturar(false, false, efectivo);
            }
        }else {
            $("#modal-pagar").closeModal();
            facturar(false, false, efectivo);
        }
    }else{
        if(confirm("El valor ingresado en el campo efectivo debe ser positivo. ¿Desea omitir el calculo y facturar?")){
            $("#modal-pagar").closeModal();
            facturar();
        }
    }
}

function facturar(closeModal = false,modalPendientePagar = false,efectivo = null){

    if(closeModal == true){
        $("#modal-pagar").closeModal();
    }
    var enviar = true;

    if ($("#estado").val() == 'Pendiente por pagar'){
        if ($("#dias_credito").val() < 1){
            alert("Los días de crédito deben ser mayor a 1");
            enviar = false;
        }else if ($("#dias_credito").val() > 120){
            alert("La cantidad máxima de días de créditos son 120 días");
            enviar = false;
        }else{
            enviar = true;
        }

        if($("#numero_cuotas").val() <=0){
            alert("El número de cuotas debe ser mayor a 1");
            enviar = false;
        }
    }else if($("#estado").val() == 'Pedida'){
        enviar = true;
    }


    if (enviar){

        if(validFactura()) {
            DialogCargando("Facturando ...");
            $("#contenedor-boton-facturar").addClass("hide");
            $("#progress-detalle-factura").removeClass("hide");
            if (modalPendientePagar) {
                $("#contenedor-botones-pendiente-pagar-modal").addClass("hide");
                $("#progress-pendiente-pagar-modal").removeClass("hide");
            }
            id_cliente = $("#cliente").val();
            var params = {};
            if ($("#estado").val() == "Pendiente por pagar") {
                params.numero_cuotas = $("#numero_cuotas").val();
                params.dias_credito = $("#dias_credito").val();
                params.tipo_periodicidad_notificacion = $("#tipo_periodicidad_notificacion").val();
                params.periodicidad_notificacion = $("#periodicidad_notificacion").val();
                params.fecha_primera_notificacion = $("#fecha_primera_notificacion").val();
            }else if ($("#estado").val() == "Pedida") {
                params.numero_cuotas = '0';
                params.dias_credito = '0';
                params.tipo_periodicidad_notificacion = '';
                params.periodicidad_notificacion = '';
                params.fecha_primera_notificacion = '';
            }

            params.id_cliente = id_cliente;

            var aux = 1;
            $(".id-pr").each(function (indice) {
                var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
                if (cant >= 0.1) {
                    if ($(".id-pr").eq(indice).val()) {
                        params["producto_" + aux] = {producto: $(".id-pr").eq(indice).val(), cantidad: cant};
                        aux++;
                    }
                }
            });

            var url = $("#base_url").val() + "/factura/facturar";
            params._token = $("#general-token").val();
            params.estado = $("#estado").val();

            params.valor_puntos = valor_puntos_redimidos;
            params.token_puntos = token_puntos;
            params.observaciones = $("#observaciones").val();

            //si se ingresan valors en los medios de pago
            if(valor_medios_pago > 0){
                $('.valor-medio-pago').each(function (i,el) {
                    if($(el).val() > 0) {
                        var tp = $(el).data('tipo-pago');
                        params["valor_tp_"+tp] = $(el).val();
                        params["codigo_tp_"+tp] = $('#codigo_tipo_pago_'+tp).val();
                    }
                })
            }

            if(efectivo != null)
                params.efectivo = efectivo;

            $.post(url, params, function (data) {
                if (data.success) {
                    if (localStorage.deletePedido) {
                        if (localStorage.loadPedido)
                            localStorage.removeItem("loadPedido");
                        localStorage.removeItem("deletePedido");
                        localStorage.removeItem("productosC");
                    }
                    if(data.notificacion){
                        var msg = {
                            'ubicacion' : 'notificacion',
                            'mensaje':data.mensajeNotificacion
                        };
                        conn.send(JSON.stringify(msg));
                        localStorage.setItem("notificacion",data.mensajeNotificacion);
                    }
                    window.location.href = data.url;

                    if (sessionStorage.lista_factura_abierta){
                        sessionStorage.removeItem('lista_factura_abierta');
                    }
                }
                $("body").scrollTop(50);
                $("#contenedor-boton-facturar").removeClass("hide");
                $("#progress-detalle-factura").addClass("hide");
                if (modalPendientePagar) {
                    $("#modal-pendiente-pagar").closeModal();
                    $("#contenedor-botones-pendiente-pagar-modal").removeClass("hide");
                    $("#progress-pendiente-pagar-modal").addClass("hide");
                }
            }).error(function (jqXHR, error, state) {
                CerrarDialogCargando();
                $("#contenedor-boton-facturar").removeClass("hide");
                $("#progress-detalle-factura").addClass("hide");
                if (modalPendientePagar) {
                    $("#contenedor-botones-pendiente-pagar-modal").removeClass("hide");
                    $("#progress-pendiente-pagar-modal").addClass("hide");
                    mostrarErrores("contenedor-errores-pendiente-pagar", JSON.parse(jqXHR.responseText));
                } else {
                    mostrarErrores("contenedor-errores-detalle-factura", JSON.parse(jqXHR.responseText));
                }
            });
        }
    }else{
        $("#dias_credito").val('30');
    }
}

function validFactura(){
    id_cliente = $("#cliente").val();
    var error = false;
    var mensaje = "";
    var params = {};
    params.id_cliente = id_cliente;

    $(".cantidad").each(function(i,el){
        var id_producto = $(".cantidad").eq(i).parent().parent().children("td").children(".id-pr").eq(0);
        if(!$(id_producto).val()) {
            var trash = $(id_producto).parent().parent().children("td").children(".fa-trash").eq(0);
            eliminarFila(trash);
        }else {
            var valor = $(".cantidad").eq(i).val();
            var max = $(".cantidad").eq(i).data("max");
            if (valor && max) {
                if (parseFloat(max) < parseFloat(valor)) {
                    error = true;
                    mensaje = "La cantidad de " + productos[i].nombre + " es incorrecta, el máximo valor permitido para este producto es " + max;
                    return false;
                }
            } else {
                error = true;
                mensaje = "La información es incorrecta";
            }
        }
    })

    //se ha establecido el cliente
    if(id_cliente.length){
        //se ha seleccionado por lo menos un producto
        if($(".id-pr").eq(0).val()){
            //todos los productos tienen cantidades
            var aux = 1;
            $(".id-pr").each(function(indice){
                var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
                if(cant >= 0.1){
                    if(!$(".id-pr").eq(indice).val()) {
                        error = true;
                        mensaje = "La información que intenta enviar es incorrecta, verifique que no existan campos vacios en la lista";
                    }
                }else{
                    error = true;
                    mensaje = "Todos los productos deben tener una cantidad mayor o igual a 1";
                }
            });
        }else{
            error = true;
            mensaje = "Seleccione por lo menos un producto";
        }
    }else{
        error = true;
        mensaje = "No se ha establecido ningún cliente.";
    }

    if(error){
        mostrarErrores("contenedor-errores-detalle-factura",{"error":[mensaje]});
        $("html, body").animate({
            scrollTop: 50
        }, 600);
        return false;
    }
    return true;
}

function anular(){
    $("#contenedor-botones-anular-factura").addClass("hide");
    $("#progress-anular-factura").removeClass("hide");
    var url = $("#base_url").val()+"/factura/anular";
    var params = {"_token":$("#general-token").val(),"id":$("#id").val()};
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-anular-factura").removeClass("hide");
        $("#progress-anular-factura").addClass("hide");
    }).error(function(jqXHR,error,state){
        mostrarErrores("contenedor-errores-anular-factura",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-anular-factura").removeClass("hide");
        $("#progress-anular-factura").addClass("hide");
    })
}

function guardarCliente(){
    var params = $("#modal-crear-cliente #form-cliente").serialize();
    var url = $("#base_url").val()+"/factura/store-cliente";
    $("#contenedor-botones-modal-crear-cliente").addClass("hide");
    $("#progress-modal-crear-cliente").removeClass("hide");

    $.post(url,params,function (data) {
        if(data.success){
            $("#modal-crear-cliente #form-cliente #nombre").val("");
            $("#modal-crear-cliente #form-cliente #identificacion").val("");
            $("#modal-crear-cliente #form-cliente #telefono").val("");
            $("#modal-crear-cliente #form-cliente #correo").val("");
            $("#modal-crear-cliente #form-cliente #direccion").val("");
            mostrarConfirmacion("contenedor-confirmacion-detalle-factura",{"msj":["El cliente ha sido registrado con éxito."]});
            clientes = [data.cliente];
            seleccionCliente(data.cliente.id);
            $("#modal-crear-cliente").closeModal();
        }
        $("#contenedor-botones-modal-crear-cliente").removeClass("hide");
        $("#progress-modal-crear-cliente").addClass("hide");
    }).error(function (jqXHR,error,state) {
        $("#contenedor-botones-modal-crear-cliente").removeClass("hide");
        $("#progress-modal-crear-cliente").addClass("hide");
        mostrarErrores("contenedor-errores-modal-crear-cliente",JSON.parse(jqXHR.responseText));
    })
}

function cargarEditarCliente(){
    $("#contenedor-botones-cliente .fa-spin").removeClass("hide");
    $("#contenedor-botones-cliente-up .fa-spin").removeClass("hide");
    var params = {cliente_id:$("#cliente").val(),_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/factura/form-editar-cliente";
    $.post(url,params,function(data){
        $("#contenedor-botones-cliente .fa-spin").addClass("hide");
        $("#contenedor-botones-cliente-up .fa-spin").addClass("hide");
        $("#modal-editar-cliente #contenedor-datos-cliente-modal").html(data);
        $("#modal-editar-cliente").openModal();
        inicializarMaterialize();
    }).error(function (jqXHR,error,state) {
        window.location.reload(true);
    })
}

function editarCliente(){
    var params = $("#modal-editar-cliente #form-cliente").serialize();
    var url = $("#base_url").val()+"/factura/update-cliente";
    $("#contenedor-botones-modal-editar-cliente").addClass("hide");
    $("#progress-modal-editar-cliente").removeClass("hide");

    $.post(url,params,function (data) {
        if(data.success){
            
            var url = $("#base_url").val()+"/factura/total-cliente";
            var params = {filtro:"",_token:$("#general-token").val()};
            $.post(url,params,function(data) {
                console.log(data)
                if (data.success) {
                    if (data.clientes.length) {
                        clientes = data.clientes;

                        seleccionCliente($("#cliente").val());
                        $("#modal-editar-cliente").closeModal();
                        mostrarConfirmacion("contenedor-confirmacion-detalle-factura",["El cliente ha sido editado con éxito."]);
                    }
                }
            });
        }
        $("#contenedor-botones-modal-editar-cliente").removeClass("hide");
        $("#progress-modal-editar-cliente").addClass("hide");
    }).error(function (jqXHR,error,state) {
        $("#contenedor-botones-modal-editar-cliente").removeClass("hide");
        $("#progress-modal-editar-cliente").addClass("hide");
        mostrarErrores("contenedor-errores-modal-editar-cliente",JSON.parse(jqXHR.responseText));
    })
}

function loadPedido(){

    if(localStorage.productosC){

        var productos_aux = JSON.parse(localStorage.productosC);
        var aux = 0;
        productos = [];
        for(var pr in productos_aux){
            aux++;
            var producto = {id:productos_aux[pr].IdProducto,nombre:productos_aux[pr].Nombre,stock:productos_aux[pr].Stock,sigla:productos_aux[pr].Sigla,precio_costo:productos_aux[pr].Precio,iva:productos_aux[pr].Iva,utilidad:productos_aux[pr].utilidad};
            if(aux == 1){
                loadProducto(producto,$("#tabla-detalle-producto tbody tr td .nombre").eq(0),productos_aux[pr].Cantidad,productos_aux[pr].Stock);
            }else{
                agregarElementoFactura();
                var last = $("#tabla-detalle-producto tbody tr td .nombre").length - 1;
                loadProducto(producto,$("#tabla-detalle-producto tbody tr td .nombre").eq(last),productos_aux[pr].Cantidad,productos_aux[pr].Stock);
            }
        }
        valoresFactura();
    }
}

function loadProducto(producto,input_nombre_aux,cantidad,stock){
    var precioSinIva = parseFloat(producto.precio_costo)+parseFloat(((producto.precio_costo*producto.utilidad)/100));
    var totalSinIva = precioSinIva * parseFloat(cantidad);
    $(input_nombre_aux).val(producto.nombre);
    $(input_nombre_aux).parent().children(".id-pr").eq(0).val(producto.id);
    $(input_nombre_aux).parent().parent().children("td").children(".cantidad").eq(0).val(cantidad);
    $(input_nombre_aux).parent().parent().children("td").children(".cantidad").eq(0).attr("data-max",stock);
    $(input_nombre_aux).parent().parent().children("td").children(".unidad").eq(0).text(producto.sigla);
    $(input_nombre_aux).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ "+formato_numero(precioSinIva,2,',','.'));
    var iva_data = 0;
    if(productos.iva != "" && productos.iva != null){
        iva_data = parseFloat(producto.iva);
    }
    $(input_nombre_aux).parent().parent().children("td").children(".iva").eq(0).text(formato_numero(iva_data,2,',','.')+"%");
    $(input_nombre_aux).parent().parent().children("td").children(".vlr-total").eq(0).text("$ "+formato_numero(totalSinIva,2,',','.'));
    var x = parseInt(fila) - 1;
    $.extend(producto,{
        filaProd:'filaProd_' + x
    });
    productos.push(producto);
    $('#estado-factura').html('' +
        '<i class="material-icons prefix red-text">check_box</i>' +
        '<input type="text" name="estado" id="estado" value="Pedida" readonly>');
}
function maxNumeroCuotas() {
    var numero_coutas = $("#numero_cuotas").val();

    if (numero_coutas > 12){
        alert("El número máximo de cuotas son 12");
        $("#numero_cuotas").val('1');
    }

}
function cargarDatosNotificacion() {
    dias_credito = $("#dias_credito").val();
    numero_cuotas = $("#numero_cuotas").val();
    tipo_periodicidad_notificacion = $("#tipo_periodicidad_notificacion").val();
    fecha_primera_notificacion = $("#fecha_primera_notificacion").val();
    estado_factura = "Pedida";

    if (tipo_periodicidad_notificacion != ''){
        if (numero_cuotas > 0 || numero_cuotas <= 12){
            if (dias_credito > 0){
                if (dias_credito <= 120){
                    $("#modal-forma-abono").closeModal();
                    $("#modal-pendiente-pagar").closeModal();
                    $("#modal-abonos").openModal(
                        {complete: function() {window.location.reload(true); }}
                    );
                    $("#num-coutas").html(numero_cuotas);
                }else {
                    alert('Los dias de credito son máximo 120 dias');
                }
            }else{
                alert('Los dias de credito son minimo de 1 dia');
            }

        }else {
            alert('La cantidad de cuotas debe ser mayor que cero y menor o igual a 12');
            $("#numero_cuotas").val('1');
        }
    }else {
        alert("Debe seleccionar un tipo de periodicidad para las notificaciones");
    }
}

function abrirModalAbonos(id, estado) {
    if (estado == 'Pedida'){
        $("#modal-abonos").closeModal();
        $("#modal-pendiente-pagar").openModal(
            {complete: function() {window.location.reload(true); }}
        );
    }else{
        $("#modal-pendiente-pagar").closeModal();
        $("#modal-abonos").animate({"max-height":"220px"},500);
        $("#load-info-abonos").removeClass("hide");
        $("#info-abonos").addClass("hide");
        $("#contenedor-botones-abonos").addClass("hide");
        $("#progress-abonos").addClass("hide");
        $("#modal-abonos").openModal(
            {complete: function() {window.location.reload(true); }}
        );
    }
    var params = {_token:$("#general-token").val(),id:id,estado:estado};
    var url = $("#base_url").val()+"/factura/abonos";
    $.post(url,params,function (data) {
        $("#info-abonos").html(data);

        $("#modal-abonos").animate({"max-height":"70%"},500);
        $("#load-info-abonos").addClass("hide");
        $("#info-abonos").removeClass("hide");
        $("#contenedor-botones-abonos").removeClass("hide");
        $("#progress-abonos").addClass("hide");
        inicializarMaterialize();
    })
}

function guardarAbono(){
    if($('#form-abono').length > 0) {
        var url = $("#base_url").val() + "/factura/store-abono";
        var params = $("#form-abono").serialize();

        if (estado_factura == 'Pedida') {
            params = params + '&dias_credito=' + dias_credito + '&numero_cuotas=' + numero_cuotas +
                '&tipo_periodicidad_notificacion=' + tipo_periodicidad_notificacion +
                '&fecha_primera_notificacion=' + fecha_primera_notificacion + '&estado=' + estado_factura;
        }

        $("#contenedor-botones-abonos").addClass("hide");
        $("#progress-abonos").removeClass("hide");
        $.post(url, params, function (data) {
            if (data.success) {
                window.location.reload(true);
            }
            $("#contenedor-botones-abonos").removeClass("hide");
            $("#progress-abonos").addClass("hide");
        }).error(function (jqXHR, error, status) {
            mostrarErrores("contenedor-errores-abonos", JSON.parse(jqXHR.responseText));
            $("#contenedor-botones-abonos").removeClass("hide");
            $("#progress-abonos").addClass("hide");
        })
    }else{
        $('#modal-abonos').closeModal();
    }
}
function calcularRegreso(efectivo_cliente,total_pagar) {
    var efectivo_cliente = parseInt(efectivo_cliente);
    var total_pagar = parseInt(total_pagar);
    var regreso = efectivo_cliente - total_pagar;
    if(total_pagar <= efectivo_cliente){
        $("#regreso-modal-cliente").text("$ "+formato_numero((regreso),2,',','.'));
        $("#mensaje-efectivo").html("");
    }else{
        $("#regreso-modal-cliente").text("$ 0");
        $("#mensaje-efectivo").html("El efectivo es insuficiente para realizar el pago");
    }
}
function estadoFactura(id_factura,estado,total_pagar_cliente) {

    var total_a_pagar = parseFloat(total_pagar_cliente);

    if (window.confirm("Realmente quiere pagar la totalidad de la factura?")) {
        $("#total-pagar-modal-cliente").text("$ "+formato_numero(total_a_pagar,2,',','.'));
        $("#div-efectivo-cliente").html("<strong>Efectivo </strong><input type='text' class='num-entero' value='0' name='efectivo_cliente' id='efectivo_cliente' onblur='calcularRegreso(this.value,"+total_a_pagar+")'>" +
            "<input type='hidden' id='total_a_pagar' value='"+total_a_pagar+"'><input type='hidden' id='id_factura' value='"+id_factura+"'>" +
            "<input type='hidden' id='estado' value='"+estado+"'>");
        $("#regreso-modal-cliente").text("$ 0");
        $("#modal-pagar-cliente").openModal(
            {complete: function() {window.location.reload(true); }}
        );

    }else{
        if(!$("#"+id_factura).is(':checked')) {
            //alert("Está activado");
            $("#"+id_factura).prop("checked", "checked");
        } else {
            //alert("No está activado");
            $("#"+id_factura).prop("checked", "");
        }
    }
}

function validarPagoFactura(){
    var efectivo = $("#efectivo_cliente").val();
    var total = $("#total_a_pagar").val();

    if(total <= efectivo){
        $("#modal-pagar-cliente").closeModal();
        ejecutarCambioEstadoFactura();
    }else{
        $("#mensaje-efectivo").html("El efectivo es insuficiente para realiza el pago");
    }
}
function ejecutarCambioEstadoFactura() {
    var url = $("#base_url").val()+"/factura/estado";
    var token = $("#general-token").val();

    var params = {};
    params.id_factura = $("#id_factura").val();
    params.estado = $("#estado").val()
    params.total_a_pagar = $("#total_a_pagar").val();
    $.ajax({
        url: url,
        headers: {'X-CSRF-TOKEN': token},
        type: 'POST',
        dataType: 'json',
        data:params,

        success: function (data) {
             //console.log(data.response);
             $("#modal-pagar-cliente").closeModal();
             $("#columna-estado-"+$("#id_factura").val()).html('<i class="material-icons prefix green-text tooltipped" data-tooltip="Pagada">check_circle</i>');
             //alert(data.response);
             mostrarConfirmacion("contenedor-confirmacion-facturas",{"dato":[data.response]});
             //inicializarMaterialize();
            window.location.reload(true);
         },
         error: function (data,jqXHR) {
             //console.log('Error:', data);
             mostrarErrores("contenedor-errores-facturas",JSON.parse(jqXHR.responseText));
             //alert(data.response);
             window.location.reload(true);
        }
    });
}

function setFocus(input_ID){
    //console.log('setfocus fila = ' + input_ID)
    $('#' + input_ID).focus();
    $('input:enabled.barcode').first().focus();
    //console.log($('input:enabled.barcode'))
}
function obtenerMaximoTotalStockProductos(id, fila, accion){
    var total = 0;
    var cantDisponible = 0
    var botonEliminar = "";

    setTimeout(function(){
        if(fila != undefined || fila != ''){

            $('#' + fila).each(function() {
                id = $(this).children().next().children(".id-pr").val();
                botonEliminar = $(this).children().next().next().next().next().next().children(".fa-trash").attr('id');

            });
        }
        $('.producto_' + id).each(function(){
            total = total + parseFloat($(this).val());
            if (parseFloat(total) <= parseFloat($('#'+id).data("max"))){
                cantDisponible = total;
            }
        });
        //alert(total+"+++++++"+$('#'+id).data("max"));
        if (parseFloat(total) > parseFloat($('#'+id).data("max"))){
            var totalDisponible =  parseFloat($('#'+id).data("max"));
            var cantdisponible =  parseFloat($('#'+id).data("max")) - cantDisponible;
            Materialize.toast("El valor ingresado es incorrecto, Cantidad disponible " +  cantdisponible + " de " + totalDisponible , 4000,'red');
            $('#' + botonEliminar).click();
            if(accion == 'barCode'){
                // $('#' + botonEliminar).click();
            }else if(accion == 'cantidad'){
                $('#' + id).focus()
            }
        }
    }, 500);
    CerrarDialogCargando()
}
function generarFacturaAbierta() {
    if (validFacturaAbierta()){
        var i = 0;
        var n = 0;
        var params = [];
        var array_productos = [];

        $(".id-pr").each(function (indice) {
            var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
            if (cant >= 0.1) {
                if ($(".id-pr").eq(indice).val()) {
                    var existe = false;
                    for(var k in  array_productos){
                        if (array_productos[k].id == $(".id-pr").eq(indice).val()){
                            array_productos[k].cantidad += cant;
                            existe = true;
                        }
                    }
                    if(!existe){
                        var producto = "";
                        for(var x in  productos){
                            if (productos[x].id == $(".id-pr").eq(indice).val()){
                                producto = productos[x];
                                producto.cantidad = cant;
                            }
                        }
                        array_productos.push(producto);
                    }
                    params[i] = {producto: productos[i], cantidad: cant};
                    i++;
                }
            }
        });

        var data = JSON.stringify(array_productos);
        //localStorage.lista_factura_abierta = data;
        sessionStorage.lista_factura_abierta = data;
        window.location.href = $("#base_url").val()+"/factura/create";

    }
}
function eliminateDuplicates(arr) {
    var i,
        len=arr.length,
        out=[],
        obj={};

    for (i=0;i<len;i++) {
        obj[arr[i]]=0;
    }
    for (i in obj) {
        out.push(i);
    }
    return out;
}
function venderFacturaAbierta(){
    if(validFacturaAbierta()){
        $("#total-pagar-modal").text("$ "+formato_numero(valoresFactura()
                ,2,',','.'));
        $("#efectivo-modal").val("0");
        $("#regreso-modal").text("$ 0");
        $("#modal-pagar").openModal();
    }
}

function validFacturaAbierta(){
    var error = false;
    var mensaje = "";
    var params = {};

    $(".cantidad").each(function(i,el){
        var id_producto = $(".cantidad").eq(i).parent().parent().children("td").children(".id-pr").eq(0);
        if(!$(id_producto).val()) {
            var trash = $(id_producto).parent().parent().children("td").children(".fa-trash").eq(0);
            eliminarFila(trash);
        }else {
            var valor = $(".cantidad").eq(i).val();
            var max = $(".cantidad").eq(i).data("max");
            if (valor && max) {
                if (parseFloat(max) < parseFloat(valor)) {
                    error = true;
                    mensaje = "La cantidad de " + productos[i].nombre + " es incorrecta, el máximo valor permitido para este producto es " + max;
                    return false;
                }
            } else {
                error = true;
                mensaje = "La información es incorrecta";
            }
        }
    })

    //se ha seleccionado por lo menos un producto
    if($(".id-pr").eq(0).val()){
        //todos los productos tienen cantidades
        var aux = 1;
        $(".id-pr").each(function(indice){
            var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
            if(cant >= 0.1){
                if(!$(".id-pr").eq(indice).val()) {
                    error = true;
                    mensaje = "La información que intenta enviar es incorrecta, verifique que no existan campos vacios en la lista";
                }
            }else{
                error = true;
                mensaje = "Todos los productos deben tener una cantidad mayor o igual a 1";
            }
        });
    }else{
        error = true;
        mensaje = "Seleccione por lo menos un producto";
    }


    if(error){
        mostrarErrores("contenedor-errores-detalles-factura",{"error":[mensaje]});
        $("html, body").animate({
            scrollTop: 50
        }, 600);
        return false;
    }
    return true;
}

function validPagarFacturaAbierta(){
    var efectivo = parseInt($("#efectivo-modal").val());
    var total = valoresFactura();
    if(total <= efectivo){
        $("#modal-pagar").closeModal();
        facturarFacturaAbierta();
    }else{
        if(confirm("El valor ingresado en el campo efectivo debe ser mayor al valor total de la factura. ¿Desea ignorar esto y facturar?")){
            $("#modal-pagar").closeModal();
            facturarFacturaAbierta();
        }
    }
}

function facturarFacturaAbierta(cerrar_modal_calculo = false){
    if(cerrar_modal_calculo){
        $("#modal-pagar").closeModal();
    }
    var enviar = true;

    if(validFacturaAbierta()) {
        $("#contenedor-botones-factura-abiera").addClass("hide");
        $("#progress-detalle-factura-abierta").removeClass("hide");

        var params = {};

        var aux = 1;
        $(".id-pr").each(function (indice) {
            var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
            if (cant >= 0.1) {
                if ($(".id-pr").eq(indice).val()) {
                    params["producto_" + aux] = {producto: $(".id-pr").eq(indice).val(), cantidad: cant};
                    aux++;
                }
            }
        });

        var url = $("#base_url").val() + "/factura/facturar-factura-abierta";
        params._token = $("#general-token").val();
        params.estado = "abierta";


        $.post(url, params, function (data) {
            if (data.success) {
                window.location.href = $("#base_url").val()+"/factura/detalle-factura-abierta";
            }
            $("body").scrollTop(50);

        }).error(function (jqXHR, error, state) {

            $("#contenedor-botones-factura-abiera").removeClass("hide");
            $("#progress-detalle-factura-abierta").addClass("hide");
            mostrarErrores("contenedor-errores-detalles-factura", JSON.parse(jqXHR.responseText));
        });
    }
}

function eliminarFila(trash){
    if($("#tabla-detalle-producto tbody tr").length < 2){
        mostrarErrores("contenedor-errores-detalles-factura",{"1":["No puede eliminar el elemento, una factura debe tener por lo menos un elemento en la lista de detalles."]});
    }else {
        idFila = $(trash).parent().parent().attr('id');
        id = $(trash).parent().parent().children("td").children(".id-pr").eq(0).val();
        var eliminar = true;
        if(id != "" && typeof id != "undefined") {
            encontrado = false;
            var repeticiones = 0;
            $(".id-pr").each(function (ind,el) {
                if($(el).val() == id)repeticiones++;
            })

            if(repeticiones == 1) {
                for(i in productos){
                    if(productos[i].id == id && productos[i].filaProd == idFila){
                        productos.splice(i,1);
                        encontrado = true;
                    }
                }

                if(!encontrado){
                    eliminar = false;
                    alert("El producto no pudo ser eliminado, la información analizada no coincide");
                }else{
                    if(localStorage.loadPedido){
                        var aux_pr = JSON.parse(localStorage.productosC);
                        for(i in aux_pr){
                            if(aux_pr[i].IdProducto == id){
                                delete aux_pr[i];
                            }
                        }
                        localStorage.productosC = JSON.stringify(aux_pr);
                    }
                }
            }else{
                eliminar = true;
            }
        }
        if(eliminar) {
            $(trash).parent().parent().remove();
        }
        if (sessionStorage.lista_factura_abierta){
            sessionStorage.removeItem('lista_factura_abierta');
            var i = 0;
            var params = [];
            $(".id-pr").each(function (indice) {
                var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
                if (cant >= 0.1) {
                    if ($(".id-pr").eq(indice).val()) {
                        params[i] = {producto: productos[i], cantidad: cant};
                        i++;
                    }
                }
            });
            var data = JSON.stringify(params);
            sessionStorage.lista_factura_abierta = data;
        }
        valoresFactura();
    }
}

function showPuntosCliente(){
    if(clienteSeleccionado != null && clienteSeleccionado.predeterminado != "si"){

        if(clienteSeleccionado.valor_puntos > 0) {
            $("#modal-puntos #texto-puntos").eq(0).html("<strong>Cliente: </strong>" + clienteSeleccionado.nombre + " - " + clienteSeleccionado.tipo_identificacion + ": " + clienteSeleccionado.identificacion);
            $("#modal-puntos #texto-valor-puntos").eq(0).html("<strong>Valor puntos: </strong>$ " + formato_numero(clienteSeleccionado.valor_puntos,2,',','.'));
            $("#modal-puntos #texto-total-factura").eq(0).html("<strong>Total factura: </strong>$ " + formato_numero(total_factura-valor_medios_pago,2,',','.'));

            /*if (clienteSeleccionado.valor_puntos <= total_factura){
             //redime todos
             $("#modal-puntos #redimir").attr("disabled", true);
             }*/

            if (clienteSeleccionado.valor_puntos < total_factura)
                $("#modal-puntos #valor").eq(0).val(parseFloat(clienteSeleccionado.valor_puntos).toFixed(2));
            else
                $("#modal-puntos #valor").eq(0).val(parseFloat(total_factura).toFixed(2) - valor_medios_pago);

            $("#modal-pagar").closeModal();
            $("#modal-puntos").openModal();
            $("#modal-puntos #valor").focus();
        }else{
            $("#modal-puntos #texto-puntos").eq(0).html("");
            $("#modal-puntos #texto-valor-puntos").eq(0).html("");
            $("#modal-puntos #valor").eq(0).val(0);
            alert("El cliente seleccionado no contiene puntos para redimir");
        }
    }else{
        $("#modal-pagar").closeModal();
        $("body,html").animate({
            scrollTop:'10px'
        },500);
        mostrarErrores("contenedor-errores-detalle-factura",["Para utilizar el modo de pago de puntos seleccione un cliente diferente a su cliente predeterminado"]);
    }
}

function showMediosPago(){
    $("#modal-pagar").closeModal();
    $("#modal-medios-pago").openModal();
}

function redimir(){
    if(clienteSeleccionado != null && clienteSeleccionado.predeterminado != "si") {
        if($("#modal-puntos #valor").eq(0).val() > 0) {
            DialogCargando("Generando token de puntos ...");
            var params = {
                _token: $("#general-token").val(),
                cliente: clienteSeleccionado.id,
                valor: $("#modal-puntos #valor").eq(0).val()
            };

            var url = $("#base_url").val()+"/factura/generar-token-puntos";

            $.post(url,params,function(data){
                if(data.success){
                    var token = data.token;
                    token_puntos = token;
                    $("#datos_token #nombre_negocio").text(token.nombre_negocio);
                    $("#datos_token #fecha").text(token.fecha_vigencia);
                    $("#datos_token #token").text(token.token);
                    $("#datos_token #valor").text("$ "+formato_numero(token.valor,0,',','.'));
                    $("#datos_token #valido").text(token.fecha_vigencia);
                    //imprimir("datos_token");
                    valor_puntos_redimidos = $("#modal-puntos #valor").eq(0).val();
                    $("#modal-puntos").closeModal();
                    //$("#modal-pagar").openModal();
                    pagar();
                }
                CerrarDialogCargando();
            }).error(function(jqXHR,state,error){
                $("#modal-puntos").closeModal();
                $("body,html").animate({
                    scrollTop:'10px'
                },500);
                mostrarErrores("contenedor-errores-detalle-factura",JSON.parse(jqXHR.responseText));
                CerrarDialogCargando();
            })
        }else{
            alert("Ingrese el valor en pesos que desea redimir");
        }
    }
}
function removeDuplicates(originalArray, prop) {
    var newArray = [];
    var lookupObject  = {};

    for(var i in originalArray) {
        lookupObject[originalArray[i][prop]] = originalArray[i];
    }

    for(i in lookupObject) {
        newArray.push(lookupObject[i]);
    }
    return newArray;
}
function loadPedidoFacturaAbierta(){

    //var productos_aux = JSON.parse(localStorage.lista_factura_abierta);
    var productos_aux = JSON.parse(sessionStorage.lista_factura_abierta);
    var aux = 0;

    if(sessionStorage.lista_factura_abierta){
        var productos_aux = JSON.parse(sessionStorage.lista_factura_abierta);
        var aux = 0;
        productos = [];
        for(var pr in productos_aux){
            aux++;
            var producto = {
                id:productos_aux[pr].id,
                nombre:productos_aux[pr].nombre,
                precio_costo:productos_aux[pr].precio_costo,
                iva:productos_aux[pr].iva,
                utilidad:productos_aux[pr].utilidad,
                sigla:productos_aux[pr].sigla,
            };
            if(aux == 1){
                loadProductoFacturaAbierta(producto,$("#tabla-detalle-producto tbody tr td .nombre").eq(0),productos_aux[pr].cantidad,productos_aux[pr].stock);
            }else{
                agregarElementoFactura();
                var last = $("#tabla-detalle-producto tbody tr td .nombre").length - 1;
                loadProductoFacturaAbierta(producto,$("#tabla-detalle-producto tbody tr td .nombre").eq(last),productos_aux[pr].cantidad,productos_aux[pr].stock);
            }
        }
        //console.log(producto);
        valoresFactura();
    }

}
function loadProductoFacturaAbierta(producto,input_nombre_aux,cantidad,stock){
    var precioSinIva = parseFloat(producto.precio_costo)+parseFloat(((producto.precio_costo*producto.utilidad)/100));
    var totalSinIva = precioSinIva * parseFloat(cantidad);
    $(input_nombre_aux).val(producto.nombre);
    $(input_nombre_aux).parent().children(".id-pr").eq(0).val(producto.id);
    $(input_nombre_aux).parent().parent().children("td").children(".cantidad").eq(0).val(cantidad);
    $(input_nombre_aux).parent().parent().children("td").children(".cantidad").eq(0).attr("data-max",stock);
    $(input_nombre_aux).parent().parent().children("td").children(".cantidad").eq(0).attr("id",producto.id);
    $(input_nombre_aux).parent().parent().children("td").children(".cantidad").eq(0).addClass('producto_'+producto.id);
    $(input_nombre_aux).parent().parent().children("td").children(".unidad").eq(0).text(producto.sigla);
    var iva_data = 0;
    if(producto.iva != "" && producto.iva != null){
        iva_data = parseFloat(producto.iva);
    }
    $(input_nombre_aux).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ "+formato_numero(precioSinIva+((precioSinIva * iva_data)/100),2,',','.'));
    $(input_nombre_aux).parent().parent().children("td").children(".iva").eq(0).text(formato_numero(iva_data,2,',','.')+"%");
    $(input_nombre_aux).parent().parent().children("td").children(".vlr-total").eq(0).text("$ "+formato_numero(totalSinIva,2,',','.'));
    var x = parseInt(fila) - 1;
    $.extend(producto,{
        filaProd:'filaProd_' + x
    });
    productos.push(producto);


    //console.log(productos);
    if (localStorage.lista_factura_abierta){
        $('#estado-factura').html('' +
            '<i class="material-icons prefix red-text">check_box</i>' +
            '<input type="text" name="estado" id="estado" value="Pagada" readonly>');
    }

}
function borrarFacturaAbierta() {
    if (sessionStorage.lista_factura_abierta){
        sessionStorage.removeItem('lista_factura_abierta');
        window.location.href = $("#base_url").val()+"/factura";
    }
}