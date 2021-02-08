var id_select = 0;
var permiso_editar = false;
var permiso_eliminar = false;
$(function(){
    $("body,html").on("keyup","#form-categoria",function(event){
        if(event.keyCode == 13){
            $("#btn-accion-categoria").click();
        }
    })
   $("#btn-accion-categoria").click(function(){
       $("#progres-accion-categoria").removeClass("hide");
       $("#contenedor-botones-accion-categoria").addClass("hide");
       var params = $("#form-categoria").serialize();
       var url = $("#base_url").val()+"/categoria/accion";

       $.post(url,params,function(data){
           if(data.success){
               //mostrarConfirmacion("contenedor-confirmacion-accion-categoria", {"dato": [data.mensaje]});
               $("#progres-accion-categoria").addClass("hide");
               $("#contenedor-botones-accion-categoria").removeClass("hide");
               limpiarForm();
               if(data.location == "productos"){
                   $('#select-categoria option[value!="0"]').remove();//Elimino los valores que tenga el select
                   opciones='<option value="" >Seleccione una categoria</option>';
                   $.each(data.valores, function(i, item) {
                       seleccionado="";
                       //id del objeto creado
                       if(data.id_anterior == data.valores[i].id) seleccionado=' selected="true" '; //Si coincide el id con el recien ingresado se selecciona
                       opciones+='<option value="' +data.valores[i].id +'" '+ seleccionado  + ' >' + data.valores[i].nombre + '</option>';
                   })
                   $('#select-categoria').append(opciones);//Actualizo los valores del select
                   inicializarMaterialize();
                   $("#modal-accion-categoria").closeModal();
               }else{
                   window.location.reload(true);
               }
           }
       }).error(function (jqXHR,status,error) {
           mostrarErrores("contenedor-errores-accion-categoria",JSON.parse(jqXHR.responseText));
           $("#progres-accion-categoria").addClass("hide");
           $("#contenedor-botones-accion-categoria").removeClass("hide");
       })
   });
    $("#btn-action-form-categoria-configuracion").click(function(){
        // alert('ok');
        $("#contenedor-action-form-categoria").addClass("hide");
        $("#progress-action-form-categoria").removeClass("hide");
        var url = $("#form-categoria").attr("action");
        var data = $("#form-categoria").serialize();


        $.post(url,data,function(data){
            console.log(data);
            if(data.success){
               window.location.reload(true);
            }
            $("#contenedor-action-form-categoria").removeClass("hide");
            $("#progress-action-form-categoria").addClass("hide");
        }).error(function(jqXHR,error,estado){
            mostrarErrores("contenedor-errores-categoria",JSON.parse(jqXHR.responseText));
            mostrarErrores("contenedor-errores-configuracion_inicial",JSON.parse(jqXHR.responseText));
            $("#contenedor-action-form-categoria").removeClass("hide");
            $("#progress-action-form-categoria").addClass("hide");
            //alert(url);
        })
    });
    

    /**
     *  FILTRO
     */

    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarCategorias(input);
    });

    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarCategorias($(this));
    });

    $("#lista-categorias").on("click",".pagination li a",function(e){
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
    cargarTablaCategorias();
})

function buscarCategorias(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());

    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/categoria/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#lista-categorias").html(data);
        inicializarMaterialize();

        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function getEdicion(id){
    var url = $("#base_url").val()+"/categoria/form/"+id;
    $.post(url,{"_token":$("#general-token").val()},function(data){
        $("#contenido-accion-categoria").html(data);
        $("#modal-accion-categoria").openModal();
    }).error(function(jqXHR,ststus,error){
        mostrarErrores("contenedor-errores-categorias",JSON.parse(jqXHR.responseText));
    })
}
function limpiarForm(){
    $("#form-categoria #nombre").val("");
    $("#form-categoria #descripcion").val("");
}



function eliminar(id){
    var url = $("#base_url").val()+"/categoria/destroy/"+id;
    var params = {_token:$("#general-token").val()};
    $("#contenedor-botones-eliminar-categoria").addClass("hide");
    $("#progress-eliminar-categoria").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-eliminar-categoria").removeClass("hide");
        $("#progress-eliminar-categoria").addClass("hide");
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            $('#modal-eliminar-categoria').closeModal();
            mostrarErrores("contenedor-errores-categorias",errores);
        }else{
            $('#modal-eliminar-categoria').closeModal();
            mostrarErrores("contenedor-errores-categorias",JSON.parse(jqXHR.responseText));
        }

        $("#contenedor-botones-eliminar-categoria").removeClass("hide");
        $("#progress-eliminar-categoria").addClass("hide");
    })
}

function setPermisoEditar(permiso){
    permiso_editar = permiso;
}
function setPermisoEliminar(permiso){
    permiso_eliminar = permiso;
}

function cargarTablaCategorias() {
    var url = $("#base_url").val()+"/categoria";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_categorias = $('#tabla_categorias').dataTable({ "destroy": true });
    tabla_categorias.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_categorias').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_categorias = $('#tabla_categorias').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/categoria/list-categorias",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "descripcion", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar)
                $('td', row).eq(2).html('<a onclick="getEdicion('+data.id+')"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(2).addClass('hide');
            if(permiso_eliminar)
                $('td', row).eq(3).html('<a href="#modal-eliminar-categoria" class="modal-trigger" onclick="javascript: id_select = '+data.id+'"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(3).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_categorias.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [2,3] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}