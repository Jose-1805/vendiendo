var id_select = 0;
var compuesto_select = 0;
var permisoEditarProducto = 0;
var permisoEstadoProducto = 0;
var permiso_eliminar = false;
var columnDefs = [{},{},{}];
var poner_cantidad = true;
$(document).ready(function(){
    if(!permisoEditarProducto) {
        columnDefs[1] = { "targets": [13], "visible": false, "searchable": false };
    }
    if(!permisoEditarProducto) {
        columnDefs[2] = { "targets": [14], "visible": false, "searchable": false };
    }

    cargarTablaProductos();
});

$(function(){
    setInterval(function () {
        calcularPromedioPonderado();
    },500);

    $("#btn-action-form-producto").click(function(){
        DialogCargando("Guardando ...");
        /*$("#contenedor-action-form-producto").addClass("hide");
        $("#progress-action-form-producto").removeClass("hide");*/
        var url = $("#form-producto").attr("action");
        var params = new FormData(document.getElementById("form-producto"));

        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function(data){
            //alert('Post correcto');
                data = JSON.parse(data);
                if(data.success){
                    if(data.href) {
                        window.location.href = data.href;
                    }else if(data.location == "compra") {
                        localStorage.setItem("strDataProducto",JSON.stringify(data));
                        window.close();
                    }
                    else {
                        window.location.reload(true);
                    }
                    $("html, body").animate({
                        scrollTop: 50
                    }, 600);
                }
            CerrarDialogCargando();
        }).error(function(jqXHR,error,estado){
            //alert('error');
                mostrarErrores("contenedor-errores-producto",JSON.parse(jqXHR.responseText));
                CerrarDialogCargando();
                $("html, body").animate({ scrollTop: "0px" }, 600);
        })
    });

    $("#btn-action-form-producto-app").click(function(){

        $("#contenedor-action-form-producto-app").addClass("hide");
        $("#progress-action-form-producto-app").removeClass("hide");
        var url = $("#base_url").val()+"/productos/store-revision-producto-inventario";
        var params = new FormData(document.getElementById("form-producto-app"));

        $.ajax({
            url: url,
            type: "post",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function(data){
            //alert('Post correcto');
                data = JSON.parse(data);
                if(data.success){
                    window.location.href = $("#base_url").val()+"/productos/revision-inventario";
                    $("html, body").animate({
                        scrollTop: 50
                    }, 600);
                }
                $("#contenedor-action-form-producto-app").removeClass("hide");
                $("#progress-action-form-producto-app").addClass("hide");
        }).error(function(jqXHR,error,estado){
            //alert('error');
                mostrarErrores("contenedor-errores-inventario-productos",JSON.parse(jqXHR.responseText));
                $("#contenedor-action-form-producto-app").removeClass("hide");
                $("#progress-action-form-producto-app").addClass("hide");
                $("html, body").animate({ scrollTop: "0px" }, 600);
        })
    });

    $("body").on("click","#contenedor-materia-prima-productos .materias-primas a i",function(){
        $(this).parent().parent().remove();
        reestructurarPropiedadesMateriasPrimas();
    });

    $("#contenedor-proveedores-productos").on("click",".proveedores a i",function(){
        $(this).parent().parent().remove();
        reestructurarPropiedadesProveedores();
    });

    /*Filtro*/

    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarProductos($(this));
    });


    $("#busqueda-productos-app-2, #busqueda-productos-app").keyup(function(event){
        if(event.keyCode == 13)
            buscarProductosApp($(this));
    });

    $(".btn-buscar").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarProductos(input);
    });

    $(".btn-buscar-productos-app").click(function(){
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarProductosApp(input);
    });

    $("#contenedor-lista-productos").on("click",".pagination li a",function(e){
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

    $(".paginate-productos-inventario").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        if($("#busqueda-productos-app").val()){
            $("#busqueda-productos-app-2").val($("#busqueda-productos-app").val());
            url += "&filtro="+$("#busqueda-productos-app").val();
        }else if($("#busqueda-productos-app-2").val()){
            $("#busqueda-productos-app-").val($("#busqueda-productos-app-2").val());
            url += "&filtro="+$("#busqueda-productos-app-2").val();
        }
        window.location.href = url;
    })



    $("body,html").on("change",".select-materia-prima",function () {
        $(this).parent().parent().children("div").children("#unidad").val($(this).find(":selected").data("unidad"));
        $(this).parent().parent().children("div").children("#precio_unitario").val($(this).find(":selected").data("valor"));
    })

    $("body,html").on("change",".select-materia-prima",function(){
        calcularValorProductoCompuesto();
    })

    $("body,html").on("keyup",".cantidad_mp",function () {
        calcularValorProductoCompuesto();
    })
});

