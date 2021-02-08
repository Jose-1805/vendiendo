var total = 0;
var pedido = [];
var class_color = "";
var class_color_text = "";
var productos_en_vista = [];
var id_producto_calculadora = null;
var cliente_seleccionado = null;
var cantidad = 0;
var validacion_stock = true;
var token_puntos = null;
var valor_puntos_redimidos = 0;
var valor_medios_pago = 0;

$(function(){
    if(localStorage.getItem('num_cols')){
        if(localStorage.getItem('num_cols') == 2)dosColumnas();
        else if(localStorage.getItem('num_cols') == 3) tresColumnas();
    }
    $("#btn-dos-columnas").click(function () {
        dosColumnas();
    })
    $("#btn-tres-columnas").click(function () {
        tresColumnas();
    })

    $("body").on("mouseenter",".elemento .card",function () {
        $(this).addClass("z-depth-2");
    })

    $("body").on("mouseleave",".elemento .card",function () {
        $(".elemento .card.z-depth-2").removeClass("z-depth-2");
    })

    $(".total").click(function(){
        $(".total").removeClass("hide");
        $(this).addClass("hide");
        if($("#lista-pedido").hasClass("hide")){
            $("#lista-pedido").removeClass("hide");
            $("#datos-precios").addClass("hide");
        }else{
            $("#lista-pedido").addClass("hide");
            $("#datos-precios").removeClass("hide");
            $("#datos-precios #efectivo").val("");
            $("#datos-precios #efectivo").focus();
        }
    })

    $("body").on("click",".mas",function () {
        adicionar($(this).parent().parent().parent());
    })

    $("body").on("click",".menos",function () {
        disminuir($(this).parent().parent().parent(),$(this).parent().parent().parent().data("producto"));
    })

    $("body").on("click",".menos-buscar",function(e){
        var id = $(this).parent().parent().data("producto");
        disminuir($("#elemento-"+id),id);
    })
    $("body").on("click",".mas-buscar",function(e){
        var id = $(this).parent().parent().data("producto");
        adicionar($("#elemento-"+id),id);
    })

    $("body").on("click",".btn-eliminar-item",function () {
        var id = $(this).parent().data("producto");
        eliminarProducto(id,true);
    })

    $("#buscar").focus(function () {
        //si tiene contenido anterior en la busqueda se muestra
        if($("#contenido-buscar").html() != ""){
            mostrarContenidoBuscar();
        }
    })

    $("#buscar").keyup(function (e) {
        if(e.keyCode == 13 && $(this).val()){
            $("#contenido-buscar").html("<p class='center-align'>Cargando <i class='fa fa-spinner fa-spin'></i></p>")
            mostrarContenidoBuscar();
            var url = $("#base_url").val()+"/mispedidos/buscar-productos";
            var params = {_token:$("#general-token").val(),buscar:$(this).val(),class_color:class_color};
            $.post(url,params,function (data) {
                var html = "<div class='col s12 right-align margin-bottom-10'><i class='fa fa-times-circle pink-text darken-1' style='cursor: pointer;' onclick='cerrarContenidoBuscar()'></i></div>"+data;
                $("#contenido-buscar").html(html);
                actualizarInformacion();
                load(1);
            }).error(function (jqXHR,error,state) {
                alert("Ocurrio un error inesperado.");
                //window.location.reload();
            })
        }
    })

    $("body").on("click",".btn-cargar-mas",function(){
        $(this).parent().remove();
       var categoria = $(this).data("categoria");
       cargarMasProductos(categoria);
    });

    $(window).resize(function(){
        establecerTamanosYPosiciones();
    })


    if(localStorage.getItem("pedido")){
        pedido = JSON.parse(localStorage.getItem("pedido"));
        actualizarInformacion();
    }

    $("body").click(function (e) {
        if($("#contenido-buscar").find($(e.target)).length == 0 && $(e.target).attr("id") != "contenido-buscar" && $(e.target).attr("id") != "buscar")cerrarContenidoBuscar();
    })
    $("body,html").keyup(function (e) {
        //si se presiona Esc se cierra el contenido de buscar
        if(e.keyCode == 27){
            cerrarContenidoBuscar();
            cerrarTecladoNumerico();
        }
    })

    $("body").on("click",".elemento-cantidad",function (e) {
        var id = $(this).parent().parent().data("producto");
        if(typeof id == 'undefined'){
            var id = $(this).parent().data("producto");
        }
        cantidad = 0;
        for (i in pedido) {
            if (pedido[i].id == id) {
                    cantidad = pedido[i].cantidad;
            }
        }
        id_producto_calculadora = id;
    })

    $("#valor-numero").bind("update",function(e){
        if(id_producto_calculadora != null){
            //controla si el elemento es encontrado en la variable interna
            //si no se encuentra debe ser agregado para visualizar la información en pantalla
            var encontrado = false;
            var elemento_informacion = $("#elemento-"+id_producto_calculadora).children(".info-producto");
            var stock = parseInt($(elemento_informacion).children(".pr_stock").val());
            var sobre_stock = false;
            if(stock < parseInt($(this).data("valor")) && validacion_stock){
                lanzarToast("La cantidad máxima permitida para este producto es "+stock,"Error",8000,"red-text");
                sobre_stock = true;
            }

            for (i in pedido) {
                if (pedido[i].id == id_producto_calculadora) {
                    encontrado = true;
                    if(!sobre_stock) {
                        pedido[i].cantidad = $(this).data("valor");
                    }else{
                        setValueTecladoNumerico(pedido[i].cantidad);
                    }
                }
            }

            if(!encontrado){
                if(sobre_stock){
                    setValueTecladoNumerico(0);
                }else {
                    var nombre = $(elemento_informacion).children(".pr_nombre").val();
                    var id = $(elemento_informacion).children(".pr_id").val();
                    var precio = $(elemento_informacion).children(".pr_precio").val();

                    var objeto = {id: id, nombre: nombre, precio: precio, cantidad: $(this).data("valor")};
                    pedido.push(objeto);
                }
            }


            actualizarInformacion();
        }
    })

    $("#valor-numero").bind("start",function(e){
        if(cantidad != 0)setValueTecladoNumerico(cantidad);
    })

    $("#valor-numero").bind("close",function(e){
        id_producto_calculadora = null;
    })
    
    $("#seleccion-cliente").click(function(){
        verClientes();
    })
    
    $("#datos-precios #efectivo").keyup(function (e) {
       actualizarDatosPrecios();
    });

    $("#Listado ul.tabs .tab").click(function(){
        setTimeout(function () {
            establecerTamanosYPosiciones();
        },10);
    })

    establecerTamanosYPosiciones();

    $("#modal-puntos #valor").keyup(function(){
        if(cliente_seleccionado != null && cliente_seleccionado.predeterminado != "si") {
            if($(this).val() > parseInt(cliente_seleccionado.valor_puntos) || $(this).val() > parseInt(total - valor_medios_pago)){
                alert("El valor a redimir no es correcto");
                if (cliente_seleccionado.valor_puntos < total )
                    $(this).val(parseFloat(cliente_seleccionado.valor_puntos));
                else
                    $(this).val(parseFloat(total - valor_medios_pago));
            }

        }
    });

    $("#redimir").change(function(){
        if(cliente_seleccionado != null && cliente_seleccionado.predeterminado != "si") {
            if ($(this).val() == 1) {
                if (cliente_seleccionado.valor_puntos < (total-valor_medios_pago))
                    $("#modal-puntos #valor").eq(0).val(parseFloat(cliente_seleccionado.valor_puntos.toFixed(2)));
                else
                    $("#modal-puntos #valor").eq(0).val(parseFloat(total.toFixed(2) -  valor_medios_pago));

                $("#modal-puntos #valor").eq(0).attr("readonly", "readonly");
            } else {
                if (cliente_seleccionado.valor_puntos < (total-valor_medios_pago))
                    $("#modal-puntos #valor").eq(0).val(parseFloat(cliente_seleccionado.valor_puntos.toFixed(2)));
                else
                    $("#modal-puntos #valor").eq(0).val(parseFloat(total.toFixed(2)-valor_medios_pago));

                $("#modal-puntos #valor").eq(0).prop("readonly", false);
            }
        }
    });

    $(".valor-medio-pago").keyup(function(){
        valor_medios_pago = 0;

        $('.valor-medio-pago').each(function (i,el) {
            if($(el).val()) {
                valor_medios_pago += parseInt($(el).val());
            }
        })

        if((parseFloat(valor_medios_pago)+parseFloat(valor_puntos_redimidos)) > Math.round(total,0)){
            $(this).val('');
            mostrarErrores('contenedor-errores-medios-pago',{'error':['El valor máximo permitido en los medios de pago es $ '+number_format(total,2)]});
        }else {
            actualizarDatosPrecios();
        }
    })

    $("body").on("click",".mas-informacion",function (e) {
        var nombre = $(this).parent().children('.nombre').html();
        var descripcion = $(this).parent().children('.descripcion').html();
        var valor = $(this).parent().children('.valor').html();
        $('#modal-mas-informacion .modal-content .nombre').html(nombre);
        $('#modal-mas-informacion .modal-content .descripcion').html(descripcion);
        $('#modal-mas-informacion .modal-content .valor').html(valor);
        $('#modal-mas-informacion').openModal();
    })
});

