$(document).ready(function(){
    $("#btn-clientes-factura-lista").click(function () {
        $("#div-lista-clientes-facturas").each(function() {
            displaying = $(this).css("display");
            if(displaying == "block") {
                $(this).fadeOut('slow',function() {
                    $(this).css("display","none");
                });
            } else {
                $(this).fadeIn('slow',function() {
                    $(this).css("display","block");
                });
            }
        });
    });
    $("#btn-clientes-detalle-factura-lista").click(function () {
        $("#contenedor-lista-facturas-por-cobrar").each(function() {
            displaying = $(this).css("display");
            if(displaying == "block") {
                $(this).fadeOut('slow',function() {
                    $(this).css("display","none");
                });
            } else {
                $(this).fadeIn('slow',function() {
                    $(this).css("display","block");
                });
            }
        });
    });
    $('#almacen').change(function () {
        window.location.href = $('#base_url').val()+'/reporte/cuentas-cobrar-facturas/'+$(this).val();
    })
    // $("#btn-clientes-detalle-factura-lista").keypress(function(e){
    //     if(e.which == 13){//Enter key pressed
    //         $('#btn-clientes-detalle-factura-lista').click();//Trigger search button click event
    //     }
    // });
    cargaTablaReporteCuentasxCobrar();
    cargaTablaReporteCuentasxCobrarDetalles();
});

function reporteExcelCCG(){
    var url = $("#base_url").val()+"/reporte/excel-cuentas-cobrar-facturas-general";
    if($("#almacen").length)url += "/"+$("#almacen").val();
    window.location.href = url;
}

function reporteExcelCCC(cliente_id){
    var url = $("#base_url").val()+"/reporte/excel-cuentas-cobrar-facturas-cliente/"+cliente_id;
    if($("#almacen").length)url += "/"+$("#almacen").val();
    window.location.href = url;
}

function cargaTablaReporteCuentasxCobrar(){
    var params = "";
    if($('#almacen').length)
        params = "almacen="+$('#almacen').val();
    var i=0;
    var tabla_reporte_cuentas_x_cobrar_factura = $('#tabla_reporte_cuentas_x_cobrar_factura').dataTable({ "destroy": true });
    tabla_reporte_cuentas_x_cobrar_factura.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_cuentas_x_cobrar_factura').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_cuentas_x_cobrar_factura = $('#tabla_reporte_cuentas_x_cobrar_factura').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-cuentas-x-cobrar-factura?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center " },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
//            console.log(data)
            $('td', row).eq(0).html(data.data_1);
            $('td', row).eq(1).html(data.data_2);
            $('td', row).eq(2).html(data.data_3);
            $('td', row).eq(3).html(data.data_4);
            $('td', row).eq(4).html(data.data_5);
            $('td', row).eq(5).html('<a href="'+data.data_6+'"><i class="fa phpdebugbar-fa-chevron-right"></i></a>');
             if(i === 0){
                setTimeout(function () { 
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize(); 
                },700);
                i=1;
            }else{
                i++;
            }
        }
        ,
        "iDisplayLength": 5,
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5] }] ,  "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}


function cargaTablaReporteCuentasxCobrarDetalles(){
    var i=0;
    var params = "cliente_id="+$('#cliente_id').val();
    var tabla_reporte_cuentas_x_cobrar_factura_detalle = $('#tabla_reporte_cuentas_x_cobrar_factura_detalle').dataTable({ "destroy": true });
    tabla_reporte_cuentas_x_cobrar_factura_detalle.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_cuentas_x_cobrar_factura_detalle').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_cuentas_x_cobrar_factura_detalle = $('#tabla_reporte_cuentas_x_cobrar_factura_detalle').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-cuentas-x-cobrar-factura-detalle?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
            //console.log(data)
           $('td', row).eq(0).html(data.data_1);
           $('td', row).eq(1).html(data.data_2);
           $('td', row).eq(2).html(data.data_3);
           $('td', row).eq(3).html(data.data_4);
           $('td', row).eq(4).html(data.data_5);
           $('td', row).eq(5).html(data.data_6);
           $('td', row).eq(6).html(data.data_7);
           $('td', row).eq(7).html(data.data_8);
           $('td', row).eq(8).html(data.data_9);
           $('td', row).eq(7).css('color', 'red');
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
        
        "iDisplayLength": 5,
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6,7,8] }] ,  "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}