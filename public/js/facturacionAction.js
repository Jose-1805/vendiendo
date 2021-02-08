var extenciones = ["jpg","jpeg","png","svg","gif"];
var id_select = null;
$(function(){
    $("#logo").change(function () {
        readURL(this);
    });

    /**
     *  FILTRO
     */
    $("#busqueda2, #busqueda").keyup(function(){
        if($(this).attr("id") == "busqueda")
            $("#busqueda2").val($("#busqueda").val());
        else
            $("#busqueda").val($("#busqueda2").val());

        var filtro = $(this).val();
        var url = $("#base_url").val()+"/facturacion/filtro-resoluciones";
        var params = {filtro:filtro,_token:$("#general-token").val()};

        $.post(url,params,function(data){
            $("#contenedor-lista-resoluciones").html(data);
        })
    });

    $("#contenedor-lista-resoluciones").on("click",".pagination li a",function(e){
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
})

function uploadLogo() {
    var input = document.getElementById('logo');

    if (input.files && input.files[0]) {
        var data = new FormData(document.getElementById('form-logo'));
        var url = $("#base_url").val()+"/facturacion/upload-logo";

        $(".btn-upload-logo").addClass('hide');
        $("#progres-upload-logo").removeClass('hide');
        DialogCargando("Cambiando imagen ...");
        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: data,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(res){
            CerrarDialogCargando();
            res = JSON.parse(res);
            if(res.success){
                var mensajes = {};
                mensajes.confirm =  ["El logo ha sido almacenado con éxito"];
                if(res.mensaje){
                    mensajes.mensaje = res.mensaje;
                }
                mostrarConfirmacion("contenedor-confirmacion-upload-logo",mensajes);
            }else{
                console.log(res);
                alert("NO SUCCESS");
            }
            $(".btn-upload-logo").removeClass('hide');
            $("#progres-upload-logo").addClass('hide');
        }).error(function (jqXHR,error,status) {
            CerrarDialogCargando();
            $(".btn-upload-logo").removeClass('hide');
            $("#progres-upload-logo").addClass('hide');
            mostrarErrores("contenedor-errores-upload-logo",JSON.parse(jqXHR.responseText));
        });
    }else{
        mostrarErrores("contenedor-errores-upload-logo",{"error":["Seleccione un archivo"]})
        $("#logo").val("");
        $('#preview-logo').attr('src', "");
    }
}

function guardarDatosFacturacion() {
    var url = $("#base_url").val()+"/facturacion/guardar-datos-facturacion";
    $("#encabezado_factura").val(CKEDITOR.instances.encabezado_factura.getData());
    $("#pie_factura").val(CKEDITOR.instances.pie_factura.getData());
    var params = $("#form-datos-facturacion").serialize();
    $("#contenedor-botones-encabezado-factura").addClass('hide');
    $("#progres-encabezado-factura").removeClass('hide');
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        CerrarDialogCargando();
        if(data.success){
            mostrarConfirmacion("contenedor-confirmacion-datos-facturacion",{"confirm":["Los datos de la facturación se almacenaron con éxito."]});
        }

        $("#contenedor-botones-encabezado-factura").removeClass('hide');
        $("#progres-encabezado-factura").addClass('hide');
        $("body").scrollTop($("#contenedor-confirmacion-datos-facturacion").position().top);
    }).error(function(jqXHR,error,state){
        CerrarDialogCargando();
        $("#contenedor-botones-encabezado-factura").removeClass('hide');
        $("#progres-encabezado-factura").addClass('hide');
        mostrarErrores("contenedor-errores-datos-facturacion",JSON.parse(jqXHR.responseText));
        $("body").scrollTop($("#contenedor-confirmacion-datos-facturacion").position().top);
    })
}

