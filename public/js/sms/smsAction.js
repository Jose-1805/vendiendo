var sms_id ='';
//numero de caracteres
var max_chars_mensaje = 120;
var max_chars_titulo = 20;

var permisoEditarSMS = false;
var permisoCrearSMS = false;
var permisoEliminarSMS = false;

$(document).ready(function(){
    $("input[id=seleccionar-todos]").change(function(){
        var lista="";
        var checked_status = this.checked;

        $(".lista-telefonos").each(function () {
            this.checked = checked_status;
            if (checked_status){
                var telefono = this.id.split("-");
                console.log(telefono);
                lista += telefono[1]+'-';
            }else {
                lista+="";
            }
            //console.log(this.id);
            
        });
        var listado ='';
        if (lista.length > 0){
            listado = lista.slice(0,-1);
        }
        $("#telefonos").val(listado);

    });


    $("#titulo_sms").focus(function(){
        $("#span_titulo").css("display", "inline").fadeOut(10000);
        var chars = $("#titulo_sms").val().length;
        var diff = max_chars_titulo - chars;
        $("#numero-caracteres-titulo").removeClass('hide');
        $('#numero-caracteres-titulo').html(diff+ " carácteres");
    });
    $("#mensaje_sms").focus(function(){
        $("#span_mensaje").css("display", "inline").fadeOut(10000);
        var chars = $("#mensaje_sms").val().length;
        var diff = max_chars_mensaje - chars;
        $("#numero-caracteres-mensaje").removeClass('hide');
        $("#numero-caracteres-mensaje").html(diff+ " carácteres");
    });

    $("#btn-action-form-sms").click(function(){
        $("#progress-action-form-sms").removeClass("hide");
        $("#contenedor-action-form-sms").addClass("hide");
        var params = $("#form-sms").serialize();
        var url = $("#base_url").val()+"/sms/store";

        $.post(url,params,function(data){
            if(data.success){
                mostrarConfirmacion("contenedor-confirmacion-sms", {"dato": [data.mensaje]});
                $("#contenedor-confirmacion-sms").fadeOut(6000);
                $("#progress-action-form-sms").addClass("hide");
                window.location.href = "/sms";
            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-sms",JSON.parse(jqXHR.responseText));
            $("#progress-action-form-sms").addClass("hide");
            $("#contenedor-action-form-sms").removeClass("hide");
        })
        //window.location.reload();
    });
    $("#btn-action-form-duplicar-sms").click(function(){
        $("#progress-action-form-duplicar-sms").removeClass("hide");
        $("#contenedor-botones-duplicar-sms").addClass("hide");
        var params = $("#form-sms").serialize();
        var url = $("#base_url").val()+"/sms/store";

        $.post(url,params,function(data){
            if(data.success){
                mostrarConfirmacion("contenedor-confirmacion-sms-duplicar", {"dato": [data.mensaje]});
                $("#contenedor-confirmacion-sms-duplicar").fadeOut(6000);
                $("#progress-action-form-duplicar-sms").addClass("hide");
                window.location.href = "/sms";

            }
        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-sms-duplicar",JSON.parse(jqXHR.responseText));
            $("#progress-action-form-duplicar-sms").addClass("hide");
            $("#contenedor-botones-duplicar-sms").removeClass("hide");
        })
        //window.location.reload();
    });
    $("#btn-action-form-sms-edit").click(function(){
        $("#progress-action-form-sms").removeClass("hide");
        $("#contenedor-action-form-sms").addClass("hide");
        var params = $("#form-sms").serialize();
        var url = $("#base_url").val()+"/sms/update/"+$("#id-sms").val();

        $.post(url,params,function(data){
            if(data.success){
                mostrarConfirmacion("contenedor-confirmacion-sms", {"dato": [data.mensaje]});
                $("#contenedor-confirmacion-sms").fadeOut(6000);
                $("#progress-action-form-sms").addClass("hide");
                window.location.href = "/sms";
            }

        }).error(function (jqXHR,status,error) {
            mostrarErrores("contenedor-errores-sms",JSON.parse(jqXHR.responseText));
            $("#progress-action-form-sms").addClass("hide");
            $("#contenedor-action-form-sms").removeClass("hide");
        });
    });

    $('#numero-caracteres-mensaje').html(max_chars_mensaje + " carácteres");
    $('#numero-caracteres-titulo').html(max_chars_titulo + " carácteres");

    $('#mensaje_sms').keyup(function() {
        var chars = $(this).val().length;
        var diff = max_chars_mensaje - chars;
        $('#numero-caracteres-mensaje').html(diff+ " carácteres");
        if(diff <= 0){
            alert("Llegaste al limite de la cantidad de carácteres permitidos" + diff);
            $('#numero-caracteres-mensaje').html(0+ " carácteres");
            var cadena = $('#mensaje_sms').val().slice(0,diff-1);
            $('#mensaje_sms').val(cadena);
            $('#mensaje_sms').attr("maxlength", max_chars_mensaje);
        }
    });
    $('#titulo_sms').keyup(function() {
        var chars = $(this).val().length;
        var diff = max_chars_titulo - chars;
        $('#numero-caracteres-titulo').html(diff+ " carácteres");
        if(diff <= 0){
            alert("Llegaste al limite de la cantidad de carácteres permitidos");
            $('#numero-caracteres-titulo').html(0+ " carácteres");
            var cadena = $('#titulo_sms').val().slice(0,diff-1);
            $('#titulo_sms').val(cadena);
            $('#titulo_sms').attr("maxlength", max_chars_titulo);
        }
    });
    //cargarTablaSms()
});

// function cargarTablaSms(){
//     var url = $("#base_url").val() + "/";
// }

function viewAsignartelefonos() {
    var url = $("#base_url").val()+"/sms/create/"+sms_id;

    $.ajax({
        url: url,
        beforeSend: function () {
            $("#resultado").html("Procesando, espere por favor...");
        },
        success: function(data) {
            window.location.href = "/sms/create/"+sms_id;
        }
    });

    //window.location.href = url + "/sms/create/"+ sms_id;
    $("#modal-agregar-telefonos").closeModal();

}
function agregarListaTelefonos(){
    var lista="";
    var cantidad = 0;
    totalChecks = document.getElementById("form-sms").elements.length;
    for(k=0;k<totalChecks;k++){
        if(document.getElementById("form-sms").elements[k].type==="checkbox" && document.getElementById("form-sms").elements[k].id != 'estado' && document.getElementById("form-sms").elements[k].id != 'seleccionar-todos'){
            cantidad ++;
            if(document.getElementById("form-sms").elements[k].checked===true){
                var telefono = document.getElementById("form-sms").elements[k].id.split("-");
                lista += telefono[1]+'-';
            }else{
                //document.getElementById("seleccionar-todos").checked= false;
                lista+="";
            }
        }
    }
    var listado ='';
    listado = lista.slice(0,-1);
    if (listado.split("-").length != cantidad){
        document.getElementById("seleccionar-todos").checked= false;
    }else{
        document.getElementById("seleccionar-todos").checked= true;
    }
    //console.log(listado.split("-").length);
    if (lista.length > 0){
         listado = lista.slice(0,-1);
    }
    $("#telefonos").val(listado);
}
function openFormDuplicar(id_sms) {
    $("#contenido-duplicar-sms").empty();
    $("#modal-duplicar-sms").openModal();

    var url = $("#base_url").val()+"/sms/view-duplicar-sms/"+id_sms;

    $.get(url,function (res) {
        $("#contenido-duplicar-sms").html(res);
        inicializarMaterialize();

    })
}
function seleccionarTodos(estado) {
        var lista="";
        var checked_status = estado;

        $(".lista-telefonos").each(function () {
            this.checked = checked_status;
            if (checked_status){
                var telefono = this.id.split("-");
                console.log(telefono);
                lista += telefono[1]+'-';
            }else {
                lista+="";
            }
        });
        var listado ='';
        if (lista.length > 0){
            listado = lista.slice(0,-1);
        }
        $("#telefonos").val(listado);


}
function verTelefonos(telefonos) {
    $("#modal-telefonos-sms").openModal();

    var array_telefonos = telefonos.split('-');
    var html = "<ul class='collection'>";
    for (var i=0; i<array_telefonos.length; i++){
        html += "<li class='collection-item'>"+array_telefonos[i]+"</li>";
    }
    html += "</ul>";
    console.log(html);
    $("#contenido-telefonos-sms").html(html);

}
function eliminarSms(id_sms) {
    var url = $("#base_url").val()+"/sms/destroy";
    var params = {_token:$("#general-token").val(),id:id_sms};
    $("#contenedor-botones-eliminar-sms").addClass('hide');
    $("#progress-eliminar-sms").removeClass('hide');
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }else{
            var errores = {"1":["Para eliminar un mensaje de texto, debe hacerlo 24 horas antes de su fecha de programación"]};
            $('#modal-eliminar-sms').closeModal();
            mostrarErrores("contenedor-errores-sms",errores);
        }
        $("#contenedor-botones-eliminar-sms").removeClass("hide");
        $("#progress-eliminar-sms").addClass("hide");
    }).error(function(jqXHR,error,state){

        console.log(jqXHR.status);
        
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            $('#modal-eliminar-sms').closeModal();
            mostrarErrores("contenedor-errores-sms",errores);
        }
        $("#contenedor-botones-eliminar-sms").removeClass("hide");
        $("#progress-eliminar-sms").addClass("hide");
    })
}


