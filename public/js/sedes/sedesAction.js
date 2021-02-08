$(document).ready(function(){

    $("#btn-action-form-sede").click(function () {
        $("#progress-action-form-sede").removeClass("hide");
        $("#contenedor-action-form-sede").addClass("hide");
        var url = $("#form-sede").attr("action");
        var params = $("#form-sede").serialize();

        $.post(url,params,function (data) {
            if(data.success){
                mostrarConfirmacion('contenedor-confirmacion-sede-form',{"dato":[data.mensaje]});
                $("#contenedor-confirmacion-sede-form").fadeOut(6000);
                $("#contenedor-action-form-sede").addClass("hide");
                window.location.href = "/sede";
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-sede-form",JSON.parse(jqXHR.responseText));
            $("#progress-action-form-sede").addClass("hide");
            $("#contenedor-action-form-sede").removeClass("hide");
        })

    });

    


});
function estadoSede(id_sede,estado) {
    //alert(estado);
    var url = $("#base_url").val()+"/sede/estado/"+id_sede+"/"+estado;
    var token = $("#general-token").val();

    if (window.confirm("Realmente quiere cambiar el estado de la sede?")) {
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'GET',
            dataType: 'json',
            data:{id_sede: id_sede},

            success: function (data) {
                console.log(data.response);
                //alert(data.response);
                mostrarConfirmacion("contenedor-confirmacion-sedeIndex",{"dato":[data.response]})
                inicializarMaterialize();
            },
            error: function (data,jqXHR) {
                console.log('Error:', data);
                mostrarErrores("contenedor-errores-sedeIndex",JSON.parse(jqXHR.responseText));
                //alert(data.response);
                window.location.reload(true);
            }
        });
        //window.location.reload(true);
    }else{

        if(!$("#"+id_sede).is(':checked')) {
            //alert("Está activado");
            $("#"+id_sede).prop("checked", "checked");
        } else {
            //alert("No está activado");
            $("#"+id_sede).prop("checked", "");
        }
    }

}
function traerMunicipios(codigo_depto) {
    var url = $("#base_url").val()+"/sede/municipios/"+codigo_depto;
    var token = $("#general-token").val();
    $("#LISTA_MUNICIPIOS").empty();
    if(codigo_depto != ""){
        $("#LISTA_MUNICIPIOS").attr('disabled', false);
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'GET',
            dataType: 'json',
            data:{codigo_depto: codigo_depto},

            success: function (data) {
                //console.log(data.response);
                var objetos = data.response;
                if(objetos.length >= 1){
                    $("#LISTA_MUNICIPIOS").attr('disabled', false);
                    //$("#INPUT_DATALIST").val();
                    $(objetos).each(function (key,value) {
                        console.log(value);
                        $("#LISTA_MUNICIPIOS").append("<option value='"+value.id+"'>"+value.nombre+"</option>");
                    });
                }else {

                    $("#DIV_LISTA_MUNICIPIOS_ERROR").html('<span>No hay municipios para este departamento</span>');
                }
                inicializarMaterialize();
            },
            error: function (data,jqXHR) {
                //console.log('Error:', data);
                $("#DIV_LISTA_MUNICIPIOS_ERROR").html('<span>Oops! ocurrio un error</span>');
            }
        });
    }else{
        $("#LISTA_MUNICIPIOS").attr('disabled', true);
    }
}

