var id_select = 0;
var id_select_relacion = 0;
var input_nombre = null;
var productos = [];
var materiasPrimas = [];
var tipo = null;
var proveedores = null;
var numero_cuotas = null;
var dias_credito = null;
var tipo_periodicidad_notificacion = null;
var periodicidad_notificacion = null;
var fecha_primera_notificacion = null;

var valor_abono_last = 0;
var fecha_abono_last = "";
var comentario_abono_last = "";
var total_pagar_compra = "";
var tipo_producto_proveedor = "productos";
var ventana_producto = null;
var ventana_materia_prima = null;
var columnDefs = [{},{},{}];
var permiso_editar_compra = 0;

var filaCompra = 1;
var barCodeProducto = "";
var accion = "";
var accion_global = "";
var input_global_ID = "";
var id_select_relacion_MP = 0;

var proveedor = [];
var click_action = false;
var seleccionable = false;


function setPermisoEditarCompra(valor){
    permiso_editar_compra = valor;
}

$(document).ready(function(){
    if(!permiso_editar_compra) {
        columnDefs[0] = { "targets": [7], "visible": false, "searchable": false };
    }
    cargarTablaCompras();
});

function cargarTablaCompras() {
    var url_compra = $("#base_url").val()+"/compra/detalle/";


    var checked = "";
    var i=1;
    var ComprasTabla = $('#ComprasTabla').dataTable({ "destroy": true });
    ComprasTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#ComprasTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    ComprasTabla = $('#ComprasTabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/compra/list-compras",
            "type": "GET"
        },
        "columns": [
            { "data": "numero", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "nombre_proveedor", 'className': "text-center" },
            { "data": "usuario_creador", 'className': "text-center" },
            { "data": "estado", "className": "text-center"},
            { "data": "estado_pago", "className": "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center", "width": "80px" }
        ],
        "createdRow": function (row, data, index) {
            var posicion = 7;
            if(permiso_editar_compra){
                var editar_estados = '';
                if((data.estado_pago != 'Pagada' || data.estado != 'Recibida')){
                    editar_estados = "<a href='#modal-estados-compra' style='margin-right: 15px;' class='modal-trigger' onclick=\"openFormEstados('" + data.id + "', '" + data.valor + "', '"+data.numero+"')\"><i class='fa fa-list fa-2x tooltipped' data-tooltip='Actualizar Estado' style='cursor: pointer;'></i></a>";
                }
                var opciones = editar_estados+"<a href= '" + url_compra + data.id + "' style='margin-right: 15px;'><i class='fa fa-chevron-right tooltipped' data-tooltip='Detalle'></i></a>";
                if(data.dias_credito > 0)
                    opciones += "<a href='#modal-abonos-compra' class='modal-trigger tooltipped' data-tooltip='Abonos' onclick=\"detalleAbono('" + data.id + "')\"><i class='fa fa-paypal'></i></a>";

                $('td', row).eq(posicion).html(opciones).css('width' , '5% !important');
                posicion++;
            }
            //$('td', row).eq(posicion).html("<a href= '" + url_compra + data.id + "'><i class='fa fa-chevron-right'></i></a>").css('width' , '5% !important');
            //$('td', row).eq(posicion).html("<td><a href='#modal-abonos-compra' class='modal-trigger tooltipped' data-tooltip='Abonos' onclick=\"detalleAbono('" + data.id + "')\"><i class='fa fa-paypal'></i></a></td>");

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === ComprasTabla.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        "columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,7] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}


$(function () {
    /**
     *  FILTRO
     */
    $("#busqueda2, #busqueda").keyup(function(event){
        if(event.keyCode == 13)
            buscarCompra($(this));
    });

    $(".btn-buscar").click(function () {
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarCompra(input);
    })

    $("#busqueda-proveedor-2, #busqueda-proveedor").keyup(function(event){
        if(event.keyCode == 13)
            buscarProveedor($(this));
    });

    $(".btn-buscar-proveedor").click(function () {
        var input = $(this).parent().children("input[type=text]").eq(0);
        buscarProveedor();
        $(".dataTables_filter label input").focus();
    })


    $("#" +
        "" +
        "functionbuscarProveedorbusqueda-proveedor-2, #busqueda-proveedor").focusout(function(){
        buscarProveedor($(this));
    });


    $("#tabla-detalle-producto tbody").on("click","tr td .fa-trash",function(){
        console.log("Entro a uno... ");
        eliminarElementoDetalleProducto($(this));
    })

    $("#tabla-detalle-producto tbody").on("keydown","tr td .nombre",function(){
        $(this).blur();
    })

    $("#tabla-detalle-producto tbody").on("click keyup","tr td .barCodeCompras",function(){
        var tipo = $(this).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        if (typeof tipo == "undefined") {
            $(this).val("");
            //mostrarErrores("contenedor-errores-detalles-compra", {"1": ["Seleccione el tipo de elemento  a agregar."]});
            Materialize.toast("Seleccione el tipo de elemento  a agregar.", 3000, "red")
        }
    });
    $("#tabla-detalle-producto tbody").on("keyup blur","tr td .barCodeCompras",function(){
        setTimeout(function(){
            $("#contenedor-errores-detalles-compra").fadeOut(3000);
        },2000);

    });

    //$( this ).off("click");
    $("#tabla-detalle-producto tbody").on("click","tr td .nombre",function(e){
        e.stopPropagation();
        if (e.originalEvent.type == "click"){
            barCodeProducto = $(this).parent().parent().children("td").children(".barCodeCompras").eq(0).val()
            if(typeof $("#proveedor").val() == "undefined" || $("#proveedor").val() == "") {
                mostrarErrores("contenedor-errores-detalles-compra",{"1":["Seleccione un proveedor."]});
            }else {
                input_nombre = $(this);
                $(input_nombre).blur();
                tipo = $(input_nombre).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
                if (typeof tipo == "undefined") {
                    mostrarErrores("contenedor-errores-detalles-compra", {"1": ["Seleccione el tipo de elemento a agregar."]});
                } else {
                    if(tipo == "producto") {
                        $('#filtroCompras').removeClass('hide');
                        $("#titulo-modal-compras").text("Productos")
                        $("#link-crear-producto").removeClass("hide");
                        $("#link-crear-materia-prima").addClass("hide");
                        $("#contenedor-select-categorias").removeClass("hide");
                        $("#btn-cambiar-lista-elementos").removeClass("hide").attr("onclick", "cambiarListaElementos()");
                        $("#svg-cambiarListaElementos").attr("onclick", "");
                    }else {
                        var nom_proveedor = $("#proveedor").val()
                        $('#filtroCompras').addClass('hide');
                        $("#link-crear-producto").addClass("hide");
                        $("#link-crear-materia-prima").removeClass("hide");
                        $("#titulo-modal-compras").text("Materias primas");
                        $("#contenedor-select-categorias").addClass("hide");
                        //$("#btn-cambiar-lista-elementos").addClass("hide");
                        $("#btn-cambiar-lista-elementos").attr("onclick", "cargarListaElementosMp('todos', '"+nom_proveedor+"' )");
                        $("#svg-cambiarListaElementos").attr("onclick", "");
                    }
                    var load = $(input_nombre).parent().children(".fa-spin").eq(0);
                    $(load).removeClass("hide");

                    var url = $("#base_url").val()+"/compra/lista-elementos-compra";
                    var params = {_token:$("#general-token").val(),proveedor:$("#proveedor").val(),filtro:$("#busqueda-elemento").val(),categoria:$("#categoria").val(),tipo_elemento:tipo,tipo:tipo_producto_proveedor};
                    $.post(url,params,function(data){
                        $("#contenedor-elementos").html(data);
                        seleccionable = true;
                        $("#modal-elementos-compra").openModal();

                        if(tipo == "materia prima"){
                            cargarListaElementosMp("proveedor_actual", $("#proveedor").val());
                            $(".dataTables_filter label input").focus();
                        }else{
                            if(tipo_producto_proveedor == "productos"){
                                cargarListaProductosProveedorActual($("#proveedor").val())
                            }else{
                                cargarListaProductosOtrosProveedores($("#proveedor").val())
                            }
                            $(".dataTables_filter label input").focus();
                        }
                        $(load).addClass("hide");
                        inicializarMaterialize();
                        click_action = true;
                    })
                }
            }
        }

    });

//Crear elemento
    $(".link-crear-elemento").click(function(){
        DialogCargando();
        var pr = $("#proveedor").val();
        var barCodeProductoCompras = barCodeProducto == undefined ? "" : barCodeProducto
        var url = $(this).data("href")+"&pr_="+pr+"&barCodeProducto_="+barCodeProductoCompras;
        if($(this).data("tipo-elemento") == "producto") {
            ventana_producto =  window.open(url, '', 'width=1000,height=600,resizable=0,toolbar=0,menubar=0');
            CerrarDialogCargando()
            ventana_producto.addEventListener('beforeunload', function(){
                if(localStorage.getItem("strDataProducto")) {
                    var response = JSON.parse(localStorage.getItem("strDataProducto"));
                    localStorage.removeItem("strDataProducto");
                    var url = $("#base_url").val() + "/productos/datos-producto";
                    var params = {_token: $("#general-token").val(), id: response.seleccion, proveedor: pr};
                    $(input_nombre).parent().children(".fa-spin").eq(0).removeClass("hide");
                    $.post(url, params, function (data) {
                        if (data.success) {
                            if ($(input_nombre).val() != "") {
                                valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
                                for (i in productos) {
                                    if (valor == productos[i].id) {
                                        productos.splice(i, 1);
                                    }
                                }
                            }
                            productos.push(data.producto);

                            establecerCamposProducto(accion_global, input_global_ID, data)
                            valoresCompra();
                            $(input_nombre).parent().children(".fa-spin").eq(0).addClass("hide");
                            input_nombre = null;
                            tipo = null;
                        } else {
                            alert("Ocurrio un error al seleccionar el producto, por favor intente nuevamente");
                        }
                    })
                }
            }, true);
        }else{
            ventana_materia_prima =  window.open(url, '', 'width=1000,height=600,resizable=0,toolbar=0,menubar=0');
            CerrarDialogCargando()
            ventana_materia_prima.addEventListener('beforeunload', function(){
                if(localStorage.getItem("strDataMateriaPrima")) {
                    var response = JSON.parse(localStorage.getItem("strDataMateriaPrima"));
                    localStorage.removeItem("strDataMateriaPrima");
                    var url = $("#base_url").val() + "/materia-prima/datos-materia-prima";
                    var params = {_token: $("#general-token").val(), id: response.seleccion,proveedor:pr};
                    $(input_nombre).parent().children(".fa-spin").eq(0).removeClass("hide");

                    $.post(url, params, function (data) {
                        if (data.success) {
                            if($(input_nombre).val() != ""){
                                valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
                                for(i in materiasPrimas){
                                    if(valor == materiasPrimas[i].id){
                                        materiasPrimas.splice(i,1);
                                    }
                                }
                            }
                            materiasPrimas.push(data.materia_prima);

                            establecerCamposProducto(accion_global, input_global_ID, data)
                            valoresCompra();
                            $(input_nombre).parent().children(".fa-spin").eq(0).addClass("hide");
                            input_nombre = null;
                            tipo = null;
                        } else {
                            alert("Ocurrio un error al seleccionar el producto, por favor intente nuevamente");
                        }
                    })
                }
            }, true);
        }
    })

    $("#tabla-detalle-producto tbody").on("change","tr td p input[type=radio]",function () {
        input_nombre = $(this).parent().parent().parent().children("td").children(".nombre").eq(0);
        var tipo_elm = tipo = $(input_nombre).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        id = $(input_nombre).parent().children(".id-pr").eq(0).val();
        if(id != "") {
            if(tipo_elm == "materia prima") {
                $(input_nombre).parent().parent().children("td").children(".btn-edit-precio").remove();
                for (i in productos) {
                    if (productos[i].id == id) {
                        productos.splice(i, 1);
                    }
                }
            }else if(tipo_elm == "producto"){
                for (i in materiasPrimas) {
                    if (materiasPrimas[i].id == id) {
                        materiasPrimas.splice(i, 1);
                    }
                }
            }
        }
        $(input_nombre).parent().parent().children("td").children(".barCodeCompras").eq(0).val("");
        $(input_nombre).val("");
        $(input_nombre).parent().children(".id-pr").eq(0).val("");
        $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
        $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text("");
        //$(input_nombre).parent().parent().children("td").children(".iva").eq(0).text("0");
        $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(0,0,',','.'));
        $(input_nombre).parent().parent().children("td").children(".vlr-subtotal").eq(0).text(""+formato_numero(0,0,',','.'));
        input_nombre = null;
        valoresCompra();
    });

    $("body").on("keyup",".cantidad",function(){
        valoresCompra();
    })

    $("#busqueda-elemento").keyup(function(event){
        if(event.keyCode == 13) {
            event.preventDefault();
            var load = $(this).parent().children("button").eq(0).children(".icono-load-buscar").eq(0);
            var icon = $(this).parent().children("button").eq(0).children(".icono-buscar").eq(0);
            filtroElementos(load,icon);
        }
    })

    $("#btn-buscar-elementos").click(function(){
        var load = $(this).children(".icono-load-buscar").eq(0);
        var icon = $(this).children(".icono-buscar").eq(0);
        filtroElementos(load,icon);
    })

    $("#categoria").change(function(){
        filtroElementos($("#progress-contenedor-elementos"));
    })

    $("#contenedor-elementos").on("click",".content-table-slide .pagination li a",function(e){
        e.preventDefault();
        var page = $(this).attr("href").split("=")[1]
        filtroElementos(null,null,page)
    });


    $("#tipo_periodicidad_notificacion").change(function(){
        if($(this).val() == "quincenal" || $(this).val() == "mensual"){
            $("#periodicidad").removeClass("hide");
            $("#periodicidad_notificacion").val('1');
        }else{
            $("#periodicidad").addClass("hide");
        }
    });

    $(".btn-toggle-datos-proveedor").click(function(){
        $("#info-proveedor").slideToggle(500);
        if($(this).hasClass("fa-angle-double-up")){
            $(this).removeClass("fa-angle-double-up");
            $(this).addClass("fa-angle-double-down");

            $("#contenedor-botones-cliente-up").removeClass("hide");
            $("#contenedor-botones-cliente").addClass("hide");
        }else{
            $("#contenedor-botones-cliente-up").addClass("hide");
            $("#contenedor-botones-cliente").removeClass("hide");
            $(this).removeClass("fa-angle-double-down");
            $(this).addClass("fa-angle-double-up");
        }

    })

    $(".btn-toggle-estados-compra").click(function(){
        $("#estados-compra").slideToggle(500);
        if($(this).hasClass("fa-angle-double-up")){
            $(this).removeClass("fa-angle-double-up");
            $(this).addClass("fa-angle-double-down");
        }else{
            $(this).removeClass("fa-angle-double-down");
            $(this).addClass("fa-angle-double-up");
        }

    })
});