function buscarProductos(input){
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    var filtro = $(input).val();
    var url = $("#base_url").val()+"/productos/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#contenedor-lista-productos").html(data);
        inicializarMaterialize();
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function setPermisoEditarProducto(valor){
    permisoEditarProducto = valor;
}

function setPermisoEstadoProductos(valor){
    permisoEstadoProducto = valor;
}

function cargarTablaProductos() {
    var url = $('#base_url').val();
    var checked = "";
    var i=1;
    var ProductosTabla = $('#ProductosTabla').dataTable({ "destroy": true });
    ProductosTabla.fnDestroy();
    ProductosTabla = $('#ProductosTabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/productos/list-productos",
            "type": "GET"
        },
        "columns": [
            { "data": "id", 'className': "text-center hide" },
            { "data": "id", 'className': "text-center" },
            { "data": "nombre", 'className': "text-center" },
            { "data": "precio_costo", 'className': "text-center" },
            { "data": "iva", 'className': "text-center" },
            { "data": "utilidad", "className": "text-center"},
            { "data": "costo_venta", 'className': "text-center" },
            { "data": "stock", 'className': "text-center" },
            { "data": "umbral", "className": "text-center"},
            { "data": "descripcion", "className": "text-center hide"},
            { "data": "unidad_id", "className": "text-center hide"},
            { "data": "categoria_id", "className": "text-center hide"},
            { "data": "opciones", "className": "text-center"},
            /*{ "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center" },*/
        ],
        "createdRow": function (row, data, index) {
            $('td', row).eq(9).html(data.descripcion.length > 20 ? data.descripcion.substring(0, 20) + '...' : data.descripcion.substring(0, 20));

            if(data.tipo_producto == "Terminado") {$('td', row).eq(1).html("<div class='col s12 center tooltipped' data-tooltip='Producto "+ data.tipo_producto +"'><p><img src='img/sistema/t.png' alt='' style='max-height: 20px;vertical-align: middle;'></p></div>")};
            if(data.tipo_producto == "Compuesto"){
                var btn_plus = "";
                if(data.omitir_stock_mp != 'si')
                    btn_plus = "<i class='fa fa-plus-circle cyan-text cambiar-stock' onclick='modalCambiarStock("+data.id+")' style='cursor:pointer;vertical-align: middle'></i>";
                $('td', row).eq(1).html("<div class='col s12 center tooltipped' data-tooltip='Producto "+ data.tipo_producto +"'><p><img src='img/sistema/c.png'   alt='' style='max-height: 20px;vertical-align: middle;'>"+btn_plus+"</p></div>")
            }
            if(data.tipo_producto == "Preparado") {$('td', row).eq(1).html("<div class='col s12 center tooltipped' data-tooltip='Producto "+ data.tipo_producto +"'><p><img src='img/sistema/p.png' alt='' style='max-height: 20px;vertical-align: middle;'></p></div>")};

            var detalle = "<a href='#modal-detalle-producto' class='modal-trigger tooltipped' data-tooltip='Ver detalle del producto' onclick=\"detalleProductos('" + data.id + "', '" + data.tipo_producto +"')\"><i class='fa fa-list fa-2x' style='cursor: pointer;'></i></a>";
            var editar = "";
            var estado = "";
            var eliminar = "";
            if(permisoEditarProducto){
                editar = "<a href='" + url + '/productos/edit/' + data.id + "' class='tooltipped' data-tooltip='Editar producto'><i class='fa fa-pencil-square-o fa-2x' style='cursor: pointer;margin-left: 10px;'></i></a>";
            }
            if(permisoEstadoProducto){
                if(data.estado == "Activo"){
                    checked = "On";
                    estado = "<div class='switch' style='display: inline;margin-left: -4px;'><label><input style='transform: translateY(17%);' type='checkbox' id='"+data.id+"' checked onclick=\"estadoProducto(this.id,'"+ data.estado +"')\"><span style='transform: translateY(-42%);' class='lever tooltipped' data-tooltip='Cambiar estado del producto'></span></label></div>";
                }else{
                    checked = "Off";
                    estado = "<div class='switch' style='display: inline; margin-left: -4px;'><label><input type='checkbox' id='"+data.id+"' onclick=\"estadoProducto(this.id,'"+ data.estado +"')\"><span style='transform: translateY(-42%);' class='lever tooltipped' data-tooltip='Cambiar estado del producto'></span></label></div>";
                }
            }

            if(permiso_eliminar && data.eliminar)
                eliminar = '<a onclick="eliminar('+data.id+')"><i class="fa fa-trash fa-2x red-text" style="cursor: pointer;"></i></a>';
            var optioons = detalle + editar + estado+eliminar;
            $('td', row).eq(12).html(optioons);

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === ProductosTabla.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
            //console.log(ProductosTabla.data().length+ " "+i)

            },
        "columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [3,4,5,6,11,12,13,14] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}

function buscarProductosApp(input){
    var filtro = $(input).val();
    window.location.href = $("#base_url").val()+"/productos/revision-inventario/?filtro="+filtro;
}

function detalleProductos(id,tipo_producto) {
    var tablaDatos = $("#datos-producto");
    var url = $("#base_url").val()+"/productos/detalle/"+id+"/"+tipo_producto;

    $("#datos-producto").empty();
    $.get(url, function(res){
       // inicializarMaterialize();
        $("#contenido-detalle").html(res);
       /* $(res).each(function(key,value){
            tablaDatos.append("<tr><td>"+value.id+"</td><td>"+value.nombre+"</td></tr>");
        });*/
    });
}

function agregarProveedorProducto(r,boton,accion = 'create'){

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
            "<a class='right' style='margin-bottom: -22px;'><i class='fa fa-close'></i></a>"+
            "<div class='input-field col s12 l3'>";
        var num_col = 3;
        if(r == "c")num_col = 2;
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

                    html += "<div class='input-field col s12 l2'>"+
                        "<label class='active' for='precio_costo_"+numero+"'>Precio costo</label>"+
                        "<input type='text' class='num-real precio_costo' name='precio_costo_"+numero+"' id='precio_costo_"+numero+"' maxlength='10' placeholder='Ingrese precio costo'>"+
                        "</div>";
                    if(r == "c") {

                        html += "<div class='input-field col s12 l2'>" +
                        "<label class='active' for='iva_" + numero + "'>Iva % en compra</label>" +
                        "<input type='text' class='num-real iva' name='iva_" + numero + "' id='iva_" + numero + "' maxlength='100' placeholder='Ingrese iva'>" +
                        "</div>";
                    }

                         html += "<div class='input-field col s12 l"+num_col+"'>"+
                         "<label class='active' for='utilidad_"+numero+"'>Utilidad %</label>"+
                         "<input type='text' class='num-real utilidad' name='utilidad_"+numero+"' id='utilidad_"+numero+"' maxlength='5' placeholder='Ingrese la utilidad'>"+
                         "</div>";

                    if(accion == "create" || poner_cantidad) {

                        html += "<div class='input-field col s12 l1'>" +
                            "<label class='active' for='cantidad_" + numero + "'>Cantidad</label>" +
                            "<input type='text' class='num-entero cantidad' value='0' name='cantidad_" + numero + "' id='cantidad_" + numero + "' placeholder='Ingrese la cantidad'>" +
                            "</div>";
                    }

                         html += "<div class='input-field col s12 l"+num_col+"'>"+
                         "<label class='active' for='precio_venta_"+numero+"'>Precio venta</label>"+
                         "<input type='text' class='num-real precio_venta' name='precio_venta_"+numero+"' id='precio_venta_"+numero+"' maxlength='100' placeholder='Ingrese el precio de venta al público'>"+
                         "</div></div>";

                    $("#contenedor-proveedores-productos").append(html);
                    $(boton).removeClass('disabled');
                    inicializarMaterialize();
                })
            }
        })
    }
}
function agregarMateriaProducto(boton){
    //if(!$(boton).hasClass('disabled')){
        /*$(boton).addClass('disabled');
        $(boton).addClass("hide");*/
        DialogCargando("Cargando ...");
    var numero = $(".materias-primas").length + 1;
    //alert(numero);
    var params ={id:"select-materia-prima-"+numero,_token:$("#general-token").val()};
    var url = $("#base_url").val()+"/productos/select";
    var html = "<div id='materia-"+numero+"' class='materias-primas col s12'>"+
        "<a class='right'><i class='fa fa-close'></i></a>"+
        "<div class='input-field col s12 m4'>";
    $.post($("#base_url").val()+"/productos/count",{_token:$("#general-token").val()},function(data) {
        if (data == 0 || data == "0") {
            alert("Usted no tiene materias primas relacionadas");
            /*$(boton).removeClass('disabled');
            $(boton).removeClass("hide");*/
            CerrarDialogCargando();
        }else if(data == (numero-1)){
            alert("Usted no puede agregar más de "+(numero-1)+" materias primas");
            /*$(boton).removeClass('disabled');
            $(boton).removeClass("hide");*/
            CerrarDialogCargando();
        }else{
            $.post(url,params,function(data){
                html += data+"<label>Materia prima</label></div>";

                html += "<div class='input-field col s12 m2'>"+
                    "<label class='active'>Precio unitario</label>"+
                    "<input type='text' name='precio_costo' id='precio_unitario' value='' disabled='disabled'>"+
                    "</div>";

                html += "<div class='input-field col s12 m2'>"+
                "<label class='active'>Unidad</label>"+
                "<input type='text' name='unidad' id='unidad' value='Unidad' disabled='disabled'>"+
                "</div>";

                html += "<div class='input-field col s12 m3'>"+
                    "<label class='active'>Cantidad</label>"+
                    "<input type='text' class='num-real cantidad_mp' name='cantidad-"+numero+"' id='cantidad-"+numero+"' maxlength='10'>"+
                    "</div></div>";

                $("#contenedor-materia-prima-productos").append(html);
                /*$(boton).removeClass('disabled');
                $(boton).removeClass("hide");*/
                CerrarDialogCargando();
                inicializarMaterialize();
            })
        }
    })
//}
}
function reestructurarPropiedadesProveedores(){
    $(".proveedores").each(function(index){
        var id = $(".proveedores").eq(index).attr("id");
        var viejo = id.split("-")[id.split("-").length - 1];
        var nuevo = (index+1);

        $(".proveedores").eq(index).children(".titulo-modal").eq(0).text("Proveedor #"+nuevo);
        $("#proveedor-"+viejo).attr("id","proveedor-"+nuevo);
        $("#select-proveedor-"+viejo).prop("name","select-proveedor-"+nuevo);
        $("#select-proveedor-"+viejo).attr("id","select-proveedor-"+nuevo);
        $("#proveedor_actual_"+viejo).val(nuevo);
        $("#proveedor_actual_"+viejo).parent().children("label").eq(0).prop("for","proveedor_actual_"+nuevo);
        $("#proveedor_actual_"+viejo).prop("id","proveedor_actual_"+nuevo);
        $("#precio_costo_"+viejo).prop("name","precio_costo_"+nuevo);
        $("#precio_costo_"+viejo).attr("id","precio_costo_"+nuevo);
        $("#iva_"+viejo).prop("name","iva_"+nuevo);
        $("#iva_"+viejo).attr("id","iva_"+nuevo);
        $("#utilidad_"+viejo).prop("name","utilidad_"+nuevo);
        $("#utilidad_"+viejo).attr("id","utilidad_"+nuevo);
        $("#cantidad_"+viejo).prop("name","cantidad_"+nuevo);
        $("#cantidad_"+viejo).attr("id","cantidad_"+nuevo);
        $("#precio_venta_"+viejo).prop("name","precio_venta_"+nuevo);
        $("#precio_venta_"+viejo).attr("id","precio_venta_"+nuevo);

    })
}
function reestructurarPropiedadesMateriasPrimas(){
    $(".materias-primas").each(function(index){
        var viejo = $(".materias-primas").eq(index).attr("id").split("-")[1];
        //alert(viejo);
        var nuevo = (index+1);

        $("#materia-"+viejo).attr("id","materia-"+nuevo);
        $("#select-materia-prima-"+viejo).prop("name","select-materia-prima-"+nuevo);
        $("#select-materia-prima-"+viejo).attr("id","select-materia-prima-"+nuevo);
        $("#cantidad-"+viejo).prop("name","cantidad-"+nuevo);
        $("#cantidad-"+viejo).attr("id","cantidad-"+nuevo);

    });
    calcularValorProductoCompuesto();
}

