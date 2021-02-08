
<?php
$font_size = 11;
?>
<div id="factura-pos">
    <div class="hide" style="text-align: center;padding-top: 20px; font-size: {{$font_size}}px;">
        <?php
        $historial = $factura->productosHistorial()->select("*")->get();
        $cliente = $factura->cliente;
        $usuarioAdministrador = $factura->usuario;
        ?>

        <div style="text-align: left;">
            <p style="margin: 0px !important;padding: 0px; text-align: center;"><strong>{{$factura->usuario->nombre_negocio}}</strong></p>
            <p style="margin: 0px !important;padding: 0px; text-align: center;"><strong>NIT: {{$factura->usuario->nit}}</strong></p>

            @if($factura->usuario->encabezado_factura)
                <div style="text-align:center;">
                    {!! str_replace("</p><p>","<br/>",$factura->usuario->encabezado_factura) !!}
                </div>
                <br>
            @endif
            <p style="margin: 0px !important;padding: 0px;"><strong>Factura de venta: </strong>{{$factura->numero}}</p>
            <p style="margin: 0px !important;padding: 0px;"><strong>Fecha de venta: </strong>{{date("Y-m-d H:i:s",strtotime($factura->created_at))}}</p>

            <?php
            $admin =  \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
            ?>
            @if($admin->datos_cliente_vendedor == "si")
                @if($factura->cliente->predeterminado != "si")
                    <p style="margin: 0px !important;padding: 0px;"><strong>Cliente: </strong>{{ substr($factura->cliente->nombre,0,30) }}</p>
                    <p style="margin: 0px !important;padding: 0px;"><strong>ID Cliente: </strong>{{$factura->cliente->identificacion}}</p>
                @endif
            @endif
        </div>
        <p>-------------------------------------------------------------------------------------------</p>
        <?php $subtotal = 0; $iva = 0; ?>
        <table class="centered" style="text-align: center;font-size: {{$font_size}}px; width: 100%;">
            <thead>
            <th style="padding: 3px !important;">Producto</th>
            <th style="padding: 3px !important;">Cant.</th>
            <th style="padding: 3px !important;">Vlr. Und.</th>
            <th style="padding: 3px !important;">Total</th>
            </thead>
            <tbody>
            @foreach($historial as $historial)
                <tr>
                    <td style="max-width: 45px;padding: 2px !important;word-wrap: break-word;;">{{ substr($historial->producto->nombre,0,30) }}</td>
                    <td style="max-width: 45px;padding: 2px !important;word-wrap: break-word;">{{$historial->cantidad}}</td>
                    <?php
                    $valor =  $historial->precio_costo_nuevo+(($historial->precio_costo_nuevo*$historial->utilidad_nueva)/100);
                    $valor += (($valor * $historial->iva_nuevo)/100);
                    ?>
                    <td style="max-width: 45px;padding: 2px !important;word-wrap: break-word;">{{"$ ".number_format($valor,0,',','.')}}</td>
                    <td style="max-width: 45px;padding: 2px !important;word-wrap: break-word;">{{"$ ".number_format(($valor * $historial->cantidad),0,',','.')}}</td>
                </tr>
                <?php
                $subtotal += $historial->cantidad * ($historial->precio_costo_nuevo+(($historial->precio_costo_nuevo*$historial->utilidad_nueva)/100));
                $iva += $historial->cantidad * ((($historial->precio_costo_nuevo+(($historial->precio_costo_nuevo*$historial->utilidad_nueva)/100)) * $historial->iva_nuevo)/100);
                ?>
            @endforeach
            </tbody>
        </table>
        <p>-------------------------------------------------------------------------------------------</p>
        <table class="centered" style="text-align: center;font-size: {{$font_size}}px; width: 100%;">
            <tbody>
            <tr><td style="text-align:right;"><strong>Subtotal: </strong></td><td style="text-align:right;">{{"$ ".number_format(round($factura->subtotal),0,',','.')}}</td></tr>
            <tr><td style="text-align:right;"><strong>Total iva: </strong></td><td style="text-align:right;">{{"$ ".number_format(round($factura->iva),0,',','.')}}</td></tr>
            <tr><td style="text-align:right;"><strong>Total: </strong></td><td style="text-align:right;">{{"$ ".number_format(round($factura->subtotal+$factura->iva),0,',','.')}}</td></tr>
            <?php
                $token_info = $factura->token()->where('estado','Inhabilitado')->first();
                $puntos = 0;
                $descuento = $factura->descuento;
            ?>
            @if($descuento > 0)
                <tr><td style="text-align:right;"><strong>Descuento: </strong></td><td style="text-align:right;">{{"$ ".number_format(round($factura->descuento),0,',','.')}}</td></tr>
            @endif
            @if($token_info)
                <?php $puntos = $token_info->valor; ?>
                <tr><td style="text-align:right;"><strong>Valor puntos: </strong></td><td style="text-align:right;">{{"$ ".number_format(round($puntos),0,',','.')}}</td></tr>
            @endif


            <?php
            $valor_medios_pago = 0;
            //$medios_pago = $factura->tiposPago()->select('facturas_tipos_pago.*','tipos_pago.nombre')->get();
            ?>

            @foreach($medios_pago as $mp)
                <tr>
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
                    <td style="text-align:right"><strong >Efectivo: </strong></td>
                    <td style="text-align:right"> {{"$ ".number_format(round(($factura->subtotal+$factura->iva) - ($factura->descuento + $valor_puntos + $valor_medios_pago)),0,',','.')}}</td>
                </tr>
            @endif

            </tbody>
        </table>
        <p>-------------------------------------------------------------------------------------------</p>

        @if ($token_info)
            <div style="text-align: left;">
                <p>---------------------------------------------------------------------------------------------------------------------------------------</p>
                <p style="margin: 0px !important;padding: 0px;"><strong>Número Token: </strong>{{$token_info->token}}</p>
                <p style="margin: 0px !important;padding: 0px;"><strong>Valor Token de puntos: </strong>{{number_format($token_info->valor,2,',','.')}}</p>
                <br>
                <p style="margin: 0px !important;padding: 0px;"><strong>Firma: </strong></p>
                <p>-------------------------------------------------------------------------------------------</p>
            </div>
        @endif

        <div>
            @if($usuarioAdministrador->observaciones_bn == "si" && $factura->observaciones)
                <strong>Observaciones</strong>
                <p>{{$factura->observaciones}}</p>
                <p>-------------------------------------------------------------------------------------------</p>
            @endif

            <p style="/*font-size: small;*/">Resolución DIAN – Factura Sistema POS: {{ $factura->resolucion->prefijo.' '.$factura->resolucion->numero}} {{$factura->resolucion->fecha}} – Rango: {{$factura->resolucion->inicio}} – {{$factura->resolucion->fin}} </p>
            <p>-------------------------------------------------------------------------------------------</p>
            {!! str_replace("</p><p>","<br/>",$usuarioAdministrador->pie_factura) !!}
            @if($usuarioAdministrador->datos_cliente_vendedor == "si")
                <p>Atendido por: {{$factura->usuarioCreador->nombres ." ". $factura->usuarioCreador->apellidos}}</p>
            @endif
			<p>---- Vendiendo.co - Software Punto de Venta Tel. +57 (1) 7020806 ------</p>
        </div>
    </div>
</div>