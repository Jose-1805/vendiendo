var precioCosto = 0;
var utilid = 0;
var ivaa = 0;
var i = 1;
$(document).ready(function(){
    var options_proveedores = $("#proveedores-count").val();
    //alert("cargo"+ options_proveedores);

    $("#imagen").change(function(){
        mostrarImagen(this,"preview");
        $("#imagen_producto").remove();
    });

    $("body").on("blur keyup",".precio_costo",function(e) {
        i = (this.id).replace("precio_costo_", "");
        combrobarCamposVacios(i);
    });
     $("body").on("blur keyup",".iva",function(e){
        i = (this.id).replace("iva_", "");
        combrobarCamposVacios(i);
    });

    $("body").on("blur keyup",".utilidad",function(e){
        i = (this.id).replace("utilidad_", "");
        combrobarCamposVacios(i);
    });
    $("body").on("keyup",".precio_venta",function(event){
        //if(event.keyCode != 9) {
                var precio_venta = parseFloat($(this).val());

            if (!$.isNumeric(precio_venta)) {
                precio_venta = 0;
                $(this).val("0");
            }
            var padre = $(this).parent().parent();

            if ($(padre).children("div").children(".precio_costo").val() && $(padre).children("div").children(".precio_costo").val() != "NaN") {
                var input_precio_costo = $(padre).children("div").children(".precio_costo").eq(0);
                var precio_costo = parseFloat($(input_precio_costo).val());

                var iva = 0;
                var input_iva = $(padre).children("div").children(".iva");
                if ($(input_iva).val()) {
                        iva = parseFloat($(input_iva).val());
                }


                precio_antes_iva = (precio_venta / ((100 + iva) / 100));

                /** Aquí se juntan las dos ecuaciones para calcular la utilizar, puedes usar la de la lineas anteriores o la siguiente**/
                var utilidad = (((precio_venta / ((100 + iva) / 100)) / precio_costo) - 1) * 100;

                var input_utilidad = $(padre).children("div").children(".utilidad");

                if($.isNumeric(utilidad))
                    $(input_utilidad).val(parseFloat(utilidad).toFixed(7));
                else
                    $(input_utilidad).val("");
            } else {
                $(padre).children("div").children(".utilidad").val("");
            }
        //}
    })
    
    $("body").on("focus",".precio_venta",function () {
        var valor = $(this).val();
        $(this).attr("data-decimals","2");
        if($.isNumeric(valor)){
            valor = parseFloat(valor).toFixed(2);
            $(this).val(valor);
            $(this).keyup();
        }
        $(this).addClass("num-real");
        var input_utilidad = $(this).parent().parent().children("div").children(".utilidad").eq(0);
        $(input_utilidad).removeClass("num-real");
        $(input_utilidad).prop("readonly","readonly");
        $(input_utilidad).css({cursor:"no-drop",color:"#b0b0b0"});
    })


    $("body").on("focusout","#precio_costo",function(event){
            var precio_costo = parseFloat($(this).val());
            if (!$.isNumeric(precio_costo)) {
                precio_costo = 0;
                $(this).val("0");
            }
            var iva = 0;
            if ($("#iva").val())iva = parseFloat($("#iva").val());

            if ($("#utilidad").val() && $("#utilidad").val() != "NaN") {
                var utilidad = parseFloat($("#utilidad").val());
                var precio_venta = ((precio_costo * utilidad) / 100) + precio_costo;//valor sin iva
                precio_venta += (precio_venta * iva) / 100;
                $("#precio_venta").val(parseFloat(precio_venta).toFixed(5));
            } else if ($("#precio_venta").val() && $("#precio_venta").val() != "NaN") {
                var precio_venta = parseFloat($("#precio_venta").val());

                precio_antes_iva = (precio_venta / ((100 + iva) / 100));

                /** Aquí se juntan las dos ecuaciones para calcular la utilizar, puedes usar la de la lineas anteriores o la siguiente**/
                var utilidad = (((precio_venta / ((100 + iva) / 100)) / precio_costo) - 1) * 100;
                $("#utilidad").val(parseFloat(utilidad).toFixed(7));
            }
    })

    $("body").on("keyup","#utilidad",function(event){
        if(event.keyCode != 9 && event.keyCode != 8 && event.keyCode != 8) {
            var utilidad = parseFloat($(this).val());
            if (!$.isNumeric(utilidad)) {
                utilidad = 0;
                $(this).val("0");
            }
            var padre = $(this).parent().parent();

            if ($("#precio_costo").val() && $("#precio_costo").val() != "NaN") {
                var precio_costo = parseFloat($("#precio_costo").val());
                var iva = 0;
                if ($("#iva").val())iva = parseFloat($("#iva").val()).toFixed(5);
                var precio_venta = ((precio_costo * utilidad) / 100) + precio_costo;//valor sin iva
                precio_venta += (precio_venta * iva) / 100;
                var dec = 7;
                if($('#precio_venta').data("decimals")){
                    dec = 2;
                }
                $("#precio_venta").val(parseFloat(precio_venta).toFixed(dec));
            } else {
                $("#precio_venta").val("");
            }
        }
    })

    $("body").on("keyup","#iva",function(event){
        //if(event.keyCode != 9 && event.keyCode != 8 && event.keyCode != 8) {
            var iva = parseFloat($(this).val());
            if (!$.isNumeric(iva)) {
                iva = 0;
                $(this).val("0");
            }
            var padre = $(this).parent().parent();

            if ($("#precio_costo").val() && $("#precio_costo").val() != "NaN") {
                var precio_costo = parseFloat($("#precio_costo").val());

                if ($("#utilidad").val()) {
                    var utilidad = parseFloat($("#utilidad").val());
                    var precio_venta = ((precio_costo * utilidad) / 100) + precio_costo;//valor sin iva
                    precio_venta += (precio_venta * iva) / 100;
                    var dec = 7;
                    if($('#precio_venta').data("decimals")){
                        dec = 2;
                    }
                    if($.isNumeric(precio_venta))
                        $("#precio_venta").val(parseFloat(precio_venta).toFixed(dec));
                    else
                        $("#precio_venta").val("");
                } else if ($("#precio_venta").val() && $("#precio_venta").val() != "NaN") {
                    var precio_venta = parseFloat($("#precio_venta").val());

                    var utilidad = (((precio_venta / ((100 + iva) / 100)) / precio_costo) - 1) * 100;
                    if($.isNumeric(utilidad))
                        $("#utilidad").val(parseFloat(utilidad).toFixed(7));
                    else
                        $("#utilidad").val("");
                }
            }
        //}
    })
    $("body").on("keyup","#precio_venta",function(event){
        //if(event.keyCode != 9 && event.keyCode != 8) {
            var precio_venta = parseFloat($(this).val());
            if (!$.isNumeric(precio_venta)) {
                precio_venta = 0;
                $(this).val("0");
            }
            var padre = $(this).parent().parent();

            if ($("#precio_costo").val() && $("#precio_costo").val() != "NaN") {
                var precio_costo = 0;
                if($("#precio_costo").val())
                    precio_costo = parseFloat($("#precio_costo").val());

                var iva = 0;
                if ($("#iva").val())
                    iva = parseFloat($("#iva").val());


                precio_antes_iva = (precio_venta / ((100 + iva) / 100));

                /** Aquí se juntan las dos ecuaciones para calcular la utilizar, puedes usar la de la lineas anteriores o la siguiente**/
                var utilidad = (((precio_venta / ((100 + iva) / 100)) / precio_costo) - 1) * 100;
                if($.isNumeric(utilidad))
                    $("#utilidad").val(parseFloat(utilidad).toFixed(7));
                else
                    $("#utilidad").val("");
            } else {
                $("#utilidad").val("");
            }
        //}
    })

    $("body").on("focus","#precio_venta",function () {
        var valor = $(this).val();
        $(this).attr("data-decimals","2");
        if($.isNumeric(valor)){
            valor = parseFloat(valor).toFixed(2);
            $(this).val(valor);
            $(this).keyup();
        }
        $(this).addClass("num-real");
        var input_utilidad = $("#utilidad");
        $(input_utilidad).removeClass("num-real");
        $(input_utilidad).prop("readonly","readonly");
        $(input_utilidad).css({cursor:"no-drop",color:"#b0b0b0"});
    })

});

