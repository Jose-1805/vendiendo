$(document).ready(function(){
    $("#btn-proveedores-compras-lista").click(function () {
        $("#div-lista-proveedores-compras").each(function() {
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
    $("#btn-compras-detalle-proveedor").click(function () {
        $("#contenedor-lista-compras-pagar-proveedor").each(function() {
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
    
    cargaTablaReporteCuentasxPagarProveedores();
    cargaTablaReporteCuentasxPagarProveedoresDetalles();
});
function reporteExcelCPG(){
    var url = $("#base_url").val()+"/reporte/excel-cuentas-pagar-compras";
    window.location.href = url;
}
function reporteExcelCPP(proveedor_id){
    var url = $("#base_url").val()+"/reporte/excel-cuentas-pagar-proveedor/"+proveedor_id;
    window.location.href = url;
}


function cargaTablaReporteCuentasxPagarProveedores(){
    var i=0;
    var params = "";
    var t_rep_cuentas_x_pagar_proveedores = $('#t_rep_cuentas_x_pagar_proveedores').dataTable({ "destroy": true });
    t_rep_cuentas_x_pagar_proveedores.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_cuentas_x_pagar_proveedores').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_cuentas_x_pagar_proveedores = $('#t_rep_cuentas_x_pagar_proveedores').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-cuentas-x-pagar-proveedores?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
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
            $('td', row).eq(5).html('<a href="'+data.data_6+'"><i class="fa fa-chevron-right"></i></a>');

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
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5] }], "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}


function cargaTablaReporteCuentasxPagarProveedoresDetalles(){
    var i=0;
    var params = "proveedor_id="+$('#proveedor_id').val();
    var t_rep_cuentas_x_pagar_proveedores_detalles = $('#t_rep_cuentas_x_pagar_proveedores_detalles').dataTable({ "destroy": true });
    t_rep_cuentas_x_pagar_proveedores_detalles.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_cuentas_x_pagar_proveedores_detalles').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_cuentas_x_pagar_proveedores_detalles = $('#t_rep_cuentas_x_pagar_proveedores_detalles').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-cuentas-x-pagar-proveedores-detalles?"+params, 
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
            { "data": null, 'className': "text-center" },
        ],
        "fnRowCallback": function (row, data, index) {
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
        }
        ,
        "iDisplayLength": 5,
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6,7,8] }], "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}