function estadoProducto(id_producto,estado) {

    var url = $("#base_url").val()+"/productos/estado/"+id_producto+"/"+estado;
    var token = $("#general-token").val();

    if (window.confirm("Realmente quiere cambiar el estado del producto?")) {
        $.ajax({
            url: url,
            headers: {'X-CSRF-TOKEN': token},
            type: 'GET',
            dataType: 'json',
            data:{id_producto: id_producto},

            success: function (data) {
                console.log(data.response);
                //alert(data.response);
                mostrarConfirmacion("contenedor-confirmacion-productoIndex",{"dato":[data.response]})
                inicializarMaterialize();
            },
            error: function (data,jqXHR) {
                console.log('Error:', data);
                mostrarErrores("contenedor-errores-productoIndex",JSON.parse(jqXHR.responseText));
                //alert(data.response);
                window.location.reload(true);
            }
        });
        //window.location.reload(true);
    }else{

        if(!$("#"+id_producto).is(':checked')) {
            //alert("Está activado");
            $("#"+id_producto).prop("checked", "checked");
        } else {
            //alert("No está activado");
            $("#"+id_producto).prop("checked", "");
        }
    }

}

function aprobarEdiciones(){
    var url = $("#base_url").val()+"/productos/aprobar-edicion";
    var params = {producto:id_select,_token:$("#general-token").val()};

    $("#contenedor-botones-aprobar-producto-inventario").addClass("hide");
    $("#progress-aprobar-producto-inventario").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            window-location.reload(true);
        }

        $("#contenedor-botones-aprobar-producto-inventario").removeClass("hide");
        $("#progress-aprobar-producto-inventario").addClass("hide");
    }).error(function (jqXHR,state,error) {
        $("#modal-aprobar-producto-inventario").closeModal();
        mostrarErrores("contenedor-errores-inventario-productos",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-aprobar-producto-inventario").removeClass("hide");
        $("#progress-aprobar-producto-inventario").addClass("hide");
    });
}

