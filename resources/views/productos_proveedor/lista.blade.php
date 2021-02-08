@include("templates.mensajes",["id_contenedor"=>"lista-productos"])
<?php
?>
<table class="bordered highlight centered">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Barcode</th>
        <th>Precio</th>
        <th>Unidad</th>
        <th>Categoria</th>
        <th>Detalle </th>
        <th >Editar</th>
        <th colspan="2">Estado<br><label style="font-size: 10px">Inactivo/Activo</label></th>
    </tr>
    </thead>
    <tbody id="datos">
    <?php $imagen=""?>
    @if(count($productos))
        @foreach($productos as $producto)

           <tr>
                <td class="">{{$producto->nombre}}</td>
                <td>{{$producto->barcode}}</td>
                <td>$ {{number_format($producto->precio_costo, 2, "," , "." )}}</td>
                <td>{{$producto->unidad->sigla}}</td>
                <td>{{$producto->categoria->nombre}}</td>
                <td>
                     <a href="#" class="tooltipped" data-tooltip="Ver detalle del producto" onclick="javascript: detalleProducto({{$producto->id}})"><i class="fa fa-list fa-2x" style="cursor: pointer;"></i></a>
                </td>
                <td><a href="{{url('productos-proveedor/edit/'.$producto->id)}}"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a></td>

                   <?php
                       $checked ="";
                        if($producto->estado == "Activo")
                            $checked = "checked";
                   ?>
                   <td>
                       <div class="switch">
                           <label>
                               Off
                               <input type="checkbox" id="{{$producto->id}}" {{$checked}} onclick="estadoProducto(this.id,'{{$producto->estado}}')">
                               <span class="lever"></span>
                               On
                           </label>
                       </div>
                   </td>
            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="8" class="center"><p>Sin resultados</p></td>
        </tr>
    @endif
    </tbody>

</table>
@if(count($productos))
    {!! $productos->render() !!}
@endif
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