function cerrarContenidoBuscar() {
    $("#contenido-buscar").slideUp(500);
}

function actualizarDatosPrecios(){
    var efectivo = $("#datos-precios #efectivo").val();

    if(!$.isNumeric(efectivo))efectivo = 0;

    if(valor_puntos_redimidos > 0){
        $('#puntos-redimidos').removeClass('hide');
        $('#total-puntos-redimidos').text('$ '+number_format(valor_puntos_redimidos,2));
    }else {
        $('#puntos-redimidos').addClass('hide');
    }

    if(valor_medios_pago > 0){
        $('#medios-pago').removeClass('hide');
        $('#total-medios-pago').text('$ '+number_format(valor_medios_pago,2));
    }else {
        $('#medios-pago').addClass('hide');
    }
    var _total = (parseFloat(efectivo) + parseFloat(valor_medios_pago) + parseFloat(valor_puntos_redimidos));
    if(_total >= total){
        $("#cambio").text("$ "+number_format(_total-total));
        $("#descuento").text("$ 0");
    }else{
        $("#cambio").text(0);
        $("#descuento").text("$ "+number_format(total-_total));
    }

}

function setClassColor(class_) {
    class_color = class_;
    $(".item-lista-pedido i.circle").addClass(class_color);
}

function setClassColorText(class_) {
    class_color_text = class_;
}

