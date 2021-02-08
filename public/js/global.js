/**
 * Created by JM-DEVELOPER1 on 18/04/2016.
 */

var tamano_fuente = 15;//Tamaño de fuente base para aumentar la clase text-center desde el master.blade.php Linea 425
var indexedDB = window.indexedDB || window.mozIndexedDB || window.webkitIndexedDB || window.msIndexedDB;
var dataBase = null;
var AbrirModal = [];
var color_botones = "#455a64";
var length_rows_tables = 5;



$(document).ready(function(){

    $('body').on('length.dt','.dataTable',function ( e, settings, len) {
        setServerLengthRowsTables(len);
    })
    $('body').on('preInit.dt','.dataTable',function (e, settings) {
        var api = new $.fn.dataTable.Api( settings );
        api.page.len(length_rows_tables);
    })

    document.body.addEventListener("offline", function () {
        alert('No hay conexión a internet, el sistema solo habilitara la funcionalidad de ventas mientras la conexión se reestablece');
    }, false);
    document.body.addEventListener("online", function () {
        updateOnlineStatus("online")
    }, false);

    AbrirModal = localStorage['abrir_modal'];

    dataBase = indexedDB.open("medb3", 1);

    dataBase.onupgradeneeded = function (e) {

        active = dataBase.result;

        object = active.createObjectStore("parametros", { keyPath : 'id', autoIncrement : false });
        object.createIndex('tamano_fuente', 'tamano_fuente', { unique : false });
        object.createIndex('color_botones', 'color_botones', { unique : false });

    };

    dataBase.onsuccess = function (e) {
        var active = dataBase.result;
        var data = active.transaction(["parametros"], "readwrite");
        var object = data.objectStore("parametros");
        // loadAll();
        var anadir = {
            'id' : 1,
            'tamano_fuente' : tamano_fuente,
            'color_botones' : color_botones
        };
        var req = object.add(anadir);

        console.log('Base de datos cargada correctamente');
        load(1);
    };

    dataBase.onerror = function (e)  {
        console.log('Error cargando la base de datos');
    };
/*
 if (AbrirModal == "Si") {
 setTimeout(function(){
 $('#btn-factura-abierta').click()
 },500);
 localStorage['abrir_modal'] = "No";
 }
 */

    $(".boton-menu-hamburguesa").click(function(event){
        event.preventDefault();
        event.stopPropagation();
        data_id=$(this).attr("data-id");

        $(".boton-menu-hamburguesa").addClass("pestana-no-seleccionada");
        $("#"+data_id+"_btn").removeClass("pestana-no-seleccionada");
        $("#"+data_id+"_btn").addClass("pestana-seleccionada");

        $(".menu-hamburguesa").addClass("hide");
        $("#menu-"+data_id).removeClass("hide");

    });
});

function setServerLengthRowsTables(len) {
    setLengthRowsTables(len);
    var params = {_token:$('#general-token').val(),length:len};
    var url = $('#base_url').val()+"/set-length-rows-tables";
    $.post(url,params);
}

function setLengthRowsTables(len) {
    length_rows_tables = len;
}
function load(id) {
    //dataBase = indexedDB.open("medb2", 2);

    var active = dataBase.result;
    var data = active.transaction(["parametros"], "readonly");
    var object = data.objectStore("parametros");

    var request = object.get(parseInt(id));

    request.onsuccess = function () {
        var result = request.result;

        if (result !== undefined) {
            //alert("id:"+result.id+"-tamano_fuente:"+result.tamano_fuente);
            tamano_fuente = result.tamano_fuente;
            color_botones = result.color_botones;
            $(".text-center").css("font-size", tamano_fuente+"px !important");
            $('.blue-grey.darken-2').attr('style', function(i,s) {
                if(s === undefined || s === null)
                    s='';
                return s + 'background-color:'+ color_botones +' !important;';
            });
            $('.blue-grey-text').attr('style', function(i,s) {
                if(s === undefined || s === null)
                    s='';
                return s + 'color:'+ color_botones +' !important;';
            });

            $("#ColorBtn").val(color_botones);

        }
    };
}

