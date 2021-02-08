$(document).ready(function(){
    cargaTablaReporteCuentasxcobrrarProveedores();
});


function reporteExcelCCP(){
    var url = $("#base_url").val()+"/reporte/excel-cuenta-cobrar-proveedores";
    window.location.href = url;
}



function cargaTablaReporteCuentasxcobrrarProveedores(){
    var i=0;
    var params = "";
    var t_rep_cuentas_x_cobrar_proveedores = $('#t_rep_cuentas_x_cobrar_proveedores').dataTable({ "destroy": true });
    t_rep_cuentas_x_cobrar_proveedores.fnDestroy();
    $.fn.dataTable.ext.errMode = 'none';
    $('#t_rep_cuentas_x_cobrar_proveedores').on('error.dt', function(e, settings, techNote, message) {

        //var error=["Error por favor contactese con el administrador"],422;
        //mostrarErrores("contenedor-errores-cierre-caja",JSON.parse(error));
       console.log( 'An error has been reported by DataTables: ', message);
    })
    t_rep_cuentas_x_cobrar_proveedores = $('#t_rep_cuentas_x_cobrar_proveedores').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": $("#base_url").val()+"/reporte/list-reporte-cuentas-x-cobrar-proveedores?"+params, 
            "type": "GET"
        },
        "columns": [
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" },
            { "data": null, 'className': "text-center" }
        ],
        "fnRowCallback": function (row, data, index) {
        	var checked = "";
        	var disabled = "";
            $('td', row).eq(0).html(data.data_1);
            $('td', row).eq(1).html(data.data_2);
            $('td', row).eq(2).html(data.data_3);
            $('td', row).eq(3).html(data.data_4);
            $('td', row).eq(4).html(data.data_5);
            $('td', row).eq(5).html(data.data_6);
            $('td', row).eq(6).html(data.data_7);

            if(data.data_8 == "PAGADA"){
                checked = "checked";
                disabled ='disabled';
            }

            $('td', row).eq(7).html(              
                    "<div class='switch'>"+
                        "<label>"+
                            "<input type='checkbox' "+disabled+" id='"+data.data_id+"' "+checked+" onclick=\"estadoCuentaXCobrar(this.id,'"+data.data_8+"')\">"+
                            "<span class='lever'></span>"+
                        "</label>"+
                    "</div>"
					);
             if(i === 0){
                setTimeout(function () { 
                    $(".dataTables_filter label input").css('width','auto'); 
                    inicializarMaterialize(); },700);
                i=1;
            }else{
                i++;
            }
        }
        ,
        "iDisplayLength": 5,
        "bLengthChange": true,"aoColumnDefs": [{ 'bSortable': false, 'aTargets': [0,1,2,3,4,5] }], //"searching": false,
        "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]

    });
}