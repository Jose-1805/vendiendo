$(function(){
    $("#btn-ver").click(function(){
        var params = $("#form-filtos-objetivos-ventas").serialize();
        var url = $("#base_url").val()+"/reporte/list-objetivos-ventas";
        $("#progress-objetivos-ventas").removeClass("hide");
        $("#contenedor-objetivos-ventas").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-objetivos-ventas").html(data);
            $("#progress-objetivos-ventas").addClass("hide");
            $("#contenedor-objetivos-ventas").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-objetivos-ventas").html("");
            $("#progress-objetivos-ventas").addClass("hide");
            $("#contenedor-objetivos-ventas").removeClass("hide");
            mostrarErrores("contenedor-errores-objetivos-ventas",JSON.parse(jqXHR.responseText));
        })
    })

    $("#btn-grafica").click(function(){
        var params = $("#form-filtos-objetivos-ventas").serialize();
        var url = $("#base_url").val()+"/reporte/grafica-objetivos-ventas";
        $("#contenedor-objetivos-ventas").html("");
        $("#progress-objetivos-ventas").removeClass("hide");
        //$("#contenedor-objetivos-ventas").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-objetivos-ventas").html(data);
            $("#progress-objetivos-ventas").addClass("hide");
            //$("#contenedor-objetivos-ventas").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-objetivos-ventas").html("");
            $("#progress-objetivos-ventas").addClass("hide");
            //$("#contenedor-objetivos-ventas").removeClass("hide");
            mostrarErrores("contenedor-errores-objetivos-ventas",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-objetivos-ventas").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtos-objetivos-ventas").serialize();
            url += "&"+params;
        $("#progress-objetivos-ventas").removeClass("hide");
        $("#contenedor-objetivos-ventas").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-objetivos-ventas").html(data);
            $("#progress-objetivos-ventas").addClass("hide");
            $("#contenedor-objetivos-ventas").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtos-objetivos-ventas").serialize();
    var url = $("#base_url").val()+"/reporte/excel-objetivos-ventas/?"+params;
    window.location.href = url;
}

function cargaTablaReporteObjetivosVentas(){
    var i=0;
    var params = $("#form-filtos-objetivos-ventas").serialize();
    var t_rep_objetivo_ventas = $('#t_rep_objetivo_ventas').dataTable({ "destroy": true });
    t_rep_objetivo_ventas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_objetivo_ventas').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_objetivo_ventas = $('#t_rep_objetivo_ventas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-objetivos-ventas?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
            var aux = 0;
            if(data.data_5){
                $('td', row).eq(aux).html(data.data_5);
                aux++;
            }else{
                $('td', row).eq(4).addClass('hide');
            }
            $('td', row).eq(aux).html(data.data_1);
            aux++;
            $('td', row).eq(aux).html(data.data_2);
            aux++;
            $('td', row).eq(aux).html(data.data_3);
            aux++;
            $('td', row).eq(aux).html(data.data_4);
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