function setValidacionStock(validacion) {
    validacion_stock = validacion;
}

function dosColumnas() {
    $(".elemento").removeClass("l4");
    $(".elemento").addClass("l6");

    $(".elemento .info-producto").removeClass("l12");
    $(".elemento .info-producto").addClass("l6");

    $(".elemento .contenedor-img-producto").removeClass("l12");
    $(".elemento .contenedor-img-producto").addClass("l6");

    $(".botones-elemento").removeClass("l6");
    $(".botones-elemento").removeClass("offset-l3");
    $(".botones-elemento").addClass("l4");
    $(".botones-elemento").addClass("offset-l4");

    $('.botones-control-pedidos').css({
        marginTop:'-70px'
    })

    establecerTamanosYPosiciones();
    localStorage.setItem('num_cols',2);
}

function tresColumnas() {
    $(".elemento").removeClass("l6");
    $(".elemento").addClass("l4");

    $(".elemento .info-producto").removeClass("l6");
    $(".elemento .info-producto").addClass("l12");

    $(".elemento .contenedor-img-producto").removeClass("l6");
    $(".elemento .contenedor-img-producto").addClass("l12");

    $(".botones-elemento").removeClass("l4");
    $(".botones-elemento").removeClass("offset-l4");
    $(".botones-elemento").addClass("l6");
    $(".botones-elemento").addClass("offset-l3");

    $('.botones-control-pedidos').css({
        marginTop:'0px'
    })
    establecerTamanosYPosiciones();
    localStorage.setItem('num_cols',3);
}

