var valor = 0;
$(function(){
    $(".btn-numero").click(function () {
        var num = $(this).children("span").text();
        var nuevo = "";
        if(valor == 0 || valor == null)nuevo = num;
        else nuevo = valor+""+num
        valor = nuevo;
        $("#teclado-numerico #valor-numero").text(number_format(nuevo,0));
        $("#teclado-numerico #valor-numero").data("valor",valor);
        $("#valor-numero").trigger("update");
    })

    $(".btn-opcion#borrar").click(function(){
        if(valor != 0 ) {
            valor = "" + valor.slice(0, -1);
            $("#teclado-numerico #valor-numero").text(number_format(valor, 0));
            $("#teclado-numerico #valor-numero").data("valor", valor);
            $("#valor-numero").trigger("update");
        }
    })

    $(".btn-opcion#vaciar").click(function(){
        if(valor != 0) {
            valor = 0;
            $("#teclado-numerico #valor-numero").text("0");
            $("#teclado-numerico #valor-numero").data("valor", valor);
            $("#valor-numero").trigger("update");
        }
    })

    $(".btn-opcion#cerrar").click(function(){
        cerrarTecladoNumerico();
    })

    $("body").on("click",".trigger-teclado-numerico",function () {
        tecladoNumerico($(this),0);
    })

    $("body").click(function (e) {
        if($("#teclado-numerico").find($(e.target)).length == 0 && $(e.target).attr("id") != "teclado-numerido" && !$(e.target).hasClass("trigger-teclado-numerico"))cerrarTecladoNumerico();
    })

})

function tecladoNumerico(elemento,valor_nuevo){
    valor = valor_nuevo;
    $("#teclado-numerico #valor-numero").text(number_format(valor_nuevo,0));
    var left = $(elemento).offset().left;
    var top = $(elemento).offset().top;
    var ancho = $(elemento).width();
    var alto = $(elemento).height();

    var ancho_calculadora = $("#teclado-numerico").width();

    var ancho_ventana = $(window).width();

    var nuevo_left = left - ((ancho_calculadora/2)-(ancho/2));

    if(ancho_ventana < (nuevo_left+ancho_calculadora)){
        nuevo_left = (ancho_ventana/2)-(ancho_calculadora/2);
    }

    $("#teclado-numerico").css({left:nuevo_left+"px",top:(top+alto)+"px"});
    $("#teclado-numerico").fadeIn(500);
    $("#valor-numero").trigger("start");
}

function cerrarTecladoNumerico(){
    $("#teclado-numerico").fadeOut(500);
    valor = 0;
    $("#teclado-numerico #valor-numero").text(number_format(valor,0));
    $("#teclado-numerico #valor-numero").data("valor",valor);
    $("#valor-numero").trigger("close");
}

function setValueTecladoNumerico(value) {
    valor = value;
    $("#teclado-numerico #valor-numero").text(number_format(valor,0));
}