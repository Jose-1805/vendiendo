$(function(){
    $("#btn-ver").click(function(){
        var params = $("#form-filtros-factura").serialize();
        var url = $("#base_url").val()+"/reporte/list-factura";
        $("#progress-factura").removeClass("hide");
        $("#contenedor-reporte-factura").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-reporte-factura").html(data);
            $("#progress-factura").addClass("hide");
            $("#contenedor-reporte-factura").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-reporte-factura").html("");
            $("#progress-factura").addClass("hide");
            $("#contenedor-reporte-factura").removeClass("hide");
            mostrarErrores("contenedor-errores-factura",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-reporte-factura").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtros-factura").serialize();
            url += "&"+params;
        $("#progress-factura").removeClass("hide");
        $("#contenedor-reporte-factura").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-reporte-factura").html(data);
            $("#progress-factura").addClass("hide");
            $("#contenedor-reporte-factura").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtros-factura").serialize();
    var url = $("#base_url").val()+"/reporte/excel-factura/?"+params;
    window.location.href = url;
}

function cargaTablaReporteFacturas(){
    $('#t_footer_subtotal').text("");
    $('#t_footer_iva').text("");
    $('#t_footer_facturas').text("");
    var i=0;
    var params = $("#form-filtros-factura").serialize();
    var t_rep_facturas = $('#t_rep_facturas').dataTable({ "destroy": true });
    t_rep_facturas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_facturas').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_facturas = $('#t_rep_facturas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-facturas?"+params, 
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

            $('#t_footer_subtotal').text("");
            $('#t_footer_iva').text("");
            $('#t_footer_facturas').text("");
            $('td', row).eq(0).html(data.numero);
            $('td', row).eq(1).html(data.estado);
            $('td', row).eq(2).html(data.subtotal);
            $('td', row).eq(3).html(data.iva);
            $('td', row).eq(4).html(data.total);
            $('td', row).eq(5).html(data.puntos);
            $('td', row).eq(6).html(data.valor_medios_pago);
            $('td', row).eq(7).html(data.descuento);
            $('td', row).eq(8).html(data.efectivo);
            $('#t_footer_subtotal').text(data.data_t_subtotal);
            $('#t_footer_iva').text(data.data_t_iva);
            $('#t_footer_descuento').text(data.data_t_descuento);
            $('#t_footer_valor_puntos').text(data.data_t_valor_puntos);
            $('#t_footer_facturas').text(data.data_t_facturas);
            $('#t_footer_valor_medios_pago').text(data.data_t_valor_medios_pago);
            $('#t_footer_efectivo').text(data.data_t_efectivo);
             if(i === 0){
                setTimeout(function () { 
                    inicializarMaterialize(); 
                },700);
                $(".dataTables_filter label input").css('width','auto'); 
                i=1;
            }else{
                i++;
            }
        }
        ,
        "iDisplayLength": 5,
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6,7,8] }],// "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}