var skip = null;
var filtro = "";
var producto_id = null;
var productosObj = {};
$(function(){
    $("body,html").on("click",".show-producto-proveedor",function(e){
        e.stopPropagation();
        producto_id = $(this).data("producto");
        var contenido = $("#info-prod-"+producto_id).html();
        $("#info-producto-proveedor").html(contenido);
        productosObj = JSON.parse(localStorage.lista_pedido);
        if(productosObj[producto_id]){
            $("#modal-info-producto-proveedor .modal-footer #cantidad").eq(0).val(parseInt(productosObj[producto_id].cantidad));
        }else{
            $("#modal-info-producto-proveedor .modal-footer #cantidad").eq(0).val("");
        }
        $("#modal-info-producto-proveedor").openModal();
    })

    $(window).scroll(function(e) {
        var documentHeight = $(document).height();
        var windowHeight = $(window).height();
        var scrollTop = $(window).scrollTop();
        if((scrollTop+windowHeight) == documentHeight){
            cargarProductos();
        }
    });


    $("#busqueda_pr_2, #busqueda_pr").keyup(function(event){
        if(event.keyCode == 13) {
            if ($(this).attr("id") == "busqueda")
                $("#busqueda2").val($("#busqueda").val());
            else
                $("#busqueda").val($("#busqueda2").val());

            filtro = $(this).val();
            skip = -1
            $("#contenedor-lista-productos").html("");
            cargarProductos();
        }
    });

    $(".btn-buscar_pr").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        if ($(input).attr("id") == "busqueda")
            $("#busqueda2").val($("#busqueda").val());
        else
            $("#busqueda").val($("#busqueda2").val());

        filtro = $(input).val();
        skip = -1
        $("#contenedor-lista-productos").html("");
        cargarProductos();
    });

})

function cargarProductos(){
    if(!skip) {
        skip = -1;
    }
    var url = $("#base_url").val()+"/productos/busqueda-proveedor";
    var params = {filtro:filtro,skip:skip};
    DialogCargando("Cargando productos ... ");
    $.get(url,params,function(data){
        skip = data.skip;
        $("#contenedor-lista-productos").append(data.view);
        if(data.results == 0){
            $("#busqueda-proveedor-sin-resultados").removeClass("hide");
        }else{
            $("#busqueda-proveedor-sin-resultados").addClass("hide");
        }
        CerrarDialogCargando();
    })
}

function agregarProductoCarrito(btn){
    var cantidad = $(btn).parent().children("#cantidad").eq(0).val();
    if($.isNumeric(cantidad) && cantidad > 0){
        var url = $("#base_url").val()+"/productos/info-producto-proveedor";
        var params = {_token:$("#general-token").val(),producto:producto_id,cantidad:cantidad};
        DialogCargando("Agregando producto ...");
        productosObj = JSON.parse(localStorage.lista_pedido);
        $.post(url,params,function(data){
            if(data.success){
                productosObj[producto_id]= data.producto;
            }
            $(btn).parent().children("#cantidad").eq(0).val("");
            $("#info-producto-proveedor").html("");
            $("#modal-info-producto-proveedor").closeModal();
            $("#btn-pedidos-proveedor").removeClass("hide");
            CerrarDialogCargando();
            localStorage.setItem("lista_pedido",JSON.stringify(productosObj));
        });
    }else{
        alert("Ingrese una cantidad valida (Números enteros mayores a 0)");
    }

}
