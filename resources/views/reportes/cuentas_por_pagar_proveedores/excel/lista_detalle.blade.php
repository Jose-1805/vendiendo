<div id="informacion-cliente">
    <tr>
        <td colspan="9" style="text-align: center;font-size: 30px;"><strong style="color: #0584b1;">Cuentas por pagar a {{$proveedor->nombre}} </strong>"{{ \App\General::fechaActualString() }}"</td>
    </tr>
    <br>
    <tr>
        <td><strong>Identificaci&oacute;n: </strong></td>
        <td style="text-align: left">{{ $proveedor->nit }}</td>
    </tr>
    <tr>
        <td><strong>Direcci&oacute;n: </strong></td>
        <td>{{ $proveedor->direccion }}</td>
    </tr>
    <tr>
        <td><strong>Telefono: </strong></td>
        <td style="text-align: left">{{ $proveedor->telefono }}</td>
    </tr>
    <br>

        <?php
        $total_credito = $compras_proveedor_all->sum('valor');
        $total_abonos = $total_abonos;
        $saldo_cobrar = $total_credito - $total_abonos;
        $count_vencidas = 0;
        foreach ($compras_proveedor_all as $cpa){
            $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
            $dias_trascurridos = \App\General::dias_transcurridos(date_format($cpa->created_at,'Y-m-d'),$fecha_actual);
            if ($cpa->dias_credito <= $dias_trascurridos){
                $count_vencidas ++;
            }
        }
        $cuentas_dia = count($compras_proveedor_all) - $count_vencidas;
        ?>
        <table id="informacion-general">
           <tr>
               <th></th>
               <th></th>
               <th style="color: #0584b1;">Facturas vencidas</th>
               <th style="color: #0584b1;">Facturas al dia</th>
               <th style="color: #0584b1;">Total cr&eacute;dito</th>
               <th style="color: #0584b1;">Total abonos</th>
               <th style="color: #0584b1;">Saldo por cobrar</th>
           </tr>
            <tr>
                <td></td>
                <td></td>
                <td style="text-align: center">{{ $count_vencidas }}</td>
                <td style="text-align: center">{{ $cuentas_dia }}</td>
                <td>{{ number_format($total_credito,2,',','') }}</td>
                <td>{{ number_format($total_abonos,2,',','') }}</td>
                <td>{{ number_format($saldo_cobrar,2,',','') }}</td>
            </tr>
        </table>
    <br>
</div>
<table id="detalle">
    <thead>
        <tr>
            <th rowspan="2">Compra</th>
            <th rowspan="2">Fecha</th>
            <th rowspan="2">Valor compra</th>
            <th rowspan="2">Valor abonos</th>
            <th colspan="3" style="text-align: center;">D&iacute;as de cr&eacute;dito</th>
            <th rowspan="2">Vencidas</th>
            <th rowspan="2">Saldo</th>
        </tr>
        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th style="text-align: center">1-30</th>
            <th style="text-align: center">31-60</th>
            <th style="text-align: center">61-120</th>
        </tr>
    </thead>
    <tbody>
    @forelse($compras_proveedor as $fc)
        <?php
        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        $dias_trascurridos = \App\General::dias_transcurridos(date_format($fc->created_at,'Y-m-d'),$fecha_actual);

        $saldo = $fc->valor - $fc->abonos()->sum('valor');

        $rango_30 = $rango_60 = $rango_120 = $vencida='';
        if ($fc->dias_credito >1 && $fc->dias_credito <= 30)$rango_30 = number_format($saldo,2,',','');
        if ($fc->dias_credito >31 && $fc->dias_credito <= 60)$rango_60 = number_format($saldo,2,',','');
        if ($fc->dias_credito >61 && $fc->dias_credito <= 120)$rango_120 = number_format($saldo,2,',','');

        if ($fc->dias_credito - $dias_trascurridos <= 0)$vencida = number_format($saldo,2,',','');
        ?>
        <tr>
            <td>{{ $fc->numero }}</td>
            <td>{{ $fc->created_at }}</td>
            <td>{{number_format($fc->valor,2,',','')}}</td>
            <td>{{number_format($fc->abonos()->sum('valor'),2,',','')}}</td>
            <td>{{ $rango_30 }}</td>
            <td>{{ $rango_60 }}</td>
            <td>{{ $rango_120 }}</td>
            <td style="color: #ff0000">{{ $vencida }}</td>
            <td>{{ number_format($saldo,2,',','') }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9">
                Sin resultados
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
