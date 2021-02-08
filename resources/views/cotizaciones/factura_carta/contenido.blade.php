<?php
    $subtotal = 0;
    $iva = 0;
?>
<div id="contenedor-contenido"  style="width: 100%; ">
    <!--<div style="page-break-after: always;">

    </div>-->
    <div id="datos-tabla" style="width: 100% !important; ;margin: 0px 0px 10px 10px;overflow: auto">
            @if($cotizacion->productosHistorial->count())
                <div style="width: auto">
                    <p style="width: 100%;text-align: right;"><em>Cotización No.</em> {{$cotizacion->numero}}</p>
                    <table>
                        <tr height="2" style="text-align: center; color: #000 !important; font-family: Arial, sans-serif; font-size: 9pt; margin-left: 1%;  background-color: lightsteelblue;border: 1px solid #000;">
                            <th  style="text-align: center;border: 1px solid #000;width: 2em;">Cant.</th>
                            <th style="text-align: center;border: 1px solid #000;width: 22em;">Producto</th>
                            <th style="text-align: center;border: 1px solid #000;width: 2em;">Unidad</th>
                            <th style="text-align: center;border: 1px solid #000;width: 10em;">Valor unitario</th>
                            <th style="text-align: center;border: 1px solid #000;width: 6em;">Iva</th>
                            <th style="text-align: center;border: 1px solid #000;width: 10em;">Subtotal</th>
                        </tr>

                        <tbody style="font-family: Arial, sans-serif; font-size: 8pt; margin-left: 1%;">
                        @foreach($cotizacion->productosHistorial()->select("productos_historial.*","cotizaciones_productos_historial.cantidad")->get() as $historial)
                            <tr style="border: 1px solid #000;">
                                <td width="5" style="text-align: center;border: 1px solid #000;">{{$historial->cantidad}}</td>
                                <td style="text-align: left;border: 1px solid #000;">{{$historial->producto->nombre}}</td>
                                <td style="text-align: center;border: 1px solid #000;width: 2em;">{{$historial->producto->unidad->sigla}}</td>
                                <?php
                                $valor =  $historial->precio_costo_nuevo+(($historial->precio_costo_nuevo*$historial->utilidad_nueva)/100);
                                $subtotal += $valor * $historial->cantidad;
                                $iva_aux = (($valor * $historial->iva_nuevo)/100);
                                $valor += $iva_aux;
                                $iva += $iva_aux * $historial->cantidad;
                                ?>
                                <td style="text-align: center;border: 1px solid #000;width: 3em;">{{"$ ".number_format($valor,2,',','.')}}</td>
                                <td style="text-align: center;border: 1px solid #000;">{{number_format($historial->iva_nuevo,2,',','.')."%"}}</td>
                                <td style="text-align: center;border: 1px solid #000;">{{"$ ".number_format(($valor * $historial->cantidad),2,',','.')}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div style="font-family: Arial, sans-serif; font-size: 8pt; margin-right: 1%;width: 100%; text-align: right;">
                    <p><strong>Subtotal: </strong> {{"$ ".number_format(round($subtotal),0,',','.')}}</p>
                    <p><strong>Iva: </strong> {{"$ ".number_format(round($iva),0,',','.')}}</p>
                    <p><strong>Total a pagar: </strong> {{"$ ".number_format(round($subtotal+$iva),0,',','.')}}</p>
                </div>
            @else
                <p class="col s12 center">Sin resultados</p>
            @endif

    </div>
    <hr>
    <div id="datos-tabla" style="float: left; box-sizing: border-box; width: 100%;margin-right: 10px; margin: 0px 0px 10px 10px;overflow:hidden; ">
        <?php
            $admin =  \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
        ?>

        {!! $admin->pie_factura !!}
        @if($admin->datos_cliente_vendedor == "si")
            <p>Atendido por: {{$cotizacion->usuarioCreador->nombres ." ". $cotizacion->usuarioCreador->apellidos}}</p>
        @endif
    </div>
</div>


