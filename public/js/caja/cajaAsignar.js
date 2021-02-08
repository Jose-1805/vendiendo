var caja_select = null;
var usuario_select = null;
var valor = 0;
var caja_dragstart = null;

$(function () {
    $("span.usuario.hide").each(function(i,el){
        var html = '<li class="usuario" data-usuario="'+$(el).data("usuario")+'">'+$(el).html()+'</li>';
        $(el).parent().append(html);
        $(el).remove();
    })

    $(".usuario").draggable({
        connectToSortable: ".contenedor-usuarios",
        revert: "true",
        appendTo: "body",
        zIndex:1000
    });


    $(".contenedor-usuarios").sortable({
        revert: true,
    });

    //PARA CUANDO YA EXISTEN CAJAS CON USUARIOS ASIGNADOS Y SE PUEDA SEGUIR HACIENDO DRAN & DROP
    //SE DEBE LLAMAR DESPUES $(".usuario").draggable... Y $(".contenedor-usuarios").sortable({...
    $(".asignado").each(function(i,el){
        $(el).removeClass("contenedor-usuarios");
    })


    $("body").on("dragstart",".usuario",function(event,ui){
        if($(event.target).parent().hasClass("caja"))
            caja_dragstart = $(event.target).parent().data("caja");
        else
            caja_dragstart = null;

        $(event.target).removeClass("leftTop");
        $("body .contenedor-usuarios").addClass("active-class");

        $(".caja").each(function (i, el) {
             if (!$(el).children(".usuario").length && $(el).hasClass("destino")) {
                 $(el).addClass("contenedor-usuarios");
                 $(el).removeClass("asignado");
             }
        })
    })

    $("body").on("dragstop",".usuario",function(event,ui){
        caja_select = null;
        valor = null;
        var elemento = event.target;
        var caja = $(elemento).parent();
        var asignado = false;
        if($(caja).hasClass("caja")){
            var cantidad = 0;
            $(caja).children(".usuario").each(function(i,el){
                if($(el).html() != "")cantidad++;
            })

            if(cantidad > 1){
                var html = '<li class="collection-item avatar usuario" style="min-height: 52px;">'+$(elemento).html()+'</li>';
                $(".contenedor-usuarios.inicial").eq(0).append(html);
                $(elemento).remove();

                $(".usuario").draggable({
                    connectToSortable: ".contenedor-usuarios",
                    revert: "true",
                });

                $(".contenedor-usuarios").sortable({
                    revert: true,
                });
            }
            asignado = true;
            //alert($(caja).children(".usuario").length+"\n"+$(caja).html());
        }


        $("body .contenedor-usuarios").removeClass("active-class");
        if(asignado){
            $(caja).removeClass("contenedor-usuarios");
            $(caja).addClass("asignado");
            caja_select = $(caja).data("caja");

            if(caja_dragstart != caja_select){
                //valor = prompt("Ingrese el valor inicial de la caja","0");
                $("#modal-asignar").openModal({dismissible: false});
                $("#modal-asignar #valor").val("");
                $("#modal-asignar #valor").focus();

            }
        }


        if(caja_dragstart){
            $("#dropdown_opciones_caja_"+caja_dragstart).html('<li onclick="cerrar('+caja_dragstart+')"><a href="#!">Cerrar</a></li>');
        }

        setTimeout(function(){
            $(elemento).removeClass("leftTop");
            $(elemento).addClass("leftTop");
        },500);

        $(".caja").each(function (i, el) {
            if (!$(el).children(".usuario").length && $(el).hasClass("destino")) {
                $(el).addClass("contenedor-usuarios");
                $(el).removeClass("asignado");
            }
        })

        usuario_select = $(elemento).data("usuario");
        if(!asignado) {
            setTimeout(function () {
                asignacion();
            },500);
        }
    })

    $("body").on("keyup","#efectivo_real",function(){
        var calculado = $("#efectivo_calculado").val();
        var efectivo_real = parseInt($(this).val());
        if(!efectivo_real)efectivo_real = 0;

        if(calculado > efectivo_real){
            $("#faltante").addClass("red-text");
            $("#restante").html("0");
            $("#faltante").html(number_format((calculado-efectivo_real),0));
        }else{
            $("#faltante").removeClass("red-text");
            $("#restante").html(number_format((efectivo_real - calculado),0));
            $("#faltante").html("0");
        }
    })

    $("#modal-asignar #valor").keyup(function (e) {
        if(e.keyCode == 13){
            sendAsignacion();
        }
    })
})

