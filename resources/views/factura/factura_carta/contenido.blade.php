<div id="contenedor-contenido"  style="width: 100%; ">
    <!--<div style="page-break-after: always;">

    </div>-->
    <div id="datos-tabla" style="width: 100% !important; ;margin: 0px 0px 10px 10px;overflow: auto">
        @if(
               (Auth::user()->bodegas == 'no' && $factura->productosHistorial->count())
           ||  (Auth::user()->bodegas == 'si' && $factura->productosHistorialUtilidad->count())
           )
            <div style="width: auto">
                @if(Auth::user()->bodegas == 'no')
                    <p style="width: 100%;text-align: right;"><em>Factura de venta No.</em> {{$factura->resolucion->prefijo.' '.$factura->numero}}</p>
                @else
                    <p style="width: 100%;text-align: right;"><em>Factura de venta No.</em> {{$factura->almacen->prefijo.' '.$factura->numero}}</p>
                @endif
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
                    <?php
                    if(Auth::user()->bodegas == 'si'){
                        $historiales = $factura->productosHistorialUtilidad()->select("historial_utilidad.*","facturas_historial_utilidad.cantidad")->get();
                    }else{
                        $historiales = $factura->productosHistorial()->select("productos_historial.*","facturas_productos_historial.cantidad")->get();
                    }
                    ?>
                    @foreach($historiales as $historial)
                        <tr style="border: 1px solid #000;">
                            <td width="5" style="text-align: center;border: 1px solid #000;">{{$historial->cantidad}}</td>
                            <td style="text-align: left;border: 1px solid #000;">{{$historial->producto->nombre}}</td>
                            <td style="text-align: center;border: 1px solid #000;width: 2em;">{{$historial->producto->unidad->sigla}}</td>
                            <?php
                            if(Auth::user()->bodegas == 'si'){
                                $historial_costo = $factura->productosHistorialCosto()->where('historial_costos.producto_id',$historial->producto->id)->first();
                                $historial->precio_costo_nuevo = $historial_costo->precio_costo_nuevo;
                                $historial->iva_nuevo = $historial_costo->iva_nuevo;
                                $historial->utilidad_nueva = $historial->utilidad;
                            }
                            $valor =  $historial->precio_costo_nuevo+(($historial->precio_costo_nuevo*$historial->utilidad_nueva)/100);
                            $valor += (($valor * $historial->iva_nuevo)/100);
                            ?>
                            <td style="text-align: center;border: 1px solid #000;width: 3em;">{{"$ ".number_format($valor,2,',','.')}}</td>
                            <td style="text-align: center;border: 1px solid #000;">{{number_format($historial->iva_nuevo,2,',','.')."%"}}</td>
                            <td style="text-align: center;border: 1px solid #000;">{{"$ ".number_format(($valor * $historial->cantidad),2,',','.')}}</td>
                        </tr>
                    @endforeach


                    <tr><td colspan="4"></td><td style="text-align:right"><strong >Subtotal: </strong></td><td style="text-align:right"> {{"$ ".number_format(round($factura->subtotal),0,',','.')}}</td></tr>
                    <tr><td colspan="4"><td style="text-align:right"><strong >Iva: </strong></td><td style="text-align:right"> {{"$ ".number_format(round($factura->iva),0,',','.')}}</td></tr>
                    @if($factura->descuento > 0)
                        <tr><td colspan="4"><td style="text-align:right"><strong >Descuento: </strong></td><td style="text-align:right"> {{"$ ".number_format(round($factura->descuento),0,',','.')}}</td></tr>
                    @endif
                    <tr><td colspan="4"><td style="text-align:right"><strong >Total: </strong></td><td style="text-align:right"> {{"$ ".number_format(round($factura->subtotal+$factura->iva-$factura->descuento),0,',','.')}}</td></tr>

                    <?php
                        $token_info = $factura->token()->where('estado','Inhabilitado')->first();
                        $puntos = 0;
                        $descuento = $factura->descuento;
                    ?>

                    @if($token_info)
                        <?php $puntos = $token_puntos->valor; ?>
                        <tr><td colspan="4"><td style="text-align:right"><strong >Valor puntos: </strong></td><td style="text-align:right"> {{"$ ".number_format(round($puntos),0,',','.')}}</td></tr>
                    @endif

                    <?php
                        $valor_medios_pago = 0;
                        $medios_pago = $factura->tiposPago()->select('facturas_tipos_pago.*','tipos_pago.nombre')->get();
                    ?>

                    @foreach($medios_pago as $mp)
                        <tr>
                            <td colspan="4"></td>
                            <td style="text-align:right"><strong >{{$mp->nombre}}
                                    @if($mp->codigo_verificacion)
                                        {{' ('.$mp->codigo_verificacion.')'}}
                                    @endif: </strong></td>

                            <td style="text-align:right"> {{"$ ".number_format(round($mp->valor),0,',','.')}}</td>
                        </tr>

                        <?php $valor_medios_pago += $mp->valor; ?>
                    @endforeach

                    @if(round(($factura->subtotal+$factura->iva) - ($factura->descuento + $valor_puntos + $valor_medios_pago)) > 0)
                        <tr>
                            <td colspan="4"></td>
                            <td style="text-align:right"><strong >Efectivo: </strong></td>
                            <td style="text-align:right"> {{"$ ".number_format(round(($factura->subtotal+$factura->iva) - ($factura->descuento + $valor_puntos + $valor_medios_pago)),0,',','.')}}</td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="4"></td>
                        <td style="text-align:right"><strong >Total recibido: </strong></td>
                        <td style="text-align:right"> {{"$ ".number_format(round($factura->subtotal+$factura->iva-$factura->descuento),0,',','.')}} </td>
                    </tr>

                    </tbody>
                </table>
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

        @if($admin->observaciones_color == "si" && $factura->observaciones)
            <strong>Observaciones</strong>
            <p style="border-bottom: 2px solid #000; padding-bottom: 15px;">{{$factura->observaciones}}</p>
        @endif

        <p style="/*font-size: small;*/">Resolución DIAN – Factura Sistema POS: {{$factura->resolucion->numero}} {{$factura->resolucion->fecha}} – Rango: {{$factura->resolucion->inicio}} – {{$factura->resolucion->fin}} </p>
        {!! str_replace("</p><p>","<br/>",$admin->pie_factura) !!}
        @if($admin->datos_cliente_vendedor == "si")
            <p>Atendido por: {{$factura->usuarioCreador->nombres ." ". $factura->usuarioCreador->apellidos}}</p>
        @endif
    </div>
</div>