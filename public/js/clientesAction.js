var id_select = 0;
var permiso_editar = false;
var permiso_eliminar = false;
$(function(){
    $("body,html").on("keyup","#form-cliente",function(event){
        if(event.keyCode == 13){
            $("#btn-accion-cliente").click();
        }
    })
   $("#btn-accion-cliente").click(function(){
       $("#progres-accion-cliente").removeClass("hide");
       $("#contenedor-botones-accion-cliente").addClass("hide");
       var params = $("#form-cliente").serialize();
       var url = $("#base_url").val()+"/cliente/accion";

       $.post(url,params,function(data){
           if(data.success){
               //mostrarConfirmacion("contenedor-confirmacion-accion-cliente", {"dato": [data.mensaje]});
               $("#progres-accion-cliente").addClass("hide");
               $("#contenedor-botones-accion-cliente").removeClass("hide");
               limpiarForm();
               if(data.location == "productos"){
                   $('#select-cliente option[value!="0"]').remove();//Elimino los valores que tenga el select
                   opciones='<option value="" >Seleccione una cliente</option>';
                   $.each(data.valores, function(i, item) {
                       seleccionado="";
                       //id del objeto creado
                       if(data.id_anterior == data.valores[i].id) seleccionado=' selected="true" '; //Si coincide el id con el recien ingresado se selecciona
                       opciones+='<option value="' +data.valores[i].id +'" '+ seleccionado  + ' >' + data.valores[i].nombre + '</option>';
                   })
                   $('#select-cliente').append(opciones);//Actualizo los valores del select
                   inicializarMaterialize();
                   $("#modal-accion-cliente").closeModal();
               }else{
                   window.location.reload(true);
               }
           }
       }).error(function (jqXHR,status,error) {
           mostrarErrores("contenedor-errores-accion-cliente",JSON.parse(jqXHR.responseText));
           $("#progres-accion-cliente").addClass("hide");
           $("#contenedor-botones-accion-cliente").removeClass("hide");
       })
   });

    cargarTablaClientes();
})


function getEdicion(id){
    var url = $("#base_url").val()+"/cliente/edit/"+id;
    DialogCargando("Cargando ...");
    $.post(url,{"_token":$("#general-token").val()},function(data){
        $("#contenido-accion-cliente").html(data);
        $("#modal-accion-cliente").openModal();
        CerrarDialogCargando();
        inicializarMaterialize();
    }).error(function(jqXHR,ststus,error){
        mostrarErrores("contenedor-errores-lista-clientes",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function editar(){
    var params = $("#modal-accion-cliente #form-cliente").serialize();
    var url = $("#base_url").val()+"/cliente/update";
    DialogCargando("Actualizando ...");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }
    }).error(function (jqXHR,error,state) {
        mostrarErrores("contenedor-errores-modal-accion-cliente",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}

function setPermisoEditar(permiso){
    permiso_editar = permiso;
}

function setPermisoEliminar(permiso){
    permiso_eliminar = permiso;
}

function cargarTablaClientes() {
    var url = $("#base_url").val()+"/cliente";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_clientes = $('#tabla_clientes').dataTable({ "destroy": true });
    tabla_clientes.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_clientes').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_clientes = $('#tabla_clientes').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/cliente/list-clientes",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "identificacion", 'className': "text-center" },
            { "data": "telefono", 'className': "text-center" },
            { "data": "correo", 'className': "text-center" },
            { "data": "direccion", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar)
                $('td', row).eq(5).html('<a onclick="getEdicion('+data.id+')"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(5).addClass('hide');
            if(permiso_eliminar && data.eliminar)
                $('td', row).eq(6).html('<a onclick="eliminar('+data.id+')"><i class="fa fa-trash fa-2x red-text" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(6).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_clientes.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [5,6] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function eliminar(id,confirmacion = true){
    if(confirmacion && id != null){
        $("#modal-eliminar-cliente").openModal();
        id_select = id;
    }else if(!confirmacion && id_select && !id){
        var url = $("#base_url").val()+"/cliente/destroy";
        var params = {cliente:id_select,_token:$("#general-token").val()};
        DialogCargando("Eliminando ...");
        $.post(url,params,function(data){
            if(data.success){
                cargarTablaClientes();
                mostrarConfirmacion("contenedor-confirmacion-lista-clientes",["El cliente ha sido eliminado con éxito"]);
                CerrarDialogCargando();
                $("#modal-eliminar-cliente").closeModal();
                moveTop(null,500,50);
            }
        }).error(function (jqXHR,error,state) {
            CerrarDialogCargando();
            $("#modal-eliminar-cliente").closeModal();
            mostrarErrores("contenedor-errores-lista-clientes",JSON.parse(jqXHR.responseText));
        })
    }
}

function guardar(){
    var params = $("#modal-crear-cliente #form-cliente").serialize();
    var url = $("#base_url").val()+"/cliente/store";
    DialogCargando("Guardando ...");
    $.post(url,params,function (data) {
        if(data.success){
            window.location.reload();
        }
    }).error(function (jqXHR,error,state) {
        mostrarErrores("contenedor-errores-modal-crear-cliente",JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    })
}