function cambiarListaElementos(){
    var tipo_de_elemento =  $("#tipo_elemento").val() == "producto" ? "producto" : "materia_prima_otros_proveedores";
    var url = $("#base_url").val()+"/compra/lista-elementos-compra";
    if(tipo_producto_proveedor == "productos")tipo_producto_proveedor = "productos_otros_proveedores";
    else if(tipo_producto_proveedor == "productos_otros_proveedores")tipo_producto_proveedor = "productos";
    var params = {_token:$("#general-token").val(),proveedor:$("#proveedor").val(),filtro:$("#busqueda-elemento").val(),categoria:$("#categoria").val(),tipo_elemento:tipo_de_elemento,tipo:tipo_producto_proveedor};
    $("#progress-contenedor-elementos").removeClass("hide");
    $("#contenedor-elementos").addClass("hide");

    $.post(url,params,function(data){

        $("#contenedor-elementos").html(data);
        if(tipo_producto_proveedor == "productos"){
            cargarListaProductosProveedorActual($("#proveedor").val())
        }else{
            cargarListaProductosOtrosProveedores($("#proveedor").val())
        }


        $("#progress-contenedor-elementos").addClass("hide");
        $("#contenedor-elementos").removeClass("hide");
        inicializarMaterialize();
    })


}

function cargarListaElementosMp(tipos_de_Mp, proveedor_ID){
    var nom_proveedor = $("#proveedor").val()
    if(tipos_de_Mp == "todos"){
        $("#titulo-modal-compras").text("Materias primas de otros proveedores");
        $("#btn-cambiar-lista-elementos").attr("onclick", "cargarListaElementosMp('proveedor_actual', '"+nom_proveedor+"' )");
        $("#svg-cambiarListaElementos").attr("onclick", "");
    }else{
        $("#titulo-modal-compras").text("Materias primas");
        $("#btn-cambiar-lista-elementos").attr("onclick", "cargarListaElementosMp('todos', '"+nom_proveedor+"' )");
        $("#svg-cambiarListaElementos").attr("onclick", "");
    }


    var columnDefsss = [{},{}];
    var params = "_token="+$("#general-token").val()+"&proveedor_ID="+proveedor_ID + "&tipos_de_Mp="+tipos_de_Mp;
    var i=1;
    var MateriasPrimasTabla = $('#MateriasPrimasTabla').dataTable({ "destroy": true });
    MateriasPrimasTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#MateriasPrimasTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    });
    MateriasPrimasTabla = $('#MateriasPrimasTabla').DataTable({
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/compra/list-mp-otros-proveedores?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": "nombre", 'className': "text-center" },
            { "data": "valor_proveedor", 'className': "text-center" },
            { "data": "stock", 'className': "text-center" },
            { "data": "umbral", 'className': "text-center" },
            { "data": "sigla", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className":"text-center"}
        ],
        "fixedColumns": true,
        "createdRow": function (row, data, index) {
            $('td',row).eq(0).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(1).css('width','32%').css('min-width','32%').css('max-width','32%');
            $('td',row).eq(2).css('width','32%').css('min-width','32%').css('max-width','32%');
            $('td',row).eq(3).css('width','10%').css('min-width','10%').css('max-width','10%');
            $('td',row).eq(4).css('width','10%').css('min-width','10%').css('max-width','10%');
            $('td',row).eq(5).css('width','10%').css('min-width','10%').css('max-width','10%');
            $('td',row).eq(6).css('width','10%').css('min-width','10%').css('max-width','10%');
            $('td',row).eq(2).text("$ "+formato_numero(data.valor_proveedor,2,',','.'));
            var posicion = 0;
            if(tipos_de_Mp == "todos"){
                $('td',row).eq(0).addClass('hide');
                $('td', row).eq(6).html("<a class='modal-trigger' href='#modal-relacion-proveedor-mp' onclick='id_select_relacion_MP="+data.id +"'><i class='fa fa-plus-circle cyan-text waves-effect waves-light' style='cursor: pointer;'></i></a>")
            }else{
                $(row).attr('onClick', "seleccionElemento('', 'modal_productos_compra', '" + data.id + "' )").css('cursor', 'pointer')
                $('td', row).eq(posicion).html("<input type='radio' id='radio-producto-"+ data.id +"' name='radio-materia' value='"+data.id+"'><label for='radio-producto-" + data.id + "'></label>")
                $('td',row).eq(6).addClass('hide');
            }

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === MateriasPrimasTabla.data().length){
                if(tipos_de_Mp != "todos") {
                    $('#relacionar').addClass('hide')
                    $('#chk_materia_prima').removeClass('hide')

                }else{
                    $('#chk_materia_prima').addClass('hide')
                    $('#relacionar').removeClass('hide')
                }
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },500)
                i=1;
            }else{
                i++;
            }
        },
        "columnDefs": columnDefsss,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [6] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}

