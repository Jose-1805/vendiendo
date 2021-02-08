var id_select = 0;
var input_nombre = null;
var productos_traslado = [];
var clientes = null;
var fila = 1;
var numero_fila_actual_producto = "";
var clienteSeleccionado = null;
var total_traslado = 0 ;
var columnDefs = [{},{}];
var permiso_editar = false;

var traslado_detalle = null;
var relacion_detalle = null;
var cliente = null;
var puntos_cliente = 0;
var token_puntos_traslado = null;
var traslado = null;

var valor_puntos_redimidos_traslado = 0;
$(function(){
    cargarTablaTraslados();

    $(".btn-buscar-cliente").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarCliente(input);
    });

    $("#tabla-detalle-producto tbody").on("click","tr td .fa-trash",function(){
        eliminarFilaTraslado($(this));
    })

    $("#tabla-detalle-producto tbody").on("dkeydown","tr td .nombre",function(){
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
        valoresTraslado();
    })

    $("body").on("keyup",".vlr-unitario",function(){
        var id = $(this).parent().parent().children('td').children('.id-pr').val();
        var precio_venta = parseFloat($(this).val());
        if(id && $.isNumeric(precio_venta)) {
            for (i in productos_traslado) {
                if(productos_traslado[i].id == id){
                    _iva = parseFloat(productos_traslado[i].iva);
                    _precio_costo = parseFloat(productos_traslado[i].precio_costo);
                    productos_traslado[i].utilidad = (((precio_venta / ((100 + _iva) / 100)) / _precio_costo) - 1) * 100;
                }
            }
        }
        valoresTraslado();
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

    $("body").on("keypress", function(e) {

        if((e.keyCode == 10 || e.keyCode == 13) && e.ctrlKey){

            var cantidadFilas = $('#tabla-detalle-producto tbody').find('tr').length;
            var valorUltimaFila = ($('#tabla-detalle-producto tbody tr:last-child').children().children().val())

            if(cantidadFilas > 1 && valorUltimaFila == ''){
                $('table tr:last-child').remove();
            }
            guardarTraslado();
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

    $("#btn-limpiar-lista-productos").click(function () {
        if($(".id-pr").eq(0).val()){
            //var nFilas = $("#tabla-detalle-producto >tbody >tr").length;
            $("#tabla-detalle-producto tbody tr").remove();

            agregarElementoTraslado();
            //loadPedidoFacturaAbierta();
            valoresTraslado();

        }else{
            alert("No existe productos en la lista");
        }
    });

    $("#efectivo-modal-traslado").keyup(function(){
        var efectivo = parseInt($(this).val());
        var total = total_traslado;
        total = total - valor_puntos_redimidos_traslado;
        if(total < efectivo){
            $("#regreso-modal-traslado").text("$ "+formato_numero((efectivo-total),0,',','.'));
        }else{
            $("#regreso-modal").text("$ 0");
        }
    })


    $("#traslado-redimir").change(function(){
        if(cliente) {
            if ($(this).val() == 1) {
                if (puntos_cliente < total_traslado)
                    $("#modal-puntos-traslado #valor").eq(0).val(parseFloat(puntos_cliente).toFixed(2));
                else
                    $("#modal-puntos-traslado #valor").eq(0).val(parseFloat(total_traslado).toFixed(2));

                $("#modal-puntos-traslado #valor").eq(0).attr("readonly", "readonly");
            } else {
                if (puntos_cliente < total_traslado)
                    $("#modal-puntos-traslado #valor").eq(0).val(parseFloat(puntos_cliente).toFixed(2));
                else
                    $("#modal-puntos-traslado #valor").eq(0).val(parseFloat(total_traslado).toFixed(2));

                $("#modal-puntos-traslado #valor").eq(0).prop("readonly", false);
            }
        }
    });

    $("body,html").on("click",".historial-traslado",function () {
        cargarHistorial($(this).data("traslado"));
    })

    $(".info-producto").keyup(function(e){
        var cantidad = $(this).val();
        if(cantidad < 0)
            $(this).val("0");
        var maximo = $(this).data("cantidad");
        if(cantidad > maximo){
            $(this).val(maximo);
        }
        var total = 0;
        $(".info-producto").each(function(i,el){
            var cant = $(el).val();
            var valor = $(el).data("valor");
            total += valor * cant;
        })
        $("#valor-total").text(number_format(total,2));
    })

    $('.recibido').keyup(function () {
        var cantidad = $(this).val();
        $('#cant_recibida_'+$(this).data('producto')).text(cantidad);
    })

    $('#btn-procesar-traslado').click(function () {
        procesarTraslado();
    })
})

function cargarTablaTraslados() {
    var url = $("#base_url").val()+"/traslado";
    var checked = "";
    var i=1;
    var TrasladosTabla = $('#TrasladosTabla').dataTable({ "destroy": true });
    TrasladosTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#TrasladosTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
   RemiscionesTabla = $('#TrasladosTabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/traslado/list-traslados",
            "type": "GET"
        },
        "columns": [
            { "data": "id", 'className': "text-center hide" },
            { "data": "numero", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "almacen", 'className': "text-center" },
            { "data": "usuario", "className": "text-center"},
            { "data": "estado", "className": "text-center" },
            { "data": "detalles", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === TrasladosTabla.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [2,4,6,7] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

//POPUP FACTURA
function buscarProductos_(load = null,icon = null,page=null,numero_fila_actual_producto){
    if(load != null)
        $(load).removeClass("hide");
    if(icon != null)
        $(icon).addClass("hide");
    var url = $("#base_url").val()+"/productos/filtro";
    var params = {_token:$("#general-token").val(),vista:"traslados.lista_productos"};//,filtro:$("#busqueda-producto").val(),categoria:$("#categoria").val(),page:page};
    $.post(url,params,function(data){
        $("#contenedor-productos-modal").html(data);
        $("#footer-modal-detalles-factura").removeClass("modal-fixed-footer");
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
            "url": $("#base_url").val()+"/productos/list-factura-productos?"+params+"&stock=true",
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
            $(row).attr('onClick', "checkradioButton('radio-producto-"+data.id+"')");
            if(data.stock > 0) {
                $('td', row).eq(0).html("<p style='margin: 0px !important;padding: 0px !important;'' class='radio_producto'>" +
                "<input type='radio' id='radio-producto-" + data.id + "' name='radio-producto' value='" + data.id + "' onclick=\"seleccionProducto(null,'" + numero_fila_actual_producto + "',null,event);\" >" +
                "<label for='radio-producto-" + data.id + "'></label>" +
                "</p>");
            }else{
                $('td', row).eq(0).html("");
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

function agregarElementoTraslado(){
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
            "<input type='text' class='barcode barCodeProducto' id='barCodeProducto_" + fila + "'  placeholder='Codigo de barras' onchange=\"seleccionProducto('barCode', 'filaProd_" + fila + "', this.id,event);\">" +
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
            "<input type='text' value='1' min='1' class='excepcion num-real center-align cantidad' onblur=\"\">" +
            "</td>" +

            "<td>" +
            "<input type='text' value='1' min='1' class='excepcion num-real center-align vlr-unitario' onblur=\"\">" +
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
                            for (i in productos_traslado) {
                                if (valor == productos_traslado[i].id) {
                                    productos_traslado.splice(i, 1);
                                }
                            }
                        }
                    }
                    $.extend(data.producto,{
                        filaProd:numero_fila_actual_producto
                    });

                    for (i in productos_traslado) {
                        if (data.producto.id == productos_traslado[i].id) {
                            data.producto.utilidad = productos_traslado[i].utilidad;
                            productos_traslado.splice(i, 1);
                        }
                    }
                    if(!$.isNumeric(data.producto.utilidad))data.producto.utilidad = 0;
                    productos_traslado.push(data.producto);


                    if(accion == 'barCode'){
                        $('#' + fila_ID).each(function(){
                            $(this).children().children('.barcode').prop('disabled', true);
                            $(this).children().next().children().val(data.producto.nombre);//producto
                            $(this).children().next().children(".id-pr").val(data.producto.id);//unidad
                            $(this).children().next().next().children(".unidad").text(data.producto.sigla);//unidad
                            $(this).children().next().next().next().children(".cantidad").val("1").data("max",data.producto.stock).attr("id", data.producto.id).addClass('producto_' + data.producto.id);//cantidad
                            var valor_unitario = parseFloat(data.producto.precio_costo);
                            var valor_con_iva = valor_unitario + ((valor_unitario * data.producto.iva)/100);
                            $(this).children().next().next().next().children(".vlr-unitario").val(formato_numero(valor_con_iva,2,'.',''));
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
                        var valor_unitario = parseFloat(data.producto.precio_costo);
                        var valor_con_iva = valor_unitario + ((valor_unitario * data.producto.iva)/100);
                        $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).val(valor_con_iva);
                        $(input_nombre).parent().parent().children("td").children(".vlr-total").eq(0).text("$ "+formato_numero(valor_con_iva,2,',','.'));

                    }

                    if($('#'+ fila_ID).closest("tr").is(":last-child")){

                        agregarElementoTraslado();
                    }else{
                        $('input:enabled.barcode').first().focus();
                    }

                    valoresTraslado();
                    input_nombre = null;
                    $("#modal-detalles-factura").closeModal();
                    /*nuevo*/
                    //if (accion != 'barCode'){
                    //}
                    CerrarDialogCargando();
                } else {
                    CerrarDialogCargando();
                    Materialize.toast(data.mensaje, 4000,'red');
                }

                $("#progress-detalles-factura-modal").addClass("hide");
                $("#contenedor-botones-detalles-factura-modal").removeClass("hide");
            })
        }
    }
}

function valoresTraslado() {
    //return true;
    var subtotal = 0;
    var total = 0;
    var iva = 0;
    var productos_analizados = [];
    for(i in productos_traslado){
        if($.inArray(productos_traslado[i].id, productos_analizados) == -1) {
            productos_analizados.push(productos_traslado[i].id);
            var precio_venta_sin_iva = parseFloat(productos_traslado[i].precio_costo) + parseFloat(((productos_traslado[i].precio_costo * productos_traslado[i].utilidad) / 100));
            subtotal += parseFloat(valorTotalProducto(productos_traslado[i].id, precio_venta_sin_iva,productos_traslado[i].iva));
            var iva_data = 0;
            if (productos_traslado[i].iva != "" && productos_traslado[i].iva != null) {
                iva_data = parseFloat(productos_traslado[i].iva);
            }
            iva += parseFloat(getIvaProducto(productos_traslado[i].id, precio_venta_sin_iva, iva_data));
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

function guardarTraslado(){
    if(validTraslado()){
            DialogCargando("Guardando traslado ...");

            var params = {};

            params.almacen = $("#select_almacen").val();


            var aux = 1;
            $(".id-pr").each(function (indice) {
                var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
                if (cant >= 0.1) {
                    if ($(".id-pr").eq(indice).val()) {
                        var __utilidad = 0;
                        for(i in productos_traslado){
                            if($(".id-pr").eq(indice).val() == productos_traslado[i].id)
                                __utilidad = productos_traslado[i].utilidad;
                        }
                        params["producto_" + aux] = {producto: $(".id-pr").eq(indice).val(), cantidad: cant,utilidad:__utilidad};
                        aux++;
                    }
                }
            });

            var url = $("#base_url").val() + "/traslado/store";
            params.fecha_vencimiento = $("#fecha_vencimiento").val();
            params._token = $("#general-token").val();

            $.post(url, params, function (data) {
                if (data.success) {
                    if(data.notificacion){
                        var msg = {
                            'ubicacion' : 'notificacion',
                            'mensaje':data.mensajeNotificacion
                        };
                        conn.send(JSON.stringify(msg));
                        localStorage.setItem("notificacion",data.mensajeNotificacion);
                    }
                    window.location.reload();

                }
                $("body").scrollTop(50);
            }).error(function (jqXHR, error, state) {
                CerrarDialogCargando();
                $("#contenedor-boton-facturar").removeClass("hide");
                $("#progress-detalle-factura").addClass("hide");
                mostrarErrores("contenedor-errores-detalle-traslado", JSON.parse(jqXHR.responseText));
            });
    }
}

function validTraslado(){
    var error = false;
    var mensaje = "";

    id_almacen = $("#select_almacen").val();

    $(".cantidad").each(function(i,el){
        var id_producto = $(".cantidad").eq(i).parent().parent().children("td").children(".id-pr").eq(0);
        if(!$(id_producto).val()) {
            var trash = $(id_producto).parent().parent().children("td").children(".fa-trash").eq(0);
            eliminarFilaTraslado(trash);
        }else {
            var valor = $(".cantidad").eq(i).val();
            if (!valor){
                error = true;
                mensaje = "La información es incorrecta";
            }
        }
    });
    //se ha seleccionado un almacén
    if(id_almacen.length){
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
        mensaje = "No se ha establecido ningún almacén.";
    }

    if(error){
        mostrarErrores("contenedor-errores-detalle-traslado",{"error":[mensaje]});
        $("html, body").animate({
            scrollTop: 50
        }, 600);
        return false;
    }
    return true;
}

function setFocus(input_ID){
    //console.log('setfocus fila = ' + input_ID)
    $('#' + input_ID).focus();
    $('input:enabled.barcode').first().focus();
    //console.log($('input:enabled.barcode'))
}

function eliminarFilaTraslado(trash){
    if($("#tabla-detalle-producto tbody tr").length < 2){
        mostrarErrores("contenedor-errores-detalles-traslado",{"1":["No puede eliminar el elemento, una traslado debe tener por lo menos un elemento en la lista de detalles."]});
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
                for(i in productos_traslado){
                    if(productos_traslado[i].id == id && productos_traslado[i].filaProd == idFila){
                        productos_traslado.splice(i,1);
                        encontrado = true;
                    }
                }

                if(!encontrado){
                    eliminar = false;
                    alert("El producto no pudo ser eliminado, la información analizada no coincide");
                }
            }else{
                eliminar = true;
            }
        }
        if(eliminar) {
            $(trash).parent().parent().remove();
        }
        valoresTraslado();
    }
}

function quitarProductoDetalle(traslado = null,relacion = null,confirmacion = true){
    if(confirmacion){
        traslado_detalle = traslado;
        relacion_detalle = relacion;
        $("#modal-eliminar-producto-detalle").openModal();
    }else {
        params = {_token: $("#general-token").val(), traslado: traslado_detalle, relacion: relacion_detalle};

        url = $("#base_url").val() + "/traslado/quitar-producto-detalle";

        DialogCargando("Procesando ...");

        $.post(url, params, function (data) {
            if (data.success) {
                $("#subtotal_traslado").html(data.subtotal);
                $("#iva_traslado").html(data.iva);
                $("#total_traslado").html(data.total);
                $("#total-pagar-modal-traslado").text(data.total);
                $("#total-pagar-neto-traslado").text(data.total);
                total_traslado = parseFloat(data.total_num);
                CerrarDialogCargando();
                $("#modal-eliminar-producto-detalle").closeModal();

                $("#elemento_"+relacion_detalle).fadeOut(500,function(){
                    $("#elemento_"+relacion_detalle).remove();
                })

                traslado_detalle = null;
                relacion_detalle = null;

            }
        }).error(function (jqXHR, state, error) {
            CerrarDialogCargando();
            $("#modal-eliminar-producto-detalle").closeModal();
            moveTop(null, null, 50);
            mostrarErrores("contenedor-errores-detalle-traslado", JSON.parse(jqXHR.responseText));

            traslado_detalle = null;
            relacion_detalle = null;
        })
    }
}

function facturarTraslado(){
    /*if (valor_puntos_redimidos_traslado > 0){
        $("#puntos-redimidos").removeClass('hide');
        $("#total-puntos-modal-traslado").text("$ "+formato_numero(valor_puntos_redimidos_traslado,2,',','.'));
    }

    $("#total-pagar-neto-traslado").text("$ "+formato_numero(Math.abs(total_traslado - valor_puntos_redimidos_traslado),2,',','.'));


    $("#efectivo-modal-traslado").val("0");
    if ($("#tipo-cliente").val() == 'si')
        $("#btn-redimir-puntos").addClass('hide');

    $("#regreso-modal-traslado").text("$ 0");*/
    $("#modal-pagar-traslado").openModal();
}

function guardarFacturaTraslado(){
    DialogCargando("Facturando ...");
    var params = {};
    var aux = 1;
    $(".info-producto").each(function(i,el){
        params["producto_"+aux] = {producto:$(el).data("producto"),cantidad:$(el).val()};
        aux++;
    })
    var url = $("#base_url").val() + "/traslado/facturar";
    params._token = $("#general-token").val();
    params.traslado = $("#id").val();

    $.post(url, params, function (data) {
        if (data.success) {
            window.location.href = data.url;
        }
        $("body").scrollTop(50);
    }).error(function (jqXHR, error, state) {
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-pagar", JSON.parse(jqXHR.responseText));
    });
}

function procesarTraslado() {
    var params = $('#form-procesar-traslado').serialize();

    url = $("#base_url").val() + "/traslado/procesar";

    DialogCargando("Procesando ...");

    $.post(url, params, function (data) {
        if (data.success) {
            window.location.reload();
        }
    }).error(function (jqXHR, state, error) {
        CerrarDialogCargando();
        moveTop(null, null, 50);
        mostrarErrores("contenedor-errores-detalle-traslado", JSON.parse(jqXHR.responseText));
    })
}