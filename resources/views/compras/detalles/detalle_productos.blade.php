<div class="content-table-slide col s12">
    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
        <thead>
        <th>Producto</th>
        <th>Cantidad</th>
        <th>Unidad</th>
        <th>Valor</th>
        <th>Iva</th>
        <th>Subtotal (con IVA)</th>
        <th>Devolución</th>
        </thead>

        <tbody>
        @foreach($compra_producto_detalles as $productoHistorial)
            <tr>
                <?php
                    if(Auth::user()->bodegas == 'si')
                        $producto = \App\Models\ABProducto::permitidos()->find($productoHistorial->producto_id);
                    else
                        $producto = \App\Models\Producto::permitidos()->find($productoHistorial->producto_id);
                ?>
                <td>{{$producto->nombre}}</td>
                <td>{{$productoHistorial->cantidad}}</td>
                <td>{{$producto->unidad->nombre}}</td>
                <td>{{"$ ".number_format($productoHistorial->precio_costo_nuevo,2,',','.')}}</td>
                <td>{{$productoHistorial->iva_nuevo."%"}}</td>
                <?php
                    $subtotal = $productoHistorial->cantidad * ($productoHistorial->precio_costo_nuevo + (($productoHistorial->precio_costo_nuevo * $productoHistorial->iva_nuevo)/100));
                    ?>
                <td>{{"$ ".number_format($subtotal,2,',','.')}}</td>
                @if($compra->estado == 'Recibida')
                <td>
                    <a href="#modal-detalle-compra" class="modal-trigger tooltipped" data-tooltip="Devolucion" onclick="openFormDevolution('{{$producto->id}}','{{$productoHistorial->id}}','{{$productoHistorial->cantidad}}','{{$producto->unidad->nombre}}','{{$producto->nombre}}','{{$compra->id}}','Producto','{{$productoHistorial->precio_costo_nuevo + (($productoHistorial->precio_costo_nuevo * $productoHistorial->iva_nuevo)/100)}}','{{$compra->proveedor->id}}','{{$productoHistorial->id}}')"><i class="fa fa-thumbs-down red-text" style="cursor: pointer;"></i></a>
                </td>
                    @else
                <td>
                    <a href="#!" class="tooltipped" data-tooltip="Pendiente de recibo" ><i class="fa fa-ban red-text" style="cursor: pointer;"></i></a>
                </td>
                    @endif
            </tr>
        @endforeach
        </tbody>
    </table>
</div>