function sendAsignacion(){
    valor = $("#modal-asignar #valor").val();
    if($.isNumeric(valor)){
        asignacion();
        $("#modal-asignar").closeModal();
    }else{
        lanzarToast("El campo valor debe ser numérico.","Error",8000,"red-text");
    }
}

function asignacion(){
    //alert("Usuario: "+usuario_select +" -- Caja: "+caja_select);
    var params = {usuario:usuario_select,caja:caja_select,valor:valor,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/caja/asignar";
    $.post(url,params,function(){
        if(caja_select) {
            $("#dropdown_opciones_caja_" + caja_select).html(
                '<li onclick="cerrar(' + caja_select + ')"><a href="#!">Cerrar</a></li>' +
                '<li><a href="#modal-realizar-transaccion" onclick="caja_select = '+caja_select+'" class="modal-trigger">Envío a caja maestra</a></li>'
            )
        }
        caja_select = null;
        usuario_select = null;
        inicializarMaterialize();
    }).error(function(jqXHR,state,error){
       // window.location.reload();
    })
}

function abrir(id){
    var params = {_token:$("#general-token").val(),caja:id};
    var url = $("#base_url").val()+"/caja/abrir";
    DialogCargando("Procesando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload();
        }
    }).error(function (jqXHR,error,state) {
        alert("Ocurrio un error inesperado, la página sera actualizada");
        window.location.reload();
    })
}

function cerrar(id){
    caja_select = id;
    var params = {_token:$("#general-token").val(),caja:id};
    var url = $("#base_url").val()+"/caja/vista-cerrar";
    DialogCargando("Cargando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload();
        }else {
            $("#contenido-cerrar-caja").html(data);
            $("#modal-cerrar-caja").openModal();
            CerrarDialogCargando();
        }
    }).error(function (jqXHR,error,state) {
        alert("Ocurrio un error inesperado, la página sera actualizada");
        //window.location.reload();
    })
}

function cerrar_send(){
    var params = {_token:$("#general-token").val(),caja:caja_select,efectivo_real:$("#efectivo_real").val()};
    var url = $("#base_url").val()+"/caja/cerrar";
    DialogCargando("Procesando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload();
        }
    }).error(function (jqXHR,error,state) {
        caja_select = null;
        alert("Ocurrio un error inesperado, la página sera actualizada");
        //window.location.reload();
    })
}

function realizarTransaccion(){
    var params = $("#form-realizar-transaccion").serialize()+"&caja="+caja_select;
    var url = $("#base_url").val()+"/caja/realizar-transaccion";
    DialogCargando("Realizando envio ...");
    $.post(url,params,function (data) {
        if(data.success){
            $("#modal-realizar-transaccion").closeModal();
            $("#modal-realizar-transaccion #valor").val("");
            $("#modal-realizar-transaccion #comentario").val("");
            $("#modal-realizar-transaccion #tipo option[value='']").prop("selected","selected");
            ocultarErrores("contenedor-errores-realizar-transaccion");
            CerrarDialogCargando();
            lanzarToast("El envío ha sido realizado con éxito","Confirmacion",8000);
        }
    }).error(function (jqXHR,error,state) {
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-realizar-transaccion",JSON.parse(jqXHR.responseText));
    })
}