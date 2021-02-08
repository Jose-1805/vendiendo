@if(count($productos))
    @if(!isset($reporte) || !$reporte)
        <p class="titulo-modal center-align">PRODUCTOS</p>
    @endif
    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
        <thead>
            @if(isset($reporte) && $reporte)
                <tr>
                    <th colspan="6" style="text-align: center">PRODUCTOS</th>
                </tr>
            @endif
            <tr>
                <th style="text-align: center;">Nombre</th>
                <th style="text-align: center;">Cantidad</th>
                <th style="text-align: center;">Unidad</th>
                <th style="text-align: center;">Valor</th>
                <th style="text-align: center;">Iva</th>
                <th style="text-align: center;">Subtotal (con IVA)</th>
            </tr>
        </thead>

        <tbody>

        @foreach($productos as $p)
            <tr>
                <?php
                    if(Auth::user()->bodegas == 'si')
                        $producto = \App\Models\ABProducto::permitidos()->find($p->id);
                    else
                        $producto = \App\Models\Producto::permitidos()->find($p->id);
                ?>
                <td style="text-align: center;">{{$producto->nombre}}</td>
                <td style="text-align: center;">{{$p->cantidad}}</td>
                <td style="text-align: center;">{{$producto->unidad->nombre}}</td>
                <td style="text-align: center;">{{number_format($p->precio_costo_nuevo,2,',','')}}</td>
                <td style="text-align: center;">{{$p->iva_nuevo."%"}}</td>
                <?php
                $subtotal = $p->cantidad * ($p->precio_costo_nuevo + (($p->precio_costo_nuevo * $p->iva_nuevo)/100));
                ?>
                <td style="text-align: center;">{{number_format($subtotal,2,',','')}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
@if($materias_primas)

    @if(!isset($reporte) || !$reporte)
        <p class="titulo-modal center-align">MATERIAS PRIMAS</p>
    @endif
    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
        <thead>
            @if(isset($reporte) && $reporte)
                <tr>
                    <th colspan="6" style="text-align: center">MATERIAS PRIMAS</th>
                </tr>
            @endif
            <tr>
                <th colspan="2" style="text-align: center;" >Nombre</th>
                <th style="text-align: center;">Cantidad</th>
                <th style="text-align: center;">Unidad</th>
                <th colspan="2" style="text-align: center;" >Valor</th>
            </tr>
        </thead>

        <tbody>
        @foreach($materias_primas as $materia)
            <tr>
                <?php
                $m = \App\Models\MateriaPrima::find($materia->id);
                ?>
                <td colspan="2" style="text-align: center;">{{$materia->nombre}}</td>
                <td style="text-align: center;">{{$materia->cantidad}}</td>
                <td style="text-align: center;">{{$m->unidad->nombre}}</td>
                <td colspan="2" style="text-align: center;">{{number_format($materia->precio_costo_nuevo,2,',','')}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif

@if($lista_devoluciones)

    @if(!isset($reporte) || !$reporte)
        <p class="titulo-modal center-align">DEVOLUCIONES</p>
    @endif
    <?php
        if(!isset($reporte))$reporte = false;
    ?>
    @include('compras.devoluciones.index',["estado"=>false,"reporte"=>$reporte])
@endif