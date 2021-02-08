$(function(){
    $("#btn-ver").click(function(){
        cargarTablaReporteVentas();
    });

})

function reporteExcel(){
    var params = $("#form-filtos-ventas").serialize();
    var url = $("#base_url").val()+"/reporte/excel-ventas/?"+params;
    window.location.href = url;
}

function cargarTablaReporteVentas(){
    var i = 0;
    var params = $("#form-filtos-ventas").serialize();
    var tabla_reporte_ventas = $('#tabla_reporte_ventas').dataTable({ "destroy": true });
    tabla_reporte_ventas.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_ventas').on('error.dt', function(e, settings, techNote, message) {
        $("#progress-ventas").addClass("hide");
        $("#contenedor-ventas").removeClass("hide");    
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_ventas = $('#tabla_reporte_ventas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-ventas?"+params, //GET
            //"data":  {_token:$("#_token").val(),fecha_inicio :$('#fecha_inicio').val(),fecha_fin:$("#fecha_fin").val()}, //POST
            "type": "GET"
        },
        "columns": [
            { "data": "almacen", 'className': "text-center" },
            { "data": "numero", 'className': "text-center" },
            { "data": "created_at", 'className': "text-center" },
            { "data": "nombre", 'className': "text-center" },
            { "data": "subtotal", 'className': "text-center hidden-xs" },
            { "data": "iva", 'className': "text-center" },
            { "data": "descuento", 'className': "text-center" },
            { "data": "total", 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
            if(!data.almacen)
                $('td',row).eq(0).addClass('hide');

            $("#progress-ventas").addClass("hide");
            $("#contenedor-ventas").removeClass("hide");
            var fecha = new Date(data.created_at.date);   
            //console.log(data);
            $("#info_t_rep_ventas").html('<div class="col s12 right-align" style="border-top: 1px solid #00c0e4;">'
                    +'<strong>Total sin iva:</strong>'+data.totalSubtotal
                    +'</div>' +
                '<div class="col s12 right-align">' +
                '   <strong>Total iva:</strong>'+data.totalIva
                +'</div>' +
                '<div class="col s12 right-align">' +
                '   <strong>Total descuentos:</strong>'+data.totalDescuento
                +'</div>' +
                '<div class="col s12 right-align">' +
                '<strong>Total:</strong>'+data.totalFinal+
                '</div>');
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [4,5,6] }]
         , "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}