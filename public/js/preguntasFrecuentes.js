var id_select = null;
function storePregunta(){
    var params = $("#form-crear-pregunta-frecuente").serialize();
    var url = $("#base_url").val()+"/home/pregunta-frecuente-store";

    $("#contenedor-botones-crear-pregunta-frecuente").addClass("hide");
    $("#progress-crear-pregunta-frecuente").removeClass("hide");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }else{
            $("#contenedor-botones-crear-pregunta-frecuente").removeClass("hide");
            $("#progress-crear-pregunta-frecuente").addClass("hide");
        }
    }).error(function(jqXHR,state,error){
        $("#contenedor-botones-crear-pregunta-frecuente").removeClass("hide");
        $("#progress-crear-pregunta-frecuente").addClass("hide");
        mostrarErrores("contenedor-errores-crear-pregunta-frecuente",JSON.parse(jqXHR.responseText));
    })
}

function showEditarPregunta(id) {
    var params = {id:id,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/home/pregunta-frecuente-show-editar";
    $("#info-pregunta-frecuente").html("");
    $("#contenedor-botones-editar-pregunta-frecuente").addClass("hide");
    $("#progress-editar-pregunta-frecuente").addClass("hide");
    $("#load-info-pregunta-frecuente").removeClass("hide");

    $.post(url,params,function(data){
        $("#load-info-pregunta-frecuente").addClass("hide");
        $("#info-pregunta-frecuente").html(data);
        $("#contenedor-botones-editar-pregunta-frecuente").removeClass("hide");
    }).error(function(jqXHR,state,error){
        $("#load-info-pregunta-frecuente").addClass("hide");
        mostrarErrores("contenedor-errores-editar-pregunta-frecuente",JSON.parse(jqXHR.responseText));
    })
}

function updatePregunta(){
    var params = $("#form-editar-pregunta-frecuente").serialize();
    var url = $("#base_url").val()+"/home/pregunta-frecuente-update";

    $("#contenedor-botones-editar-pregunta-frecuente").addClass("hide");
    $("#progress-editar-pregunta-frecuente").removeClass("hide");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }else{
            $("#contenedor-botones-editar-pregunta-frecuente").removeClass("hide");
            $("#progress-editar-pregunta-frecuente").addClass("hide");
        }
    }).error(function(jqXHR,state,error){
        $("#contenedor-botones-editar-pregunta-frecuente").removeClass("hide");
        $("#progress-editar-pregunta-frecuente").addClass("hide");
        mostrarErrores("contenedor-errores-editar-pregunta-frecuente",JSON.parse(jqXHR.responseText));
    })
}

function showDeletePregunta(id) {
    id_select = id;
    $("#modal-eliminar-pregunta-frecuente").openModal();
}

function deletePregunta(){
    var params = {id:id_select,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/home/pregunta-frecuente-delete";

    $("#contenedor-botones-eliminar-pregunta-frecuente").addClass("hide");
    $("#progress-eliminar-pregunta-frecuente").removeClass("hide");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }else{
            $("#contenedor-botones-eliminar-pregunta-frecuente").removeClass("hide");
            $("#progress-eliminar-pregunta-frecuente").addClass("hide");
        }
    }).error(function(jqXHR,state,error){
        $("#contenedor-botones-eliminar-pregunta-frecuente").removeClass("hide");
        $("#progress-eliminar-pregunta-frecuente").addClass("hide");
        mostrarErrores("contenedor-errores-eliminar-pregunta-frecuente",JSON.parse(jqXHR.responseText));
    })
}
function filtrarPregunta() {
    var pregunta_id = $("#select-preguntas").val();
    var url = $("#base_url").val()+"/home/show/" + pregunta_id;
    $.get(url,function (data) {
        $("#lista-preguntas").html(data);
    });
}