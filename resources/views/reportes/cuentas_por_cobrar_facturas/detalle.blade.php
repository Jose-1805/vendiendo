<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Cuentas por cobrar a {{$cliente->nombre}} <label style="font-size: 16px">"{{ \App\General::fechaActualString() }}"</label></p>

        <div class="input-field col s12" >
            <button class="btn blue-grey darken-2 waves-effect waves-light btn-clientes-detalle-factura" id="btn-clientes-detalle-factura-lista" style="float: right;margin-top: -80px;padding: 0px 5px !important;">
                <i class="fa fa-eye" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
        </div>
        <div class="col s12 right-align" style="margin-top: -77px;margin-left: -45px;">
            <i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcelCCC({{$cliente->id}})"></i>
        </div>
        <div class="col s12 ">
            <div class="col s12" style="margin-left: 60px;">
                <p class="" style="width: 150px;display: inline-block;"><strong>Identificación: </strong></p>
                <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-identificacion">{{ $cliente->identificacion }}</p>
            </div>
            <div class="col s12" style="margin-left: 60px;">
                <p class="" style="width: 150px;display: inline-block;"><strong>Dirección: </strong></p>
                <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-direccion">{{ $cliente->direccion }}</p>
            </div>
            <div class="col s12" style="margin-left: 60px;">
                <p class="" style="width: 150px;display: inline-block;"><strong>Telefono: </strong></p>
                <p class="dato-proveedor grey-text text-darken-1" style="display: inline-block;" id="txt-telefono">{{ $cliente->telefono }}</p>
            </div>
        </div>
        <div class="col s12 m12 center-block" id="datos-facturas">
            <?php
                $total_credito = $facturas_cliente_all->sum('subtotal') + $facturas_cliente_all->sum('iva');
                $total_abonos = $total_abonos;
                $saldo_cobrar = $total_credito - $total_abonos;
                $count_vencidas = 0;
                foreach ($facturas_cliente_all as $fc){
                    $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
                    $dias_trascurridos = \App\General::dias_transcurridos(date_format($fc->created_at,'Y-m-d'),$fecha_actual);
                    if ($fc->dias_credito <= $dias_trascurridos){
                        $count_vencidas ++;
                    }
                }
                $cuentas_dia = count($facturas_cliente_all) - $count_vencidas;
            ?>
            <p class="col s12 m1 text-center" style="display: inline-block;font-size: 20px;"></p>
            <p class="col s12 m2 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Vencidas </strong><br>{{ $count_vencidas }}</p>
            <p class="col s12 m2 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Al dia </strong><br>{{ $cuentas_dia }}</p>
            <p class="col s12 m2 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Total credito</strong><br>${{ number_format($total_credito,2,',','.') }}</p>
            <p class="col s12 m2 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Total abonos</strong><br>${{ number_format($total_abonos,2,',','.') }}</p>
            <p class="col s12 m3 text-center" style="display: inline-block;font-size: 20px;"><strong style="color: #0584b1;">Saldo por cobrar </strong><br>${{ number_format($saldo_cobrar,2,',','.') }}</p>
        </div>
        <?php
        if ($pos == '')
            $display = 'none';
        else
            $display = 'block';
        ?>

        <div class="col s12 divider"></div>
        <div id="contenedor-lista-facturas-por-cobrar" class="col s12 content-table-slide" style="display: {{ $display }}">
        <input type="hidden" name="cliente_id" id="cliente_id" value="{{$cliente->id}}">
            @include('reportes.cuentas_por_cobrar_facturas.lista_detalle')
        </div>

        @if(isset($almacen))
            <input type="hidden" name="almacen" id="almacen" value="{{$almacen}}">
        @endif
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/cuentas_X_cobrar_facturas.js')}}"></script>
@stop