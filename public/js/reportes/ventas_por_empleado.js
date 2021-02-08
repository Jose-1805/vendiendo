$(function(){
    $("#btn-ver").click(function(){
        var params = $("#form-filtos-ventas-por-empleado").serialize();
        var url = $("#base_url").val()+"/reporte/list-ventas-por-empleado";
        $("#progress-ventas-por-empleado").removeClass("hide");
        $("#contenedor-ventas-por-empleado").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-ventas-por-empleado").html(data);
            $("#progress-ventas-por-empleado").addClass("hide");
            $("#contenedor-ventas-por-empleado").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-ventas-por-empleado").html("");
            $("#progress-ventas-por-empleado").addClass("hide");
            $("#contenedor-ventas-por-empleado").removeClass("hide");
            mostrarErrores("contenedor-errores-ventas-por-empleado",JSON.parse(jqXHR.responseText));
        })
    })

    $("#btn-grafica").click(function(){
        var params = $("#form-filtos-ventas-por-empleado").serialize();
        var url = $("#base_url").val()+"/reporte/grafica-ventas-por-empleado";
        $("#contenedor-ventas-por-empleado").html("");
        $("#progress-ventas-por-empleado").removeClass("hide");
        //$("#contenedor-ventas-por-empleado").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-ventas-por-empleado").html(data);
            $("#progress-ventas-por-empleado").addClass("hide");
            //$("#contenedor-ventas-por-empleado").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-ventas-por-empleado").html("");
            $("#progress-ventas-por-empleado").addClass("hide");
            //$("#contenedor-ventas-por-empleado").removeClass("hide");
            mostrarErrores("contenedor-errores-ventas-por-empleado",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-ventas-por-empleado").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtos-ventas-por-empleado").serialize();
            url += "&"+params;
        $("#progress-ventas-por-empleado").removeClass("hide");
        $("#contenedor-ventas-por-empleado").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-ventas-por-empleado").html(data);
            $("#progress-ventas-por-empleado").addClass("hide");
            $("#contenedor-ventas-por-empleado").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtos-ventas-por-empleado").serialize();
    var url = $("#base_url").val()+"/reporte/excel-ventas-por-empleado/?"+params;
    window.location.href = url;
}

function cargaTablaReporteVentasPorEmpleado(){
    var i=0;
    var params = $("#form-filtos-ventas-por-empleado").serialize();
    var t_rep_ventas_por_empleado = $('#t_rep_ventas_por_empleado').dataTable({ "destroy": true });
    t_rep_ventas_por_empleado.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_ventas_por_empleado').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_ventas_por_empleado = $('#t_rep_ventas_por_empleado').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-ventas-por-empleado?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
            $('td', row).eq(0).html(data.data_1);
            $('td', row).eq(1).html(data.data_2);
            $('td', row).eq(2).html(data.data_3);
            $('td', row).eq(3).html(data.data_4);
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
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3] }],// "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}