function ActualizarIDB() {

    var active = dataBase.result;
    var data = active.transaction(["parametros"], "readwrite");
    var object = data.objectStore("parametros");

    var my_id = parseInt(1);
    var request = object.get(my_id);

    request.onerror = function (e) {
        alert(request.error.name + '\n\n' + request.error.message);
    };

    request.onsuccess = function (e) {
        var parametros = e.target.result;

        parametros.tamano_fuente = tamano_fuente;
        parametros.color_botones = color_botones;

        //alert('Object successfully added');
        var requestUpdated = object.put(parametros);
        requestUpdated.onerror = function (event){
            console.log("Ocurrió un error en la actualización");
        };

        requestUpdated.onsuccess = function (event){
            console.log("Actualización correctamente realizada...");
            $(".text-center").css("font-size", tamano_fuente+"px");
        };


    };
}

/*para busqueda dinamica en divs Busqueda en proveedores*/
$(function () {
    var minlength = 3;
    $("#search_disponibles").keyup(function () {
        var that = this,
            value = $(this).val();
        if (value.length >= minlength) {
           } else if(value.length == 0) {
        }
    });

    $(".btn,.btn-floating,.btn-flat,a").each(function (i,el) {
        if(!$(el).attr("href"))$(el).attr("href","#");
        $(el).addClass("focus-tecla");
    })

    $(".focus-tecla").each(function (i,el) {
        $(el).attr("data-focus-tecla",i);
    })

    $("body,html").on("keydown",".focus-tecla",function (e) {
        focusTeclaClick($(this),e);
    })

});

function focusTeclaClick(elemento,evento){
    if (evento.keyCode == 40 || evento.keyCode == 38) {
        evento.preventDefault();
        var index_actual = $(elemento).data("focus-tecla");
        var index_nuevo = null;

        if (index_actual != null) {
            if (evento.keyCode == 40) {
                index_nuevo = index_actual + 1;
            } else if (evento.keyCode == 38) {
                index_nuevo = index_actual - 1;
            }
            setFocusFocusTecla(index_nuevo,evento);
        }
    }
}

function setFocusFocusTecla(index,evento){
    var elemento_nuevo = $(".focus-tecla").eq(index);

    if ($(elemento_nuevo).length == 1 && $(elemento_nuevo).is(":visible")) {
        //console.log("Asignado "+index+" HTML: "+$(elemento_nuevo).html());
        $(elemento_nuevo).focus();
    } else {
        if ($(elemento_nuevo).length != 1) {
            if (evento.keyCode == 40) {
                index = 0;
            } else if (evento.keyCode == 38) {
                index = $(".focus-tecla").length - 1;
            }
        }else{//no esta visible
            if (evento.keyCode == 40) {
                index++;
            } else if (evento.keyCode == 38) {
                index--;
            }
        }
        setFocusFocusTecla(index,evento);
    }
}

/********************************************************/

$(window).load(function(){
    if (AbrirModal == "Si") {
        $('#btn-factura-abierta').click();
        localStorage['abrir_modal'] = "No";
    }
});

function nuevaFacturaAbierta(){
    localStorage['abrir_modal'] = "Si";
    var win = window.open($('#base_url').val());
    win.blur();
    window.focus();
    //return false;
    //console.log(win.sessionStorage);
    console.log(win.document.set);

}

function checkradioButton(id){
   var radio = $("#"+id);
    if (radio.is(':checked')) { } else {
        radio.prop('checked', true);
        radio.click();
    }
}

function oculta_div_flotante(){
    var div = $(".div_flotate");
    //alert(div.is(":visible"))
    if(div.is(":visible")){
        div.addClass("hide");
        $("#muestra_div").removeClass('hide');
    }else{
        div.removeClass('hide');
        $("#muestra_div").addClass('hide');
        $("#muestra_div").css({
            right:'10px !important',
             top: '60px !important'
        });
    }
}