function agregarResolucion() {
    var url = $("#base_url").val()+"/facturacion/store-resolucion";
    var params = $("#form-agregar-resolucion").serialize();
    $("#contenedor-botones-agregar-resolucion").addClass('hide');
    $("#progres-agregar-resolucion").removeClass('hide');
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
            mostrarConfirmacion("contenedor-confirmacion-agregar-resolucion",{"confirm":["La resolución ha sido almacenada con éxito."]});
            mostrarConfirmacion("contenedor-confirmacion-configuracion_inicial",{"confirm":["La resolución ha sido almacenada con éxito."]});
            window.location.reload(true);
            $("#numero").val("");
            $("#fecha").val("");
            $("#inicio").val("");
            $("#fin").val("");
        }

        $("#contenedor-botones-agregar-resolucion").removeClass('hide');
        $("#progres-agregar-resolucion").addClass('hide');
        $("body").scrollTop($("#contenedor-confirmacion-agregar-resolucion").position().top);
    }).error(function(jqXHR,error,state){
        CerrarDialogCargando();
        $("#contenedor-botones-agregar-resolucion").removeClass('hide');
        $("#progres-agregar-resolucion").addClass('hide');
        mostrarErrores("contenedor-errores-agregar-resolucion",JSON.parse(jqXHR.responseText));
        mostrarErrores("contenedor-errores-configuracion_inicial",JSON.parse(jqXHR.responseText));
        $("body").scrollTop($("#contenedor-errores-agregar-resolucion").position().top);
    })
}

function traerEditarResolucion(id){
    var url = $("#base_url").val()+"/facturacion/form-editar-resolucion";
    var params = {"_token":$("#general-token").val(),"id":id};
    $("#edit_"+id).addClass("hide");
    $("#load_edit_"+id).removeClass("hide");
    $.post(url,params,function (data) {
        $("#contenedor-datos-resolucion").html(data);
        $("#edit_"+id).removeClass("hide");
        $("#load_edit_"+id).addClass("hide");
        $("#modal-editar-resolucion").openModal();
    }).error(function (jqXHR,error,state) {
        $("#edit_"+id).removeClass("hide");
        $("#load_edit_"+id).addClass("hide");
        mostrarErrores("contenedor-errores-lista-resoluciones",JSON.parse(jqXHR.responseText));
    })
}

function editarResolucion(){
    var params = $("#form-editar-resolucion").serialize();
    var url = $("#base_url").val()+"/facturacion/editar-resolucion";
    $("#contenedor-botones-editar-resolucion").addClass("hide");
    $("#progress-editar-resolucion").removeClass("hide");
    DialogCargando("Guardando ...");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-editar-resolucion").removeClass("hide");
        $("#progress-editar-resolucion").addClass("hide");
    }).error(function(jqXHR,error,state){

        CerrarDialogCargando();
        if(jqXHR.status == 401){
            mostrarErrores("contenedor-errores-lista-resoluciones",JSON.parse(jqXHR.responseText));
            $("#modal-editar-resolucion").closeModal();
        }else{
            mostrarErrores("contenedor-errores-modal-editar-resolucion",JSON.parse(jqXHR.responseText));
        }
        $("#contenedor-botones-editar-resolucion").removeClass("hide");
        $("#progress-editar-resolucion").addClass("hide");
    })
}

function eliminarResolucion(id) {
    var params = {"_token":$("#general-token").val(),"id":id};
    var url = $("#base_url").val()+"/facturacion/destroy-resolucion";
    $("#contenedor-botones-eliminar-resolucion").addClass("hide");
    $("#progress-eliminar-resolucion").removeClass("hide");
    DialogCargando("Eliminando ...");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-eliminar-resolucion").removeClass("hide");
        $("#progress-eliminar-resolucion").addClass("hide");
    }).error(function (jqXHR,error,state) {
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-modal-eliminar-resolucion",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-eliminar-resolucion").removeClass("hide");
        $("#progress-eliminar-resolucion").addClass("hide");
    })
}

function readURL(input) {
    if (input.files && input.files[0]) {
        var datos = input.files[0].name.split('.');
        if(datos.length) {
            if(extenciones.indexOf(datos[(datos.length-1)]) >= 0) {
                var reader = new FileReader();

                reader.onload = function (e) {
                    $('#preview-logo').attr('src', e.target.result);
                }

                reader.readAsDataURL(input.files[0]);
            }else{
                $("#logo").val("");
                $('#preview-logo').attr('src', "");
                mostrarErrores("contenedor-errores-upload-logo",{"error":["Seleccione únicamente archivos con extensión .jpg, .jpeg, .png, .svg o .gif"]})
            }
        }else{
            $("#logo").val("");
            $('#preview-logo').attr('src', "");
            mostrarErrores("contenedor-errores-upload-logo",{"error":["El archivo seleccionado no es valido"]})
        }
    }else{
        $("#logo").val("");
        $('#preview-logo').attr('src', "");
    }
}

