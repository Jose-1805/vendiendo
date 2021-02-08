$(function(){
	cargaTablaMisPedidos();
})

function showDetallePedido(id){
    var url = $("#base_url").val()+"/productos/detalle-pedido";
    var params = {_token:$("#general-token").val(),"id":id};

    DialogCargando("Cargando ...");
    $.post(url,params,function (data) {
        $("#contenido-detalle-pedido").html(data);
        $("#modal-detalle-pedido").openModal();
        CerrarDialogCargando();
    });
}

function cargaTablaMisPedidos(){
        var i=0;
        var params ="";
        var tabla_mis_pedidos = $('#tabla_mis_pedidos').dataTable({ "destroy": true });
        tabla_mis_pedidos.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla_mis_pedidos').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_mis_pedidos = $('#tabla_mis_pedidos').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/productos/list-mis-pedidos?"+params, 
                "type": "GET"
            },
            "columns": [
                { "data": 'consecutivo' , "defaultContent": "", 'className': "lef"},
                { "data": 'valor_total' , "defaultContent": "", 'className': "text-center"},
                { "data": 'proveedor' , "defaultContent": "", 'className': "text-center"},
                { "data": 'fecha' , "defaultContent": "", 'className': "text-center"},
                { "data": 'estado' , "defaultContent": "", 'className': "text-center"},
                { "data": null , "defaultContent": "", 'className': "text-center"}
            ],
            "createdRow": function (row, data, index) {
            	//console.log(data)
            	$(row).attr('data-id',data.id);
                $('td', row).eq(0).addClass("lef");
             	$('td', row).eq(5).html("<a href='#' onclick=\"showDetallePedido("+data.id+")\"><i class='fa fa-list'></i></a>");
            },
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
            "columnDefs": columnDefs,        
            "iDisplayLength": 5,
            "bLengthChange": true,
            "aoColumnDefs": [{ 'bSortable': false, 'aTargets':  [0,1,2,3,4,5] }] ,
            "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
        });
    }