var menuState = false;
$(function(){


    $("#contenedor-botones-fijos-menu .item-menu.btn-footer-menu").click(function(){
        var id_div = $(this).attr("href");
        $(".contenedor-seccion").slideUp(500,function(){

        });
        $(id_div).slideDown(500);
    })

    $('input[type=date]').change(function(){
        $(".picker__close").click();
    });

    $(".logo-fireos").click(function(){
        var url = $("#base_url").val();
        window.location.href = url;
    });

    $("body, html").click(function (event) {

        if(menuState ){
            $("#contenedor-menu-app").fadeOut(500);
            menuState = false;
        }
    })

    $(".btn-menu-app").click(function(){

        $("#contenedor-menu-app").fadeToggle(500,function(){
            menuState = true;
        });

        if($(this).attr('id')=='menu-desplegable'){
            menuState = false;
        }
    })

    $(".contenedor-img-item").click(function(){
        window.location.href = ($(this).data('url'));
    })
    /***********************************
     * MENSAJES DE ERRORES Y VALIDACIÓN
     *********************************/
    $("body").on("click",".contenedor-errores i.btn-cerrar-errores",function(){
        var idPadre = $(this).parent().attr("id");
        ocultarErrores(idPadre);
    });


    $("body").on("click",".contenedor-confirmacion i.btn-cerrar-confirmacion",function(){
        var idPadre = $(this).parent().attr("id");
        ocultarErrores(idPadre);
    });

    $("body").on("submit","form",function (event) {
        event.preventDefault();
    })

    /**
     * INICIALIZACION DE PLUGINS MATERIALIZECSSS
     */
    inicializarMaterialize();

    $(".close-validacion-ruta").click(function(){
        $(".validacion-ruta").remove();
    });
    $("#contenedor-validacion-caja .validacion-ruta .close-validacion-ruta-caja").click(function(){
        $(this).parent().parent().remove();
    });

    if(localStorage.getItem("slide-movimientos") === null) {
        iniciarInfoMovimientos(1500,6,200,1500,1,.1);
    }

    $(window).resize(function(){
        if(window.innerHeight+200 > window.innerWidth){
            $("#background").css({"background-size": "auto 100%"});
        }else{
            $("#background").css({"background-size": "100% auto"});
        }
    })

    if(window.innerHeight+200 > window.innerWidth){
        $("#background").css({"background-size": "auto 100%"});
    }else{
        $("#background").css({"background-size": "100% auto"});
    }


    if(localStorage.getItem("lista_pedido")){
        productosObj = JSON.parse(localStorage.lista_pedido);
        if($.map(productosObj, function(n, i) { return i; }).length) {
            $("#btn-pedidos-proveedor").removeClass("hide");
        }
    }else{
        localStorage.setItem("lista_pedido","{}");
    }

    $("#btn-pedidos-proveedor").click(function(){
        showPedidosProveedor();
    })

    $("#modal-detalle-pedido-proveedor").on("click",".eliminar-item-pedido-proveedor",function(){
        var id_producto = $(this).parent().parent().data("producto");
        var productos = JSON.parse(localStorage.lista_pedido);
        delete productos[id_producto];
        localStorage.lista_pedido = JSON.stringify(productos);
        $(this).parent().parent().remove();
        if(!$.map(productos, function(n, i) { return i; }).length){
            delete productos;
            $("#modal-detalle-pedido-proveedor").closeModal();
            $("#btn-pedidos-proveedor").addClass("hide");
            localStorage.setItem("lista_pedido","{}");
        }
    })

    $(".toast-vendiendo #btn-cerrar-toast").click(function(){
        $(".toast-vendiendo").removeClass("active");
        $(".toast-vendiendo").animate({
            bottom:"-100px"
        },700,function () {
            $(".toast-vendiendo").slideUp(0,function () {
                $(".toast-vendiendo #titulo").text("");
                $(".toast-vendiendo #mensaje").text("");
            });
        });
    });

    //consultar notificaciones de eventos
    setInterval(function () {
        //notificacionesEventos();
    },10000);

})

function iniciarInfoMovimientos(duracion,repeticiones,delayItem,delayGroup,opacidad,opacidadFinal){
    $(".info-movimientos").removeClass("hide");
    iniciarInfoMovimiento($(".slide-derecha i"),duracion,repeticiones,delayItem,delayGroup,true,opacidad,opacidadFinal);
    iniciarInfoMovimiento($(".slide-izquierda i"),duracion,repeticiones,delayItem,delayGroup,false,opacidad,opacidadFinal);
    setTimeout(function () {
        $(".info-movimientos").fadeOut(700,function () {
            $(".info-movimientos").addClass("hide");
        });
        localStorage.setItem("slide-movimientos", true);
    }, ((delayGroup+duracion) * repeticiones) + 2000);
}

function iniciarInfoMovimiento(element_group,duracion,repeticiones,delayItem,delayGroup,direccion,opacidad,opacidadFinal){
    var delay = 0;
    for (var i = 0; i < repeticiones; i++) {
        $(element_group).each(function (index, el) {
            if(!direccion)
            el = $(".slide-izquierda i").eq(($(".slide-izquierda i").length - 1) - index);
            setTimeout(function () {
                addAnimationSlide(el, duracion, opacidad, opacidadFinal);
            }, delay);
            delay += delayItem;
        })
        delay += delayGroup;
    }
}

