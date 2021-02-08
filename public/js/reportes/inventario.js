

$(document).ready(function() {
    cargarTablaReporteInventario(1);
});

function cambiarFiltro(seleccion = false){
    cargarTablaReporteInventario(seleccion)
}

function reporteExcel(){
    var filtro = $("input:radio[name=filtro]:checked").val();
    var url = $("#base_url").val()+"/reporte/excel-inventario/?filtro="+filtro;
    if($('#almacen').length)
        url += '&almacen='+$('#almacen').val();
    window.location.href = url;
}

function cargarTablaReporteInventario(seleccion){
    var i = 0;
    if(!seleccion){
        if($('#todos').prop('checked'))seleccion = $('#todos').val();
        if($('#bajo-umbral').prop('checked'))seleccion = $('#bajo-umbral').val();
        if($('#sobre-umbral').prop('checked'))seleccion = $('#sobre-umbral').val();
    }
    var params = "filtro="+seleccion;
    if($('#almacen').length)
        params += '&almacen='+$('#almacen').val();

    var tabla_reporte_inventario = $('#tabla_reporte_inventario').dataTable({ "destroy": true });
    tabla_reporte_inventario.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_inventario').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_inventario = $('#tabla_reporte_inventario').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-inventario?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": "barcode", 'className': "text-center" },
            { "data": "nombre", 'className': "text-center" },
            { "data": "umbral", 'className': "text-center" },
            { "data": "stock", 'className': "text-center hidden-xs" },
            { "data": "precio_costo", 'className': "text-center" },
            { "data": "costo_total", 'className': "text-center" },
            { "data": "unidad", 'className': "text-center" },
            { "data": "categoria", 'className': "text-center" }
        ], "fnRowCallback": function (row, data, index) {
            if(data.show_costos == 0){
                $('td', row).eq(4).addClass('hide');
                $('td', row).eq(5).addClass('hide');
            }
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
        "iDisplayLength": 5,"bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [4,5] }], "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}