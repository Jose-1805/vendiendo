<?php
    $productos = $pedido->productos()->select("productos.*","pedidos_proveedor_productos.valor_actual","pedidos_proveedor_productos.cantidad","pedidos_proveedor_productos.promocion_proveedor_id")->get();
?>
<div class="row">
<p class="col s12 m6 l5"><strong>Consecutivo: </strong>00{{$pedido->consecutivo}}</p>
<p class="col s12 m6 l7"><strong>Estado: </strong>{{$pedido->estado}}</p>
<p class="col s12 m6 l5" style="margin-top: -10px;"><strong>Usuario: </strong>{{$pedido->administrador->nombres." ".$pedido->administrador->apellidos}}</p>
<p class="col s12 m6 l7" style="margin-top: -10px;"><strong>Negocio: </strong>{{$pedido->administrador->nombre_negocio}}</p>
<p class="col s12 m6 l5" style="margin-top: -10px;"><strong>Celular: </strong>{{$pedido->administrador->telefono}}</p>
<p class="col s12 m6 l7" style="margin-top: -10px;"><strong>Email: </strong>{{$pedido->administrador->email}}</p>
<p class="col s12 m6 l5" style="margin-top: -10px;"><strong>Valor: </strong>$ {{number_format($pedido->valor_total,2,',','.')}}</p>
<p class="col s12 m6 l7" style="margin-top: -10px;"><strong>Fecha: </strong>{{date("Y-m-d",strtotime($pedido->created_at))}}</p>

    <div class="col s12 content-table-slide">
        <table class="table centered highlight">
            <thead>
                <th>Producto</th>
                <th>Valor unitario</th>
                <th>Cantidad</th>
                <th>Valor total</th>
            </thead>

            <tbody>
                @forelse($productos as $p)
                    <tr>
                        <td>{{$p->nombre}}</td>
                        <?php
                            $clase = "";
                            if($p->promocion_proveedor_id != "")
                                $clase = "badge-vendiendo";
                        ?>
                        <td class="{{$clase}}">$ {{number_format($p->valor_actual,2,',','.')}}</td>
                        <td>{{$p->cantidad}}</td>
                        <td>$ {{number_format(($p->cantidad * $p->valor_actual),2,',','.')}}</td>
                    </tr>
                @empty
                    <tr>
                        <td class="center-align">Sin resultados</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>