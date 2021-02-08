var conn = null;
var sin_notificaciones = [];
$(function(){
    $("#full-contenedor-notificaciones, .btn-notificaciones").click(function(event){
        if($(event.target).hasClass("full-contenedor-notificaciones")){
            $("#full-contenedor-notificaciones").fadeOut(500);
            $("body").css({"overflow":"auto"});
        }

        if($(event.target).hasClass("btn-notificaciones")){
            $("#full-contenedor-notificaciones").fadeToggle(500);
            $('ul.tabs').tabs('select_tab', 'inventario');
            $("body").css({"overflow":"hidden"});
        }
    })

    $("#btn-cerrar-notificaciones").click(function(){
        $("#full-contenedor-notificaciones").fadeOut(500);
        $("body").css({"overflow":"auto"});
    });
    
    $("#tabs-notificaciones .tab a").click(function () {
        var tab = $(this).attr("id");
        vistaNotificaciones(tab);
    })

    $("body").on("click",".btn-notificacion-leida",function(){
        var elemento = $(this);
        var icono = $(elemento).children(".fa-check-circle").eq(0);
        $(icono).removeClass("fa-check-circle");
        $(icono).addClass("fa-circle-o-notch fa-spin");
        var id_contenedor = "";
        if($(elemento).data("tipo") == "inventario") id_contenedor = "inventario";
        else if($(elemento).data("tipo") == "cuentas por cobrar") id_contenedor = "cuentas_cobrar";
        else if($(elemento).data("tipo") == "cuentas por pagar") id_contenedor = "cuentas_pagar";

        var params = {tipo:$(elemento).data("tipo"),notificacion:$(elemento).data("notificacion"),_token:$("#general-token").val()};
        var url = $("#base_url").val()+"/notificacion/marcar-notificacion-leida";
        $.post(url,params,function (data) {
            if(data.success) {
                cargarNumerosNotificaciones(false);
                $(icono).removeClass("fa-circle-o-notch fa-spin");
                $(icono).addClass("fa-check-circle");
                $(elemento).removeClass("btn-notificacion-leida");
                $(elemento).addClass("grey-text");
                $(elemento).attr("title","");
                $(elemento).css({"cursor":"default"});
            }
        }).error(function(jqXHR,error,state){
            $(icono).removeClass("fa-circle-o-notch fa-spin");
            $(icono).addClass("fa-check-circle");
            mostrarErrores("contenedor-errores-notificaciones", JSON.parse(jqXHR.responseText));
        });
    })

    $("#btn-recargar-notificaciones").click(function(){
        var tab = $("#tabs-notificaciones .tab").children(".active").eq(0).attr("id");
        vistaNotificaciones(tab,true);
    })

    $("#todo-leido").change(function(){
        var id_tab = $("#tabs-notificaciones .tab").children(".active").eq(0).attr("id");

        var tipo = "";
        var titulo = "";
        var nombreStorage = "";
        switch (id_tab){
            case 'tab-inventario':
                tipo = 'inventario';
                titulo = "Inventario";
                nombreStorage = "notificaciones_inventario";
                break;
            case 'tab-cuentas-cobrar':
                tipo = 'cuentas por cobrar';
                titulo = "Cuentas por cobrar";
                nombreStorage = "notificaciones_cuentas_cobrar";
                break;
            case 'tab-cuentas-pagar':
                tipo = 'cuentas por pagar';
                titulo = "Cuentas por pagar";
                nombreStorage = "notificaciones_cuentas_pagar";
                break;
        }
        var id_contenedor = $("#"+id_tab).attr("href");

        //
        $(id_contenedor+" .collection .collection-item .btn-notificacion-leida").addClass("grey-text");
        $(id_contenedor+" .collection .collection-item .btn-notificacion-leida").attr("title","");
        $(id_contenedor+" .collection .collection-item .btn-notificacion-leida").css({"cursor":"default"});
        $(id_contenedor+" .collection .collection-item .btn-notificacion-leida").removeClass("btn-notificacion-leida");

        var url = $("#base_url").val()+"/notificacion/marcar-todo-leido";
        var params = {_token:$("#general-token").val(),tipo:tipo};
        $("#label-todo-leido").addClass("hide");
        $("#load-todo-leido").removeClass("hide");
        $.post(url,params,function(data){
            $("#todo-leido").prop("checked",false);
            $("#label-todo-leido").removeClass("hide");
            $("#load-todo-leido").addClass("hide");
            cargarNumerosNotificaciones(false);
        })
        //
        /*$("#" + id_contenedor).addClass("hide");
        numeroNotificaciones(tipo,id_tab,titulo,nombreStorage);
        $("#load-notificaciones").removeClass("hide");
        var url = $("#base_url").val() + "/notificacion/notificaciones";
        var params = {_token: $("#general-token").val(), tipo: tipo};
        var numero = 0;
        $.post(url, params, function (data) {
            $("#" + id_contenedor).html(data);
            $("#load-notificaciones").addClass("hide");
            $("#" + id_contenedor).removeClass("hide");
        })*/
    })

    conn = new WebSocket('wss://vendiendo.com.co/wss2/');
    //conn = new WebSocket('ws://localhost:8080');
    conn.onopen = function(e) {
        //ActualizarValor("soporte/traer-total-tickets", "numero-notificaciones", false);
        //console.log("Connection established!");
        //var objeto=JSON.parse(e.data);
        Conectarse();
    }
    conn.onmessage = function(e) {
        var objeto = JSON.parse(e.data);
        //console.log(e.data);
        if(objeto.ubicacion=='notificacion'){
            cargarNumerosNotificaciones();
            lanzarNotificacion(objeto.mensaje);
        }
    };



    if(localStorage.getItem("notificacion")){
        lanzarNotificacion(localStorage.getItem('notificacion'));
        localStorage.removeItem('notificacion');
    }



    cargarNumerosNotificaciones();

    $("#btn-ver-mas-notificaciones").click(function () {
        var id_tab = $("#tabs-notificaciones .tab").children(".active").eq(0).attr("id");
        verMasNotificaciones(id_tab);
    })

    localStorage.setItem("notificaciones_inventario_indice",10);
    localStorage.setItem("notificaciones_cuentas_cobrar_indice",10);
    localStorage.setItem("notificaciones_cuentas_pagar_indice",10);
})

