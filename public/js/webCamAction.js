var id_select = 0;
var columnDefs = [{},{},{}];
$(document).ready(function(){
    cargarTablaWebCam();
});

function cargarTablaWebCam() {
    var url = $('#base_url').val() + '/webcam/list-webcam';
    var i=1;
    var WebcamTabla = $('#WebcamTabla').dataTable({ "destroy": true});
    WebcamTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#WebcamTabla').on('error.dt', function(e, settings, techNote, message){
       console.log('An error has been reported by DataTables: ', message);
    });
    WebcamTabla = $('#WebcamTabla').DataTable({
        "processing":true,
        "serverSide":true,
        "ajax":{
            "url":url,
            "type":"GET"
        },
        "columns":[
            {"data": "nombre", "className": "text-center"},
            {"data": "alias", "className": "text-center"},
            {"data": "nombre_ubicacion", "className": "text-center"},
            {"data": null, "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""},
            {"data": null, "className": "text-center", "defaultContent":""}
        ],
        "createdRow": function(row, data, index){
            var posicion = 3;
            if(true){
                $("td", row).eq(3).html("<a onclick=\"getEdicion('"+data.id+"')\"><i class='fa fa-pencil-square-o fa-2x' style='cursor: pointer;'></i></a>");
                posicion++;
            }
            if(true){
                $("td", row).eq(4).html("<a  href='"+data.url+"' target = '_blank'><i class='fa fa-video-camera fa-2x' style='cursor: pointer;'></i></a>");
                posicion++;
            }
            if(true){
                $("td", row).eq(5).html("<a href='#modal-eliminar-webcam' class='modal-trigger' onclick='id_select = "+data.id+"' ><i class='fa fa-trash fa-2x' style='cursor: pointer;'></i></a>");
            }
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === WebcamTabla.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');                    
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        "columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [3,4,5] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}

$(function(){
   $("#btn-accion-webCam").click(function(){
       $("#progres-accion-webCam").removeClass("hide");
       $("#contenedor-botones-accion-webCam").addClass("hide");
       var params = $("#form-webcam").serialize();
       var url = $("#base_url").val()+"/webcam/accion";

       $.post(url,params,function(data){
        console.log(data)
           if(data.success){
              $("#progres-accion-webCam").addClass("hide");
              $("#contenedor-botones-accion-webCam").removeClass("hide");      
              window.location.reload(true);               
           }
       }).error(function (jqXHR,status,error) {
           mostrarErrores("contenedor-errores-accion-webcam",JSON.parse(jqXHR.responseText));
           $("#progres-accion-webCam").addClass("hide");
           $("#contenedor-botones-accion-webCam").removeClass("hide");
       })
   });
  

    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarWebCam(input);
    });

    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarWebCam($(this));
    });

    $("#lista-webCams").on("click",".pagination li a",function(e){
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

function buscarWebCam(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());

    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/webcam/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#lista-webCams").html(data);
        inicializarMaterialize();

        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function getEdicion(id){
    var url = $("#base_url").val()+"/webcam/form/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        $("#contenido-accion-webCam").html(data);
        $("#modal-accion-webCam").openModal();
    }).error(function(jqXHR,ststus,error){
        mostrarErrores("contenedor-errores-webcam",JSON.parse(jqXHR.responseText));
    })
}

function getVer(id){
    var url = $("#base_url").val()+"/webcam/view/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        $("#contenido-ver-webCam").html(data);
        $("#modal-ver-webCam").openModal();
    }).error(function(jqXHR,ststus,error){
        mostrarErrores("contenedor-errores-webcam",JSON.parse(jqXHR.responseText));
    })
}

function limpiarForm(){
    $('form#form-webcam').find('input:text, input:password, input:file, select, textarea').val("");
}



function eliminar(id){
    var url = $("#base_url").val()+"/webcam/destroy/"+id;
    var params = {_token:$("#general-token").val()};
    $("#contenedor-botones-eliminar-webcam").addClass("hide");
    $("#progress-eliminar-webcam").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-eliminar-webcam").removeClass("hide");
        $("#progress-eliminar-webcam").addClass("hide");
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            $('#modal-eliminar-webcam').closeModal();
            mostrarErrores("contenedor-errores-webcams",errores);
        }else{
            $('#modal-eliminar-webcam').closeModal();
            mostrarErrores("contenedor-errores-webcams",JSON.parse(jqXHR.responseText));
        }

        $("#contenedor-botones-eliminar-webcam").removeClass("hide");
        $("#progress-eliminar-webcam").addClass("hide");
    })
}