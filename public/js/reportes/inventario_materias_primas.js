

$(document).ready(function() {
    cargarTablaReporteInventarioMP(1);
});

$(function(){
    $("#contenedor-inventario-materias-primas").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var filtro = $("input:radio[name=filtro]:checked").val();
        if(filtro){
            url += "&filtro="+filtro;
        }
        window.location.href = url;
    })
})

function cambiarFiltro(seleccion){
    cargarTablaReporteInventarioMP(seleccion)
    //var url = $("#base_url").val()+"/reporte/inventario-materias-primas/?filtro="+seleccion;
    //window.location.href = url;
}

function reporteExcel(){
    var filtro = $("input:radio[name=filtro]:checked").val();
    var url = $("#base_url").val()+"/reporte/excel-inventario-materias-primas/?filtro="+filtro;
    window.location.href = url;
}

function cargarTablaReporteInventarioMP(seleccion){
    var i=0;
    var params = "filtro="+seleccion;
    var tabla_reporte_inventario_mp = $('#tabla_reporte_inventario_mp').dataTable({ "destroy": true });
    tabla_reporte_inventario_mp.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#tabla_reporte_inventario_mp').on('error.dt', function(e, settings, techNote, message) {
        console.log( 'An error has been reported by DataTables: ', message);
    })
    tabla_reporte_inventario_mp = $('#tabla_reporte_inventario_mp').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-inventario-materias-primas?"+params,            
            "type": "GET"
        },
        "columns": [
            { "data": "codigo", 'className': "text-center" },
            { "data": "nombre", 'className': "text-center" },
            { "data": "descripcion", 'className': "text-center" },
            { "data": "umbral", 'className': "text-center" },
            { "data": "stock", 'className': "text-center" },
            { "data": "precio", 'className': "text-center" },
            { "data": "unidad", 'className': "text-center" },
            { "data": "proveedor", 'className': "text-center" }
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
        },
        "iDisplayLength": 5,"bLengthChange": true,"lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]//,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [2,3,4] }] 

    });
}