function lanzarNotificacion(mensaje){
    // var audio = new Audio($("#APP_URL").val()+'/assets/sounds/pop.mp3');
    // audio.play();
    if (Notification) {
        if (Notification.permission !== "granted") {
            Notification.requestPermission()
        }
        var title = "Vendiendo.co"
        var extra = {
            icon: "https://vendiendo.com.co/img/sistema/LogoVendiendo.png",
            body: mensaje
        }
        var noti = new Notification( title, extra)
        noti.onclick = {
// Al hacer click
        }
        noti.onclose = {
// Al cerrar
        }
        //setTimeout( function() { noti.close() }, 10000)
        //cargarNumerosNotificaciones();
    }
}

function Conectarse(){
    var params = {
        'roomId': mRoomid,
        'username': mUsername,
        'action': 'connect'
    };
    //console.log(params);
    conn.send(JSON.stringify(params));
}
/**
 * CArgando notificaciones
 */
function numeroNotificaciones(tipo,id_tab,titulo,storage_nombre,updateView) {

    var url = $("#base_url").val()+"/notificacion/count-notificaciones";
    var params = {_token:$("#general-token").val(),tipo:tipo};
    var numero = 0;
    var id_contenedor = $("#"+id_tab).attr("href");
    $.post(url,params,function(data){
        if(data == '0' || data == 0){
            localStorage.setItem(storage_nombre,0);
            if(updateView) {
                $(id_contenedor).html("");
                localStorage.setItem(storage_nombre+"_indice",10);
            }
        }else{
            if($.isNumeric(data)){
                numero = data;
                if(localStorage.getItem(storage_nombre) && (parseInt(numero) != localStorage.getItem(storage_nombre)) && updateView) {
                    $(id_contenedor).html("");
                    localStorage.setItem(storage_nombre+"_indice",10);
                }
                localStorage.setItem(storage_nombre,data);
            }else{
                localStorage.setItem(storage_nombre,0);
                if(updateView) {
                    $(id_contenedor).html("");
                    localStorage.setItem(storage_nombre+"_indice",10);
                }
            }
        }
        $("#"+id_tab).text(titulo+" ("+numero+")");
        dibujarNumeroNotificaciones();
    })
}