function cargarListaProductosProveedorActual(proveedor_ID){
    $("#titulo-modal-compras").text("Productos");
    var params = "_token="+$("#general-token").val()+"&proveedor="+proveedor_ID;
    var i=1;
    var ProductosProveedorActualTabla = $('#ProductosProveedorActualTabla').dataTable({ "destroy": true });
    ProductosProveedorActualTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#ProductosProveedorActualTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    });
    ProductosProveedorActualTabla = $('#ProductosProveedorActualTabla').DataTable({
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/compra/list-productos-proveedor-actual?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": "nombre", "className": "text-center" },
            { "data": "precio_costo_nuevo", "className": "text-center"},
            { "data": "iva_nuevo", "className": "text-center" },
            { "data": "stock", "className": "text-center" },
            { "data": "umbral", "className": "text-center" },
            { "data": "sigla", "className": "text-center"},
            { "data": "nombre_categoria", "className": "text-center"}
        ],
        "fixedColumns": true,
        "createdRow": function (row, data, index) {
            $('td',row).eq(0).css('width','4%').css('min-width','4%').css('max-width','4%');
            $('td',row).eq(1).css('width','36%').css('min-width','36%').css('max-width','36%');
            $('td',row).eq(2).css('width','32%').css('min-width','32%').css('max-width','32%');
            $('td',row).eq(3).css('width','4%').css('min-width','4%').css('max-width','4%');
            $('td',row).eq(4).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(5).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(6).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(7).css('width','6%').css('min-width','6%').css('max-width','6%');

            $(row).attr('onClick', "seleccionElemento('', 'modal_productos_compra', '" + data.id + "' )").css('cursor', 'pointer')
            $('td', row).eq(0).html("<input type='radio' id='radio-producto-"+ data.id +"' name='radio-producto' value='"+data.id+"'><label for='radio-producto-" + data.id + "'></label>")
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === ProductosProveedorActualTabla.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },500)
                i=1;
            }else{
                i++;
            }
        },
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}

function cargarListaProductosOtrosProveedores(proveedor_ID){
    $("#titulo-modal-compras").text("Productos de otros proveedores");
    var params = "_token="+$("#general-token").val()+"&proveedor="+proveedor_ID;
    var i=1;
    var ProductosOtrosProveedoresTabla = $('#ProductosOtrosProveedoresTabla').dataTable({ "destroy": true });
    ProductosOtrosProveedoresTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#ProductosOtrosProveedoresTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    });
    ProductosOtrosProveedoresTabla = $('#ProductosOtrosProveedoresTabla').DataTable({
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/compra/list-productos-otros-proveedores?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": "nombre", "className": "text-center" },
            { "data": "precio_costo", "className": "text-center" },
            { "data": "iva", "className": "text-center" },
            { "data": "stock", "className": "text-center" },
            { "data": "sigla", "className": "text-center"},
            { "data": "nombre_categoria", "className": "text-center"},
            { "data": "nom_proveedor", "className": "text-center"},
            { "data": null, "defaultContent": "", "className": "text-center" }
        ],
        "fixedColumns": true,
        "createdRow": function (row, data, index) {

            $('td',row).eq(0).css('width','32%').css('min-width','32%').css('max-width','32%');
            $('td',row).eq(1).css('width','32%').css('min-width','32%').css('max-width','32%');
            $('td',row).eq(2).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(3).css('width','4%').css('min-width','4%').css('max-width','4%');
            $('td',row).eq(4).css('width','4%').css('min-width','4%').css('max-width','4%');
            $('td',row).eq(5).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(6).css('width','6%').css('min-width','6%').css('max-width','6%');
            $('td',row).eq(7).css('width','6%').css('min-width','6%').css('max-width','6%');

            $('td', row).eq(7).html("<a class='modal-trigger' href='#modal-relacion-proveedor' onclick='id_select_relacion="+data.id +"'><i class='fa fa-plus-circle cyan-text waves-effect waves-light' style='cursor: pointer;'></i></a>")
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === ProductosOtrosProveedoresTabla.data().length){
                setTimeout(function () {
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },500)
                i=1;
            }else{
                i++;
            }
        },
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [7] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}

function filtroElementos(load = null,icon = null,page=null){
    if(load != null)
        $(load).removeClass("hide");
    if(icon != null)
        $(icon).addClass("hide");


    var url = $("#base_url").val()+"/compra/lista-elementos-compra";
    if(page==null)
        var params = {_token:$("#general-token").val(),proveedor:$("#proveedor").val(),tipo_elemento:tipo,filtro:$("#busqueda-elemento").val(),categoria:$("#categoria").val(),tipo:tipo_producto_proveedor};
    else
        var params = {_token:$("#general-token").val(),proveedor:$("#proveedor").val(),tipo_elemento:tipo,filtro:$("#busqueda-elemento").val(),categoria:$("#categoria").val(),page:page,tipo:tipo_producto_proveedor};
    $.post(url,params,function(data){
        $("#contenedor-elementos").html(data);
        if(load != null)
            $(load).addClass("hide");
        if(icon != null)
            $(icon).removeClass("hide");
        inicializarMaterialize();
    })
}

function eliminarElementoDetalleProducto(elemento,mensaje = true){
    if($("#tabla-detalle-producto tbody tr").length == 1){
        if(mensaje)
            mostrarErrores("contenedor-errores-detalles-compra",{"1":["No puede eliminar el elemento, una compra debe tener por lo menos un elemento en la lista de detalles."]});
    }else {
        input_nombre = $(elemento).parent().parent().children("td").children(".nombre").eq(0);
        var tipo_elm = tipo = $(input_nombre).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        id = $(elemento).parent().parent().children("td").children(".id-pr").eq(0).val();
        var eliminar = true;
        if(id != "") {
            encontrado = false;
            if(tipo_elm == "producto") {
                for (i in productos) {
                    if (productos[i].id == id) {
                        productos.splice(i, 1);
                        encontrado = true;
                    }
                }
            }else if(tipo_elm == "materia prima"){
                for (i in materiasPrimas) {
                    if (materiasPrimas[i].id == id) {
                        materiasPrimas.splice(i, 1);
                        encontrado = true;
                    }
                }
            }
            if(!encontrado){
                eliminar = false;
                alert("El producto no pudo ser eliminado, la información analisada no coincide");
            }
        }
        input_nombre = null;
        if(eliminar) {
            $(elemento).parent().parent().remove();
            $(".radio-tipo-elemento").each(function(indice){
                /*var radio1 = $(".radio-tipo-elemento").eq(indice).children("p").eq(0).children("input[type=radio]").eq(0);
                 var label1 = $(".radio-tipo-elemento").eq(indice).children("p").eq(0).children("label").eq(0);
                 $(radio1).attr("id","tipo_"+(indice+1)+"_pr");
                 $(radio1).attr("name","tipo_"+(indice+1));
                 $(label1).attr("for","tipo_"+(indice+1)+"_pr");

                 var radio2 = $(".radio-tipo-elemento").eq(indice).children("p").eq(1).children("input[type=radio]").eq(0);
                 var label2 = $(".radio-tipo-elemento").eq(indice).children("p").eq(1).children("label").eq(0);
                 $(radio2).attr("id","tipo_"+(indice+1)+"_mp");
                 $(radio2).attr("name","tipo_"+(indice+1));
                 $(label2).attr("for","tipo_"+(indice+1)+"_mp");*/
            });
            valoresCompra();
        }
    }
}

function buscarCompra(input){
    $(".icono-buscar").addClass("hide");
    $(".icono-load-buscar").removeClass("hide");
    if($(input).attr("id") == "busqueda")
        $("#busqueda2").val($("#busqueda").val());
    else
        $("#busqueda").val($("#busqueda2").val());

    var filtro = $(input).val();
    var url = $("#base_url").val()+"/compra/filtro";
    var params = {filtro:filtro,_token:$("#general-token").val()};

    $.post(url,params,function(data){
        $("#lista-compras").html(data);
        //inicializarMaterialize();
        $(".icono-buscar").removeClass("hide");
        $(".icono-load-buscar").addClass("hide");
    })
}

function buscarProveedor(){
    var url = $("#base_url").val()+"/compra/list-proveedores";
    var i=1;
    var ProveedoresTabla = $('#ProveedoresTabla').dataTable({ "destroy": true });
    ProveedoresTabla.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#ProveedoresTabla').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    ProveedoresTabla = $('#ProveedoresTabla').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": url,
            "type": "GET"
        },
        "columns": [
            { "data": null, "defaultContent": "", "className": "text-center" },
            { "data": "nit", 'className': "text-center" },
            { "data": "nombre", 'className': "text-center" },
            { "data": "correo", 'className': "text-center" }
        ],
        "tabIndex": 1,
        "createdRow": function (row, data, index) {
            $(row).attr('onClick', "checkradioButton('proveedor"+data.id+"')").css('cursor', 'pointer');
            $("td", row).eq(0).html("<p><input name='proveedor' type='radio' id='proveedor" + data.id + "' value='" + data.id + "' onclick=\"'"+ proveedor.push(data) +"'; seleccionProveedor(event)\" /><label for='proveedor" + data.id + "'></label></p>")

        },

        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === ProveedoresTabla.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                    $('table#ProveedoresTabla thead tr th').each(function() {
                        $(this).attr('tabindex',-1);
                    });
                },700)
                i=1;
            }else{
                i++;
            }
        },
        "tabIndex": -1,
        "columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
    $("#modal-proveedores").openModal({dismissible: false,complete: function() {
        proveedores = null;
        if($("#proveedor").val() == ""){
            $("#contenedor-detalles-compra").addClass("hide");
            $("#contenedor-estados-compra").addClass("hide");
        }else{
            $("#contenedor-detalles-compra").removeClass("hide");
            $("#contenedor-estados-compra").removeClass("hide");
        }
    }});
}

