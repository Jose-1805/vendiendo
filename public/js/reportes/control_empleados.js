$(function(){
    $("#btn-ver").click(function(){
        $("#contenedor-control-empleados").removeClass('hide')
        cargaTablaReporteControlEmpleados();
    })
})

    function cargaTablaReporteControlEmpleados(){
        var i= 1;
        var params = "&select_nombre="+$("#select_nombre").val()+"&fecha_inicio="+$("#fecha_inicio").val()+"&_token="+$("#_token").val()+"&fecha_fin="+$("#fecha_fin").val();
        if($('#almacen').length)
            params += "&almacen="+$("#almacen").val();
        var tabla_reporte_control_empleados = $('#tabla_reporte_control_empleados').dataTable({ "destroy": true });
        tabla_reporte_control_empleados.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_reporte_control_empleados').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_reporte_control_empleados = $('#tabla_reporte_control_empleados').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/reporte/list-reporte-control-empleados?"+params, //GET
                //"data":  {_token:$("#_token").val(),fecha_inicio :$('#fecha_inicio').val(),fecha_fin:$("#fecha_fin").val()}, //POST
                "type": "GET"
            },
            "columns": [
                { "data": "creacion_registro", 'className': "text-center" },
                { "data": "nombre", 'className': "text-center" },
                { "data": "cedula", 'className': "text-center" },
                { "data": "fecha_llegada", 'className': "text-center" },
                { "data": "fecha_salida", 'className': "text-center" },
                { "data": "lugar", 'className': "text-center" }
            ],
            "fnRowCallback": function (row, data, index) {
               if(i == 1){
                    setTimeout(function(){
                        $(".dataTables_filter label input").css('width','auto');
                        inicializarMaterialize();
                    },700);
                }else{
                    i++;
                }
               //$('td', row).eq(5).html("$"+data.unidad.sigla);
            },
           "iDisplayLength": 5,
           "bLengthChange": true,
            "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,2,3,4] }],
            "lengthMenu": [[5,10, 25, 50,100], [5, 10, 25, 50,100]], "bInfo": true, //"searching": false,
           
        });
    }

function reporteExcel(){
    var params = "&select_nombre="+$("#select_nombre").val()+"&fecha_inicio="+$("#fecha_inicio").val()+"&_token="+$("#_token").val()+"&fecha_fin="+$("#fecha_fin").val();

    if($('#almacen').length)
        params += "&almacen="+$("#almacen").val();

    var url = $("#base_url").val()+"/reporte/excel-reporte-control-empleados/?"+params;
    window.location.href = url;
}