function setPermisoEditarSMS(valor){
    permisoEditarSMS = valor;
}

function setPermisoEliminarSMS(valor){
    permisoEliminarSMS = valor;
}

function setPermisoCrearSMS(valor){
    permisoCrearSMS = valor;
}

function cargaTablaSms(){

        var i=0;
        var params ="";
        var tabla_sms = $('#tabla_sms').dataTable({ "destroy": true });
        tabla_sms.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_sms').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_sms = $('#tabla_sms').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/sms/list-sms?"+params, 
                "type": "GET"
            },
            "columns": [
                { "data": 'titulo' , "defaultContent": "", 'className': "text-center"},
                { "data": 'mensaje' , "defaultContent": "", 'className': "text-center"},
                { "data": 'f_h_programacion' ,"defaultContent": "", 'className': "text-center"},
                { "data": null , "defaultContent": "", 'className': "text-center"},
                { "data": 'estado' , "defaultContent": "", 'className': "text-center"},
                { "data": null , "defaultContent": "", 'className': "text-center"},
                { "data": null , "defaultContent": "", 'className': "text-center"}
            ],
            "createdRow": function (row, data, index) {
                $('td', row).eq(3).html("<a href='#!'' onclick=\"verTelefonos('"+data.telefonos+"')\"><span style='font-weight: 300;font-size: 0.8rem;color: #fff;background-color: #26a69a;border-radius: 2px;padding: 2.6px !important;'><b>"+data.total_telefonos+"</b></span></a>");
            
                if(data.estado == 'enviado'){
                    if(permisoCrearSMS)
                        $('td', row).eq(5).html("<a href='#!' onclick=\"openFormDuplicar("+data.id+")\"><i class='fa fa-clone fa-2x tooltipped' data-tooltip='Duplicar mensaje' style='cursor: pointer;'></i></a>");
                    else
                        $('td', row).eq(5).css('display','none');                        
                }else if(data.estado == 'pendiente'){
                    if(permisoEditarSMS)
                        $('td', row).eq(5).html("<a href='"+data.url_editar+"' class='tooltipped' data-tooltip='Editar mensaje'><i class='fa fa-pencil-square-o fa-2x' style='cursor: pointer;'></i></a>");
                    else
                        $('td', row).eq(5).css('display','none');  
                }else{
                    $('td', row).eq(5).css('display','none');
                }
                   
                if(permisoEliminarSMS)
                    $('td', row).eq(6).html("<a href='#modal-eliminar-sms' class='modal-trigger' onclick=\"javascript: id_select = "+data.id+"\"><i class='fa fa-trash fa-2x tooltipped' data-tooltip='Eliminar mensaje'  style='cursor: pointer;'></i></a>");
                else
                    $('td', row).eq(6).css('display','none');  

                 $('td',row).eq(1).addClass("truncate").css("min-width", "100px");
                 $('td',row).eq(2).css("max-width", "150px").css("min-width", "150px");
                 $('td',row).eq(3).css("max-width", "50px");
                 $('td',row).eq(5).css("max-width", "30px");
                 $('td',row).eq(6).css("max-width", "30px");
                // $('td', row).eq(2).html("("+data.tipo_identificacion+") "+data.identificacion);
                // $('td',row).eq(4).css("min-width", "150px");
                // $('td', row).eq(5).html("<a style='cursor: pointer;' onclick=\"showDetalle("+data.id+")\"><i class='fa fa-angle-right fa-2x'></i></a>");
            },
            "fnRowCallback": function (row, data, index) {
                if(i === 0){
                    setTimeout(function () {
                        $(".dataTables_filter label input").css('width','auto');
                        inicializarMaterialize(); 
                    },700);
                   $(".dataTables_filter label input").css('width','auto'); 
                   i=1;
               }else{
                   i++;
               }
            },
            "columnDefs": columnDefs,        
            "iDisplayLength": 5,
            "bLengthChange": true,
            "aoColumnDefs": [{ 'bSortable': false, 'aTargets':  [0,1,2,3,4,5,6] }] ,
            "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
        });
    }