function buscarProveedorTest(element) {
    if($(element).attr("id") == "busqueda-proveedor")
        $("#busqueda-proveedor-2").val($("#busqueda-proveedor").val());
    else
        $("#busqueda-proveedor").val($("#busqueda-proveedor-2").val());

    var filtro = $(element).val();
    var url = $("#base_url").val()+"/compra/buscar-proveedor";
    var params = {filtro:filtro,_token:$("#general-token").val()};
    $("#datos-proveedor .buscar-proveedor .fa-search").addClass("hide");
    $("#datos-proveedor .buscar-proveedor .fa-spin").removeClass("hide");
    $("#contenedor-detalles-compra").addClass("hide");
    $("#contenedor-estados-compra").addClass("hide");
    $.post(url,params,function(data){
        if(data.success){
            if(data.proveedores.length) {
                proveedores = data.proveedores;
                var tabla = "<table class='table'>" +
                    "<thead>" +
                    "<th></th>" +
                    "<th>NIT</th>" +
                    "<th>Nombre</th>" +
                    "<th>Correo</th>" +
                    "</thead>" +
                    "<tbody>";
                $.each(data.proveedores, function (i, proveedor) {
                    tabla += "<tr>" +
                        "<td><p><input name='proveedor' type='radio' id='proveedor" + proveedor.id + "' value='" + proveedor.id + "'/><label for='proveedor" + proveedor.id + "'></label></p></td>" +
                        "<td>" + proveedor.nit + "</td>" +
                        "<td>" + proveedor.nombre + "</td>" +
                        "<td>" + proveedor.correo + "</td>" +
                        "</tr>";
                })
                tabla += "</tbody></table>";
            }else{
                tabla = "<p>No se han encontrado proveedores con el filtro ingresado</p>";
            }

            $("#modal-proveedores .modal-content #contenedor-proveedores").html(tabla);
            $("#modal-proveedores").openModal({dismissible: false,complete: function() {
                proveedores = null;
                if($("#proveedor").val() == ""){
                    $("#contenedor-detalles-compra").addClass("hide");
                    $("#contenedor-estados-compra").addClass("hide");
                }else{
                    $("#contenedor-detalles-compra").removeClass("hide");
                    $("#contenedor-estados-compra").removeClass("hide");
                }
            }});
        }else{
            $(".dato-proveedor").text("No establecido");
            $("#proveedor").val("");
        }
        $("#datos-proveedor .buscar-proveedor .fa-search").removeClass("hide");
        $("#datos-proveedor .buscar-proveedor .fa-spin").addClass("hide");
    }).error(function(jqXHR,error,state){
        $(".dato-proveedor").text("No establecido");
        $("#proveedor").val("");
        $("#btn-editar-proveedor").addClass("hide");
        $(".btn-crear-proveedor").removeClass("hide");
        $("#datos-proveedor .buscar-proveedor .fa-search").removeClass("hide");
        $("#datos-proveedor .buscar-proveedor .fa-spin").addClass("hide");
    })
}

$("#modal-proveedores .modal-content #contenedor-proveedores").keypress(function(e) {
    if(e.which == 13) {
        var prv = $('input:radio[name=proveedor]:checked').val();
        if(prv != null){
            checkradioButton("proveedor"+prv);
            seleccionProveedor(event)
        }
    }
});

//function seleccionProveedor(event = null,nombre, nit, contacto, direccion, telefono, correo, id) {
function seleccionProveedor(event = null,id = null) {
    if(id)prv = id;
    else prv = $('input:radio[name=proveedor]:checked').val();
    if(event != null)
        if(typeof event.which !== "undefined"){
            if(event.x === 0)
                return false;
        }

    $.each(proveedor, function(i, proveed){
        if(proveed.id ==  prv){
            $("#txt-nombre").text(proveed.nombre);
            $("#txt-nit").text(proveed.nit);
            $("#txt-contacto").text(proveed.contacto);
            $("#txt-direccion").text(proveed.direccion);
            $("#txt-telefono").text(proveed.telefono);
            $("#txt-correo").text(proveed.correo);
            $("#proveedor").val(proveed.id);
            $("#contenedor-detalles-compra").removeClass("hide");
            $("#contenedor-estados-compra").removeClass("hide");
            $("#modal-proveedores").closeModal();

            if($(".btn-toggle-datos-proveedor").eq(0).hasClass("fa-angle-double-up")){
                $(".btn-toggle-datos-proveedor").eq(0).click();
            }else{
                $("#contenedor-botones-cliente-up").removeClass("hide");
                $("#contenedor-botones-cliente").addClass("hide");
            }
        }
    });
    limpiarTabla();
    $(".radio-tipo-elemento p input[type=radio]").eq(0).prop("checked","checked");
    $(".barCodeCompras").eq(0).focus();
}

function limpiarTabla(){

    agregarElementoCompra();
    $("#tabla-detalle-producto tbody tr td .fa-trash").each(function (i,element) {
        eliminarElementoDetalleProducto(element,false);
    })
    productos = [];
    materiasPrimas = [];
}

function agregarElementoCompra(cero_vacios = false){
    var vacios = 0;
    $(".id-pr").each(function(indice){
        if (!$(".id-pr").eq(indice).val()){
            vacios++;
        }
    });
    if ((vacios == 1 && !cero_vacios) || (vacios == 0 && cero_vacios)) {
        var html = "<tr>" +
            "<td class='radio-tipo-elemento'>" +
            "<p style='margin-top: -25px !important; ' class='left-align'>" +
            "<input name='tipo_" + filaCompra + "' type='radio' style='position: relative; margin-top:-22px' id='tipo_" + filaCompra + "_pr' value='producto' checked='checked' />" +
            "<label for='tipo_" + filaCompra + "_pr' style='height: 4px !important;font-size: 0.85rem !important;'>Producto</label>" +
            "</p>" +
            "<p class='left-align'>" +
            "<input name='tipo_" + filaCompra + "' type='radio' style='position: relative; margin-top:-9px' id='tipo_" + filaCompra + "_mp' value='materia prima' />" +
            "<label for='tipo_" + filaCompra + "_mp' style='height: 4px !important;font-size: 0.85rem !important;'>Materia P</label>" +
            "</p>" +
            "</td>" +

            "<td class='barCodeTd'>" +
            "<input class='barCodeCompras' id='barCodeCompra_" + filaCompra + "' placeholder='Código de barras' onchange=\"seleccionElemento(this.id, 'barCodeProductosCompras')\">" +
            "</td>" +

            "<td style='text-align: center'>" +
            "<input type='text' class='nombre' placeholder='Click aquí'>" +
            "<i class='fa fa-spin fa-spinner hide' style='margin-top: -40px;'></i>" +
            "<input type='hidden' class='id-pr'>" +
            "</td>" +

            "<td class='cantidadTd'>" +
            "<input type='text' value='1' min='1' class='num-real center-align cantidad'>" +
            "</td>" +

            "<td class='unidadTd'>" +
            "<p class='unidad'></p>" +
            "</td>" +

            "<td>" +
            "<p class='vlr-unitario' style='white-space: nowrap;'>$ 0</p>" +
            "</td>" +

            "<td>" +
            "<p class='vlr-subtotal' style='white-space: nowrap;'>$ 0</p>" +
            "</td>" +

            /*"<td class='ivaTd'>"+
             "<p class='iva'>0</p>"+
             "</td>"+*/

            "<td>" +
            "<i class='fa fa-trash red-text text-darken-1 waves-effect waves-light' title='Eliminar elemento' style='cursor: pointer;margin-top: -16px'></i>" +
            "</td>" +
            "</tr>";

        filaCompra++;
        $("#tabla-detalle-producto tbody").append(html);
        $("#tabla-detalle-producto tbody .barCodeCompras").eq($("#tabla-detalle-producto tbody .barCodeCompras").length - 1).focus();
        //alert($("#tabla-detalle-producto tbody .barCodeCompras").length);
    }
}

function seleccionElemento(barCodeCompra_ID, accion_actual, elemento_id) {
    if(!tipo)
        tipo = "producto";
    if(seleccionable || barCodeCompra_ID) {
        seleccionable = false;
        var tip = $('#' + barCodeCompra_ID).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        if (typeof tip == "undefined" && accion_actual == "barCodeProductoCompras") {
            mostrarErrores("contenedor-errores-detalles-compra", {"1": ["Selecciona el tipo de elemento a agregar."]});
            $('#contenedor-errores-detalles-compra').setTimeout(function () {
                $(this).hide(1000)
            }, 3000);
            return false;
        }
        accion_global = accion_actual;
        input_global_ID = barCodeCompra_ID;
        barCodeProducto = $("#" + barCodeCompra_ID).val();
        if (tipo == "producto") {
            seleccionProducto(barCodeCompra_ID, accion_actual, elemento_id);
        } else if (tipo == "materia prima") {
            seleccionMateriaPrima(barCodeCompra_ID, accion_actual, elemento_id);
        }
        /* var vacios = 0;
         $(".id-pr").each(function(indice){
         var tipo_elm = $(".id-pr").eq(indice).parent().parent().children(".radio-tipo-elemento").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
         if (tipo_elm === undefined){
         vacios++;
         }
         });
         if (vacios == 0){
         agregarElementoCompra();
         }*/
        inicializarMaterialize();
    }
}

function seleccionProducto(barCodeCompra_ID, accion_actual, id) {

    if (tipo_producto_proveedor == "productos_otros_proveedores"){
        $("#modal-elementos-compra").closeModal();
        tipo_producto_proveedor = "productos";
    }else{
        //var id = $('input:radio[name=radio-producto]:checked').val();

        var id_check = $('input:radio[name=radio-producto]:checked').attr('id');
        var proveedor = $("#proveedor").val();
        if (typeof proveedor == "undefined" || proveedor == "") {
            alert("La información es incorrecta");
            return false;
        }
        if (typeof id == "undefined" && barCodeProducto == undefined) {
            alert("Seleccione un producto");
        } else {
            var continuar = true;
            for (i in productos) {
                if (productos[i].id == id && barCodeProducto == undefined) {
                    alert("El producto seleccionado ya ha sido agregado a la compra");
                    continuar = false;
                }else if(productos[i].barcode == barCodeProducto && barCodeProducto != ""){
                    alert("El producto seleccionado ya ha sido agregado a la compra");
                    $('#'+barCodeCompra_ID).val("");
                    continuar = false;
                }

            }

            if (continuar) {
                var url = $("#base_url").val() + "/productos/datos-producto";
                var params = {_token: $("#general-token").val(), id: id, proveedor: proveedor, barCodeProducto:barCodeProducto };
                $("#progress-elementos-compra-modal").removeClass("hide");
                $("#contenedor-botones-elementos-compra-modal").addClass("hide");
                $.post(url, params, function (data) {

                    $('#'+ id_check).prop("checked", false)
                    if (data.success) {

                        if ($(input_nombre).val() != "") {
                            valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
                            for (i in productos) {
                                if (valor == productos[i].id) {
                                    productos.splice(i, 1);
                                }
                            }
                        }
                        productos.push(data.producto);
                        establecerCamposProducto(accion_actual, barCodeCompra_ID, data);
                        valoresCompra();
                        input_nombre = null;
                        tipo = null;
                        $("#modal-elementos-compra").closeModal();
                        $(".lean-overlay").remove();
                        agregarElementoCompra(true);
                    } else {
                        if(data.mensaje == "Debes crear este producto") {
                            $('#link-crear-producto').click();
                            //Materialize.toast(data.mensaje, 10000, 'red')
                        }else if(data.mensaje == "Debes relacionar este producto con el proveedor actual"){
                            id_select_relacion = data.producto.id;
                            $('#modal-relacion-º').openModal()
                            //Materialize.toast(data.mensaje, 4000, 'red')
                        }
                        Materialize.toast(data.mensaje, 4000, 'red')
                    }

                    $("#progress-elementos-compra-modal").addClass("hide");
                    $("#contenedor-botones-elementos-compra-modal").removeClass("hide");
                })
            }
        }
    }
}

