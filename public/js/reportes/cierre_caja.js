

function cargarTablaCierreCaja() {
    var fechaInicio = $("#form-filtros-cierre-caja #fecha_inicio").val();
    fechaInicio = fechaInicio.split("-");
    fechaInicio = new Date(fechaInicio);
    var fechaFin = $("#form-filtros-cierre-caja #fecha_fin").val();
    fechaFin = fechaFin.split("-");
    fechaFin = new Date(fechaFin);
    if(fechaInicio > fechaFin){
        lanzarToast("La fecha de inicio no puede ser mayor a la fecha actual","Error",8000,"red-text text-darken-2");
    }else {
        var i = 1;
        var params = $("#form-filtros-cierre-caja").serialize();
        var tabla_cierre_caja = $('#tabla_cierre_caja').dataTable({"destroy": true});
        tabla_cierre_caja.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_cierre_caja').on('error.dt', function (e, settings, techNote, message) {

            //var error=["Error por favor contactese con el administrador"],422;
            //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
            console.log('An error has been reported by DataTables: ', message);
        })
        tabla_cierre_caja = $('#tabla_cierre_caja').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val() + "/reporte/list-cierre-cajas?" + params, //GET
                "data": {
                    _token: $("#_token").val(),
                    fecha_inicio: $('#fecha_inicio').val(),
                    fecha_fin: $("#fecha_fin").val()
                }, //POST
                "type": "GET"
            },
            "columns": [
                {"data": "fecha", 'className': "text-center"},
                {"data": "saldo_efectivo", 'className': "text-center"},
                {"data": "space_1", 'className': "text-center"},
                //VENTAS
                {"data": "space_2", 'className': "text-center"},
                {"data": "ventas_efectivo", 'className': "text-center"},
                {"data": "ventas_descuento", 'className': "text-center"},
                {"data": "ventas_medios_pago", 'className': "text-center"},
                {"data": "ventas_credito", 'className': "text-center"},
                {"data": "puntos", 'className': "text-center"},
                {"data": "total_ventas", 'className': "text-center"},
                {"data": "space_3", 'className': "text-center"},
                //COMPRAS
                {"data": "space_4", 'className': "text-center"},
                {"data": "compras_efectivo", 'className': "text-center"},
                {"data": "compras_credito", 'className': "text-center"},
                {"data": "total_compras", 'className': "text-center"},
                {"data": "compras_devolucion_efectivo", 'className': "text-center"},
                {"data": "space_5", 'className': "text-center"},
                //ABONOS
                {"data": "space_6", 'className': "text-center"},
                {"data": "abonos_clientes", 'className': "text-center"},
                {"data": "abonos_proveedores", 'className': "text-center"},
                {"data": "total_abonos", 'className': "text-center"},
                {"data": "space_7", 'className': "text-center"},
                //COSTOS
                {"data": "space_8", 'className': "text-center"},
                {"data": "gastos_diarios", 'className': "text-center"},
                {"data": "costos_fijos", 'className': "text-center"},
                {"data": "total_costos", 'className': "text-center"},
                {"data": "space_9", 'className': "text-center"},
                //CONSIGNACIONES
                {"data": "space_10", 'className': "text-center"},
                {"data": "consignaciones_banco", 'className': "text-center"},
                {"data": "consignaciones_caja", 'className': "text-center"},
                {"data": "space_11", 'className': "text-center"},
                //CIERRE TOTAL CAJA GENERAL
                {"data": "space_12", 'className': "text-center"},
                {"data": "efectivo", 'className': "text-center"},
                {"data": "otros_medios_pago", 'className': "text-center"},
                {"data": "totales", 'className': "text-center"},
            ],

            "fnRowCallback": function (row, data, index) {
//            console.log(data);
                /*$('td', row).eq(2).html("$"+data.facturas_realizadas);
                 $('td', row).eq(3).html("$"+data.compras_realizadas);
                 $('td', row).eq(4).html("$"+data.efectivo_adicional);*/
                if (i === tabla_cierre_caja.data().length) {
                    setTimeout(function () {
                        $(".dataTables_filter label input").css('width', 'auto');
                        inicializarMaterialize();
                    }, 700);
                    i = 1;
                } else {
                    i++;
                }
            },
            "iDisplayLength": 5,
            "bLengthChange": true,
            "aoColumnDefs": [{'bSortable': false, 'aTargets': [2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13]}],
            "lengthMenu": [[5, 10, 25, 50, 100], [5, 10, 25, 50, 100]]


            // "iDisplayLength": 5,
            //"bLengthChange": true,
            // "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [1,2,3,4,5] }],
            // "lengthMenu": [[5,10, 25, 50,100], [5, 10, 25, 50,100]], "bInfo": true, "searching": false,
        });
    }

}

$(function(){
    $("#btn-ver").click(function(){

        cargarTablaCierreCaja();
        /*var params = $("#form-filtros-cierre-caja").serialize();
        var url = $("#base_url").val()+"/reporte/list-cierre-caja";
        $("#progress-cierre-caja").removeClass("hide");
        $("#contenedor-cierre-caja").addClass("hide");
        $.get(url,params,function (data) {
            $("#contenedor-cierre-caja").html(data);
            $("#progress-cierre-caja").addClass("hide");
            $("#contenedor-cierre-caja").removeClass("hide");
        }).error(function(jqXHR,error,state){
            $("#contenedor-cierre-caja").html("");
            $("#progress-cierre-caja").addClass("hide");
            $("#contenedor-cierre-caja").removeClass("hide");
            mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(jqXHR.responseText));
        })*/
    })

    $("#contenedor-cierre-caja").on("click",".pagination li a",function(e){
        e.preventDefault();
        var url = $(this).attr("href");
        var params = $("#form-filtros-cierre-caja").serialize();
            url += "&"+params;
        $("#progress-cierre-caja").removeClass("hide");
        $("#contenedor-cierre-caja").addClass("hide");
        $.post(url,function (data) {
            $("#contenedor-cierre-caja").html(data);
            $("#progress-cierre-caja").addClass("hide");
            $("#contenedor-cierre-caja").removeClass("hide");
        })
    })
})

function reporteExcel(){
    var params = $("#form-filtros-cierre-caja").serialize();
    var url = $("#base_url").val()+"/reporte/excel-cierre-caja/?"+params;
    window.location.href = url;
}