<?php
    $totalSinIva = 0;
    $totalIva = 0;
?>
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Detalle venta  {{$factura->estado ." (".date('Y-m-d',strtotime($factura->created_at)).")"}}</p>

        <div id="contenedor-lista-detalles-factura-abierta" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"factura-abierta"])
            <div id="lista-detalles-factura-abierta" class="col s12 content-table-slide">
                <table class="table centered">
                    <thead>
                        <th>Producto</th>
                        <th>Unidad</th>
                        <th>Cantidad</th>
                        <th>Valor unitario</th>
                        <th>Subtotal</th>
                    </thead>
                    <tbody>
                        @forelse($detalles as $d)
                            <tr>
                                <td>{{$d->producto->nombre}}</td>
                                <td>{{$d->producto->unidad->sigla}}</td>
                                <td>{{$d->cantidad}}</td>
                                <?php
                                    $valor = $d->precio_costo_nuevo + (($d->precio_costo_nuevo * $d->utilidad_nueva)/100);
                                    $valor += (($valor * $d->iva_nuevo)/100);
                                ?>
                                <td>$ {{number_format($valor,2,',','.')}}</td>
                                <td>$ {{number_format(($valor * $d->cantidad),2,',','.')}}</td>
                            </tr>
                            <?php
                                $totalSinIva += ($d->precio_costo_nuevo + (($d->precio_costo_nuevo * $d->utilidad_nueva)/100)) * $d->cantidad;
                                $totalIva +=  ((($d->precio_costo_nuevo + (($d->precio_costo_nuevo * $d->utilidad_nueva)/100)) * $d->cantidad) * $d->iva_nuevo)/100;
                            ?>
                        @empty
                            <tr>
                                <td colspan="6" class="center-align">No se han registrado ventas en esta fecha</td>
                            </tr>
                        @endforelse

                        @if(count($detalles))
                            <tr style="border-top: 1px solid #00c0e4;">
                                <td style="padding: 5px;" colspan="3"></td>
                                <th style="padding: 5px;" class="center-align">Total sin iva</th>
                                <td style="padding: 5px;">$ {{number_format($totalSinIva,2,',','.')}}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;" colspan="3"></td>
                                <th style="padding: 5px;" class="center-align">Total iva</th>
                                <td style="padding: 5px;">$ {{number_format($totalIva,2,',','.')}}</td>
                            </tr>
                            <tr>
                                <td style="padding: 5px;" colspan="3"></td>
                                <th style="padding: 5px;" class="center-align">Total</th>
                                <td style="padding: 5px;">$ {{number_format(($totalSinIva + $totalIva),2,',','.')}}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>

    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/facturaAction.js')}}"></script>
@stop