function showCambiarPrecioProducto(idProducto,element){
    $(element).children("i").removeClass("fa-pencil");
    $(element).children("i").addClass("fa-spin fa-spinner");
    var url = $("#base_url").val()+"/productos/edit-precio";
    $.post(url,{_token:$("#general-token").val(),id_producto:idProducto,id_proveedor:$("#proveedor").val()},function(data){
        $("#contenedor-form-edit-precio").html(data);
        $("#modal-edit-precio").openModal();
        $(element).children("i").removeClass("fa-spin fa-spinner");
        $(element).children("i").addClass("fa-pencil");
    })
}

function showCambiarPrecioMateriaPrima(idMateriaPrima,element){
    $(element).children("i").removeClass("fa-pencil");
    $(element).children("i").addClass("fa-spin fa-spinner");
    var url = $("#base_url").val()+"/materia-prima/edit-precio";
    $.post(url,{_token:$("#general-token").val(),id_materia_prima:idMateriaPrima,id_proveedor:$("#proveedor").val()},function(data){
        $("#contenedor-form-edit-precio-materia-prima").html(data);
        $("#modal-edit-precio-materia-prima").openModal();
        $(element).children("i").removeClass("fa-spin fa-spinner");
        $(element).children("i").addClass("fa-pencil");
    })
}

