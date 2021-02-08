$(function(){

    $("#tipo").change(function(){
        if($(this).val() == "factura"){
            $("#nombre_cliente").parent().removeClass("hide");
            $("#nombre_proveedor").parent().addClass("hide");
            $("#identificacion_cliente").parent().removeClass("hide");
            $("#identificacion_proveedor").parent().addClass("hide");
            $("#almacen").prop("disabled",false);
        }else if($(this).val() == "compra"){
            $("#nombre_cliente").parent().addClass("hide");
            $("#nombre_proveedor").parent().removeClass("hide");
            $("#identificacion_cliente").parent().addClass("hide");
            $("#identificacion_proveedor").parent().removeClass("hide");
            $("#almacen").prop("disabled","disabled");
        }
        inicializarMaterialize();

    })
    $("#btn-ver").click(function(){
        $("#t_footer_tv").text("");
        $("#t_footer_ts").text("");
        cargaTablaReporteAbonos();
        //var params = $("#form-filtros-abonos").serialize();
        //var url = $("#base_url").val()+"/reporte/list-abonos";
        //$("#progress-abonos").removeClass("hide");
        //$("#contenedor-reporte-abonos").addClass("hide");
        //$.post(url,params,function (data) {
        //    $("#contenedor-reporte-abonos").html(data);
        //    $("#progress-abonos").addClass("hide");
        //    $("#contenedor-reporte-abonos").removeClass("hide");
        //}).error(function(jqXHR,error,state){
        //    $("#contenedor-reporte-abonos").html("");
        //    $("#progress-abonos").addClass("hide");
        //    $("#contenedor-reporte-abonos").removeClass("hide");
        //    mostrarErrores("contenedor-errores-abonos",JSON.parse(jqXHR.responseText));
        //})
    })

    $("#contenedor-reporte-abonos").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtros-abonos").serialize();
            url += "&"+params;
        $("#progress-abonos").removeClass("hide");
        $("#contenedor-reporte-abonos").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-reporte-abonos").html(data);
            $("#progress-abonos").addClass("hide");
            $("#contenedor-reporte-abonos").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtros-abonos").serialize();
    var url = $("#base_url").val()+"/reporte/excel-abonos/?"+params;
    window.location.href = url;
}

function cargaTablaReporteAbonos(){
    var i = 0;
    $("#contenedor-reporte-abonos").removeClass('hide');
    $("#t_footer_tv").text("$0,00");
    $("#t_footer_ts").text("$0,00");
    //alert()
    var params = $("#form-filtros-abonos").serialize();
    var tabla_reporte_abonos = $('#tabla_reporte_abonos').dataTable({ "destroy": true });
    tabla_reporte_abonos.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_abonos').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_abonos = $('#tabla_reporte_abonos').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-abono?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center " },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
            $("#t_footer_tv").text("$0,00");
            $("#t_footer_ts").text("$0,00");
            if(data.info_tabla == "factura")
                $("#th_abono_2").html('Cliente/Identificación');
            else
                $("#th_abono_2").html('Proveedor/NIT');

            //console.log(data);
            $('td', row).eq(0).html(data.data_1);
            $('td', row).eq(1).html(data.data_2);
            $('td', row).eq(2).html(data.data_3);
            $('td', row).eq(3).html(data.data_4);
            $('td', row).eq(4).html(data.data_5);
            $('td', row).eq(5).html(data.data_6);
            $('td', row).eq(6).html(data.data_7);
            $('td', row).eq(7).html(data.data_8);
            $("#t_footer_tv").text(data.total_valores);
            $("#t_footer_ts").text(data.total_saldos);
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6,7] }] 
        , "bInfo": false, "searching": false, "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}