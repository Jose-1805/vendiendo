$(function(){
    $("#btn-ver").click(function(){
        var params = $("#form-filtos-entradas-salidas").serialize();
        var url = $("#base_url").val()+"/reporte/list-entradas-salidas";
        $("#progress-entradas-salidas").removeClass("hide");
        $("#contenedor-entradas-salidas").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-entradas-salidas").html(data);
            $("#progress-entradas-salidas").addClass("hide");
            $("#contenedor-entradas-salidas").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-entradas-salidas").html("");
            $("#progress-entradas-salidas").addClass("hide");
            $("#contenedor-entradas-salidas").removeClass("hide");
            mostrarErrores("contenedor-errores-entradas-salidas",JSON.parse(jqXHR.responseText));
        })
    })

    $("#btn-grafica").click(function(){
        var params = $("#form-filtos-entradas-salidas").serialize();
        var url = $("#base_url").val()+"/reporte/grafica-entradas-salidas";
        $("#contenedor-entradas-salidas").html("");
        $("#progress-entradas-salidas").removeClass("hide");
        //$("#contenedor-entradas-salidas").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-entradas-salidas").html(data);
            $("#progress-entradas-salidas").addClass("hide");
            //$("#contenedor-entradas-salidas").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-entradas-salidas").html("");
            $("#progress-entradas-salidas").addClass("hide");
            //$("#contenedor-entradas-salidas").removeClass("hide");
            mostrarErrores("contenedor-errores-entradas-salidas",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-entradas-salidas").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtos-entradas-salidas").serialize();
            url += "&"+params;
        $("#progress-entradas-salidas").removeClass("hide");
        $("#contenedor-entradas-salidas").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-entradas-salidas").html(data);
            $("#progress-entradas-salidas").addClass("hide");
            $("#contenedor-entradas-salidas").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtos-entradas-salidas").serialize();
    var url = $("#base_url").val()+"/reporte/excel-entradas-salidas/?"+params;
    window.location.href = url;
}


function cargaTablaReporteEntradasSalidas(){
    var i=0;
    var params = $("#form-filtos-entradas-salidas").serialize();
    var t_rep_entradas_salidas = $('#t_rep_entradas_salidas').dataTable({ "destroy": true });
    t_rep_entradas_salidas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_entradas_salidas').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_entradas_salidas = $('#t_rep_entradas_salidas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-entradas-salidas?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": 'fecha', 'className': "text-center" },
            { "data": 'tipo', 'className': "text-center" },
            { "data": 'barcode', 'className': "text-center" },
            { "data": 'descripcion', 'className': "text-center" },
            { "data": 'cantidad', 'className': "text-center" },
            { "data": 'valor', 'className': "text-center" },
            { "data": 'origen', 'className': "text-center" },
            { "data": 'destino', 'className': "text-center" }
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
        "iDisplayLength": null,
        "bLengthChange": false,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5,6,7] }], "searching": false,
        "lengthMenu": [[], []]

    });
}