function cambiarPrecioMateriaPrima(){
    var params = $("#form-edit-precio-materia-prima").serialize()+"&proveedor="+$("#proveedor").val();
    DialogCargando("Editando ...");
    var url = $("#base_url").val()+"/materia-prima/update-precio";
    $.post(url,params,function(data){
        if(data.success){
            //mostrarErrores("contenedor-confirmacion-editar-valor-producto", ["Los datos del producto han sido editados con éxito"]);
            for(i in materiasPrimas){
                if(materiasPrimas[i].id == data.materia_prima.id){
                    delete materiasPrimas[i];
                    var url = $("#base_url").val() + "/materia-prima/datos-materia-prima";

                    input_nombre = null;
                    $(".id-pr").each(function(i,el){
                        if($(el).val() == data.materia_prima.id){
                            input_nombre = $(el).parent().children(".nombre").eq(0);
                        }
                    })

                    params = {_token: $("#general-token").val(), id: data.materia_prima.id,proveedor:$("#proveedor").val()};
                    $.post(url, params, function (data) {
                        if (data.success) {
                            materiasPrimas.push(data.materia_prima);
                            var btn_edit_precio = "<a class='btn-edit-precio' style='cursor: pointer;float: right;' onclick='showCambiarPrecioMateriaPrima("+data.materia_prima.id+",this)'><i class='fa fa-pencil'></i></a>";
                            $(input_nombre).val(data.materia_prima.nombre);
                            $(input_nombre).parent().children(".id-pr").eq(0).val(data.materia_prima.id);
                            $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
                            //$(input_nombre).parent().parent().children("td").children(".iva").eq(0).text(data.materia_prima.iva+"");
                            var valor_unitario = parseFloat(data.materia_prima.valor_proveedor);
                            $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.materia_prima.sigla);
                            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(valor_unitario,2,',','.'));
                            $(input_nombre).parent().parent().children("td").children(".btn-edit-precio").eq(0).remove();
                            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
                            $(input_nombre).parent().parent().children("td").children(".vlr-subtotal").eq(0).text("$ "+formato_numero(valor_unitario,2,',','.'));

                            valoresCompra();
                            input_nombre = null;
                            tipo = null;
                            lanzarToast("Los datos de la materia prima han sido editados con éxito","Confirmación",8000);
                            $("#modal-edit-precio-materia-prima").closeModal();
                        } else {
                            alert("Ocurrio un error al seleccionar el producto, por favor intente nuevamente");
                        }
                        CerrarDialogCargando();
                    })
                    break;
                }
            }
            valoresCompra();
            $("#modal-edit-precio").closeModal()

        }
    }).error(function(jqXHR,state,error){
        mostrarErrores("contenedor-errores-editar-valor-producto", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function cambiarPrecioProducto(){
    var params = $("#form-edit-precio").serialize()+"&proveedor="+$("#proveedor").val();
    DialogCargando("Editando ...");
    var url = $("#base_url").val()+"/productos/update-precio";
    $.post(url,params,function(data){
        if(data.success){
            //mostrarErrores("contenedor-confirmacion-editar-valor-producto", ["Los datos del producto han sido editados con éxito"]);
            for(i in productos){
                if(productos[i].id == data.producto.id){
                    delete productos[i];
                    var url = $("#base_url").val() + "/productos/datos-producto";

                    input_nombre = null;
                    $(".id-pr").each(function(i,el){
                        if($(el).val() == data.producto.id){
                            input_nombre = $(el).parent().children(".nombre").eq(0);
                        }
                    })

                    params = {_token: $("#general-token").val(), id: data.producto.id,proveedor:$("#proveedor").val()};
                    $.post(url, params, function (data) {
                        if (data.success) {
                            productos.push(data.producto);
                            var btn_edit_precio = "<a class='btn-edit-precio' style='cursor: pointer;float: right;' onclick='showCambiarPrecioProducto("+data.producto.id+",this)'><i class='fa fa-pencil'></i></a>";
                            $(input_nombre).val(data.producto.nombre);
                            $(input_nombre).parent().children(".id-pr").eq(0).val(data.producto.id);
                            $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
                            //$(input_nombre).parent().parent().children("td").children(".iva").eq(0).text(data.producto.iva+"");
                            var valor_unitario = parseFloat(data.producto.precio_costo) + ((parseFloat(data.producto.precio_costo) * parseFloat(data.producto.iva))/100);
                            $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
                            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(valor_unitario,2,',','.'));
                            $(input_nombre).parent().parent().children("td").children(".btn-edit-precio").eq(0).remove();
                            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
                            $(input_nombre).parent().parent().children("td").children(".vlr-subtotal").eq(0).text("$ "+formato_numero(valor_unitario,2,',','.'));

                            valoresCompra();
                            input_nombre = null;
                            tipo = null;
                            lanzarToast("Los datos del producto han sido editados con éxito","Confirmación",8000);
                        } else {
                            alert("Ocurrio un error al seleccionar el producto, por favor intente nuevamente");
                        }
                        CerrarDialogCargando();
                    })
                    break;
                }
            }
            valoresCompra();
            $("#modal-edit-precio").closeModal()

        }
    }).error(function(jqXHR,state,error){
        mostrarErrores("contenedor-errores-editar-valor-producto", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function seleccionMateriaPrima(barCodeCompra_ID, accion_actual, id){
    var barCodeProducto = $("#" + barCodeCompra_ID).val();
    //var id = $('input:radio[name=radio-materia]:checked').val();
    var proveedor =$("#proveedor").val();
    if(typeof proveedor == "undefined" || proveedor == ""){
        alert("La información es incorrecta");
        return false;
    }
    if(typeof id == "undefined" && barCodeProducto == undefined){
        alert("Seleccione una materia prima");
    }else{
        var continuar = true;
        for(i in materiasPrimas){
            if(materiasPrimas[i].id == id){
                alert("La materia prima seleccionada ya ha sido agregada a la compra");
                continuar = false;
            }
        }

        if(continuar) {
            var url = $("#base_url").val() + "/materia-prima/datos-materia-prima";
            var params = {_token: $("#general-token").val(), id: id,proveedor:proveedor, barCodeProducto:barCodeProducto};
            $("#progress-elementos-compra-modal").removeClass("hide");
            $("#contenedor-botones-elementos-compra-modal").addClass("hide");
            $.post(url, params, function (data) {
                if (data.success) {

                    if($(input_nombre).val() != ""){
                        valor = $(input_nombre).parent().children(".id-pr").eq(0).val();
                        for(i in materiasPrimas){
                            if(valor == materiasPrimas[i].id){
                                materiasPrimas.splice(i,1);
                            }
                        }
                    }
                    materiasPrimas.push(data.materia_prima);
                    establecerCamposProducto(accion_actual, barCodeCompra_ID, data)
                    valoresCompra();
                    input_nombre = null;
                    tipo = null;
                    $("#modal-elementos-compra").closeModal();
                    agregarElementoCompra(true);
                } else {

                    if(data.mensaje == "Debes crear esta materia prima"){
                        $('#link-crear-materia-prima').click();
                        Materialize.toast(data.mensaje, 4000, 'red');
                    }else if(data.sugerencia == "relacionarMp"){
                        id_select_relacion_MP = data.materiasP.id;
                        $('#modal-relacion-proveedor-mp').openModal();
                        Materialize.toast(data.mensaje, 4000, 'blue')
                    }else {
                        Materialize.toast(data.mensaje, 4000, 'red');
                    }
                }

                $("#progress-elementos-compra-modal").addClass("hide");
                $("#contenedor-botones-elementos-compra-modal").removeClass("hide");
            })
        }
    }
}

/*Edison*/
function establecerCamposProducto(accion, input_ID, data){
    var idcheck = input_ID.replace('barCodeCompra', 'tipo');

    if(tipo == "producto"){
        var btn_edit_precio = "<a class='btn-edit-precio' style='cursor: pointer;' onclick='showCambiarPrecioProducto(" + data.producto.id + ",this)'><i class='fa fa-pencil'></i></a>";

        if(accion == "barCodeProductosCompras" && barCodeProducto != undefined){
            $('#' + input_ID).parent().parent().children("td").children(".nombre").eq(0).val(data.producto.nombre)
            $('#' + input_ID).val(data.producto.barcode);
            $('#' + input_ID).parent().parent().children("td").children(".id-pr").eq(0).val(data.producto.id);
            $('#' + input_ID).parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $('#' + input_ID).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
            $('#' + input_ID).parent().parent().children("td").children(".iva").eq(0).text(data.producto.iva + "%");
            var iva = 0;
            if(data.producto.iva)iva = parseFloat(data.producto.iva);
            var valor_unitario = parseFloat(data.producto.precio_costo) + ((parseFloat(data.producto.precio_costo) * iva)/100);
            $('#' + input_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ " + formato_numero(valor_unitario+" ", 2, ',', '.'));
            $('#' + input_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).children(".btn-edit-precio").eq(0).remove();
            $('#' + input_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $('#' + input_ID).parent().parent().children("td").children(".vlr-subtotal").eq(0).text("" + formato_numero(valor_unitario, 2, ',', '.'));
            $('input[name="'+idcheck+'"]').prop('disabled', true);
        }else {

            $(input_nombre).parent().parent().children("td").children(".barCodeCompras").eq(0).val(data.producto.barcode)
            $(input_nombre).val(data.producto.nombre);
            $(input_nombre).parent().children(".id-pr").eq(0).val(data.producto.id);
            $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
            $(input_nombre).parent().parent().children("td").children(".iva").eq(0).text(data.producto.iva + "");
            var iva = 0;
            if(data.producto.iva)iva = parseFloat(data.producto.iva);
            var valor_unitario = parseFloat(data.producto.precio_costo) + ((parseFloat(data.producto.precio_costo) * iva)/100);
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text("" + formato_numero(valor_unitario, 2, ',', '.'));
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).children(".btn-edit-precio").eq(0).remove();
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $(input_nombre).parent().parent().children("td").children(".vlr-subtotal").eq(0).text("" + formato_numero(valor_unitario, 2, ',', '.'));
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(0).children().eq(0).prop('disabled',true);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(1).children().eq(0).prop('disabled',true);

        }
        //console.log(valor_unitario);
    }else if(tipo == "materia prima"){
        var btn_edit_precio = "<a class='btn-edit-precio' style='cursor: pointer;' onclick='showCambiarPrecioMateriaPrima(" + data.materia_prima.id + ",this)'><i class='fa fa-pencil'></i></a>";
        if(accion == "barCodeProductosCompras" && barCodeProducto != undefined){
            $('#' + input_global_ID).val(data.materia_prima.codigo);
            $('#' + input_global_ID).parent().parent().children("td").children(".nombre").eq(0).val(data.materia_prima.nombre);
            $('#' + input_global_ID).parent().parent().children("td").children(".id-pr").eq(0).val(data.materia_prima.id);
            $('#' + input_global_ID).parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $('#' + input_global_ID).parent().parent().children("td").children(".unidad").eq(0).text(data.materia_prima.sigla);
            $('#' + input_global_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(data.materia_prima.valor_proveedor,2,',','.'));
            $('#' + input_global_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $('#' + input_global_ID).parent().parent().children("td").children(".vlr-subtotal").eq(0).text(""+formato_numero(data.materia_prima.valor_proveedor,2,',','.'));
            $('input[name="'+idcheck+'"]').prop('disabled', true);
        }else{
            $(input_nombre).val(data.materia_prima.nombre);
            $(input_nombre).parent().parent().children("td").children(".barCodeCompras").eq(0).val(data.materia_prima.codigo);
            $(input_nombre).parent().children(".id-pr").eq(0).val(data.materia_prima.id);
            $(input_nombre).parent().parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.materia_prima.sigla);
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(data.materia_prima.valor_proveedor,2,',','.'));
            $(input_nombre).parent().parent().children("td").children(".vlr-subtotal").eq(0).text(""+formato_numero(data.materia_prima.valor_proveedor,2,',','.'));
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(0).children().eq(0).prop('disabled',true);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(1).children().eq(0).prop('disabled',true);
        }
    }
    $("#tabla-detalle-producto tbody tr:last td:first-child").eq(0).children().eq(0).children().eq(0).attr("checked", "checked")
}

function valoresCompra() {
    var subtotal = 0;
    var iva = 0;
    var valor = 0;
    if (productos.length > 0){
        for(i in productos){
            var iva_x = 0;
            if(productos[i].iva)iva_x = parseFloat(productos[i].iva);
            valor = parseFloat(productos[i].precio_costo) + ((parseFloat(productos[i].precio_costo) * iva_x)/100);
            subtotal += valorTotalElemento(productos[i].id,valor,"producto");
            iva += valorTotalIvaElemento(productos[i].id,parseFloat(productos[i].precio_costo),"producto",iva_x);
        }
    }
    if (materiasPrimas.length > 0){
        for(i in materiasPrimas){
            subtotal += valorTotalElemento(materiasPrimas[i].id,parseFloat(materiasPrimas[i].valor_proveedor),"materia prima");
        }
    }
    var total = subtotal + iva;
    total_pagar_compra = total;
    var totalStr = formato_numero(total,2,',','.');

    var subtotalStr = formato_numero(subtotal,2,',','.');
    var ivaStr = formato_numero(iva,2,',','.');
    var totalStr = formato_numero(subtotal,2,',','.');

    //$("#txt-subtotal").text("$ "+subtotalStr);
    //$("#txt-iva").text("$ "+ivaStr);
    $("#txt-total-pagar").text("$ "+totalStr);

}

function valorTotalElemento(id_pr,valor,tipo){
    var elemento = null;
    $(".id-pr").each(function(indice){
        var tipo_elm = $(".id-pr").eq(indice).parent().parent().children(".radio-tipo-elemento").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        if(tipo_elm == tipo) {
            if ($(".id-pr").eq(indice).val() == id_pr) {
                elemento = $(".id-pr").eq(indice);
            }
        }
    })

    var fila = $(elemento).parent().parent();
    var cantidad = parseFloat($(fila).children("td").children(".cantidad").eq(0).val());
    var total = cantidad * valor;
    var totalStr = formato_numero(total,2,',','.');
    $(fila).children("td").children(".vlr-subtotal").text(""+totalStr);


    return total;

}
function comprar() {
    var efectivo_caja = parseInt($("#efectivo_caja").val());

    if($("#estado_pago").val() == "Pendiente por pagar"){
        $("#modal-forma-abono").openModal({
            complete: function() {window.location.reload(true); }}
        );
    }else if("Pagada") {
        realizarCompra();
    }
}
function maxNumeroCuotas() {
    var numero_coutas = $("#numero_cuotas").val();

    if (numero_coutas > 12){
        alert("El número máximo de cuotas son 12");
        $("#numero_cuotas").val('1');
    }

}
function realizarCompraConNotificaciones() {
    dias_credito = $("#dias_credito").val();
    numero_cuotas = $("#numero_cuotas").val();
    tipo_periodicidad_notificacion = $("#tipo_periodicidad_notificacion").val();
    fecha_primera_notificacion = $("#fecha_primera_notificacion").val();

    if (tipo_periodicidad_notificacion != ''){
        if (numero_cuotas > 0){
            if (dias_credito > 0){
                if (dias_credito <= 120){
                    $("#modal-forma-abono").closeModal();
                    realizarCompra();
                }else {
                    alert('Los dias de credito son máximo 120 dias');
                }
            }else{
                alert('Los dias de credito son minimo de 1 dia');
            }

        }else {
            alert('La cantidad de cuotas debe ser mayor que cero');
            $("#numero_cuotas").val('1');
        }
    }else {
        alert("Debe seleccionar un tipo de periodicidad para las notificaciones");
    }

}

function valorTotalIvaElemento(id_pr,valor,tipo,iva){
    var elemento = null;
    $(".id-pr").each(function(indice){
        var tipo_elm = $(".id-pr").eq(indice).parent().parent().children(".radio-tipo-elemento").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        if(tipo_elm == tipo) {
            if ($(".id-pr").eq(indice).val() == id_pr) {
                elemento = $(".id-pr").eq(indice);
            }
        }
    })

    var fila = $(elemento).parent().parent();
    var cantidad = parseFloat($(fila).children("td").children(".cantidad").eq(0).val());
    var total = cantidad * valor;
    var total_iva =  (total*iva)/100;
    return total_iva;

}

function eliminarUltimaFila(){

    var cantidadFilas = $('#tabla-detalle-producto tbody').find('tr').length;
    var valorUltimaFila = ($('#tabla-detalle-producto tbody tr:last-child').children().val())
    var valorUltimaFila = ($('#tabla-detalle-producto tbody tr:last-child').children().next().next().children().val())

    if(cantidadFilas > 1 && valorUltimaFila == ''){
        $('#tabla-detalle-producto tbody tr:last-child').remove();
    }
}

function realizarCompra(){
    eliminarUltimaFila();
    var id_proveedor = $("#proveedor").val();
    var error = false;
    var mensaje = "";
    var params = {};
    params.id_proveedor = id_proveedor;
    if($("#predeterminado").is(":checked")) {
        params.predeterminado = "1";
    }
    params.numero_cuotas = numero_cuotas;
    params.dias_credito = dias_credito;
    params.tipo_periodicidad_notificacion = tipo_periodicidad_notificacion;
    params.fecha_primera_notificacion = fecha_primera_notificacion;
    //se ha establecido el cliente
    if(id_proveedor.length){
        //se ha seleccionado por lo menos un producto
        if($(".id-pr").eq(0).val()){
            //todos los productos tienen cantidades
            var auxPr = 0;
            var auxMp = 0;
            $(".id-pr").each(function(indice){
                var tipo_elm = $(".id-pr").eq(indice).parent().parent().children(".radio-tipo-elemento").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();

                if (tipo_elm != undefined){
                    tipo_elm = tipo_elm.replace(" ","_");
                }
                var cant = parseFloat($(".id-pr").eq(indice).parent().parent().children("td").children(".cantidad").eq(0).val());
                if(cant >= 1){
                    if($(".id-pr").eq(indice).val()) {
                        var aux =1;
                        if(tipo_elm == "producto"){
                            auxPr++;
                            aux = auxPr;
                        }else if (tipo_elm == "materia_prima"){
                            auxMp++;
                            aux = auxMp;
                        }
                        params[tipo_elm+"_"+aux] = {id: $(".id-pr").eq(indice).val(), cantidad: cant};
                    }
                }else{
                    error = true;
                    mensaje = "Todos los elementos deben tener una cantidad mayor o igual a 1";
                }

            });
            //return false;
            params.estado = $("#estado").val();
            params.estado_pago = $("#estado_pago").val();
            if(!error) {
                var url = $("#base_url").val()+"/compra/store";
                params._token = $("#general-token").val();
                DialogCargando("Realizando compra ...");
                $.post(url,params,function(data){
                    if(data.success){
                        $("body").scrollTop(50);
                        window.location.reload(true);
                    }
                    $("#contenedor-boton-realizar-compra").removeClass("hide");
                    $("#progress-compra").addClass("hide");
                }).error(function(jqXHR,error,state){
                    CerrarDialogCargando();
                    mostrarErrores("contenedor-errores-detalles-compra",JSON.parse(jqXHR.responseText));
                })
            }
        }else{
            error = true;
            mensaje = "Agregue por lo menos un elemento a la compra";
        }
    }else{
        error = true;
        mensaje = "No se ha establecido ningun proveedor.";
    }

    if(error){
        mostrarErrores("contenedor-errores-detalles-compra",{"error":[mensaje]});
        $("body").scrollTop(100);
        $("#contenedor-boton-realizar-compra").removeClass("hide");
        $("#progress-compra").addClass("hide");
    }
}
function openFormEstados(id_compra,valor_compra, numero_compra) {

    $("#contenido-estados-compra").empty();
    inicializarMaterialize();

    var url = $("#base_url").val()+"/compra/edit/"+id_compra;
    var saldo_pagar = $("#saldo_a_pagar").val();
    $.get(url,function (res) {
        $("#contenido-estados-compra").html(res);
        $("#numeroCompra").html(numero_compra)
        //$("#contenido-estados-compra").append("<input type='text' name='valor_compra' id='valor_compra' value='"+saldo_pagar+"'>");
        inicializarMaterialize();
    });

}
function actualizarEstados() {
    var valor_compra_saldo = $("#valor_compra_saldo").val();
    var efectivo_caja = $("#efectivo_caja").val();
    var estado_pago_last = $("#estado_pago:disabled").val();
    var estado_pago_actual = '';
    if (estado_pago_last == undefined) {
        var estado_pago_actual = $("#estado_pago").val();
        if (estado_pago_actual == 'Pagada') {
            if (parseInt(valor_compra_saldo) <= parseInt(efectivo_caja)) {
                realizarActualizacionEstados();
            } else {
                if (confirm("Fondos insuficientes en la caja, desea entrar dinero a la caja?")) {
                    $("#modal-cuadre-caja").openModal();
                    $("#div-posicion").append("<input type='hidden' id='posicion' value='cambia-estado'>");
                }
                return false;
            }
        } else {
            realizarActualizacionEstados();
        }
    } else {
        realizarActualizacionEstados();
    }
}
function realizarActualizacionEstados() {

    var valor_compra_saldo = $("#valor_compra_saldo").val();


    inicializarMaterialize();

    $("#estados-compras-form").addClass("hide");
    $("#progress-action-form-estados-compra").removeClass("hide");
    $("#btn-action-form-estados-compra").addClass("hide");

    var url = $("#estados-compras-form").attr("action");
    var parametros = new FormData(document.getElementById("estados-compras-form"));
    parametros.append('valor_pagar',valor_compra_saldo);

    $.ajax({
        url: url,
        type: "POST",
        dataType: "html",
        data: parametros,
        cache: false,
        contentType: false,
        processData: false
    }).done(function (data) {
        $("#estados-compras-form").removeClass("hide");
        $("#progress-action-form-estados-compra").addClass("hide");
        $("#btn-action-form-estados-compra").addClass("hide");

        $("#mensaje-confirmacion-estados-compra").show(1000, function () {
            $("#mensaje-confirmacion-estados-compra").addClass('contenedor-confirmacion');
            $("#mensaje-confirmacion-estados-compra").html("Se actualizó correctamente los estados de la compra");
        });
        setTimeout(function () {
            $("#mensaje-confirmacion-estados-compra").fadeOut(1500);
            $("#mensaje-confirmacion-estados-compra").html("");
            location.reload(true);
        }, 3000);
        //$("#div-lista-compras").html(data);
        //$("#modal-estados-compra").closeModal();

    }).error(function (data) {
        $("#estados-compras-form").removeClass("hide");
        $("#progress-action-form-estados-compra").addClass("hide");
        $("#btn-action-form-estados-compra").removeClass("hide");

        $("#mensaje-confirmacion-estados-compra").show(1000, function () {
            $("#mensaje-confirmacion-estados-compra").addClass('contenedor-errores');
            $("#mensaje-confirmacion-estados-compra").html('Oopss! Ocurrio un error');
        });
        setTimeout(function () {
            $("#mensaje-confirmacion-estados-compra").fadeOut(1500);
            $("#mensaje-confirmacion-estados-compra").html("");
            //location.reload(true);
            inicializarMaterialize();
        }, 3000);
    });
    inicializarMaterialize();
}

function crearProveedor(){
    $("#contenedor-botones-crear-proveedor").addClass("hide");
    $("#progress-crear-proveedor").removeClass("hide");
    var url = $("#form-proveedor").attr("action");
    var data = $("#form-proveedor").serialize();
    $.post(url,data,function(data){
        if(data.success){
            $.post($("#base_url").val()+"/compra/null",{_token:$("#general-token").val()},function(){});
            $("#form-proveedor #nombre").val("");
            $("#form-proveedor #nit").val("");
            $("#form-proveedor #contacto").val("");
            $("#form-proveedor #direccion").val("");
            $("#form-proveedor #telefono").val("");
            $("#form-proveedor #correo").val("");
            mostrarConfirmacion("contenedor-confirmacion-crear-compra",["El proveedor ha sido creado con éxito"]);
            $("#modal-crear-proveedor").closeModal();
            proveedor.push(data.proveedor_data);
            seleccionProveedor(null,data.proveedor);
        }
        $("#contenedor-botones-crear-proveedor").removeClass("hide");
        $("#progress-crear-proveedor").addClass("hide");
    }).error(function(jqXHR,error,estado){
        mostrarErrores("contenedor-errores-crear-proveedor",JSON.parse(jqXHR.responseText));
        $("#contenedor-botones-crear-proveedor").removeClass("hide");
        $("#progress-crear-proveedor").addClass("hide");
    })
}
function detalleAbono(id_compra) {
    var url = $("#base_url").val()+"/compra/abono/"+id_compra;
    $("#modal-abonos-compra").openModal({
        complete: function() {window.location.reload(true); }}
    );
    $.get(url, function (res) {
        $("#lista-abonos-compra").html(res);
        inicializarMaterialize();
    });
}
function verHistorialAbonos() {
    var clase_la = $("#div-listado-abonos").hasClass('hide');

    if (clase_la){
        $("#div-listado-abonos").removeClass('hide');
        $("#div-form-abono").addClass('hide');
    }else {
        $("#div-listado-abonos").addClass('hide');
        $("#div-form-abono").removeClass('hide');
    }

}
function abonar(saldo) {
    var valor_abono = parseInt($("#valor").val());
    var efectivo_caja = parseInt($("#efectivo_caja").val());
    var numero_abonos = parseInt($("#numero-abonos-hechos").val());
    var saldo = parseInt(saldo);

    //valores ingresados
    valor_abono_last = $("#valor").val();
    fecha_abono_last = $("#fecha").val();
    comentario_abono_last = $("#nota").val();

    if(numero_abonos == 11){
        if(valor_abono == saldo){
            capacidadPago(valor_abono,efectivo_caja);
        }else{
            alert("Esta es la última cuota y debe cancelar el total de la compra");
            //return false;
        }
    }else {
        if (valor_abono <= saldo){
            capacidadPago(valor_abono,efectivo_caja);
        }else{
            alert("El valor del abono no puede ser mayor al saldo de la compra");
            //return false;
        }
    }
    valor_abono =0;
}



function capacidadPago(valor_abono, efectivo_caja) {
    //alert(valor_abono + "-" + efectivo_caja);
    if ((parseInt(efectivo_caja)-parseInt(valor_abono))>=0){
        realizarAbono();
    }else{
        if (confirm("Fondos insuficientes en la caja, desea entrar dinero a la caja?")){
            $("#modal-cuadre-caja").openModal();
            $("#div-posicion").append("<input type='hidden' id='posicion' value='realiza-abono'>");
            //$("#")
        }
        //return false;
    }
}
function realizarAbono() {
    if(confirm("Esta seguro de realizar el abono?, este no será editable")){
        $("#form-abonos-pago").addClass('hide');
        $("#progress-action-form-abonos-compra").removeClass('hide');
        $("#btn-action-form-abonos-pago").addClass('hide');

        var url = $("#base_url").val()+"/compra/abonar";
        var params = new FormData(document.getElementById('form-abonos-pago'));
        $.ajax({
            url: url,
            type: "POST",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            // var res = JSON.parse(data);
            inicializarMaterialize;

            $("#form-abonos-pago").removeClass('hide');
            $("#progress-action-form-abonos-compra").addClass('hide');
            $("#btn-action-form-abonos-pago").removeClass('hide');

            $("#lista-abonos-compra").html(data);

            $("#valor").val('');
            $("#nota").val('');

            $("#mensaje-confirmacion-abonos-compra").show(1000, function () {
                $("#mensaje-confirmacion-abonos-compra").addClass('contenedor-confirmacion');
                $("#mensaje-confirmacion-abonos-compra").html("Se realizo el abono correctamente");
            });
            setTimeout(function () {
                $("#mensaje-confirmacion-abonos-compra").fadeOut(1500);
                $("#mensaje-confirmacion-abonos-compra").html("");
                //location.reload(true);
            }, 3000);

        }).error(function (data) {
            $("#form-abonos-pago").removeClass('hide');
            $("#progress-action-form-abonos-compra").addClass('hide');
            $("#btn-action-form-abonos-pago").removeClass('hide');

            $("#mensaje-confirmacion-abonos-compra").show(1000, function () {
                $("#mensaje-confirmacion-abonos-compra").addClass('contenedor-errores');
                $("#mensaje-confirmacion-abonos-compra").html("Oops! Ocurrio un error");
            });
            setTimeout(function () {
                $("#mensaje-confirmacion-abonos-compra").fadeOut(1500);
                $("#mensaje-confirmacion-abonos-compra").html("");
                //location.reload(true);
            }, 3000);
            $("#lista-abonos-compra").html(data);
        })
    }
}
function cerrarModalCuadreCaja() {
    $("#modal-cuadre-caja").closeModal();
}
function entrarDineroCaja() {

    if($("#valor_caja").val() != '' && $("#valor_caja").val() > 0 && $("#comentario_caja").val() != '') {

        var posicion = $("#posicion").val();

        var url = $("#form-caja").attr('action');
        var compra_id = parseInt($("#tipo_abono_id").val());
        var params = new FormData(document.getElementById('form-caja'));
        params.append('tipo_abono_id', compra_id);

        if (isNaN(compra_id) && posicion == 'realiza-compra'){
            //$("#valor_caja").val('');
            //$("#comentario_caja").val('');
            entrarDineroCajaCompra();
            return true;
        }
        if (posicion == 'cambia-estado'){
            //$("#valor_caja").val('');
            //$("#comentario_caja").val('');
            entrarDineroCajaCambioEstado();
            return true;
        }

        $.ajax({
            url: url,
            type: "POST",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            $("#mensaje-confirmacion-cuadre-caja").show(1000, function () {
                $("#mensaje-confirmacion-cuadre-caja").addClass('contenedor-confirmacion');
                $("#mensaje-confirmacion-cuadre-caja").html("Se realizo la operación correctamente");
                //$("#lista-abonos-compra").html(data);

                var efectivo_caja_actual = JSON.parse(data);
                $("#efectivo_caja").val(efectivo_caja_actual.efectivo_caja);

                $("#valor").val(valor_abono_last);
                $("#fecha").val(fecha_abono_last);
                $("#nota").val(comentario_abono_last);

            });
            setTimeout(function () {
                $("#mensaje-confirmacion-cuadre-caja").fadeOut(1500);
                $("#mensaje-confirmacion-cuadre-caja").html("");

                $("#valor_caja").val('');
                $("#comentario_caja").val('');
                $("#modal-cuadre-caja").closeModal();
            }, 3000);

        }).error(function (data) {
            $("#mensaje-confirmacion-cuadre-caja").show(1000, function () {
                $("#mensaje-confirmacion-cuadre-caja").addClass('contenedor-errores');
                $("#mensaje-confirmacion-cuadre-caja").html("Oops! Ocurrio un error");
                $("#lista-abonos-compra").html(data);

            });
            setTimeout(function () {
                $("#mensaje-confirmacion-cuadre-caja").fadeOut(1500);
                $("#mensaje-confirmacion-cuadre-caja").html("");
                $("#modal-cuadre-caja").closeModal();
            }, 3000);
        });
    }else{
        alert("Existen campos vacios o nulos");
    }
}
function entrarDineroCajaCompra() {

    if($("#valor_caja").val() != '' && $("#valor_caja").val() > 0 && $("#comentario_caja").val() != '') {
        DialogCargando("Realizando operación ...");
        var url = $("#base_url").val()+"/caja/operacion-caja-compra";
        var params = new FormData(document.getElementById('form-caja'));
        $.ajax({
            url: url,
            type: "POST",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            CerrarDialogCargando();
            var efectivo_caja_actual = JSON.parse(data);
            $("#efectivo_caja").val(efectivo_caja_actual.efectivo_caja);
            $("#modal-cuadre-caja").closeModal();
            comprar();

            /*$("#mensaje-confirmacion-cuadre-caja").show(1000, function () {
             $("#mensaje-confirmacion-cuadre-caja").addClass('contenedor-confirmacion');
             $("#mensaje-confirmacion-cuadre-caja").html("Se realizo la operación correctamente");
             var efectivo_caja_actual = JSON.parse(data);
             $("#efectivo_caja").val(efectivo_caja_actual.efectivo_caja);

             });
             setTimeout(function () {
             $("#mensaje-confirmacion-cuadre-caja").fadeOut(1500);
             $("#mensaje-confirmacion-cuadre-caja").html("");

             $("#valor_caja").val('');
             $("#comentario_caja").val('');
             $("#modal-cuadre-caja").closeModal();
             }, 3000);*/

        }).error(function (data) {
            CerrarDialogCargando();
            $("#mensaje-confirmacion-cuadre-caja").show(1000, function () {
                $("#mensaje-confirmacion-cuadre-caja").addClass('contenedor-errores');
                $("#mensaje-confirmacion-cuadre-caja").html("Oops! Ocurrio un error");
                //$("#lista-abonos-compra").html(data);

            });
            setTimeout(function () {
                $("#mensaje-confirmacion-cuadre-caja").fadeOut(1500);
                $("#mensaje-confirmacion-cuadre-caja").html("");
                $("#modal-cuadre-caja").closeModal();
            }, 3000);
        });
    }else{
        alert("Existen campos vacios o nulos");
    }
}
function entrarDineroCajaCambioEstado() {

    //var valor_compra_saldo = $("#valor_compra_saldo").val();

    if($("#valor_caja").val() != '' && $("#valor_caja").val() > 0 && $("#comentario_caja").val() != '') {

        var url = $("#base_url").val()+"/caja/operacion-caja-cambio-estado";
        var params = new FormData(document.getElementById('form-caja'));
        //params.append('valor_compra',valor_compra_saldo);
        $.ajax({
            url: url,
            type: "POST",
            dataType: "html",
            data: params,
            cache: false,
            contentType: false,
            processData: false
        }).done(function (data) {
            $("#mensaje-confirmacion-cuadre-caja").show(1000, function () {
                $("#mensaje-confirmacion-cuadre-caja").addClass('contenedor-confirmacion');
                $("#mensaje-confirmacion-cuadre-caja").html("Se realizo la operación correctamente");
                var efectivo_caja_actual = JSON.parse(data);
                $("#efectivo_caja").val(efectivo_caja_actual.efectivo_caja);


            });
            setTimeout(function () {
                $("#mensaje-confirmacion-cuadre-caja").fadeOut(1500);
                $("#mensaje-confirmacion-cuadre-caja").html("");

                $("#valor_caja").val('');
                $("#comentario_caja").val('');
                $("#modal-cuadre-caja").closeModal();
            }, 3000);

        }).error(function (data) {
            $("#mensaje-confirmacion-cuadre-caja").show(1000, function () {
                $("#mensaje-confirmacion-cuadre-caja").addClass('contenedor-errores');
                $("#mensaje-confirmacion-cuadre-caja").html("Oops! Ocurrio un error");
                //$("#lista-abonos-compra").html(data);

            });
            setTimeout(function () {
                $("#mensaje-confirmacion-cuadre-caja").fadeOut(1500);
                $("#mensaje-confirmacion-cuadre-caja").html("");
                $("#modal-cuadre-caja").closeModal();
            }, 3000);
        });
    }else{
        alert("Existen campos vacios o nulos");
    }
}

function relacionarProductoProveedor(){
    DialogCargando("Relacionando producto ...");
    var params = $("#form-relacion-proveedor").serialize()+"&proveedor="+$("#proveedor").val()+"&id="+id_select_relacion;

    $("#progress-relacion-proveedor-modal").removeClass("hide");
    $("#contenedor-botones-relacion-proveedor-modal").addClass("hide");
    var url = $("#base_url").val()+"/productos/relacion-proveedor";
    $.post(url,params,function(data){
        if(data.success){
            //mostrarConfirmacion("contenedor-confirmacion-elementos-compra", ["El producto ha sido relacionado con el provedor"]);
            id_select_relacion = 0;
            $("#modal-relacion-proveedor").closeModal();
            if(accion_global != "barCodeProductosCompras"){cambiarListaElementos();}
            $("#progress-relacion-proveedor-modal").addClass("hide");
            $("#contenedor-botones-relacion-proveedor-modal").removeClass("hide");
            if(accion_global == "barCodeProductosCompras"){
                seleccionElemento(input_global_ID, accion_global );
            }
            $("#modal-relacion-proveedor #precio_costo").val("");
            $("#modal-relacion-proveedor #iva").val("");
            $("#modal-relacion-proveedor #utilidad").val("");
            $("#modal-relacion-proveedor #utilidad").css({cursor:"auto",color:"#000"});
            $("#modal-relacion-proveedor #utilidad").removeAttr("readonly");
            $("#modal-relacion-proveedor #precio_venta").val("");

            accion_global = 'modal_productos_compra';
            input_global_ID = data.producto_id;
            //$("#radio-producto-"+input_global_ID).attr('checked', 'checked');
            $("#div-footer-elementos-compra").addClass('hide');

            //$("#contenedor-confirmacion-elementos-compra").fadeOut(3000,function () {
            seleccionElemento('', accion_global,input_global_ID );
            //});
            CerrarDialogCargando();
        }
    }).error(function(jqXHR,state,error){
        CerrarDialogCargando();
        mostrarErrores("contenedor-errores-relacion-proveedor", JSON.parse(jqXHR.responseText));
        $("#progress-relacion-proveedor-modal").addClass("hide");
        $("#contenedor-botones-relacion-proveedor-modal").removeClass("hide");

        $("#contenedor-errores-relacion-proveedor").fadeOut(8000);
    });
    //seleccionElemento(input_global_ID, accion_global );
}

function relacionarProveedoresMateriaPrima(){
    DialogCargando("Relacionando materia prima ...");
    var params = $("#form-relacion-materia-prima").serialize()+"&proveedor="+$("#proveedor").val()+"&materiaPrima_ID="+id_select_relacion_MP;
    var url = $("#base_url").val()+"/materia-prima/relacionar-materia-prima";

    $.post(url, params, function(data){
        if(data.success){
            //cargarListaElementosMp("proveedor_actual", $("#proveedor").val());
            $('#form-relacion-materia-prima').trigger("reset");
            $('#modal-relacion-proveedor-mp').closeModal();
            //Materialize.toast("Se relacionó la materia prima correctamente", 4000, "blue");
            /*if(accion_global == "barCodeProductosCompras"){
             seleccionElemento(input_global_ID, accion_global );
             }*/
            seleccionElemento("","",id_select_relacion_MP);
            CerrarDialogCargando();
            id_select_relacion_MP = 0;
        }else{
            CerrarDialogCargando();
            Materialize.toast("Ocurrió un error, Por favor Intenta de nuevo.", 4000, "red")
        }
    });

}
