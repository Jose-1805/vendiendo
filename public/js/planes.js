$(function(){
    $(".controlador-lista").click(function () {
        var contenedor_lista = $(this).parent().parent().children(".contenedor-lista").eq(0);
        $(contenedor_lista).toggleClass("hide");
    })

    $(".check").change(function(e){
        var contenedor_lista = $(this).parent().parent().children(".contenedor-lista");
        var estado = false;
        if($(this).prop("checked"))
            var estado = "checked";
        else
            $("#check-seleccionar-todo").prop("checked",false);
        checked(contenedor_lista,estado);
        $(this).focus();
    })
    
    $("#check-seleccionar-todo").change(function () {
        var contenedor_lista = $("#contenedor-lista-check-modulos");
        var estado = false;
        if($(this).prop("checked"))
            var estado = "checked";
        checked(contenedor_lista,estado);
    })
})

function action(){
    var url = $("#form-plan").attr("action");

    var params = $("#form-plan").serialize();
    $("#contenedor-botones-plan").addClass("hide");
    $("#progress-plan").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            $("body").scrollTop(0);
            window.location.reload();
        }else {
            console.log('if');
            $("#contenedor-botones-plan").removeClass("hide");
            $("#progress-plan").addClass("hide");
        }
    }).error(function(jqXHR,state,error){
        $("body").scrollTop(0);
        mostrarErrores("contenedor-errores-accion-plan",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-plan").removeClass("hide");
        $("#progress-plan").addClass("hide");
    });
}

function checked(elemento_contenedor_lista,estado){
    $(elemento_contenedor_lista).children(".contenedor-item-lista").children(".contenedor-check").children(".check").prop("checked",estado);
    $(elemento_contenedor_lista).children(".contenedor-item-lista").children(".contenedor-lista").each(function (i,el) {
        checked(el,estado);
    })
}

