var id_select = 0;
//var columnDefs = [{}];
var permiso_editar = false;
var permiso_eliminar = false;
var editar = false;
var poner_cantidad = true;
$(function(){
    $("#btn-action-form-materia-prima").click(function(){
        /*$("#contenedor-action-form-materia-prima").addClass("hide");
        $("#progress-action-form-materia-prima").removeClass("hide");*/
        DialogCargando("Guardando ...");
        var url = $("#form-materia-prima").attr("action");
        var params = new FormData(document.getElementById("form-materia-prima"));

        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        })
        .done(function(data){
            data = JSON.parse(data);
            if(data.success){
                if(data.href) {
                    window.location.href = data.href;
                }else if(data.location == "compra") {
                    localStorage.setItem("strDataMateriaPrima",JSON.stringify(data));
                    window.close();
                }else {
                    window.location.reload(true);
                }
            }
            CerrarDialogCargando();
            /*$("#contenedor-action-form-materia-prima").removeClass("hide");
            $("#progress-action-form-materia-prima").addClass("hide");*/
        }).error(function(jqXHR,error,estado){
            CerrarDialogCargando();
            /*$("#contenedor-action-form-materia-prima").removeClass("hide");
            $("#progress-action-form-materia-prima").addClass("hide");*/
            $("html, body").animate({ scrollTop: "0px" }, 600);
            mostrarErrores("contenedor-errores-materia-prima",JSON.parse(jqXHR.responseText));
        })
    })


    /**
     *  FILTRO
     */
    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarMateriasPrimas($(this));
    });

    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarMateriasPrimas(input);
    });

    $("#contenedor-lista-materias-primas").on("click",".pagination li a",function(e){
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

    $("#contenedor-proveedores").on("click",".proveedores a i",function(){
        $(this).parent().parent().remove();
        reestructurarPropiedadesProveedores();
    })

    $("#imagen").change(function(){
        mostrarImagen(this,"preview");
    });

    //columnDefs[0] = { "targets": [7], "visible": false, "searchable": false };
    cargarTablaMateriasPrimas();

    $("body").on("keyup","._valor,._cantidad",function () {
        var total = 0;
        var cantidad = 0;
        $("._valor").each(function (i,el) {
            if($.isNumeric($(el).val()) && $(el).val() > 0){
                var el_cantidad = $(el).parent().parent().children("div").children("._cantidad");
                if($.isNumeric($(el_cantidad).val()) && $(el_cantidad).val() > 0){
                    total += parseFloat($(el).val()) * parseFloat($(el_cantidad).val());
                    cantidad += parseFloat($(el_cantidad).val());
                }
            }
        })
        $("#promedio-ponderado").html("$ "+number_format(total/cantidad,2));
    })
})

