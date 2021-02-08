$(function(){
    $("#btn-ver").click(function(){
        var params = $("#form-filtos-movimientos-bancarios").serialize();
        var url = $("#base_url").val()+"/reporte/list-movimientos-bancarios";
        $("#progress-movimientos-bancarios").removeClass("hide");
        $("#contenedor-movimientos-bancarios").addClass("hide");
        $.post(url,params,function (data) {
            $("#contenedor-movimientos-bancarios").html(data);
            $("#progress-movimientos-bancarios").addClass("hide");
            $("#contenedor-movimientos-bancarios").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-movimientos-bancarios").html("");
            $("#progress-movimientos-bancarios").addClass("hide");
            $("#contenedor-movimientos-bancarios").removeClass("hide");
            mostrarErrores("contenedor-errores-movimientos-bancarios",JSON.parse(jqXHR.responseText));
        })
    })

    $("#contenedor-movimientos-bancarios").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtos-movimientos-bancarios").serialize();
            url += "&"+params;
        $("#progress-movimientos-bancarios").removeClass("hide");
        $("#contenedor-movimientos-bancarios").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-movimientos-bancarios").html(data);
            $("#progress-movimientos-bancarios").addClass("hide");
            $("#contenedor-movimientos-bancarios").removeClass("hide");
        })
    })

    $("#desde").change(function () {
        if($(this).val() == "usuario" || $(this).val() == "facturas"){
            $("#tipo").prop("disabled",true);
        }else{
            $("#tipo").prop("disabled",false);
        }
        inicializarMaterialize();
    })
})

function reporteExcel(){
    var params = $("#form-filtos-movimientos-bancarios").serialize();
    var url = $("#base_url").val()+"/reporte/excel-movimientos-bancarios/?"+params;
    window.location.href = url;
}


function cargaTablaReporteMovimientosBancarios(){
    var i=0;
    var params = $("#form-filtos-movimientos-bancarios").serialize();
    var t_rep_movimientos_bancarios = $('#t_rep_movimientos_bancarios').dataTable({ "destroy": true });
    t_rep_movimientos_bancarios.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_movimientos_bancarios').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_movimientos_bancarios = $('#t_rep_movimientos_bancarios').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-movimientos-bancarios?"+params,
            "type": "GET"
        },
        "columns": [
            { "data": 'created_at', 'className': "text-center" },
            { "data": 'cuenta', 'className': "text-center" },
            { "data": 'tipo', 'className': "text-center" },
            { "data": 'desde', 'className': "text-center" },
            { "data": 'valor', 'className': "text-center" },
            { "data": 'saldo', 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
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