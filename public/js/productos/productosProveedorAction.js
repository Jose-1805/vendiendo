var id_select = 0;
var compuesto_select = 0;
$(function(){
    $("#btn-action-form-producto").click(function(){
        DialogCargando("Guardando ...");

        var url = $("#form-producto").attr("action");
        var params = new FormData(document.getElementById("form-producto"));

        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function(data){
                data = JSON.parse(data);
                if(data.success){
                    $("html, body").animate({
                        scrollTop: 50
                    }, 600);
                    window.location.reload();
                }
        }).error(function(jqXHR,error,estado){
                mostrarErrores("contenedor-errores-producto",JSON.parse(jqXHR.responseText));
                $("html, body").animate({ scrollTop: "0px" }, 600);
                CerrarDialogCargando();
        })
    });

    /*Filtro*/

    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarProductos($(this));
    });



    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarProductos(input);
    });


    $("#contenedor-lista-productos").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        if($("#busqueda").val()){
            $("#busqueda2").val($("#busqueda").val());
            url += "&filtro="+$("#busqueda").val();
        }else if($("#busqueda2").val()){
            $("#busqueda").val($("#busqueda2").val());
            url += "&filtro="+$("#busqueda2").val();
        }
        window.location.href = url;
    })
});

function buscarProductos(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/productos-proveedor/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#contenedor-lista-productos").html(data);
        inicializarMaterialize();
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function detalleProducto(id) {
    event.preventDefault();
    var tablaDatos = $("#datos-producto");
    var url = $("#base_url").val()+"/productos-proveedor/detalle";
    var params = {_token:$("#general-token").val(),id:id};
    DialogCargando("Cargando ...");
    $.post(url,params, function(res){
        $("#contenido-detalle").html(res);
        CerrarDialogCargando();
        $("#modal-detalle-producto").openModal();
    });
}


function estadoProducto(id_producto,estado) {
    //alert(estado);
    var url = $("#base_url").val()+"/productos-proveedor/estado/"+id_producto+"/"+estado;
    var token = $("#general-token").val();

    if (window.confirm("Realmente quiere cambiar el estado del producto?")) {
        DialogCargando("Cambiando estado ...");
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'GET',
            dataType: 'json',
            data:{id_producto: id_producto},

            success: function (data) {
                //alert(data.response);
                mostrarConfirmacion("contenedor-confirmacion-productos",{"dato":[data.response]})
                inicializarMaterialize();
                CerrarDialogCargando();
            },
            error: function (data,jqXHR) {
                console.log('Error:', data);
                mostrarErrores("contenedor-errores-productos",JSON.parse(jqXHR.responseText));
                CerrarDialogCargando();
                //alert(data.response);
                window.location.reload(true);
            }
        });
        //window.location.reload(true);
    }else{

        if(!$("#"+id_producto).is(':checked')) {
            //alert("Está activado");
            $("#"+id_producto).prop("checked", "checked");
        } else {
            //alert("No está activado");
            $("#"+id_producto).prop("checked", "");
        }
    }

}