function addAnimationSlide(el,duration,opacity,opacityEnd){
    $(el).animate({
        opacity: opacity,
    }, duration, function() {
        $(this).css({opacity:opacityEnd});
    });
}

/**
 * INICIAR TODAS LAS FUNCIONALIDADES DE MATERIALIZE CSS
 */
function inicializarMaterialize(){
    $('.dropdown-button').dropdown({
            inDuration: 300,
            outDuration: 225,
            constrain_width: false, // Does not change width of dropdown to that of the activator
            hover: false, // Activate on hover
            gutter: 0, // Spacing from edge
            belowOrigin: false, // Displays dropdown below the button
            alignment: 'left' // Displays dropdown with edge aligned to the left of button
        }
    );
    $('.dropdown-button-width').dropdown({
            inDuration: 300,
            outDuration: 225,
            constrain_width: true, // Does not change width of dropdown to that of the activator
            hover: false, // Activate on hover
            gutter: 0, // Spacing from edge
            belowOrigin: false, // Displays dropdown below the button
            alignment: 'left' // Displays dropdown with edge aligned to the left of button
        }
    );
    $('select').material_select();
    $('.modal-trigger').leanModal();
    $('.materialboxed').materialbox();
    $('.datepicker').pickadate({
        selectMonths: true, // Creates a dropdown to control month
        selectYears: 15, // Creates a dropdown of 15 years to control year
        format: 'yyyy-mm-dd',
        monthsFull: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        monthsShort: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        weekdaysFull: ['Domingo', 'Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado'],
        weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
        today: 'Hoy',
        clear: 'Limpiar',
        close: 'Cerrar',
        labelMonthNext: 'Siguiente mes',
        labelMonthPrev: 'Anterior mes',
        labelMonthSelect: 'Seleccione un mes',
        labelYearSelect: 'Seleccione un año',
    });
    $('.tooltipped').tooltip({delay: 50});
    $('.collapsible').collapsible({
        accordion : false // A setting that changes the collapsible behavior to expandable instead of the default accordion style
    });
    $('ul.tabs').tabs();

    $('body').trigger('update');
}

/**
 * Funcion para mostrar a los usuarios errores en la vista
 *
 * @param idContenedor -> id del contenedor de los errores
 * @param data -> informacion de los errores ->
 *  {"1":{"mensaje":"Mensaje de error"}, "2":{"mensaje":"Mensaje de error"}, ...}
 */

function mostrarErrores(idContenedor,data){
    $(".contenedor-confirmacion").addClass("hide");
    var html = "<i class='fa fa-close btn-cerrar-errores'></i><ul>";
    $.each(data, function(key,value){
        html += "<li>"+value+"</li>";
    });
    html += "</ul>";
    $("#"+idContenedor).html("");
    $("#"+idContenedor).append(html);
    $("#"+idContenedor).removeClass("hide");
    $("#"+idContenedor).slideDown(300);
}

function ocultarErrores(idContenedor){
    $("#"+idContenedor).slideUp(0);
    $("#"+idContenedor).html("");
    $("#"+idContenedor).addClass("hide");
}

function mostrarConfirmacion(idContenedor,data){
    $(".contenedor-errores").addClass("hide");
    var html = "<i class='fa fa-close btn-cerrar-confirmacion'></i><ul>";
    $.each(data, function(key,value){
        html += "<li>"+value+"</li>";
    })
    html += "</ul>";
    $("#"+idContenedor).html("");
    $("#"+idContenedor).append(html);
    $("#"+idContenedor).removeClass("hide");
    $("#"+idContenedor).slideDown(300);
}

function ocultarConfirmacion(idContenedor){
    $("#"+idContenedor).slideUp(0);
    $("#"+idContenedor).html("");
    $("#"+idContenedor).addClass("hide");
}

function imprimir(id)
{
    var ficha=document.getElementById(id);
    var ventimp=window.open(' ','popimpr');
    ventimp.document.write(ficha.innerHTML);
    ventimp.document.close();
    ventimp.print();
    ventimp.close();
}

