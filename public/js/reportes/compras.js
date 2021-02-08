var seleccion = null;
$(function () {
    $("#btn-ver").click(function () {
        cargarDatosTablaCompras();
    });
    $("#contenedor-reporte-compras").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtros-reporte-compras").serialize();
        url += "&"+params;
        console.log(url);
        $("#progress-reporte-compras").removeClass("hide");
        $("#contenedor-reporte-compras").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-reporte-compras").html(data);
            $("#progress-reporte-compras").addClass("hide");
            $("#contenedor-reporte-compras").removeClass("hide");
        })
    })
    
    $("body,html").on("click",".detalle-compra",function () {
        seleccion = $(this).data("compra");
        var params = {_token:$("#general-token").val(),compra:seleccion};
        var url = $("#base_url").val()+"/reporte/detalle-compra";
        DialogCargando("Cargando ...");
        $.post(url,params,function (data) {
            $("#contenedor-detalle-compra").html(data);
            $("#modal-detalle-compra").openModal();
            CerrarDialogCargando();
        })
    })
});
function cargarDatosTablaCompras() {
    var i =1;
    var params = $("#form-filtros-reporte-compras").serialize();
    var tabla_compras = $('#tabla_reporte_compras').dataTable({ "destroy": true });
    tabla_compras.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_compras').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    });
    tabla_compras = $("#tabla_reporte_compras").DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-compras?"+params, //GET
            "data":  {_token:$("#_token").val(),fecha_inicio :$('#fecha_inicio').val(),fecha_fin:$("#fecha_fin").val()}, //POST
            "type": "GET"
        },
        "columns": [
            { "data": "numero", 'className': "text-center" },
            { "data": "valor", 'className': "text-center" },
            { "data": "fecha", 'className': "text-center" },
            { "data": "proveedor", 'className': "text-center hidden-xs" },
            { "data": "usuario", 'className': "text-center" },
            { "data": "estado", 'className': "text-center" },
            { "data": "estado_pago", 'className': "text-center" },
            { "data": "devoluciones", 'className': "text-center" },
            { "data": "detalle", 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {

            if(i === tabla_compras.data().length){
                setTimeout(function(){
                    $(".dataTables_filter label input").css('width','auto');
                    inicializarMaterialize();
                },700);
                i=1;
            }else{
                i++;
            }
        },
        "iDisplayLength": 5,
        "bLengthChange": true,
        "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [2,3,4,7,8] }] ,
        "lengthMenu": [[5,10, 25, 50,100], [5, 10, 25, 50,100]]
    });
}
function reporteExcel(){
    var params = $("#form-filtros-reporte-compras").serialize();
    var url = $("#base_url").val()+"/reporte/excel-compras/?"+params;
    window.location.href = url;
}
function reporteDetalleExcel(){
    var url = $("#base_url").val()+"/reporte/excel-detalle-compra/?compra="+seleccion;
    window.location.href = url;
}