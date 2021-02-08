@include("templates.mensajes",["id_contenedor"=>"lista-productos"])
<?php
    $numColumns = 9;
?>
<table id="ProductosTabla" style='table-layout:fixed;'>
    <thead>
        <tr>
            <th class="hide"></th>
            <th width="45"></th>
            <th width="150">Nombre</th>
            <th width="90">Precio Costo</th>
            <th width="30">Iva</th>
            <th width="50">Stock</th>
            <th width="60">Umbral</th>
            <th width="150" class="hide">Descripción</th>
            <th width="60" class="hide">Unidad</th>
            <th width="90" class="hide">Categoria</th>
            <th width="150">Opciones </th>
        </tr>
    </thead>
</table>
{{--{!! $productos->render() !!}--}}




<div id="modal-detalle-producto" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal">Detalle producto</p>
        <div id="contenido-detalle" style="width: 100%">

        </div>

    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-detalle-producto">
            <!--<a href="#!" class="red-text btn-flat" onclick="javascript: detalleProducto(id_select)">Aceptar</a>-->
            <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
        </div>
    </div>
</div>

<div id="modal-producto-compuesto-stock" class="modal modal-fixed-footer " style="min-height: 60%;">
    <div class="modal-content">
        <p class="titulo-modal">Cambiar stock</p>
        @include("templates.mensajes",["id_contenedor"=>"producto-compuesto-stock"])
        <div class="col s6">
            <label for="tarea">Tarea</label>
            <select id="tarea" name="tarea">
                <option value="agregar">Agregar</option>
                <option value="quitar">Quitar</option>
            </select>
        </div>
        <div class="col s6">
            <label for="cantidad">Cantidad</label>
            <input type="text" class="num-entero" id="cantidad" name="cantidad">
        </div>
        <div class="col s12">
            <p class="col s12 titulo-modal">Componentes del producto</p>
            <div class="col s12" id="componentes-producto"></div>
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-producto-compuesto-stock">
            <a href="#!" class="red-text btn-flat" onclick="javascript: cambiarStock()">Aceptar</a>
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-producto-compuesto-stock">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","productos","inicio"))
    <div id="modal-eliminar-producto" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este producto?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-producto">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminar(null,false)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-producto">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif