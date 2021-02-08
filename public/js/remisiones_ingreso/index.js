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
var tipo_producto_proveedor = "productos";
var ventana_producto = null;
var ventana_materia_prima = null;
var columnDefs = [{},{},{}];

var filaCompra = 2;
var barCodeProducto = "";
var accion = "";
var accion_global = "";
var input_global_ID = "";
var id_select_relacion_MP = 0;

var proveedor = [];
var click_action = false;
var seleccionable = false;


$(document).ready(function(){
    cargarTablaRemisionesIngreso();
});

function cargarTablaRemisionesIngreso() {
    var url_remisiones = $("#base_url").val()+"/remision-ingreso/detalle/";


    var checked = "";
    var i=1;
    var tabla_remisiones = $('#tabla-remisiones').dataTable({ "destroy": true });
    tabla_remisiones.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla-remisiones').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_remisiones = $('#tabla-remisiones').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/remision-ingreso/list-remisiones-ingreso",
            "type": "GET"
        },
        "columns": [
            { "data": "numero", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "usuario_creador", 'className': "text-center" },
            { "data": null, "defaultContent": "", "className": "text-center", "width": "80px" }
        ],
        "createdRow": function (row, data, index) {
            var posicion = 3;
            var opciones = "<a href= '" + url_remisiones + data.id + "' style='margin-right: 15px;'><i class='fa fa-chevron-right tooltipped' data-tooltip='Detalle'></i></a>";

            $('td', row).eq(posicion).html(opciones).css('width' , '5% !important');
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_remisiones.data().length){
                setTimeout(function () {
                    inicializarMaterialize();
                },700)
                i=1;
            }else{
                i++;
            }
        },
        "columnDefs": columnDefs,
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,3] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });

}


$(function () {
    $("#tabla-detalle-producto-remision tbody").on("click","tr td .fa-trash",function(){
        //console.log("Entro a uno... ");
        eliminarElementoDetalleProducto($(this));
    })

    $("#tabla-detalle-producto-remision tbody").on("keydown","tr td .nombre",function(){
        $(this).blur();
    })

    $("#tabla-detalle-producto-remision tbody").on("click keyup","tr td .barCodeRemisionesIngreso",function(){
        var tipo = $(this).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        if (typeof tipo == "undefined") {
            $(this).val("");
            //mostrarErrores("contenedor-errores-detalles-remision-ingreso", {"1": ["Seleccione el tipo de elemento  a agregar."]});
            Materialize.toast("Seleccione el tipo de elemento  a agregar.", 3000, "red")
        }
    });
    $("#tabla-detalle-producto-remision tbody").on("keyup blur","tr td .barCodeRemisionesIngreso",function(){
        setTimeout(function(){
            $("#contenedor-errores-detalles-remision-ingreso").fadeOut(3000);
        },2000);

    });

    //$( this ).off("click");
    $("#tabla-detalle-producto-remision tbody").on("click","tr td .nombre",function(e){
        e.stopPropagation();
        if (e.originalEvent.type == "click"){
            barCodeProducto = $(this).parent().parent().children("td").children(".barCodeRemisionesIngreso").eq(0).val();
            input_nombre = $(this);
            $(input_nombre).blur();
            tipo = $(input_nombre).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
            if (typeof tipo == "undefined") {
                mostrarErrores("contenedor-errores-detalles-remision-ingreso", {"1": ["Seleccione el tipo de elemento a agregar."]});
            } else {
                if(tipo == "producto") {
                    //$('#filtroCompras').removeClass('hide');
                    $("#titulo-modal-remisiones-ingreso").text("Productos")

                    $("#link-crear-producto").removeClass("hide");
                    $("#link-crear-materia-prima").addClass("hide");

                    $("#contenedor-select-categorias").removeClass("hide");
                }else {
                    //var nom_proveedor = $("#proveedor").val()
                    //$('#filtroCompras').addClass('hide');

                    $("#link-crear-producto").addClass("hide");
                    $("#link-crear-materia-prima").removeClass("hide");

                    $("#titulo-modal-remisiones-ingreso").text("Materias primas");
                    $("#contenedor-select-categorias").addClass("hide");
                }
                var load = $(input_nombre).parent().children(".fa-spin").eq(0);
                $(load).removeClass("hide");

                var url = $("#base_url").val()+"/remision-ingreso/lista-elementos-remision-ingreso";
                var params = {_token:$("#general-token").val(),proveedor:$("#proveedor").val(),filtro:$("#busqueda-elemento").val(),categoria:$("#categoria").val(),tipo_elemento:tipo,tipo:tipo_producto_proveedor};
                $.post(url,params,function(data){
                    $("#contenedor-elementos").html(data);
                    seleccionable = true;
                    $("#modal-elementos-remision-ingreso").openModal();

                    if(tipo == "materia prima"){
                        cargarListaElementosMp();
                        $(".dataTables_filter label input").focus();
                    }else{
                        cargarListaProductos();
                        $(".dataTables_filter label input").focus();
                    }
                    $(load).addClass("hide");
                    inicializarMaterialize();
                    click_action = true;
                })
        }
        }

    });


    $("#tabla-detalle-producto-remision tbody").on("change","tr td p input[type=radio]",function () {
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
        $(input_nombre).parent().parent().children("td").children(".barCodeRemisionesIngreso").eq(0).val("");
        $(input_nombre).val("");
        $(input_nombre).parent().children(".id-pr").eq(0).val("");
        $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
        $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text("");
        //$(input_nombre).parent().parent().children("td").children(".iva").eq(0).text("0");
        $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(0,0,',','.'));
        $(input_nombre).parent().parent().children("td").children(".vlr-subtotal").eq(0).text(""+formato_numero(0,0,',','.'));
        input_nombre = null;
    });
});