function establecerTamanosYPosiciones(){
    var alto_ventana = $(window).height();
    var alto_encabezado = $("#encabezado").height();

    var alto_lista_pedido = alto_ventana - alto_encabezado;
    var alto_elementos = alto_ventana - alto_encabezado - 30;
    $("#contenido-principal").css({height:alto_elementos+"px",marginTop:(alto_encabezado+10)+"px"});
    $("#contenedor-elementos").css({height:alto_elementos+"px",maxHeight:alto_elementos+"px"});
    $("#datos-pedido").css({height:alto_lista_pedido+"px",maxHeight:alto_elementos+"px"});
    $("#datos-precios").css({height:(alto_lista_pedido-100)+"px",maxHeight:alto_elementos+"px"});
    $("#lista-pedido").css({height:(alto_lista_pedido*0.7)+"px",maxHeight:(alto_elementos/2)+"px"});
    
    $(".agotado").each(function (i,el) {
        var alto_contenido_elemento = $(this).parent().children(".contenido-elemento").height()+30;
        $(this).css({
            height:alto_contenido_elemento+"px",
            marginBottom:"-"+alto_contenido_elemento+"px",
        })
    })

    //TAMAÑO Y POSICION DEL CONTENIDO DE BUSCAR
    var alto_encabezado = $("#encabezado").height();
    var ancho_buscar = $("#buscar").width();
    $("#contenido-buscar").css({
        top:alto_encabezado+"px",
        width:ancho_buscar+"px",
    })
}

function adicionar(elemento_padre,id_elemento){
    //el elemento se encuentra dibujado en la vista
    if($(elemento_padre).length >= 1) {
        var elemento_informacion = $(elemento_padre).children(".info-producto");
        var nombre = $(elemento_informacion).children(".pr_nombre").val();
        var id = $(elemento_informacion).children(".pr_id").val();
        var precio = $(elemento_informacion).children(".pr_precio").val();
        var stock = $(elemento_informacion).children(".pr_stock").val();


    }else{//el elemento no se ha cargado en la vista y se debe consultar en el servidor
        var elemento_padre = $("#producto-buscar-"+id_elemento);
        var nombre = $(elemento_padre).children(".pr_nombre").val();
        var id = $(elemento_padre).children(".pr_id").val();
        var precio = $(elemento_padre).children(".pr_precio").val();
        var stock = $(elemento_padre).children(".pr_stock").val();
    }

    //determina si se debe agregat (true) o actualizar (false) el elemento
    var agregar = true;

    for (i in pedido) {
        //ha sido seleccionado antes
        if (pedido[i].id == id) {
            if (pedido[i].cantidad == stock && validacion_stock) {
                lanzarToast("La cantidad máxima permitida para este producto es " + stock, "Error", 8000, "red-text");
            } else {
                pedido[i].cantidad++;
            }
            agregar = false;
            break;
        }
    }

    if (agregar) {
        var objeto = {id: id, nombre: nombre, precio: precio, cantidad: 1};
        pedido.push(objeto);
    }
    actualizarInformacion();
}

function disminuir(elemento_padre,id) {


    for (i in pedido) {
        //ha sido seleccionado antes
        if (pedido[i].id == id) {
            if (pedido[i].cantidad > 0) {
                pedido[i].cantidad--;
                break;
            }
        }
    }
    actualizarInformacion();
}

/**
 * actualiza en la vista la información del pedido
 */