function getParams () {
    // This function is anonymous, is executed immediately and
    // the return value is assigned to QueryString!
    var query_string = {};
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i=0;i<vars.length;i++) {
        var pair = vars[i].split("=");
        // If first entry with this name
        if (typeof query_string[pair[0]] === "undefined") {
            query_string[pair[0]] = decodeURIComponent(pair[1]);
            // If second entry with this name
        } else if (typeof query_string[pair[0]] === "string") {
            var arr = [ query_string[pair[0]],decodeURIComponent(pair[1]) ];
            query_string[pair[0]] = arr;
            // If third or later entry with this name
        } else {
            query_string[pair[0]].push(decodeURIComponent(pair[1]));
        }
    }
    return query_string;
};


function mostrarImagen(input,id_preview) {
    if (input.files && input.files[0]) {
        var data_file = input.files[0].name.split(".");
        var ext = data_file[data_file.length - 1];var reader = new FileReader();
        reader.onload = function (e) {
            $('#' + id_preview).attr('src', e.target.result);
        }
        if(ext == "jpg" || ext == "jpeg" || ext == "png" || ext == "svg") {
            reader.readAsDataURL(input.files[0]);
            $('#' + id_preview).removeClass("hide");
        }else{
            alert("Debe seleccionar imagenes jpg, jpeg, png, svg");
            $(input).val("");
            $("#"+id_preview).attr("src","");
            $('#' + id_preview).addClass("hide");
        }
    }else{
        $('#'+id_preview).addClass("hide");
    }
}

function DialogCargando(text = "Cargando ...") {
    $("body,html").css({
        "overflow":"hidden"
    });
    $.blockUI({ css: {
        border: 'none',
        padding: '15px',
        backgroundColor: '#000',
        '-webkit-border-radius': '10px',
        '-moz-border-radius': '10px',
        opacity: .5,
        color: '#fff'
    },
        message:
            '<div><h6  style ="font-family: inherit;"><b>'+text+'</b><h6></div>'
    });

}

function CerrarDialogCargando() {
    $("body,html").css({
        "overflow":"auto"
    });
    $.unblockUI();
}



function moveTop(elemento = null,duracion = 500,top_val = 0){
    if(elemento == null){
        elemento = "body,html";
    }
    $(elemento).animate({
        "scrollTop":top_val+"px"
    },duracion);
}

function showPedidosProveedor(){
    if(localStorage.getItem("lista_pedido")) {
        var productos = JSON.parse(localStorage.lista_pedido);
        var tabla = "<table class='bordered highlight centered'><tbody>";
        var total = 0;
        var proveedores = [];
        $.each(productos,function(index,prod){
            if($.inArray(prod.usuario_id,proveedores) == -1){
                proveedores.push(prod.usuario_id);
            }
        });

        $.each(proveedores,function(i,proveedor_id){
            var aux = 0;
            var cantidad_productos = $.map(productos,function (i,el) { return i;}).length;
            var count = 0;
            var total_proveedor = 0;
            $.each(productos,function(index,prod){
                count++;
                if(prod.usuario_id == proveedor_id) {
                    aux++;
                    if(aux == 1){
                        tabla += "<tr>" +
                        "<th colspan='5' class='center-align'>" + prod.nombres + " " + prod.apellidos  + "</th></tr>"+
                        "<tr>" +
                        "<th class='center-align'>Producto</th>"+
                        "<th class='center-align'>Valor unitario</th>"+
                        "<th class='center-align'>Cantidad</th>"+
                        "<th class='center-align'>Total</th>"+
                        "<th class='center-align'>Quitar</th>"+
                        "</tr>";
                    }
                    var total_aux = 0;
                    tabla += "<tr data-producto='" + prod.id + "'>" +
                    "<td>" + prod.nombre + "</td>";
                    if (prod.promocion) {
                        tabla += "<td><p class='badge-vendiendo'>$ " + number_format(prod.valor_con_descuento, 2) + "</p></td>";
                        total_aux = prod.valor_con_descuento * prod.cantidad;
                    } else {
                        tabla += "<td>$ " + number_format(prod.precio_costo, 2) + "</td>";
                        total_aux = prod.precio_costo * prod.cantidad;
                    }

                    tabla += "<td>" + prod.cantidad + "</td>" +
                    "<td>$ " + number_format(total_aux, 2) + "</td>" +
                    "<td><a class='eliminar-item-pedido-proveedor'><i class='fa fa-trash red-text' style='cursor: pointer;'></i></a></td>";
                    "</tr>";
                    total += total_aux;
                    total_proveedor +=  total_aux;
                }

                if(count == cantidad_productos){
                    tabla += "<tr>" +
                        "<th class='center-align' colspan='3'>Total</th>" +
                        "<th class='center-align' colspan='2'>$ "+number_format(total_proveedor,2)+"</th>" +
                        "</tr>";
                }
            });
        });

        $("#modal-detalle-pedido-proveedor #contenido-detalle-pedido-proveedor").html(tabla);
        $("#modal-detalle-pedido-proveedor").openModal();
    }
}