function vistaNotificaciones(id_tab,reload = false){

    $('#btn-ver-mas-notificaciones').addClass('hide');
    var tipo = "";
    var titulo = "";
    var nombreStorage = "";
    switch (id_tab){
        case 'tab-inventario':
            tipo = 'inventario';
            titulo = "Inventario";
            nombreStorage = "notificaciones_inventario";
            break;
        case 'tab-cuentas-cobrar':
            tipo = 'cuentas por cobrar';
            titulo = "Cuentas por cobrar";
            nombreStorage = "notificaciones_cuentas_cobrar";
            break;
        case 'tab-cuentas-pagar':
            tipo = 'cuentas por pagar';
            titulo = "Cuentas por pagar";
            nombreStorage = "notificaciones_cuentas_pagar";
            break;
    }
    var id_contenedor = $("#"+id_tab).attr("href").split("#")[1];
    if($("#"+id_contenedor).html() == "" || reload) {
        $("#" + id_contenedor).addClass("hide");
        numeroNotificaciones(tipo,id_tab,titulo,nombreStorage);
        $("#load-notificaciones").removeClass("hide");
        var url = $("#base_url").val() + "/notificacion/notificaciones";
        var params = {_token: $("#general-token").val(), tipo: tipo};
        var numero = 0;
        $.post(url, params, function (data) {
            $("#" + id_contenedor).html(data.vista);
            $("#load-notificaciones").addClass("hide");
            $("#" + id_contenedor).removeClass("hide");
            localStorage.removeItem(nombreStorage+"_indice");
            if(data.cantidad == 0){
                $('#btn-ver-mas-notificaciones').addClass('hide');
                sin_notificaciones[id_tab]= id_tab;
            }else {
                $('#btn-ver-mas-notificaciones').removeClass('hide');
            }
        })
    }else {
        if(!sin_notificaciones[id_tab]){
            $('#btn-ver-mas-notificaciones').removeClass('hide');
        }
    }
}

function cargarNumerosNotificaciones(updateView = true) {
    numeroNotificaciones("inventario","tab-inventario","Inventario","notificaciones_inventario",updateView);
    numeroNotificaciones("cuentas por cobrar","tab-cuentas-cobrar","Cuentas por cobrar","notificaciones_cuentas_cobrar",updateView);
    numeroNotificaciones("cuentas por pagar","tab-cuentas-pagar","Cuentas por pagar","notificaciones_cuentas_pagar",updateView);
}

function dibujarNumeroNotificaciones(){
    var notificaciones_inventario = 0;
    var notificaciones_cuentas_pagar = 0;
    var notificaciones_cuentas_cobrar = 0;
    var total_notificaciones = 0;

    if(localStorage.getItem("notificaciones_inventario"))
        notificaciones_inventario = parseInt(localStorage.getItem("notificaciones_inventario"));
    total_notificaciones += notificaciones_inventario;

    if(localStorage.getItem("notificaciones_cuentas_cobrar"))
        notificaciones_cuentas_cobrar = parseInt(localStorage.getItem("notificaciones_cuentas_cobrar"));
    total_notificaciones += notificaciones_cuentas_cobrar;

    if(localStorage.getItem("notificaciones_cuentas_pagar"))
        notificaciones_cuentas_pagar = parseInt(localStorage.getItem("notificaciones_cuentas_pagar"));
    total_notificaciones += notificaciones_cuentas_pagar;

    if(total_notificaciones == 0){
        //$("#btn-notificaciones").fadeOut(500);
        $("#numero-notificaciones").text("");
    }else {
        //$("#btn-notificaciones").fadeIn(500);
        $("#numero-notificaciones").text(total_notificaciones);
    }
}

function verMasNotificaciones(id_tab){
    var tipo = "";
    var nombreStorage = "";
    switch (id_tab){
        case 'tab-inventario':
            tipo = 'inventario';
            nombreStorage = "notificaciones_inventario_indice";
            break;
        case 'tab-cuentas-cobrar':
            tipo = 'cuentas por cobrar';
            nombreStorage = "notificaciones_cuentas_cobrar_indice";
            break;
        case 'tab-cuentas-pagar':
            tipo = 'cuentas por pagar';
            nombreStorage = "notificaciones_cuentas_pagar_indice";
            break;
    }
    var id_contenedor = $("#"+id_tab).attr("href").split("#")[1];
    if($("#"+id_contenedor+" #sin-notificaciones").length == 0) {

        var url = $("#base_url").val() + "/notificacion/notificaciones";
        var indice = 10;
        if (localStorage.getItem(nombreStorage)) {
            indice = parseInt(localStorage.getItem(nombreStorage));
        }
        localStorage.setItem(nombreStorage, indice + 10);
        var params = {_token: $("#general-token").val(), tipo: tipo, indice: indice};

        $("#btn-ver-mas-notificaciones").addClass("hide");
        $("#load-ver-mas-notificaciones").removeClass("hide");
        $.post(url, params, function (data) {
            $("#" + id_contenedor).append(data.vista);
            $("#btn-ver-mas-notificaciones").removeClass("hide");
            $("#load-ver-mas-notificaciones").addClass("hide");

            if(data.cantidad == 0){
                sin_notificaciones[id_tab] = id_tab;
                $('#btn-ver-mas-notificaciones').addClass('hide');
            }
        })
    }
}