function actualizarInformacion(){
    total = 0;
    for(i in pedido){
        var el = pedido[i];

        if(el.cantidad <= 0){
            eliminarProducto(el.id);
        }else {
            //pone la cantidad en el div que contiene la información y botones de selección del producto
            $("#elemento-"+el.id).children("div").children(".elemento-cantidad").html(el.cantidad);
            total += el.precio * el.cantidad;

            //si se encuentra en la lista de buscar
            if ($("#producto-buscar-" + el.id).length) {
                $("#producto-buscar-" + el.id +" p span.cantidad-buscar").html(el.cantidad);
            }

            //si ya se encuentra el item solo se actualiza su información
            if ($("#item_producto_" + el.id).length) {
                $("#item_producto_" + el.id + " i .info_cantidad").html(el.cantidad);
                $("#item_producto_" + el.id + " .info_nombre").html(el.nombre);
            } else {
                //el item se crea y se agrega a la lista

                var coleccion = $("#lista-pedido ul.collection");
                var html_item = '<li class="collection-item avatar item-lista-pedido" id="item_producto_' + el.id + '" data-producto="'+el.id+'">' +
                    '<i class="circle ' + class_color + ' trigger-teclado-numerico elemento-cantidad"><span class="info_cantidad trigger-teclado-numerico elemento-cantidad">' + el.cantidad + '</span></i>' +
                    '<span class="title truncate info_nombre">' + el.nombre + '</span>' +
                    '<a href="#!" class="btn-eliminar-item secondary-content pink-text text-darken-1"><i class="fa fa-trash"></i></a>' +
                    '</li>';
                $(coleccion).append(html_item);
            }
        }
    }
    localStorage.setItem("pedido",JSON.stringify(pedido));
    $(".info-total").html(number_format(total,2));
    total = Math.round(total,0);
    actualizarDatosPrecios();
}


/**
 * elimina un producto tanto de la lista como de la variable pedidos
 */
function eliminarProducto(id,actualizarInfo = false) {
    for(i in pedido){
        if(pedido[i].id == id){
            $("#item_producto_"+pedido[i].id).remove();
            pedido.splice(i,1);
        }
    }
    //si se encuentra en la lista de buscar
    if ($("#producto-buscar-" + id).length) {
        $("#producto-buscar-" + id +" p span.cantidad-buscar").html("0");
    }
    $("#elemento-"+id).children("div").children(".elemento-cantidad").html("0");
    if(actualizarInfo)actualizarInformacion();
}

function mostrarContenidoBuscar(){
    var alto_encabezado = $("#encabezado").height();
    var ancho_buscar = $("#buscar").width();
    $("#contenido-buscar").css({
        top:(alto_encabezado-14)+"px",
        width:ancho_buscar+"px",
    })

    $("#contenido-buscar").slideDown(500);
}


//carga más productos que tengan relacion con la categoria que tenga el id pasado como parametro
//productos diferentes a los que se encuentran en la vista
function cargarMasProductos(categoria){
    var html_boton = '<div class="col s12 center-align">'
        +'<a href="#!" data-categoria="'+categoria+'" class="btn-cargar-mas btn waves-effect waves-light '+class_color+'">Ver más</a>'
        +'</div>'
    DialogCargando();
    var url = $("#base_url").val()+"/mispedidos/mas-productos";
    var params = {
        _token:$("#general-token").val(),
        cantidad:10,
        class_color:class_color,
        class_color_text:class_color_text,
        productos_en_vista:productos_en_vista,
        categoria:categoria
    };
    $.post(url,params,function (data) {
        $("#categoria_"+categoria).append(data.view+html_boton);
        productos_en_vista = data.productos_en_vista;
        if(data.mensaje){
            lanzarToast(data.mensaje,"Mensaje",8000);
        }
        actualizarInformacion();
        establecerTamanosYPosiciones();
        CerrarDialogCargando();
        load(1);
        if(localStorage.getItem('num_cols') == 2)dosColumnas();
        else if(localStorage.getItem('num_cols') == 3) tresColumnas();
    }).error(function (jqXHR,error,state) {
        alert("Ocurrio un error inesperado.");
        //window.location.reload();
    })
}