function viewMateriasPrimas(id,accion) {
    inicializarMaterialize();
    var url = $("#base_url").val()+"/productos/contenido";
    var count_proveedor = $("#proveedores-count").val();
    var count_materia_prima = $("#materias-primas-count").val();
    if(id =="Terminado"){
        $("#contenedor-stock").addClass("hide");
        $("#precios").addClass("hide");
        $.ajax({
            url: url+"/Terminado/"+accion,
            success: function(data) {
                $('#form-contenido').empty();
                $('#form-contenido').html(data);
                $("#costo_materias_primas_hidden").html("Precio costo (Costo materias primas: $"+number_format(0,2)+")");
                if(count_proveedor == 0)
                    $("#mensaje-advertencia").html("<p style='text-align: center; background-color: #80d8ff; color: #0D47A1'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Para la creación de un producto terminado se requiere que haya registrado al menos un proveedor</p>");
                inicializarMaterialize();
            }
        });

    }else if(id=="Compuesto" || id=="Preparado" ){
        $("#precios").removeClass("hide");
        $("#contenedor-stock").removeClass("hide");
        $.ajax({
            url: url+"/Compuesto/"+accion,
            success: function(data) {
                $('#form-contenido').empty();
                $('#form-contenido').html(data);
                $("#costo_materias_primas_hidden").html("Precio costo (Costo materias primas: $"+number_format(0,2)+")");
                if(count_materia_prima == 0)
                    $("#mensaje-advertencia").html("<p style='text-align: center; background-color: #80d8ff; color: #0D47A1'><i class='fa fa-exclamation-triangle' aria-hidden='true'></i> Para la creación de un producto compuesto o preparado se requiere que haya registrado al menos una materia prima</p>");
                inicializarMaterialize();
            }
        });

    }
}

function combrobarCamposVacios(i) {
        precioCosto = parseFloat($('#precio_costo_' + i).val());
        ivaa =  parseFloat($('#iva_' + i).val());
        utilid =      parseFloat($('#utilidad_' + i).val());

    var true_costo =    (!(isNaN(precioCosto) || (precioCosto == 0)))//true cuando no es numero o es cero
    var true_utilidad = (!(isNaN(utilid) || (utilid == 0))) //true cuando no es numero o es cero


    if (true_costo && true_utilidad)
    {
        calcularPrecioVenta(i, precioCosto, ivaa, utilid)
    }else{
        $('#precio_venta_' + i).val("");
    }
}

function calcularPrecioVenta(i, costo, iv, ut){
    var pre_venta = (costo * ut) / 100 + costo;//valor sin iva
    if(iv > 0){
        pre_venta = (pre_venta * iv) / 100 + pre_venta;//valor con iva
    }
    var dec = 7;
    if($('#precio_venta_' + i).data("decimals")){
        dec = 2;
    }
    $('#precio_venta_' + i).val(pre_venta.toFixed(dec));
}