function buscarMateriasPrimas(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());

    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");

    var filtro = $(input).val();
    var url = $("#base_url").val()+"/materia-prima/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#contenedor-lista-materias-primas").html(data);
        inicializarMaterialize();
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function agregarProveedor(boton){
    if(!$(boton).hasClass('disabled')){
        $(boton).addClass('disabled');
    var numero = $(".proveedores").length + 1;
    var params ={id:"select-proveedor-"+numero,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/proveedor/select";
    var html = "<div id='proveedor-"+numero+"' class='proveedores col s12'>"+
                "<p class='titulo-modal'>Proveedor #"+numero+"</p>"+
                "<p class='right' style='margin-top: -50px;'>"+
                "<input name='proveedor_actual' type='radio' id='proveedor_actual_"+numero+"' value='"+numero+"' />"+
                "<label for='proveedor_actual_"+numero+"'>Proveedor actual</label>"+
                "</p>"+
                "<a class='right'><i class='fa fa-close'></i></a>";

                html += "<div class='input-field col s12 m6'>";

    $.post($("#base_url").val()+"/proveedor/count",{_token:$("#general-token").val()},function(data) {
        if (data == 0 || data == "0") {
            alert("Usted no tiene proveedores relacionados");
            $(boton).removeClass('disabled');
        }else if(data == (numero-1)){
            alert("Usted no puede agregar más de "+(numero-1)+" proveedores");
            $(boton).removeClass('disabled');
        }else{
            $.post(url,params,function(data){
                html += data+"<label>Proveedores</label></div>";
                    html += "<div class='input-field col s12 m3'>";

                html += "<label>Valor</label>"+
                "<input type='text' class='num-real _valor' name='valor-"+numero+"' id='valor-"+numero+"' maxlength='10'>"+
                "</div>";

                if(poner_cantidad) {
                    html += "<div class='input-field col s12 m3'>" +
                        "<label>Cantidad</label>" +
                        "<input type='text' class='num-real _cantidad' name='cantidad-" + numero + "' id='cantidad-" + numero + "' maxlength='10'>" +
                        "</div></div>";
                }


                $("#contenedor-proveedores").append(html);
                $(boton).removeClass('disabled');
                inicializarMaterialize();
            })
        }
    })
    }
}

function reestructurarPropiedadesProveedores(){
    $(".proveedores").each(function(index){
        var viejo = $(".proveedores").eq(index).attr("id").split("-")[1];
        var nuevo = (index+1);

        $("#proveedor-"+viejo).attr("id","proveedor-"+nuevo);
        $("#select-proveedor-"+viejo).prop("name","select-proveedor-"+nuevo);
        $("#select-proveedor-"+viejo).attr("id","select-proveedor-"+nuevo);
        $("#valor-"+viejo).prop("name","valor-"+nuevo);
        $("#valor-"+viejo).attr("id","valor-"+nuevo);
        $("#proveedor_actual_"+viejo).parent().children("label").attr("for","proveedor_actual_"+nuevo);
        $("#proveedor_actual_"+viejo).val(nuevo);
        $("#proveedor_actual_"+viejo).attr("id","proveedor_actual_"+nuevo);

    })
}

function detalleProveedor(id) {

    //event.preventDefault();
    var tablaDatos = $("#datos-producto");
    var url = $("#base_url").val()+"/materia-prima/detalle/"+id;

    $("#datos-producto").empty();
    $.get(url, function(res){
        $("#contenido-proveedor").html(res);

        inicializarMaterialize();

        /* $(res).each(function(key,value){
         tablaDatos.append("<tr><td>"+value.id+"</td><td>"+value.nombre+"</td></tr>");
         });*/
    });
}
function setPermisoEditar(permiso){
    permiso_editar = permiso;
}
function setPermisoEliminar(permiso){
    permiso_eliminar = permiso;
}

function cargarTablaMateriasPrimas() {
    var url = $("#base_url").val()+"/materia-prima";

    //var p_boton_ver = '@((User as CustomPrincipal).funciones.Contains(constantes_permisos.Funcion_Boton_Ver_en_Solicitudes))';

    var checked = "";
    var i=1;
    var tabla_materias_primas = $('#tabla_materias_primas').dataTable({ "destroy": true });
    tabla_materias_primas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_materias_primas').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_materias_primas = $('#tabla_materias_primas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/materia-prima/list-materias-primas",
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", 'className': "text-center" },
            { "data": "codigo", 'className': "text-center" },
            { "data": "descripcion", 'className': "text-center" },
            { "data": "unidad", 'className': "text-center" },
            { "data": "stock", 'className': "text-center" },
            { "data": "umbral", "className": "text-center"},
            { "data": "detalle", "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            if(permiso_editar)
                $('td', row).eq(7).html('<a href="'+$("#base_url").val()+'/materia-prima/edit/'+data.id+'"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(7).addClass('hide');
            if(permiso_eliminar && data.eliminar)
                $('td', row).eq(8).html('<a onclick="eliminar('+data.id+')"><i class="fa fa-trash fa-2x red-text" style="cursor: pointer;"></i></a>');
            else
                $('td', row).eq(8).addClass('hide');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_materias_primas.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [3,6,7,8] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function eliminar(id,confirmacion = true){
    if(confirmacion && id != null){
        $("#modal-eliminar-materia").openModal();
        id_select = id;
    }else if(!confirmacion && id_select && !id){
        var url = $("#base_url").val()+"/materia-prima/destroy";
        var params = {materia_prima:id_select,_token:$("#general-token").val()};
        DialogCargando("Eliminando ...");
        $.post(url,params,function(data){
            if(data.success){
                cargarTablaMateriasPrimas();
                mostrarConfirmacion("contenedor-confirmacion-lista-materias-primas",["La materia prima ha sido eliminada con éxito"]);
                CerrarDialogCargando();
                $("#modal-eliminar-materia").closeModal();
                moveTop(null,500,50);
            }
        }).error(function (jqXHR,error,state) {
            CerrarDialogCargando();
            $("#modal-eliminar-cliente").closeModal();
            mostrarErrores("contenedor-errores-lista-materias-primas",JSON.parse(jqXHR.responseText));
        })
    }
}