function verClientes() {
    DialogCargando("Cargando ...");
    var params = {_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/mispedidos/ver-clientes";
    $.post(url,params,function(data){
        $("#modal-clientes-pedido #contenedor-clientes").html(data);
        $("#modal-clientes-pedido").openModal({dismissible: false,complete: function() {
        }});
        CerrarDialogCargando();
    }).error(function(jqXHR,error,state){
        CerrarDialogCargando();
    })
}

function cargatablaListaClientesPedido(){
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
            "url": $("#base_url").val()+"/mispedidos/lista-clientes?"+params, //GET
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

            $(row).attr('onClick', "seleccionarCliente("+data.id+")");
            var checked = "";
            if(cliente_seleccionado && cliente_seleccionado.id == data.id)checked = "checked";
            $('td', row).eq(0).html("<p style='margin: 0px !important;padding: 0px !important;'' class='radio_producto'>"+
                "<input type='radio' id='cliente"+data.id+"' name='cliente' value='"+data.id+"' "+checked+">"+
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

function seleccionarCliente(cliente){
    var url = $("#base_url").val()+"/mispedidos/info-cliente";
    var params = {_token:$("#general-token").val(),cliente:cliente};
    DialogCargando("Seleccionando ...");
    $.post(url,params,function (data) {
        CerrarDialogCargando();
        cliente_seleccionado = data;
        $("#nombre-cliente").text(data.nombre);
        $("#telefono-cliente").text(data.telefono);
        $("#direccion-cliente").text(data.direccion);
        $("#modal-clientes-pedido").closeModal();
    }).error(function (jqXHR,error,state) {
        CerrarDialogCargando();
    })
}

function enviarPedido() {
    if(cliente_seleccionado != null) {
        var ver_factura = $("#ver_factura").prop("checked");
        var url = $("#base_url").val() + "/mispedidos/store";
        var params = {
            _token: $("#general-token").val(),
            cliente: cliente_seleccionado,
            pedido: pedido,
            efectivo: $("#datos-precios #efectivo").val()
        };

        if(valor_puntos_redimidos > 0){
            params.valor_puntos = valor_puntos_redimidos;
            params.token_puntos = token_puntos;
        }

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

        DialogCargando("Enviando ...");
        $.post(url, params, function (data) {
            if (data.notificacion) {
                var msg = {
                    'ubicacion': 'notificacion',
                    'mensaje': data.mensajeNotificacion
                };
                conn.send(JSON.stringify(msg));
                localStorage.setItem("notificacion", data.mensajeNotificacion);
            }
            if (data.success) {
                localStorage.removeItem("pedido");
                window.location.reload();
                if (ver_factura) {
                    $("#contenedor-factura-pos").html(data.factura_pos);
                    imprimir("factura-pos");
                }
            }
        }).error(function (jqXHR, error, state) {
            CerrarDialogCargando();
        })
    }else{
        lanzarToast("No ha seleccionado ningún cliente","Error",8000,"red-text");
    }

}

function showPuntosCliente(){

    if(cliente_seleccionado != null && cliente_seleccionado.predeterminado != "si"){
        if(cliente_seleccionado.valor_puntos > 0) {
            $("#modal-puntos #texto-puntos").eq(0).html("<strong>Cliente: </strong>" + cliente_seleccionado.nombre + " - " + cliente_seleccionado.tipo_identificacion + ": " + cliente_seleccionado.identificacion);
            $("#modal-puntos #texto-valor-puntos").eq(0).html("<strong>Valor puntos: </strong>$ " + formato_numero(cliente_seleccionado.valor_puntos,2,',','.'));
            $("#modal-puntos #texto-total-factura").eq(0).html("<strong>Total factura: </strong>$ " + formato_numero(total-valor_medios_pago,2,',','.'));

            /*if (cliente_seleccionado.valor_puntos <= total){
             //redime todos
             $("#modal-puntos #redimir").attr("disabled", true);
             }*/

            if (cliente_seleccionado.valor_puntos < total)
                $("#modal-puntos #valor").eq(0).val(parseFloat(cliente_seleccionado.valor_puntos.toFixed(2)));
            else
                $("#modal-puntos #valor").eq(0).val(parseFloat(total.toFixed(2) - valor_medios_pago));

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
        alert('Para utilizar el modo de pago de puntos seleccione un cliente diferente a su cliente predeterminado');
    }
}

function redimir(){
    if(cliente_seleccionado != null && cliente_seleccionado.predeterminado != "si") {
        if($("#modal-puntos #valor").eq(0).val() > 0) {
            DialogCargando("Generando token de puntos ...");
            var params = {
                _token: $("#general-token").val(),
                cliente: cliente_seleccionado.id,
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
                    actualizarDatosPrecios();
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