function elminarProductoInventario(){
    var url = $("#base_url").val()+"/productos/eliminar-producto-inventario";
    var params = {producto:id_select,_token:$("#general-token").val()};
    DialogCargando("Eliminando producto ...");
    $.post(url,params,function(data){
        if(data.success){
            window-location.reload(true);
        }
    }).error(function (jqXHR,state,error) {
        $("#modal-eliminar-producto-inventario").closeModal();
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-inventario-productos",JSON.parse(jqXHR.responseText));
    });
}

function cambiarStock(){
    var url = $("#base_url").val()+"/productos/cambiar-stock";
    var params = {
        tarea:$("#modal-producto-compuesto-stock #tarea").val(),
        cantidad:$("#modal-producto-compuesto-stock #cantidad").val(),
        producto:compuesto_select
    }

    $("#contenedor-botones-producto-compuesto-stock").addClass("hide");
    $("#progress-producto-compuesto-stock").removeClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            window.location.reload(true);
        }
    }).error(function (jqXHR,state,error) {
        mostrarErrores("contenedor-errores-producto-compuesto-stock",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-producto-compuesto-stock").removeClass("hide");
        $("#progress-producto-compuesto-stock").addClass("hide");
    })
}


function cargaTablaRevisionInventario(){
    var i=0;
    var params ="";
    var t_revision_inventario = $('#t_revision_inventario').dataTable({ "destroy": true });
    t_revision_inventario.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_revision_inventario').on('error.dt', function(e, settings, techNote, message) {
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_revision_inventario = $('#t_revision_inventario').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/productos/list-revision-inventario?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": 'barcode', "defaultContent": "",'className': "text-center" },
            { "data": 'nombre',"defaultContent": "", 'className': "text-center" },
            { "data": 'stock', "defaultContent": "",'className': "text-center" },
            { "data": 'medida_venta', "defaultContent": "",'className': "text-center" },
            { "data": 'unidad', "defaultContent": "", "className": "text-center" },
            { "data": 'categoria', "defaultContent": "", "className": "text-center" },
            { "data": null , "defaultContent": "", "className": "text-center" },
            { "data": null , "defaultContent": "", "className": "text-center" }
        ],
        "createdRow": function (row, data, index) {
            var html_revisar="";
            if(data.estado == "pendiente")
                 html_revisar="<a href='"+data.url_estado_pendiente+"' class='fa fa-angle-right fa-2x'></a>";
            else if(data.estado == "editado")
                html_revisar="<a href='#modal-aprobar-producto-inventario' onclick=\"id_select = "+data.id+"\" class='fa fa-thumbs-up modal-trigger'></a>";

           $('td', row).eq(6).html(html_revisar);
           $('td', row).eq(7).html("<a href='#modal-eliminar-producto-inventario' onclick=\"id_select = "+data.id+" \" class='fa fa-trash red-text modal-trigger'></a>");
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
        "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6,7] }] ,
        // "bInfo": false,
        // "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });

}

