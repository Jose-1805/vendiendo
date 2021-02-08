/**
 * Created by Desarrollador 1 on 16/05/2016.
 */
var permiso_editar = false;
var permiso_eliminar = false;
var id_select = 0;
$(function(){
    $("#form-unidad").keyup(function (event) {
        if(event.keyCode == 13){
            $("#btn-action-form-unidad").click();
        }
    })
    $("#btn-action-form-unidad").click(function(){
       // alert('ok');
        $("#contenedor-action-form-unidad").addClass("hide");
        $("#progress-action-form-unidad").removeClass("hide");
        var url = $("#form-unidad").attr("action");
        var data = $("#form-unidad").serialize();


        $.post(url,data,function(data){
            if(data.success){
                if(data.location == "productos") {
                    localStorage.setItem("strDataUnidades",JSON.stringify(data));
                    window.close();
                }else {
                    if (data.href)
                        window.location.href = data.href;
                    else
                        window.location.reload(true);
                }
            }
            $("#contenedor-action-form-unidad").removeClass("hide");
            $("#progress-action-form-unidad").addClass("hide");
        }).error(function(jqXHR,error,estado){
            mostrarErrores("contenedor-errores-unidad",JSON.parse(jqXHR.responseText));
            mostrarErrores("contenedor-errores-configuracion_inicial",JSON.parse(jqXHR.responseText));
            $("#contenedor-action-form-unidad").removeClass("hide");
            $("#progress-action-form-unidad").addClass("hide");
            //alert(url);
        })
    });

    /**
     *  FILTRO
     */
    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarUnidades(input);
    });

    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarUnidades($(this));
    });

    $("#contenedor-lista-unidades").on("click",".pagination li a",function(e){
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

    cargarTablaUnidades();

})

function buscarUnidades(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/unidades/filtro";
    //alert(url);
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#contenedor-lista-unidades").html(data);
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function eliminar(id){
    var url = $("#base_url").val()+"/unidades/destroy/"+id;
    var params = {_token:$("#general-token").val()};
    $("#contenedor-botones-eliminar-unidad").addClass("hide");
    $("#progress-eliminar-unidad").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-eliminar-unidad").removeClass("hide");
        $("#progress-eliminar-unidad").addClass("hide");
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            $('#modal-eliminar-unidad').closeModal();
            mostrarErrores("contenedor-errores-lista-unidades",errores);
        }

        $("#contenedor-botones-eliminar-unidad").removeClass("hide");
        $("#progress-eliminar-unidad").addClass("hide");
    })
}

function setPermisoEditar(permiso){
    permiso_editar = permiso;
}
function setPermisoEliminar(permiso){
    permiso_eliminar = permiso;
}

function cargarTablaUnidades() {
    var url = $("#base_url").val()+"/unidades";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_unidades = $('#tabla_unidades').dataTable({ "destroy": true });
    tabla_unidades.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_unidades').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_unidades = $('#tabla_unidades').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/unidades/list-unidades",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "sigla", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar)
                $('td', row).eq(2).html('<a href="'+$("#base_url").val()+'/unidades/edit/'+data.id+'"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(2).addClass('hide');
            if(permiso_eliminar)
                $('td', row).eq(3).html('<a href="#modal-eliminar-unidad" class="modal-trigger" onclick="javascript: id_select = '+data.id+'"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(3).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_unidades.data().length){
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