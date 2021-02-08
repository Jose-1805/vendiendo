var id_select = 0;
var permiso_editar = false;
var permiso_eliminar = false;
var perfil_auth = null;
var version_bodegas_almacenes = false;
$(function(){
    $("#btn-action-form-usuario").click(function(){
        actionUsuario();
    })
    $("#btn-guardar-version-bodegas").click(function(){
        version_bodegas_almacenes = true;
        actionUsuario();
    })


    /**
     *  FILTRO
     */
    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarUsuarios(input);
    });
    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarUsuarios($(this));
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

    $("#perfil").change(function(){
        var mensaje = true;
        if($("#perfil option:selected").html() == "administrador"){
            if(perfil_auth == 'superadministrador') {
                $("#contenedor-nit").removeClass("hide");
                $("#contenedor-regimen").removeClass("hide");
                $("#contenedor-plan").removeClass("hide");
                $("#contenedor-nombre-negocio").removeClass("hide");
                $("#contenedor-categoria").removeClass("hide");
                $("#contenedor-check-bodegas").removeClass("hide");
            }
            $("#contenedor-categorias-proveedor").addClass("hide");
            $("#contenedor-departamentos").addClass("hide");
            $("#contenedor-municipios").addClass("hide");
            $("#contenedor-select-almacen").addClass("hide");
        }else if($("#perfil option:selected").html() == "superadministrador"){
            $("#contenedor-nit").addClass("hide");
            $("#contenedor-regimen").addClass("hide");
            $("#nit").val("");
            $("#simplificado").prop("checked",false);
            $("#comun").prop("checked",false);
            $("#contenedor-plan").addClass("hide");
            $("#contenedor-nombre-negocio").addClass("hide");
            $("#contenedor-categoria").addClass("hide");
            $("#contenedor-categorias-proveedor").addClass("hide");
            $("#contenedor-departamentos").addClass("hide");
            $("#contenedor-municipios").addClass("hide");
            $("#contenedor-check-bodegas").addClass("hide");
            $("#contenedor-select-almacen").addClass("hide");
            mensaje = false;
        }else if($("#perfil option:selected").html() == "proveedor"){
            $("#contenedor-nit").removeClass("hide");
            $("#contenedor-regimen").removeClass("hide");
            $("#contenedor-departamentos").removeClass("hide");
            $("#contenedor-municipios").removeClass("hide");
            $("#contenedor-plan").addClass("hide");
            $("#contenedor-categorias-proveedor").removeClass("hide");
            $("#contenedor-nombre-negocio").addClass("hide");
            $("#contenedor-categoria").addClass("hide");
            $("#contenedor-check-bodegas").addClass("hide");
            $("#contenedor-select-almacen").addClass("hide");
        }else if($("#perfil option:selected").html() == "usuario"){
            $("#contenedor-select-almacen").removeClass("hide");
        }
        if(mensaje)
            lanzarToast("Revise nuevamente el formulario y diligencie los campos vacios","Sugerencia","8000");
    });

    $("#departamento").change(function(){
        var url = $("#base_url").val()+"/sede/municipios/"+$(this).val();
        $("#municipio").html("");
        $.get(url,function (data) {
            var contenido = "<option disabled selected>Seleccione un municipio</option>";
            $(data.response).each(function(i,el){
                contenido += "<option value='"+el.id+"'>"+el.nombre+"</option>";
            })
            $("#municipio").html(contenido);
            inicializarMaterialize();
        })
    })

    cargarTablaUsuarios();
})

function actionUsuario() {
    $("#contenedor-action-form-usuario").addClass("hide");
    $("#progress-action-form-usuario").removeClass("hide");
    var url = $("#form-usuario").attr("action");
    var data = $("#form-usuario").serialize();
    if(version_bodegas_almacenes)
        data += '&v_bodegas_almacenes=true';
    console.log(data);
    $.post(url,data,function(data){
        if(data.success){
            if(data.href)
                window.location.href = data.href;
            else
                window.location.reload(true);
        }
        $("#contenedor-action-form-usuario").removeClass("hide");
        $("#progress-action-form-usuario").addClass("hide");
    }).error(function(jqXHR,error,estado){
        mostrarErrores("contenedor-errores-usuario",JSON.parse(jqXHR.responseText));
        $("#contenedor-action-form-usuario").removeClass("hide");
        $("#progress-action-form-usuario").addClass("hide");
    })
}

function buscarUsuarios(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/usuario/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#contenedor-lista-proveedores").html(data);
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function eliminar(id){
    var url = $("#base_url").val()+"/usuario/destroy/"+id;
    var params = {_token:$("#general-token").val()};
    $("#contenedor-botones-eliminar-usuario").addClass("hide");
    $("#progress-eliminar-usuario").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
        $("#contenedor-botones-eliminar-usuario").removeClass("hide");
        $("#progress-eliminar-usuario").addClass("hide");
    }).error(function(jqXHR,error,state){
        if(jqXHR.status == 401){
            var errores = {"1":["Usted no tiene permisos para relizar esta acción"]};
            $('#modal-eliminar-usuario').closeModal();
            mostrarErrores("contenedor-errores-lista-usuarioes",errores);
        }

        $("#contenedor-botones-eliminar-usuario").removeClass("hide");
        $("#progress-eliminar-usuario").addClass("hide");
    })
}

function setPermisoEditar(permiso){
    permiso_editar = permiso;
}
function setPermisoEliminar(permiso){
    permiso_eliminar = permiso;
}

function cargarTablaUsuarios() {
    var url = $("#base_url").val()+"/usuario";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_usuarios = $('#tabla_usuarios').dataTable({ "destroy": true });
    tabla_usuarios.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_usuarios').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_usuarios = $('#tabla_usuarios').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/usuario/list-usuarios",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "perfil", 'className': "text-center" },
            { "data": "alias", 'className': "text-center" },
            { "data": "correo", 'className': "text-center" },
            { "data": "telefono", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar)
                $('td', row).eq(5).html('<a href="'+$("#base_url").val()+'/usuario/edit/'+data.id+'"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(5).addClass('hide');
            if(permiso_eliminar)
                $('td', row).eq(6).html('<a href="#modal-eliminar-usuario" class="modal-trigger" onclick="javascript: id_select = '+data.id+'"><i class="fa fa-trash fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(6).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_usuarios.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        //"columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,5,6,] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function setPefilAuth(perfil) {
    perfil_auth = perfil;
}