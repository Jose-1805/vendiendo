$(function(){
    cargarTablaDetalleUtilidad();
    cargarTablaDetalleUtilidadCostosFijos();
    cargarTablaDetalleUtilidadGastosDiarios();
    $("#btn-ver").click(function(){
        var params = $("#form-filtos-utilidades").serialize();
        var url = $("#base_url").val()+"/reporte/list-perdidas-ganancias";
        $("#progress-utilidades").removeClass("hide");
        $("#contenedor-utilidades").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-utilidades").html(data);
            $("#progress-utilidades").addClass("hide");
            $("#contenedor-utilidades").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-utilidades").html("");
            $("#progress-utilidades").addClass("hide");
            $("#contenedor-utilidades").removeClass("hide");
            mostrarErrores("contenedor-errores-utilidades",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-utilidades").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtos-utilidades").serialize();
            url += "&"+params;
        $("#progress-utilidades").removeClass("hide");
        $("#contenedor-utilidades").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-utilidades").html(data);
            $("#progress-utilidades").addClass("hide");
            $("#contenedor-utilidades").removeClass("hide");
        })
    })
})

function cargarTablaDetalleUtilidad(){
    var i= 1;
    var params = "&fecha_inicio="+$("#fecha_inicio").val()+"&_token="+$("#_token").val()+"&fecha_fin="+$("#fecha_fin").val();
    var tabla_reporte_detalle_utilidad = $('#tabla_reporte_detalle_utilidad').dataTable({ "destroy": true });
    tabla_reporte_detalle_utilidad.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_detalle_utilidad').on('error.dt', function(e, settings, techNote, message) {
       console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_detalle_utilidad = $('#tabla_reporte_detalle_utilidad').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-detalle-utilidad?"+params, //GET
            //"data":  {_token:$("#_token").val(),fecha_inicio :$('#fecha_inicio').val(),fecha_fin:$("#fecha_fin").val()}, //POST
            "type": "GET"
        },
        "columns": [
            { "data": "created_at", 'className': "text-center" },
            { "data": "producto", 'className': "text-center" },
            { "data": "venta", 'className': "text-center" },
            { "data": "costo_compra", 'className': "text-center" },
            { "data": "utilidad", 'className': "text-center" },
            { "data": "utilidad_porciento", 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
           //console.log(data);
           if(i === tabla_reporte_detalle_utilidad.data().length){
            //console.log("algo")
                setTimeout(function(){
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700);
                $('#f_total_facturas').text('$'+data.total_factura);
                $('#f_total_compras').text('$'+data.total_compras);
                $('#total_factura_compras').text('$'+data.total_factura_compras);
                $('#total_porciento').text(data.total_porciento+'%');
                $('.f_total_descuentos').text('$'+data.total_descuentos);
                $('.f_total_valor_puntos').text('$'+data.total_valor_puntos);
                i=1;
            }else{
                i++;
            }
           //$('td', row).eq(5).html("$"+data.unidad.sigla);
        },
       //"iDisplayLength": 9,
       "bLengthChange": false,
        "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,2,3,4,5] }],
        //"lengthMenu": [[5,10, 25, 50,100], [5, 10, 25, 50,100]], "bInfo": true, "searching": false,

    });
}

function cargarTablaDetalleUtilidadCostosFijos(){
        var i= 1;
        var params = "&fecha_inicio="+$("#fecha_inicio").val()+"&_token="+$("#_token").val()+"&fecha_fin="+$("#fecha_fin").val();
        var tabla_reporte_detalle_utilidad_cf = $('#tabla_reporte_detalle_utilidad_cf').dataTable({ "destroy": true });
        tabla_reporte_detalle_utilidad_cf.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_reporte_detalle_utilidad_cf').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_reporte_detalle_utilidad_cf = $('#tabla_reporte_detalle_utilidad_cf').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/reporte/list-reporte-detalle-utilidad-c-f?"+params, //GET
                //"data":  {_token:$("#_token").val(),fecha_inicio :$('#fecha_inicio').val(),fecha_fin:$("#fecha_fin").val()}, //POST
                "type": "GET"
            },
            "columns": [
                { "data": "fecha", 'className': "text-center" },
                { "data": "item", 'className': "text-center" },
                { "data": "valor", 'className': "text-center" }
            ],
            "fnRowCallback": function (row, data, index) {
               //console.log(data);
               $("#f_total_gastos").text('$ '+data.total_gastos)
            },
           //"iDisplayLength": 5,
           "bLengthChange": false, "bInfo": false, "searching": false,

        });
    }

function cargarTablaDetalleUtilidadGastosDiarios(){
        var i= 1;
        var params = "&fecha_inicio="+$("#fecha_inicio").val()+"&_token="+$("#_token").val()+"&fecha_fin="+$("#fecha_fin").val();
        var tabla_reporte_detalle_utilidad_gd = $('#tabla_reporte_detalle_utilidad_gd').dataTable({ "destroy": true });
        tabla_reporte_detalle_utilidad_gd.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_reporte_detalle_utilidad_gd').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_reporte_detalle_utilidad_gd = $('#tabla_reporte_detalle_utilidad_gd').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/reporte/list-reporte-detalle-utilidad-g-d?"+params, //GET
                //"data":  {_token:$("#_token").val(),fecha_inicio :$('#fecha_inicio').val(),fecha_fin:$("#fecha_fin").val()}, //POST
                "type": "GET"
            },
            "columns": [
                { "data": "created_at", 'className': "text-center" },
                { "data": "descripcion", 'className': "text-center" },
                { "data": "usuario", 'className': "text-center" },
                { "data": "valor", 'className': "text-center" }
            ],
            "fnRowCallback": function (row, data, index) {
               //console.log(data);
               $("#f_total_gastos_diarios").text('$ '+data.total_gastos_diarios)
            },
           //"iDisplayLength": 5,
           "bLengthChange": false, "bInfo": false, "searching": false,

        });
    }


function reporteExcel(){
    var params = 'fecha_inicio='+$("#fecha_inicio").val()+'&fecha_fin='+$("#fecha_fin").val()+'&almacen='+$("#almacen").val();
    var url = $("#base_url").val()+"/reporte/excel-perdidas-ganancias/?"+params;
    window.location.href = url;
}