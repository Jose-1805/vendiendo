<?php
    $listaCategorias = [""=>"Seleccione"]+\App\Models\Categoria::lista();
    $listaUnidades = [""=>"Seleccione"]+\App\Models\Unidad::lista();
    $listaProveedores = [""=>"Seleccione"]+\App\Models\Proveedor::lista();
?>
<table class="bordered highlight centered" id="tabla-importacion-productos" width="100%">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Barcode</th>
            <th>Stock</th>
            <th>Umbral</th>
            <th>Categoría</th>
            <th>Unidad</th>
            <th>Proveedor</th>
            <th>Revisión</th>
            <th>Ver más</th>
        </tr>
    </thead>
    <tbody></tbody>
    <footer >
            <tr id="footer-importacion-productos" class="grey lighten-3">
                <td colspan="4" class="blue-text text-accent-2">
                    <p class="font-small left-align"><strong>Nota: </strong>
                        Seleccione las siguientes opciones para procesar todos los productos (Toda la información de la lista se procesará unicamente con la información que seleccione a continuación).</p>
                </td>
                <td class="no-material-select">
                    {!! Form::select("categoria",$listaCategorias,null,["id"=>"select-categoria"]) !!}
                </td>
                <td class="no-material-select">
                    {!! Form::select("unidad",$listaUnidades,null,["id"=>"select-unidad"]) !!}
                </td>
                <td class="no-material-select">
                    {!! Form::select("proveedor",$listaProveedores,null,["id"=>"select-proveedor"]) !!}
                </td>
                <td colspan="2" class="center-align">
                    <a class="btn waves-effect waves-light blue-grey darken-2 modal-trigger" href="#modal-confirmar-procesar-todo">Procesar Todo</a>
                </td>
            </tr></footer>
</table>


<div id="modal-ver-mas" class="modal modal-fixed-footer" style="min-height: 80%;">
    <div class="modal-content">
        <p class="titulo-modal">Información del producto</p>
        @include('templates.mensajes',["id_contenedor"=>"modal-ver-mas"])
        <div id="load-ver-mas">
            <p class="center-align">Cargando <i class="fa fa-spin fa-spinner"></i></p>
        </div>
        <div class="hide" id="contenido-ver-mas">

        </div>
    </div>

    <div class="modal-footer">
            <a href="#!" class="modal-close btn-flat">Cerrar</a>
    </div>
</div>

<div id="modal-confirmar-procesar" class="modal modal-fixed-footer modal-small" style="min-height: 40%;">
    <div class="modal-content">
        <p class="titulo-modal">Confirmación</p>
        <p>¿Está seguro de procesar este producto?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-confirmar-procesar">
            <a href="#!" class="cyan-text btn-flat" onclick="procesarProducto()">Si</a>
            <a href="#!" class="modal-close btn-flat">No</a>
        </div>
    </div>
</div>

<div id="modal-confirmar-procesar-todo" class="modal modal-fixed-footer modal-small" style="min-height: 40%;">
    <div class="modal-content">
        <p class="titulo-modal">Confirmación</p>
        <p>¿Está seguro de procesar todos los productos?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-confirmar-procesar-todo">
            <a href="#!" class="cyan-text btn-flat" onclick="procesarTodo()">Si</a>
            <a href="#!" class="modal-close btn-flat">No</a>
        </div>
    </div>
</div>

<div id="modal-confirmar-rechazar" class="modal modal-fixed-footer modal-small" style="min-height: 40%;">
    <div class="modal-content">
        <p class="titulo-modal">Confirmación</p>
        <p>¿Está seguro de rechazar este producto?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-confirmar-rechazar">
            <a href="#!" class="red-text btn-flat" onclick="rechazarProducto()">Si</a>
            <a href="#!" class="modal-close btn-flat">No</a>
        </div>
    </div>
</div>
<script type="text/javascript">
     $(document).ready(function(){
         var opciones_importacion = true;
         @if(\Illuminate\Support\Facades\Auth::user()->bodegas == 'si' && count(\App\Models\Bodega::permitidos()->get()) <= 0)
             opciones_importacion = false;
         @endif
        cargaTablaImportacionProductos(opciones_importacion);
    });
    function cargaTablaImportacionProductos(opciones_importacion){
        var i=0;
        var params ="";
        var tabla_importacion_productos = $('#tabla-importacion-productos').dataTable({ "destroy": true });
        tabla_importacion_productos.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla-importacion-productos').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_importacion_productos = $('#tabla-importacion-productos').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/productos/list-importacion-productos?"+params, 
                "type": "GET"
            },
            "columns": [
                { "data": 'nombre',"defaultContent": "", 'className': "text-center" ,"width": "20%"},
                { "data": 'barcode', "defaultContent": "",'className': "text-center" ,"width": "15%"},
                { "data": 'stock', "defaultContent": "",'className': "text-center" ,"width": "5%"},
                { "data": 'umbral', "defaultContent": "",'className': "text-center" ,"width": "5%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "15%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "15%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "15%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "5%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "5%"}
            ],
            "createdRow": function (row, data, index) {

                $('td', row).eq(4).addClass("no-material-select");
                $('td', row).eq(4).html('{!! Form::select("categoria",$listaCategorias,null,["class"=>"select-categoria"]) !!}');
                $('td', row).eq(4).children().eq(0).attr('id', 'select-categoria-' + data.id + '');

                $('td', row).eq(5).addClass("no-material-select");
                $('td', row).eq(5).html('{!! Form::select("unidad",$listaUnidades,null,["class"=>"select-unidad"]) !!}');
                $('td', row).eq(5).children().eq(0).attr('id', 'select-unidad-' + data.id + '');

                $('td', row).eq(6).addClass("no-material-select");
                $('td', row).eq(6).html('{!! Form::select("proveedor",$listaProveedores,null,["class"=>"select-proveedor"]) !!}');
                $('td', row).eq(6).children().eq(0).attr('id', 'select-proveedor-' + data.id + '');
                if(opciones_importacion) {
                    $('td', row).eq(7).html("<p><i class='fa fa-check-circle cyan-text tooltipped modal-trigger' href='#modal-confirmar-procesar' data-position='bottom' data-delay='50' data-tooltip='Procesar producto' onclick=\"id_procesar = " + data.id + ";item_select = this;\" style='cursor: pointer'></i><i class='fa fa-trash red-text tooltipped modal-trigger' href='#modal-confirmar-rechazar' data-position='bottom' data-delay='50' data-tooltip='Rechazar producto' onclick=\"id_rechazar = " + data.id + ";item_select = this;\" style='cursor: pointer'></i></p>");
                }
                $('td', row).eq(8).html("<a onclick=\"verMas(" + data.id + ")\" href='#''><i class='fa fa-angle-right fa-2x'></i></a>");
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
            "aoColumnDefs": [{ 'bSortable': false, 'aTargets': [4,5,6,7,8] }] ,
            "lengthMenu": [[5,10, 25, 50], [5, 10, 25, 50]]
        });
        tabla_importacion_productos.on( 'xhr', function () {
                var json = tabla_importacion_productos.ajax.json();
                if(json.recordsTotal <= 0)
                    $("#footer-importacion-productos").addClass('hide')
                else
                    $("#footer-importacion-productos").removeClass('hide')

        } );
    }
</script>