function cargarListaElementosMp(){
    $("#titulo-modal-remisiones-ingreso").text("Materias primas");


    var columnDefsss = [{},{}];
    var params = "_token="+$("#general-token").val();
    var i=1;
    var tabla_materias_primas = $('#tabla-materias-primas').dataTable({ "destroy": true });
    tabla_materias_primas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla-materias-primas').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    });
    tabla_materias_primas = $('#tabla-materias-primas').DataTable({
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/remision-ingreso/list-materias-primas?"+params,
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
            $(row).attr('onClick', "seleccionElemento('', 'modal_productos_remision', '" + data.id + "' )").css('cursor', 'pointer')
            $('td', row).eq(posicion).html("<input type='radio' id='radio-producto-"+ data.id +"' name='radio-materia' value='"+data.id+"'><label for='radio-producto-" + data.id + "'></label>");

        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_materias_primas.data().length){
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [5] }],
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
    });
}

function cargarListaProductos(){
    $("#titulo-modal-remisiones-ingreso").text("Productos");
    var params = "_token="+$("#general-token").val();
    var i=1;
    var tabla_productos = $('#tabla-productos').dataTable({ "destroy": true });
    tabla_productos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla-productos').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    });
    tabla_productos = $('#tabla-productos').DataTable({
        "autoWidth": false,
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/remision-ingreso/list-productos?"+params,
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

            $(row).attr('onClick', "seleccionElemento('', 'modal_productos_remision', '" + data.id + "' )").css('cursor', 'pointer')
            $('td', row).eq(0).html("<input type='radio' id='radio-producto-"+ data.id +"' name='radio-producto' value='"+data.id+"'><label for='radio-producto-" + data.id + "'></label>")
        },
        "fnRowCallback": function (nRow, aData, iDisplayIndex) {
            if(i === tabla_productos.data().length){
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

function eliminarElementoDetalleProducto(elemento,mensaje = true){
    if($("#tabla-detalle-producto-remision tbody tr").length == 1){
        if(mensaje)
            mostrarErrores("contenedor-errores-detalles-remision-ingreso",{"1":["No puede eliminar el elemento, una remisión debe tener por lo menos un elemento en la lista de detalles."]});
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

        }
    }
}

function agregarElementoRemision(cero_vacios = false){
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
            "<input name='tipo_" + filaCompra + "' type='radio' style='' id='tipo_" + filaCompra + "_pr' value='producto' checked='checked' />" +
            "<label for='tipo_" + filaCompra + "_pr' style='height: 4px !important;font-size: 0.85rem !important;'>Producto</label>" +
            "</p>" +
            "<p class='left-align'>" +
            "<input name='tipo_" + filaCompra + "' type='radio' style='' id='tipo_" + filaCompra + "_mp' value='materia prima' />" +
            "<label for='tipo_" + filaCompra + "_mp' style='height: 4px !important;font-size: 0.85rem !important;'>Materia P</label>" +
            "</p>" +
            "</td>" +

            "<td class='barCodeTd'>" +
            "<input class='barCodeRemisionesIngreso' id='barCodeRemision_" + filaCompra + "' placeholder='Código de barras' onblur=\"seleccionElemento(this.id, 'barCodeProductosRemisiones')\">" +
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
            /*"<td class='ivaTd'>"+
             "<p class='iva'>0</p>"+
             "</td>"+*/

            "<td>" +
            "<i class='fa fa-trash red-text text-darken-1 waves-effect waves-light' title='Eliminar elemento' style='cursor: pointer;margin-top: -16px'></i>" +
            "</td>" +
            "</tr>";

        filaCompra++;
        $("#tabla-detalle-producto-remision tbody").append(html);
        $("#tabla-detalle-producto-remision tbody .barCodeRemisionesIngreso").eq($("#tabla-detalle-producto-remision tbody .barCodeRemisionesIngreso").length - 1).focus();
        //alert($("#tabla-detalle-producto-remision tbody .barCodeRemisionesIngreso").length);
    }
}

function seleccionElemento(barCodeRemision_ID, accion_actual, elemento_id) {
    if(!tipo)
        tipo = "producto";
    if(seleccionable || barCodeRemision_ID) {
        seleccionable = false;
        var tip = $('#' + barCodeRemision_ID).parent().parent().children(".radio-tipo-elemento ").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
        if (typeof tip == "undefined" && accion_actual == "barCodeProductoRemision") {
            mostrarErrores("contenedor-errores-detalles-remision-ingreso", {"1": ["Selecciona el tipo de elemento a agregar."]});
            $('#contenedor-errores-detalles-remision-ingreso').setTimeout(function () {
                $(this).hide(1000)
            }, 3000);
            return false;
        }
        accion_global = accion_actual;
        input_global_ID = barCodeRemision_ID;
        barCodeProducto = $("#" + barCodeRemision_ID).val();
        if (tipo == "producto") {
            seleccionProducto(barCodeRemision_ID, accion_actual, elemento_id);
        } else if (tipo == "materia prima") {
            seleccionMateriaPrima(barCodeRemision_ID, accion_actual, elemento_id);
        }
        /* var vacios = 0;
         $(".id-pr").each(function(indice){
         var tipo_elm = $(".id-pr").eq(indice).parent().parent().children(".radio-tipo-elemento").eq(0).children("p").children("input[type=radio]:checked").eq(0).val();
         if (tipo_elm === undefined){
         vacios++;
         }
         });
         if (vacios == 0){
         agregarElementoRemision();
         }*/
        inicializarMaterialize();
    }
}

function seleccionProducto(barCodeRemision_ID, accion_actual, id) {
    //var id = $('input:radio[name=radio-producto]:checked').val();

    var id_check = $('input:radio[name=radio-producto]:checked').attr('id');
    if (typeof id == "undefined" && barCodeProducto == undefined) {
        alert("Seleccione un producto");
    } else {
        var continuar = true;
        for (i in productos) {
            if (productos[i].id == id && barCodeProducto == undefined) {
                alert("El producto seleccionado ya ha sido agregado a la remisión");
                continuar = false;
            }else if(productos[i].barcode == barCodeProducto && barCodeProducto != ""){
                alert("El producto seleccionado ya ha sido agregado a la remisión");
                $('#'+barCodeRemision_ID).val("");
                continuar = false;
            }

        }

        if (continuar) {
            var url = $("#base_url").val() + "/productos/datos-producto";
            if(id || barCodeProducto) {
                var params = {_token: $("#general-token").val(), id: id, barCodeProducto: barCodeProducto};
                $("#progress-elementos-remision-ingreso-modal").removeClass("hide");
                $("#contenedor-botones-elementos-remision-ingreso-modal").addClass("hide");
                $.post(url, params, function (data) {

                    $('#' + id_check).prop("checked", false)
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
                        establecerCamposProducto(accion_actual, barCodeRemision_ID, data);
                        input_nombre = null;
                        tipo = null;
                        $("#modal-elementos-remision-ingreso").closeModal();
                        $(".lean-overlay").remove();
                        agregarElementoRemision(true);
                    } else {
                        if (data.mensaje == "Debes crear este producto") {
                            $('#link-crear-producto').click();
                            //Materialize.toast(data.mensaje, 10000, 'red')
                        } else if (data.mensaje == "Debes relacionar este producto con el proveedor actual") {
                            id_select_relacion = data.producto.id;
                            $('#modal-relacion-º').openModal()
                            //Materialize.toast(data.mensaje, 4000, 'red')
                        }
                        Materialize.toast(data.mensaje, 4000, 'red')
                    }

                    $("#progress-elementos-remision-ingreso-modal").addClass("hide");
                    $("#contenedor-botones-elementos-remision-ingreso-modal").removeClass("hide");
                })
            }
        }
    }
}

function showCambiarPrecioProducto(idProducto,element){
    $(element).children("i").removeClass("fa-pencil");
    $(element).children("i").addClass("fa-spin fa-spinner");
    var url = $("#base_url").val()+"/productos/edit-precio";
    $.post(url,{_token:$("#general-token").val(),id_producto:idProducto},function(data){
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
    var params = $("#form-edit-precio-materia-prima").serialize();
    DialogCargando("Editando ...");
    var url = $("#base_url").val()+"/materia-prima/update-precio";
    $.post(url,params,function(data){
        if(data.success){
            //mostrarErrores("contenedor-confirmacion-editar-valor-producto", ["Los datos del producto han sido editados con éxito"]);
            for(i in materiasPrimas){
                if(materiasPrimas[i].id == data.materia_prima.id){
                    delete materiasPrimas[i];
                    var url = $("#base_url").val() + "/materia-prima/datos-materia-prima-remision";

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

            $("#modal-edit-precio").closeModal()

        }
    }).error(function(jqXHR,state,error){
        mostrarErrores("contenedor-errores-editar-valor-producto", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function cambiarPrecioProducto(){
    var params = $("#form-edit-precio").serialize();
    DialogCargando("Editando ...");
    var url = $("#base_url").val()+"/productos/update-precio-actual";
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

                    params = {_token: $("#general-token").val(), id: data.producto.id};
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

            $("#modal-edit-precio").closeModal()

        }
    }).error(function(jqXHR,state,error){
        mostrarErrores("contenedor-errores-editar-valor-producto", JSON.parse(jqXHR.responseText));
        CerrarDialogCargando();
    });
}

function seleccionMateriaPrima(barCodeRemision_ID, accion_actual, id){
    var barCodeProducto = $("#" + barCodeRemision_ID).val();
    //var id = $('input:radio[name=radio-materia]:checked').val();
    if(typeof id == "undefined" && barCodeProducto == undefined){
        alert("Seleccione una materia prima");
    }else{
        var continuar = true;
        for(i in materiasPrimas){
            if(materiasPrimas[i].id == id){
                alert("La materia prima seleccionada ya ha sido agregada a la remisión");
                continuar = false;
            }
        }

        if(continuar) {
            var url = $("#base_url").val() + "/materia-prima/datos-materia-prima-remision";
            var params = {_token: $("#general-token").val(), id: id,proveedor:proveedor, barCodeProducto:barCodeProducto};
            $("#progress-elementos-remision-ingreso-modal").removeClass("hide");
            $("#contenedor-botones-elementos-remision-ingreso-modal").addClass("hide");
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
                    establecerCamposProducto(accion_actual, barCodeRemision_ID, data);
                    input_nombre = null;
                    tipo = null;
                    $("#modal-elementos-remision-ingreso").closeModal();
                    agregarElementoRemision(true);
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

                $("#progress-elementos-remision-ingreso-modal").addClass("hide");
                $("#contenedor-botones-elementos-remision-ingreso-modal").removeClass("hide");
            })
        }
    }
}

/*Edison*/
function establecerCamposProducto(accion, input_ID, data){
    if(tipo == "producto"){
        var btn_edit_precio = "<a class='btn-edit-precio' style='cursor: pointer;' onclick='showCambiarPrecioProducto(" + data.producto.id + ",this)'><i class='fa fa-pencil'></i></a>";

        if(accion == "barCodeProductosRemisiones" && barCodeProducto != undefined){
            $('#' + input_ID).parent().parent().children("td").children(".nombre").eq(0).val(data.producto.nombre)
            $('#' + input_ID).val(data.producto.barcode);
            $('#' + input_ID).parent().parent().children("td").children(".id-pr").eq(0).val(data.producto.id);
            $('#' + input_ID).parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $('#' + input_ID).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
            var iva = 0;
            if(data.producto.iva)iva = parseFloat(data.producto.iva);
            var valor_unitario = parseFloat(data.producto.precio_costo) + ((parseFloat(data.producto.precio_costo) * iva)/100);
            $('#' + input_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).text("$ " + formato_numero(valor_unitario+" ", 2, ',', '.'));
            $('#' + input_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).children(".btn-edit-precio").eq(0).remove();
            $('#' + input_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $('#' + input_ID).parent().parent().children("td").eq(0).children().eq(0).children().eq(0).prop('disabled',true);
            $('#' + input_ID).parent().parent().children("td").eq(0).children().eq(1).children().eq(0).prop('disabled',true);
            $('#' + input_ID).parent().parent().children("td").children('.barCodeRemisionesIngreso').eq(0).prop('disabled',true);

        }else {
            $(input_nombre).parent().children(".id-pr").eq(0).val(data.producto.id);
            $(input_nombre).parent().parent().children("td").children(".barCodeRemisionesIngreso").eq(0).val(data.producto.barcode)
            $(input_nombre).val(data.producto.nombre);
            $(input_nombre).parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.producto.sigla);
            var iva = 0;
            if(data.producto.iva)iva = parseFloat(data.producto.iva);
            var valor_unitario = parseFloat(data.producto.precio_costo) + ((parseFloat(data.producto.precio_costo) * iva)/100);
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text("" + formato_numero(valor_unitario, 2, ',', '.'));
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).children(".btn-edit-precio").eq(0).remove();
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(0).children().eq(0).prop('disabled',true);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(1).children().eq(0).prop('disabled',true);
            $(input_nombre).parent().parent().children("td").children('.barCodeRemisionesIngreso').eq(0).prop('disabled',true);
        }
        //console.log(valor_unitario);
    }else if(tipo == "materia prima"){
        var btn_edit_precio = "<a class='btn-edit-precio' style='cursor: pointer;' onclick='showCambiarPrecioMateriaPrima(" + data.materia_prima.id + ",this)'><i class='fa fa-pencil'></i></a>";
        if(accion == "barCodeProductosRemisiones" && barCodeProducto != undefined){
            $('#' + input_global_ID).val(data.materia_prima.codigo);
            $('#' + input_global_ID).parent().parent().children("td").children(".nombre").eq(0).val(data.materia_prima.nombre);
            $('#' + input_global_ID).parent().parent().children("td").children(".id-pr").eq(0).val(data.materia_prima.id);
            $('#' + input_global_ID).parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $('#' + input_global_ID).parent().parent().children("td").children(".unidad").eq(0).text(data.materia_prima.sigla);
            $('#' + input_global_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(data.materia_prima.valor_proveedor,2,',','.'));
            $('#' + input_global_ID).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $('#' + input_global_ID).parent().parent().children("td").eq(0).children().eq(0).children().eq(0).prop('disabled',true);
            $('#' + input_global_ID).parent().parent().children("td").eq(0).children().eq(1).children().eq(0).prop('disabled',true);
            $('#' + input_global_ID).parent().parent().children("td").children('.barCodeRemisionesIngreso').eq(0).prop('disabled',true);
        }else{
            $(input_nombre).val(data.materia_prima.nombre);
            $(input_nombre).parent().parent().children("td").children(".barCodeRemisionesIngreso").eq(0).val(data.materia_prima.codigo);
            $(input_nombre).parent().children(".id-pr").eq(0).val(data.materia_prima.id);
            $(input_nombre).parent().parent().parent().children("td").children(".cantidad").eq(0).val("1");
            $(input_nombre).parent().parent().children("td").children(".unidad").eq(0).text(data.materia_prima.sigla);
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).text(""+formato_numero(data.materia_prima.valor_proveedor,2,',','.'));
            $(input_nombre).parent().parent().children("td").children(".vlr-unitario").eq(0).append(btn_edit_precio);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(0).children().eq(0).prop('disabled',true);
            $(input_nombre).parent().parent().children("td").eq(0).children().eq(1).children().eq(0).prop('disabled',true);
            $(input_nombre).parent().parent().children("td").children('.barCodeRemisionesIngreso').eq(0).prop('disabled',true);
        }
    }
    //$("#tabla-detalle-producto-remision tbody tr:last td:first-child").eq(0).children().eq(0).children().eq(0).attr("checked", "checked")

}

function guardarRemision() {
    var efectivo_caja = parseInt($("#efectivo_caja").val());

    if($("#estado_pago").val() == "Pendiente por pagar"){
        $("#modal-forma-abono").openModal({
            complete: function() {window.location.reload(true); }}
        );
    }else if("Pagada") {
            realizarRemision();
    }
}

function eliminarUltimaFila(){

    var cantidadFilas = $('#tabla-detalle-producto-remision tbody').find('tr').length;
    var valorUltimaFila = ($('#tabla-detalle-producto-remision tbody tr:last-child').children().val())
    var valorUltimaFila = ($('#tabla-detalle-producto-remision tbody tr:last-child').children().next().next().children().val())

    if(cantidadFilas > 1 && valorUltimaFila == ''){
        $('#tabla-detalle-producto-remision tbody tr:last-child').remove();
    }
}

function realizarRemision(){
    eliminarUltimaFila();
    var error = false;
    var mensaje = "";
    var params = {};
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

        if(!error) {
            var url = $("#base_url").val()+"/remision-ingreso/store";
            params._token = $("#general-token").val();
            DialogCargando("Realizando remisión de ingreso ...");
            $.post(url,params,function(data){
                if(data.success){
                    $("body").scrollTop(50);
                    window.location.reload(true);
                }
                $("#contenedor-boton-realizar-remision-ingreso").removeClass("hide");
                $("#progress-remision-ingreso").addClass("hide");
            }).error(function(jqXHR,error,state){
                CerrarDialogCargando();
                mostrarErrores("contenedor-errores-detalles-remision-ingreso",JSON.parse(jqXHR.responseText));
            })
        }
    }else{
        error = true;
        mensaje = "Agregue por lo menos un elemento a la remisión";
    }

    if(error){
        mostrarErrores("contenedor-errores-detalles-remision-ingreso",{"error":[mensaje]});
        $("body").scrollTop(100);
        $("#contenedor-boton-realizar-remision-ingreso").removeClass("hide");
        $("#progress-remision-ingreso").addClass("hide");
    }
}
