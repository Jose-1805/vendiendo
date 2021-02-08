<?php
    $listaCategorias = [""=>"Seleccione"]+\App\Models\Categoria::listaNegocios();
    $listaUnidades = [""=>"Seleccione"]+\App\Models\Unidad::listaSuperadministrador();
?>
<table class="bordered highlight centered" id="tabla-importacion-productos">
    <thead>
        @if(count($importaciones))
            <tr>
                <td colspan="5"></td>
                <td class="no-material-select">
                    {!! Form::select("categoria",$listaCategorias,null,["id"=>"select-categoria"]) !!}
                </td>
                <td class="no-material-select">
                    {!! Form::select("unidad",$listaUnidades,null,["id"=>"select-unidad"]) !!}
                </td>
            </tr>
        @endif
        <tr>
            <th>Nombre</th>
            <th>Barcode</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Medida venta</th>
            <th>Categoría</th>
            <th>Unidad</th>
            <th>Revisión</th>
        </tr>
    </thead>

    <tbody>
        @forelse($importaciones as $imp)
            <tr>
                <td>{{$imp->nombre}}</td>
                <td>{{$imp->barcode}}</td>
                <td>{{$imp->descripcion}}</td>
                <td>$ {{number_format($imp->precio_costo,2,',','.')}}</td>
                <td>{{$imp->medida_venta}}</td>
                <td class="no-material-select">
                    {!! Form::select("categoria",$listaCategorias,null,["class"=>"select-categoria","id"=>"select-categoria-".$imp->id]) !!}
                </td>
                <td class="no-material-select">
                    {!! Form::select("unidad",$listaUnidades,null,["class"=>"select-unidad","id"=>"select-unidad-".$imp->id]) !!}
                </td>
                <td>
                    <p>
                        <i class="fa fa-check-circle cyan-text tooltipped modal-trigger" href="#modal-confirmar-procesar" data-position="bottom" data-delay="50" data-tooltip="Procesar producto" onclick="id_procesar = {{$imp->id}};item_select = this;" style="cursor: pointer"></i>
                        <i class="fa fa-trash red-text tooltipped modal-trigger" href="#modal-confirmar-rechazar" data-position="bottom" data-delay="50" data-tooltip="Rechazar producto" onclick="id_rechazar = {{$imp->id}};item_select = this;" style="cursor: pointer"></i>
                    </p>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="center-align">No existen productos importados sin revisar.</td>
            </tr>
        @endforelse
    </tbody>
</table>

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