function calcularValorProductoCompuesto(){
    var total = 0;
    if ($(".materias-primas").length > 0){
        $(".materias-primas").each(function (i,el) {
            var valor = parseFloat($(".materias-primas").eq(i).children(".input-field").children(".select-materia-prima").children(".select-materia-prima").find(":selected").data("valor"));
            var cantidad = parseFloat($(".materias-primas").eq(i).children(".input-field").children(".cantidad_mp").eq(0).val());
            //console.log(valor+" ___ "+cantidad);
            if(valor && cantidad){
                total += valor * cantidad;
            }
        })
    }else{
        total = 0;
    }
    $("#costo_materias_primas").text("$ "+number_format(total,2));
    $("#costo_materias_primas_hidden").html("Precio costo (Costo materias primas: $"+number_format(total,2)+")");
}


function setPermisoEliminar(permiso){
    permiso_eliminar = permiso;
}

function eliminar(id,confirmacion = true){
    if(confirmacion && id != null){
        $("#modal-eliminar-producto").openModal();
        id_select = id;
    }else if(!confirmacion && id_select && !id){
        var url = $("#base_url").val()+"/productos/destroy";
        var params = {producto:id_select,_token:$("#general-token").val()};
        DialogCargando("Eliminando ...");
        $.post(url,params,function(data){
            if(data.success){
                cargarTablaProductos();
                mostrarConfirmacion("contenedor-confirmacion-lista-productos",["El producto ha sido eliminado con éxito"]);
                CerrarDialogCargando();
                $("#modal-eliminar-producto").closeModal();
                moveTop(null,500,50);
            }
        }).error(function (jqXHR,error,state) {
            CerrarDialogCargando();
            $("#modal-eliminar-producto").closeModal();
            mostrarErrores("contenedor-errores-lista-productos",JSON.parse(jqXHR.responseText));
        })
    }
}

function calcularPromedioPonderado() {
    var cantidades = 0;
    var precios = 0;
    $(".precio_costo").each(function (i,el) {
        var cant = parseInt($(el).parent().parent().children("div").children(".cantidad").eq(0).val());
        if(cant > 0) {
            cantidades += cant;
            precios += parseInt($(el).val()) * cant;
        }
    })
    $("#promedio-ponderado").html("$ "+number_format(precios/cantidades,2));
}

function modalCambiarStock(id){
    compuesto_select = id;
    $("#modal-producto-compuesto-stock").openModal();
    $("#modal-producto-compuesto-stock").find("#cantidad").val("");
    $("#componentes-producto").html("<p class='center-align'>Cargando componentes <i class='fa fa-spinner fa-spin'></i></p>")
    var url = $("#base_url").val()+"/productos/componentes";
    var params = {_token:$("#general-token").val(),id:id};
    $.post(url,params,function (data) {
        $("#componentes-producto").html(data);
    })
}