function quitarProductosPedidoProveedor(){
    if(confirm("¿Esta seguro de quitar los productos seleccionados para este pedido?")) {
        localStorage.setItem("lista_pedido", "{}");
        $("#modal-detalle-pedido-proveedor").closeModal();
        $("#btn-pedidos-proveedor").addClass("hide");
    }
}

function enviarPedidoProveedor(){
    if(localStorage.lista_pedido) {
        var productos = JSON.parse(localStorage.lista_pedido);
        if($.map(productos,function(el,i){return i}).length) {
            var url = $("#base_url").val() + "/productos/store-pedido-proveedor";
            var params = {_token: $("#general-token").val(), productos: productos};
            DialogCargando("Registrando pedido ...");
            $.post(url,params,function(data){
                if(data.success){
                    localStorage.setItem("lista_pedido", "{}");
                    $("#modal-detalle-pedido-proveedor").closeModal();
                    $("#btn-pedidos-proveedor").addClass("hide");
                    $("#contenido-detalle-pedido-proveedor").html("");
                    CerrarDialogCargando();
                    lanzarToast("El pedido ha sido registrado con éxito","Confirmación",10000);
                }
            }).error(function(jqXHR,state,error){
                CerrarDialogCargando();
                moveTop("#modal-detalle-pedido-proveedor .modal-content",500,0);
                mostrarErrores("contenedor-errores-detalle-pedido",JSON.parse(jqXHR.responseText));
            });
        }else{
            alert("Selecione productos para hacer un pedido");
        }
    }else{
        alert("Selecione productos para hacer un pedido");
    }
}

/**
 *
 * @param texto a mostrar
 * @param titulo del toast
 * @param duracion en milisegundos (0 == no se debe cerrar)
 */
function lanzarToast(texto, titulo, duracion,class_titulo = "light-blue-text"){
    if(!$(".toast-vendiendo").hasClass("active")) {
        $(".toast-vendiendo").addClass("active");
        $(".toast-vendiendo #titulo").html(titulo);
        $(".toast-vendiendo #titulo").addClass(class_titulo);
        $(".toast-vendiendo #mensaje").html(texto);
        $(".toast-vendiendo").slideDown(0);
        $(".toast-vendiendo").animate({
            bottom: "10%"
        }, 500);
        if (duracion > 0) {
            setTimeout(function () {
                $(".toast-vendiendo").animate({
                    bottom: "-100px"
                }, 700, function () {
                    $(".toast-vendiendo").removeClass("active");
                    $(".toast-vendiendo").slideUp(0, function () {
                        $(".toast-vendiendo #titulo").text("");
                        $(".toast-vendiendo #mensaje").text("");
                    });
                });
            }, duracion);
        }
    }
}

function notificacionesEventos(){
    if(navigator.onLine) {
        console.log('Con conexion');
        var url = $('#base_url').val() + '/evento/notificaciones';
        $.post(url, {_token: $('#general-token').val()}, function (data) {
            if (data.registros > 0) {
                for (i = 0; i < data.eventos.length; i++) {
                    if (Notification) {
                        if (Notification.permission !== "granted") {
                            Notification.requestPermission()
                        }
                        var title = "Vendiendo.co"
                        var extra = {
                            icon: "https://vendiendo.com.co/img/sistema/LogoVendiendo.png",
                            body: data.eventos[i].titulo + '\n\n' + data.eventos[i].descripcion,
                        }
                        var noti = new Notification(title, extra)
                        noti.onclick = function () {
                            window.location.href = $('#base_url').val() + '/evento'
                        }
                        noti.onclose = {
// Al cerrar
                        }
                        //setTimeout( function() { noti.close() }, 10000)
                        //cargarNumerosNotificaciones();
                    }
                }
            }
        })
    }else {
        console.log('Sin conexion');
    }
}