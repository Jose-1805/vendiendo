var productosCarrito={};
$(document).ready(function () {
    var alturas_div = $(".categoria_card").map(function () {
        return $(this).height();
    }).get();
    altura_max = Math.max.apply(null, alturas_div);
    $(".categoria_card").height(altura_max);


    $(".div-categoria").click(function () {
        var id_categoria = this.id.replace('div-categoria-','');
        var color = $(this).data('color');

        localStorage.posicion_categoria = "producto_by_categoria";

        var url = $("#base_url").val()+"/pedido/show/"+id_categoria;

        $.get(url, function(res){
            $("#div-lista-categorias").addClass('hide');
            $("#lista-productos-categoria").removeClass('hide');
            $("#lista-productos-categoria").html(res);
            $(".producto_card").removeClass('blue-grey');
            $(".producto_card").addClass(color);
            $(".detalle-producto-card").removeClass('blue-grey');
            $(".detalle-producto-card").addClass(color);
            inicilizarPedido();
        }).done(function () {
            $("#undo-categoria").removeClass('hide');
        });
    });
    $("#undo-categoria").click(function () {
        $("#lista-productos-categoria").addClass('hide');
        $("#div-lista-categorias").removeClass('hide');
        $("#undo-categoria").addClass('hide');
    });

});
function cargarPedido() {
    var cantidadProductos =0;
    if(localStorage.getItem("productosC")){
        var obj = JSON.parse(localStorage.getItem("productosC"));
        for (x in obj){
            cantidadProductos++;
        }
        if(cantidadProductos > 0){
            if ($("#contenedor-botones-encabezado .btn-carrito").length ==0){
                $("#contenedor-botones-encabezado").append( "<a class='btn waves-effect red darken-2 btn-floating btn-carrito' id='btn-carrito' onclick='openModalCarrito()' style='pointer-events: auto;'><i class='fa fa-cart-plus'></i></a>" );
            }
        }

    }else {
        //console.log("No Existe local storage");
    }
}
function verProductos() {
    //alert(event);
    //event.preventDefault();
    var id_categoria = $("#select-categoria").val()

    var url = $("#base_url").val()+"/pedido/show/"+id_categoria;

    //$("#datos-producto").empty();
    $.get(url, function(res){
        $("#lista-productos-categoria").html(res);
        inicilizarPedido();
    });

}
function agregarCantidadProducto(id_producto) {
    var aux='1';
    var cantidadProductos=0;
    //$(".cantidad-pedido").blur();
    //$("#cantidad-"+id_producto).focusin();
    if(!$("#"+id_producto).is(':checked')) {
        //console.log("seleccionado");
        $("#detalle-producto-"+id_producto).css({'display':'none'});
        if ( localStorage.getItem("productosC")){
            if(delete productosCarrito[id_producto]){
                localStorage.setItem("productosC",JSON.stringify(productosCarrito));
            }
            for (x in productosCarrito){
                cantidadProductos++;
            }
            if (cantidadProductos == 0){
                localStorage.removeItem('productosC');
                $("#btn-carrito").remove();
            }
            $("#cantidad-"+id_producto).val(aux);
            $("#btn-agregar-carrito-"+id_producto).removeClass('light-blue-text');
            inicilizarPedido();
        }
    } else {
        $("#detalle-producto-"+id_producto).css({'display':'block'});
        $("#cantidad-"+id_producto).focus();
    }
}
function agregarCantidadProductoDiv(id_producto) {

    if(!$("#"+id_producto).is(':checked')) {
        //alert("Está activado");
        $("#"+id_producto).prop("checked", "checked");
        $("#div-contenedor-producto-card-"+id_producto).css('box-shadow', '0 0 15px 5px #00c0e4');
    } else {
        //alert("No está activado");
        $("#"+id_producto).prop("checked", "");
        $("#div-contenedor-producto-card-"+id_producto).css('box-shadow', '0 0 0 0 #999');
    }
    agregarCantidadProducto(id_producto);

}
function agregarACarrito(id_producto,producto,precio_costo,sigla,iva,utilidad,stock) {
    var cantidad = $("#cantidad-"+id_producto).val();
    //console.log(producto);
    if (cantidad == 0 || cantidad ==''){
        $("#mensaje-"+id_producto).show(1000,function() {
            $("#mensaje-"+id_producto).css({'display':'block'});
            $("#mensaje-"+id_producto).html("Cantidad no puede ser nula");
        });
        setTimeout(function() {
            $("#mensaje-"+id_producto).fadeOut(1500);
        },3000);
    }else{
        //localStorage.clear();
        if(typeof (Storage) !== "undefined"){

            var continuar=true;
            var productoObj={
                'IdProducto':id_producto,
                'Nombre':producto,
                'Stock':stock,
                'Cantidad':cantidad,
                'Precio':precio_costo,
                'Sigla':sigla,
                'Iva':iva,
                'utilidad':utilidad
            };
            if(productosCarrito[id_producto]){
                if (window.confirm("El producto seleccionado ya ha sido agregado al pedido, desea sobre-escribirlo?")) {
                    productosCarrito[id_producto] = productoObj;
                    continuar=false;
                    $("#cantidad-"+id_producto).val(productoObj.Cantidad);
                }else {
                    //productoObj=null;
                    continuar=false;
                    $("#cantidad-"+id_producto).val(productosCarrito[id_producto].Cantidad);
                    return false;                }
            }else {
                productosCarrito[id_producto]=productoObj;
            }
            $("#mensaje-confirmacion-"+id_producto).show(1000,function() {
                $("#mensaje-confirmacion-"+id_producto).css({'display':'block'});
                $("#mensaje-confirmacion-"+id_producto).css({'color':'white','background-color':'#00c0e4'});
                $("#mensaje-confirmacion-"+id_producto).html("Producto agregado al carrito");
            });
            setTimeout(function() {
                $("#mensaje-confirmacion-"+id_producto).fadeOut(1500);
            },3000);

            if ($("#contenedor-botones-encabezado .btn-carrito").length == 0) {
                $("#contenedor-botones-encabezado").append("<a class='btn waves-effect red darken-2 btn-floating btn-carrito' id='btn-carrito' onclick='openModalCarrito()' style='pointer-events: auto;'><i class='fa fa-cart-plus'></i></a>");
            }
            localStorage.setItem("productosC",JSON.stringify(productosCarrito));
            $("#btn-agregar-carrito-"+id_producto).addClass('light-blue-text');
        }else {
            alert("Lo sentimos, su navehador no es compatible");
        }


    }
}
function inicilizarPedido() {
    if(localStorage.getItem("productosC")){
        var obj = JSON.parse(localStorage.getItem("productosC"));
        for(i in obj){
            productosCarrito[i] = obj[i];
            if(productosCarrito[i]!=null){
                $("#"+productosCarrito[i].IdProducto).prop('checked',true);
                $("#detalle-producto-"+productosCarrito[i].IdProducto).css({'display':'block'});
                $("#cantidad-"+productosCarrito[i].IdProducto).val(productosCarrito[i].Cantidad);
                $("#cantidad-slider-"+productosCarrito[i].IdProducto).val(productosCarrito[i].Cantidad);
                $("#div-contenedor-producto-card-"+productosCarrito[i].IdProducto).css('box-shadow', '0 0 15px 5px #00c0e4');
                $("#btn-agregar-carrito-"+productosCarrito[i].IdProducto).addClass('light-blue-text');
            }

        }
    }
}
function openModalCarrito() {
    productosCarrito={};
    if(localStorage.getItem("productosC")){
        var obj = JSON.parse(localStorage.getItem("productosC"));
        for(i in obj){
            productosCarrito[obj[i].IdProducto] = obj[i];
        }
        var tabla ="";
        var totalPagar=0;
        var cantidadProductos=0;

        for (j in productosCarrito){
            cantidadProductos++;
        }
        if (cantidadProductos > 0){
            tabla +="<table class='striped'>" +
                "<thead><th>Nombre</th><th>Unidad</th><th>Precio unitario</th><th>Cantidad</th><th>Total sin iva</th><th>Iva</th></thead>" +
                "<tbody>";

            for(i in productosCarrito){
                if(productosCarrito[i]!=null){
                    var totalSinIva = (parseFloat(productosCarrito[i].Precio)+parseFloat(((productosCarrito[i].Precio*productosCarrito[i].utilidad)/100))) * productosCarrito[i].Cantidad;
                    var total = totalSinIva + (parseFloat(totalSinIva * productosCarrito[i].Iva)/100);
                    totalPagar = totalPagar +total;
                    tabla +="<tr>";
                    tabla += "<td>"+ productosCarrito[i].Nombre +"</td>";
                    tabla += "<td>"+ productosCarrito[i].Sigla +"</td>";
                    tabla += "<td>$"+ number_format(parseFloat(productosCarrito[i].Precio)+parseFloat(((productosCarrito[i].Precio*productosCarrito[i].utilidad)/100)),2) +"</td>";
                    tabla += "<td>"+  number_format(productosCarrito[i].Cantidad,2) +"</td>";
                    tabla += "<td>$"+ number_format(totalSinIva,2) +"</td>";
                    tabla += "<td>"+ productosCarrito[i].Iva +"% </td>";
                    tabla +="</tr>";
                }

            }
            tabla +="</tbody></table>" +
                "<div><p class='titulo-modal right-align'><b>Total a pagar: $"+number_format(totalPagar,2)+"</b></p></div>";
            $('#modal-detalle-carrito').openModal();
            $('#contenido-detalle-carrito').html(tabla);
        }
    }
}
function descartarPedido() {

    var mensaje = "";
    var aux = 1;
    if(localStorage.getItem("productosC")){
        mensaje += aux+". ¿Realmente desea descartar el pedido?, los productos seleccionados seran eliminados del carrito. \n";
        aux++;
    }
    if(localStorage.getItem("pedido")){
        mensaje += aux+". La información almacenada en Mis pedidos se perderá. \n";
        aux++;
    }
    if(mensaje != ""){
        if (window.confirm(mensaje)) {
            localStorage.removeItem('productosC');
            localStorage.removeItem('pedido');
            window.location.reload(true);
        }else {
            event.preventDefault();
        }
    }else {
        window.location.reload(true);
    }
    //console.log(localStorage)

}
function number_format(amount, decimals) {

    amount += ''; // por si pasan un numero en vez de un string
    amount = parseFloat(amount.replace(/[^0-9\.]/g, '')); // elimino cualquier cosa que no sea numero o punto

    decimals = decimals || 0; // por si la variable no fue fue pasada

    // si no es un numero o es igual a cero retorno el mismo cero
    if (isNaN(amount) || amount === 0)
        return parseFloat(0).toFixed(decimals);

    // si es mayor o menor que cero retorno el valor formateado como numero
    amount = '' + amount.toFixed(decimals);

    var amount_parts = amount.split('.'),
        regexp = /(\d+)(\d{3})/;

    while (regexp.test(amount_parts[0]))
        amount_parts[0] = amount_parts[0].replace(regexp, '$1' + '.' + '$2');

    return amount_parts.join(',');
}