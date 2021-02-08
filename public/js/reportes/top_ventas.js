$(function(){
    $("#btn-ver").click(function(){
        var params = $("#form-filtos-top-ventas").serialize();
        var url = $("#base_url").val()+"/reporte/list-top-ventas";
        $("#progress-top-ventas").removeClass("hide");
        $("#contenedor-top-ventas").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-top-ventas").html(data);
            $("#progress-top-ventas").addClass("hide");
            $("#contenedor-top-ventas").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-top-ventas").html("");
            $("#progress-top-ventas").addClass("hide");
            $("#contenedor-top-ventas").removeClass("hide");
            mostrarErrores("contenedor-errores-top-ventas",JSON.parse(jqXHR.responseText));
        })
    })

    $("#btn-grafica").click(function(){
        var params = $("#form-filtos-top-ventas").serialize();
        var url = $("#base_url").val()+"/reporte/grafica-top-ventas";
        $("#contenedor-top-ventas").html("");
        $("#progress-top-ventas").removeClass("hide");
        //$("#contenedor-top-ventas").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-top-ventas").html(data);
            $("#progress-top-ventas").addClass("hide");
            //$("#contenedor-top-ventas").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-top-ventas").html("");
            $("#progress-top-ventas").addClass("hide");
            //$("#contenedor-top-ventas").removeClass("hide");
            mostrarErrores("contenedor-errores-top-ventas",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-top-ventas").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtos-top-ventas").serialize();
            url += "&"+params;
        $("#progress-top-ventas").removeClass("hide");
        $("#contenedor-top-ventas").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-top-ventas").html(data);
            $("#progress-top-ventas").addClass("hide");
            $("#contenedor-top-ventas").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtos-top-ventas").serialize();
    var url = $("#base_url").val()+"/reporte/excel-top-ventas/?"+params;
    window.location.href = url;
}


function cargaTablaReporteTopVentas(){
    var i=0;
    var params = $("#form-filtos-top-ventas").serialize();
    var t_rep_top_ventas = $('#t_rep_top_ventas').dataTable({ "destroy": true });
    t_rep_top_ventas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_top_ventas').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_top_ventas = $('#t_rep_top_ventas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-top-ventas?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": 'nombre', 'className': "text-center" },
            { "data": 'descripcion', 'className': "text-center" },
            { "data": 'categoria', 'className': "text-center" },
            { "data": 'cantidad_vendida', 'className': "text-center" },
            { "data": 'stock', 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
            var checked = "";
            var disabled = "";
            
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
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4] }], "searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}