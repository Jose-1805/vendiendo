<?php
    $listaCategorias = [""=>"Seleccione"]+\App\Models\Categoria::lista();
    $listaUnidades = [""=>"Seleccione"]+\App\Models\Unidad::lista();
    $listaProveedores = [""=>"Seleccione"]+\App\Models\Proveedor::lista();
?>
<table class="bordered highlight centered" id="tabla-importacion-materias-primas" width="100%">
    <thead>
        <tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Valor</th>
            <th>Umbral</th>
            <th>Cantidad</th>
            <th>Unidad</th>
            <th>Proveedor</th>
            <th>Revisión</th>
        </tr>
    </thead>
    <tbody></tbody>
    <footer >
            <tr id="footer-importacion-materias-primas" class="grey lighten-3">
                <td colspan="6" class="blue-text text-accent-2">
                    <p class="font-small left-align"><strong>Nota: </strong>
                        Seleccione las siguientes opciones para procesar todas las materias primas (Toda la información de la lista se procesará unicamente con la información que seleccione a continuación).</p>
                </td>
                <td class="no-material-select">
                    {!! Form::select("unidad",$listaUnidades,null,["id"=>"select-unidad"]) !!}
                </td>
                <td class="no-material-select">
                    {!! Form::select("proveedor",$listaProveedores,null,["id"=>"select-proveedor"]) !!}
                </td>
                <td class="center-align">
                    <a class="btn waves-effect waves-light blue-grey darken-2 modal-trigger" href="#modal-confirmar-procesar-todo"><i class="fa fa-send-o"></i></a>
                </td>
            </tr></footer>
</table>



<div id="modal-confirmar-procesar" class="modal modal-fixed-footer modal-small" style="min-height: 40%;">
    <div class="modal-content">
        <p class="titulo-modal">Confirmación</p>
        <p>¿Está seguro de procesar esta materia primas?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-confirmar-procesar">
            <a href="#!" class="cyan-text btn-flat" onclick="procesarMateriaPrima()">Si</a>
            <a href="#!" class="modal-close btn-flat">No</a>
        </div>
    </div>
</div>

<div id="modal-confirmar-procesar-todo" class="modal modal-fixed-footer modal-small" style="min-height: 40%;">
    <div class="modal-content">
        <p class="titulo-modal">Confirmación</p>
        <p>¿Está seguro de procesar todas las materias primas?</p>
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
        <p>¿Está seguro de rechazar esta materia prima?</p>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-confirmar-rechazar">
            <a href="#!" class="red-text btn-flat" onclick="rechazarMateriaPrima()">Si</a>
            <a href="#!" class="modal-close btn-flat">No</a>
        </div>
    </div>
</div>
<script type="text/javascript">
     $(document).ready(function(){
        cargaTablaImportacionMateriasPrimas();
    });
    function cargaTablaImportacionMateriasPrimas(){
        var i=0;
        var params ="";
        var tabla_importacion_materias_primas = $('#tabla-importacion-materias-primas').dataTable({ "destroy": true });
        tabla_importacion_materias_primas.fnDestroy();
        $.fn.dataTable.ext.errMode = 'none';
        $('#tabla-importacion-materias-primas').on('error.dt', function(e, settings, techNote, message) {
           console.log( 'An error has been reported by DataTables: ', message);
        })
        tabla_importacion_materias_primas = $('#tabla-importacion-materias-primas').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": $("#base_url").val()+"/materia-prima/list-importacion-materias-primas?"+params,
                "type": "GET"
            },
            "columns": [
                { "data": 'codigo',"defaultContent": "", 'className': "text-center" ,"width": "20%"},
                { "data": 'nombre', "defaultContent": "",'className': "text-center" ,"width": "15%"},
                { "data": 'descripcion', "defaultContent": "",'className': "text-center" ,"width": "5%"},
                { "data": 'valor', "defaultContent": "",'className': "text-center" ,"width": "5%"},
                { "data": 'umbral', "defaultContent": "",'className': "text-center" ,"width": "5%"},
                { "data": 'cantidad', "defaultContent": "",'className': "text-center" ,"width": "5%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "15%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "15%"},
                { "data": null , "defaultContent": "", 'className': "text-center" ,"width": "15%"},
            ],
            "createdRow": function (row, data, index) {
                $('td', row).eq(6).addClass("no-material-select");
                $('td', row).eq(6).html('{!! Form::select("unidad",$listaUnidades,null,["class"=>"select-unidad"]) !!}');
                $('td', row).eq(6).children().eq(0).attr('id','select-unidad-'+data.id+'');

                $('td', row).eq(7).addClass("no-material-select");
                $('td', row).eq(7).html('{!! Form::select("proveedor",$listaProveedores,null,["class"=>"select-proveedor"]) !!}');
                $('td', row).eq(7).children().eq(0).attr('id','select-proveedor-'+data.id+'');

                $('td',row).eq(8).html("<p><i class='fa fa-check-circle cyan-text tooltipped modal-trigger' href='#modal-confirmar-procesar' data-position='bottom' data-delay='50' data-tooltip='Procesar producto' onclick=\"id_procesar = "+data.id+";item_select = this;\" style='cursor: pointer'></i><i class='fa fa-trash red-text tooltipped modal-trigger' href='#modal-confirmar-rechazar' data-position='bottom' data-delay='50' data-tooltip='Rechazar producto' onclick=\"id_rechazar = "+data.id+";item_select = this;\" style='cursor: pointer'></i></p>");
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
        tabla_importacion_materias_primas.on( 'xhr', function () {
                var json = tabla_importacion_materias_primas.ajax.json();
                if(json.recordsTotal <= 0)
                    $("#footer-importacion-materias-primas").addClass('hide')
                else
                    $("#footer-importacion-materias-primas").removeClass('hide')

        } );
    }
</script>