/**
 * Created by JM-DEVELOPER1 on 20/04/2016.
 */
var id_usuario = 0;
var id_modulo = 0;
$(function(){
    /*$("#btn-action-form-usuario").click(function(){
        $("#contenedor-action-form-usuario").addClass("hide");
        $("#progress-action-form-usuario").removeClass("hide");
        var url = $("#form-usuario").attr("action");
        var data = $("#form-usuario").serialize();
        $.post(url,data,function(data){
            if(data.success){
                window.location.reload(true);
            }
            $("#contenedor-action-form-usuario").removeClass("hide");
            $("#progress-action-form-usuario").addClass("hide");
        }).error(function(jqXHR,error,estado){
            mostrarErrores("contenedor-errores-usuario",JSON.parse(jqXHR.responseText));
            $("#contenedor-action-form-usuario").removeClass("hide");
            $("#progress-action-form-usuario").addClass("hide");
        })
    })*/


    /*
     *  FILTRO
     *//*
    $("#busqueda2, #busqueda").keyup(function(){
        if($(this).attr("id") == "busqueda")
            $("#busqueda2").val($("#busqueda").val());
        else
            $("#busqueda").val($("#busqueda2").val());

        var filtro = $(this).val();
        var url = $("#base_url").val()+"/usuario/filtro";
        var params = {filtro:filtro,_token:$("#general-token").val()};

        $.post(url,params,function(data){
            $("#contenedor-lista-proveedores").html(data);
        })
    });

    $("#contenedor-lista-proveedores").on("click",".pagination li a",function(e){
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
    })*/

    $("body").on("click","#btn-eliminar-relacion-modulo",function(){
        var cantidad = $(".lean-overlay").length;
        if(cantidad > 1){
            $(".lean-overlay").eq(cantidad-1).remove();
        }
    })

    $(".trigger-modal-admin-modulo").click(function(){
        //$("#modal-admin-modulo").openModal();
        id_modulo = $(this).data("id");
        cargarAdministrarModulo();
    })

    $("body").on("click","#btn-editar-relacion-modulo",function(){
        var url = $("#base_url").val()+"/modulo/editar-permisos";
        var params = $("#form-modulo-relacion").serialize();
        $("#contenedor-botones-edicion-relacion-modulo").addClass("hide");
        $("#progress-edicion-relacion-modulo").removeClass("hide");
        $.post(url,params,function(data){
            if(data.success){
                $("body").scrollTop(0);
                window.location.reload(true);
            }
        }).error(function(jqXHR,error,state){
            if(jqXHR.status == 401){
                var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
                mostrarErrores("contenedor-errores-edicion-relacion-modulo",errores);
            }else{
                mostrarErrores("contenedor-errores-edicion-relacion-modulo",JSON.parse(jqXHR.responseText));
            }

            $("#contenedor-botones-edicion-relacion-modulo").removeClass("hide");
            $("#progress-edicion-relacion-modulo").addClass("hide");
        })
    })

    $("body").on("click","#btn-agregar-relacion-modulo",function(){
        var url = $("#base_url").val()+"/modulo/agregar-permisos";
        var params = $("#form-modulo-relacion").serialize();
        $("#contenedor-botones-agregar-relacion-modulo").addClass("hide");
        $("#progress-agregar-relacion-modulo").removeClass("hide");
        $.post(url,params,function(data){
            if(data.success){
                $("body").scrollTop(0);
                window.location.reload(true);
            }
        }).error(function(jqXHR,error,state){
            if(jqXHR.status == 401){
                var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
                mostrarErrores("contenedor-errores-agregar-relacion-modulo",errores);
            }else{
                mostrarErrores("contenedor-errores-agregar-relacion-modulo",JSON.parse(jqXHR.responseText));
            }

            $("#contenedor-botones-agregar-relacion-modulo").removeClass("hide");
            $("#progress-agregar-relacion-modulo").addClass("hide");
        })
    })

    $("body").on("change",".check-parent",function(){
        var id = $(this).attr("id");
        if($(this).prop("checked")){
            $("."+id).prop("disabled",false);
        }else{
            $("."+id).prop("disabled",true);
        }
    })

    $('#btn-guardar-permiso-reportes').click(function () {
        guardarPermisoReportes();
    })

    $('#btn-guardar-reportes-habilitados').click(function () {
        guardarReportesHabilitados();
    })

    $('#btn-actualizar-caducidad').click(function () {
        actualizarCaducidad();
    })
})

function eliminar(){
    var url = $("#base_url").val()+"/modulo/destroy-permisos/"+id_modulo+"/"+id_usuario;
    var params = {_token:$("#general-token").val()};
    $("#contenedor-botones-eliminar-relacion-modulo").addClass("hide");
    $("#progress-eliminar-relacion-modulo").removeClass("hide");
    $.post(url,params,function(data){
        $("#contenedor-botones-eliminar-relacion-modulo").removeClass("hide");
        $("#progress-eliminar-relacion-modulo").addClass("hide");
        $("#modal-eliminar-relacion-modulo").closeModal();
        if(data.success){
            cargarAdministrarModulo();
        }
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            $('#modal-eliminar-relacion-modulo').closeModal();
            mostrarErrores("contenedor-errores-relacion-modulo",errores);
        }

        $("#contenedor-botones-eliminar-relacion-modulo").removeClass("hide");
        $("#progress-eliminar-relacion-modulo").addClass("hide");
    })
}

function cargarAdministrarModulo(){
    $("#modal-admin-modulo #contenido").html("");
    $("#modal-admin-modulo").css({"height":"auto"});
    $("#load-modal-admin-modulo").removeClass("hide");
    var url = $("#base_url").val()+"/modulo/administrar";
    var params = {_token:$("#general-token").val(),id:id_modulo};
    $.post(url,params,function(data){
        $("#load-modal-admin-modulo").addClass("hide");
        $("#modal-admin-modulo #contenido").html(data);
        $("#modal-admin-modulo").css({"height":"70%"});
        inicializarMaterialize();
    })
}

function guardarPermisoReportes() {
    var params =$('#form-permiso-reportes').serialize();
    var url = $("#base_url").val()+"/modulo/permiso-reportes";
    DialogCargando('Guardando ...');
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }
    })
}

function guardarReportesHabilitados() {
    var params =$('#form-reportes-habilitados').serialize();
    var url = $("#base_url").val()+"/modulo/reportes-habilitados";
    DialogCargando('Guardando ...');
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }
    })
}

function copiarFecha(fecha) {
    $('.datepicker').val(fecha);
}

function actualizarCaducidad() {
    var params = $('#form-caducidad').serialize();
    var url = $('#base_url').val()+'/modulo/actualizar-caducidad';
    DialogCargando('Actualizando ...');
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }
    }).error(function(jqXHR,error,estado){
        moveTop($('#caducidad-funciones .modal-content'),500,50);
        mostrarErrores("contenedor-errores-caducidad",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}