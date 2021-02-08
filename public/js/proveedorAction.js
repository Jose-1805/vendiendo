/**
 * Created by JM-DEVELOPER1 on 20/04/2016.
 */
var id_select = 0;
var permiso_edit_p = false;
var permiso_eliminar = false;
var columnDefs = [{},{}];
$(function(){
    if(!permiso_edit_p) {
        columnDefs[0] = { "targets": 6, "visible": false, "searchable": false };
    }
    cargarTablaProvedores();

    $("#btn-action-form-proveedor").click(function(){
        $("#contenedor-action-form-proveedor").addClass("hide");
        $("#progress-action-form-proveedor").removeClass("hide");
        var url = $("#form-proveedor").attr("action");
        var data = $("#form-proveedor").serialize();
        $.post(url,data,function(data){
            if(data.success){
                if(data.proveedor)localStorage.proveedor = data.proveedor;
                window.close();
                if(data.href)
                    window.location.href = data.href;
                else
                    window.location.reload(true);
            }
            $("#contenedor-action-form-proveedor").removeClass("hide");
            $("#progress-action-form-proveedor").addClass("hide");
        }).error(function(jqXHR,error,estado){
            mostrarErrores("contenedor-errores-proveedor",JSON.parse(jqXHR.responseText));
            mostrarErrores("contenedor-errores-configuracion_inicial",JSON.parse(jqXHR.responseText));
            $("#contenedor-action-form-proveedor").removeClass("hide");
            $("#progress-action-form-proveedor").addClass("hide");
        })
    })

    /**
     *  FILTRO
     */
    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarProveedores($(this));
    });

    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarProveedores(input);
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
    })
})

function setPermisoEditarProveedor(val){
    permiso_edit_p = val;
}
function setPermisoEliminarProveedor(val){
    permiso_eliminar = val;
}

function buscarProveedores(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/proveedor/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#contenedor-lista-proveedores").html(data);
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function cargarTablaProvedores(){
    if(!permiso_edit_p) {
        columnDefs[0] = { "targets": 6, "visible": false, "searchable": false };
    }
    var i=0;
    var params = "";
    var tabla_proveedores = $('#tabla_proveedores').dataTable({ "destroy": true });
    tabla_proveedores.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_proveedores').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_proveedores = $('#tabla_proveedores').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/proveedor/list-provedores?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": 'nombre', 'className': "text-center" },
            { "data": 'nit', 'className': "text-center" },
            { "data": 'contacto', 'className': "text-center" },
            { "data": 'direccion', 'className': "text-center" },
            { "data": 'telefono', 'className': "text-center" },
            { "data": 'correo', 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_edit_p){
                $('td', row).eq(6).html('<a href="'+$("#base_url").val()+'/proveedor/edit/'+data.id+'"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            }else{
                $('td', row).eq(6).css('display','none');
            }

            if(permiso_eliminar && data.eliminar){
                $('td', row).eq(7).html('<a onclick="eliminar('+data.id+')"><i class="fa fa-trash fa-2x red-text" style="cursor: pointer;"></i></a>');
            }else{
                $('td', row).eq(7).css('display','none');
            }
        },
        "fnRowCallback": function (row, data, index) {
            if(i === 0){
               setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize(); 
                },700);
               i=1;
           }else{
               i++;
           }
        },
        "columnDefs": columnDefs,        
        "iDisplayLength": 5,
        "bLengthChange": true,
        "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [6,7] }] ,
        // "bInfo": false, 
        // "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}

function eliminar(id,confirmacion = true){
    if(confirmacion && id != null){
        $("#modal-eliminar-proveedor").openModal();
        id_select = id;
    }else if(!confirmacion && id_select && !id){
        var url = $("#base_url").val()+"/proveedor/destroy";
        var params = {proveedor:id_select,_token:$("#general-token").val()};
        DialogCargando("Eliminando ...");
        $.post(url,params,function(data){
            if(data.success){
                cargarTablaProvedores();
                mostrarConfirmacion("contenedor-confirmacion-lista-proveedores",["El proveedor ha sido eliminado con éxito"]);
                CerrarDialogCargando();
                $("#modal-eliminar-proveedor").closeModal();
                moveTop(null,500,50);
            }
        }).error(function (jqXHR,error,state) {
            CerrarDialogCargando();
            $("#modal-eliminar-proveedor").closeModal();
            mostrarErrores("contenedor-errores-lista-proveedores",JSON.parse(jqXHR.responseText));
        })
    }
}