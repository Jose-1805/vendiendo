<?php
    $usuario = \Illuminate\Support\Facades\Auth::user();
    $subtotal = 0;
    $iva = 0;
    $total = 0;
        $facturar = false;
    if($remision->estado == "registrada" && $usuario->permitirFuncion("Crear","facturas","inicio"))
        $facturar = true;
    else
        $facturar = false;

    if(Auth::user()->bodegas == 'si'){
        if($remision->estado == 'registrada'){
            $productos = $remision->historialCostos()
                ->join('productos','historial_costos.producto_id','=','productos.id')
                ->join('historial_utilidad','remisiones_historial_costos.historial_utilidad_id','=','historial_utilidad.id')
                ->select(
                    "productos.id as producto_id",
                    "productos.id as id_producto",
                    "productos.nombre",
                    "productos.unidad_id",
                    "remisiones_historial_costos.cantidad as cantidad",
                    "historial_costos.precio_costo_nuevo as precio_costo",
                    "historial_utilidad.utilidad as utilidad",
                    "historial_costos.iva_nuevo as iva"
                )->get();
        }else{
            $productos = $remision->factura->productosHistorialCosto()->select("productos.id as id_producto","productos.nombre","productos.unidad_id","facturas_historial_utilidad.cantidad","historial_costos.*","historial_utilidad.utilidad as utilidad_nueva")
                ->join("facturas","facturas_historial_costos.factura_id","=","facturas.id")
                ->join("productos","historial_costos.producto_id","=","productos.id")
                ->join("facturas_historial_utilidad","facturas.id","=","facturas_historial_utilidad.factura_id")
                ->join("historial_utilidad","facturas_historial_utilidad.historial_utilidad_id","=","historial_utilidad.id")
                ->get();
        }
    }else{
        if($remision->estado == "registrada")
            $productos = $remision->productos()->select("productos.id as id_producto","productos.nombre","productos.unidad_id","productos_remisiones.*")->get();
        else
            $productos = $remision->factura->productosHistorial()->select("productos.id as id_producto","productos.nombre","productos.unidad_id","facturas_productos_historial.cantidad","productos_historial.*")
                    ->join("productos","productos_historial.producto_id","=","productos.id")
                    ->get();
            //dd($remision->getDatosValor());
    }
?>
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Detalle remisión - {{$remision->numero}}<i class="font-medium"> ({{"Estado: ".$remision->estado}})</i> </p>
        @include("templates.mensajes",["id_contenedor"=>"detalle-remision"])
        {!! Form::hidden("id",$remision->id,["id"=>"id"]) !!}
        <div class="col s12 m10 offset-m1">
            <p class="titulo-modal">Datos del cliente</p>
            <div class="col s12 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Nombre: </strong></p>
                <p class="" style="display: inline-block;">{{$remision->cliente->nombre}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Tipo identificación: </strong></p>
                <p class="" style="display: inline-block;">{{$remision->cliente->tipo_identificacion}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Identificación: </strong></p>
                <p class="" style="display: inline-block;">{{$remision->cliente->identificacion}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Teléfono: </strong></p>
                <p class="" style="display: inline-block;">{{$remision->cliente->telefono}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Correo: </strong></p>
                <p class="" style="display: inline-block;">{{$remision->cliente->correo}}</p>
            </div>

            <div class="col s12 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Dirección: </strong></p>
                <p class="" style="display: inline-block;">{{$remision->cliente->direccion}}</p>
            </div>
        </div>

        <div class="col s12 m10 offset-m1">
            <p class="titulo-modal margin-top-40">Detalles de la remisión @if($remision->estado == "generada") - <span style="font-size: small;color: #5a5a5a;">Vencimiento: {{date("Y-m-d H:i",strtotime("+".$remision->dias_vencimiento." days",strtotime($remision->created_at)))}}</span>@endif</p>
            @if($productos->count())
                <div class="content-table-slide col s12">
                    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
                        <thead>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cant. Remitida</th>
                            @if($remision->estado == "facturada")
                                <th>Cant. Facturada</th>
                            @endif
                            <th>Valor unitario</th>
                            <th>Subtotal</th>
</thead>

<tbody>
    @foreach($productos as $pr)
        <?php
            if(Auth::user()->bodegas == 'si')
                $p = \App\Models\ABProducto::find($pr->producto_id);
            else
                $p = \App\Models\Producto::find($pr->id_producto);
        ?>
        <tr id="">
            <td>{{$pr->nombre}}</td>
            <td>{{substr($p->unidad->sigla,0,5)}}</td>
            <td>{{$remision->cantidadRemitida($pr->id_producto)}}</td>
            @if($remision->estado == "facturada")
                <td>{{$pr->cantidad}}</td>
            @endif
            <?php
                if($remision->estado == "facturada"){
                    $valor =  $pr->precio_costo_nuevo+(($pr->precio_costo_nuevo*$pr->utilidad_nueva)/100);
                    $subtotal += $valor * $pr->cantidad;
                    $iva_aux = (($valor * $pr->iva_nuevo)/100);
                    $iva += $iva_aux * $pr->cantidad;
                    $valor += $iva_aux;
                }else{
                    $valor =  $pr->precio_costo+(($pr->precio_costo*$pr->utilidad)/100);
                    $subtotal += $valor * $pr->cantidad;
                    $iva_aux = (($valor * $pr->iva)/100);
                    $iva += $iva_aux * $pr->cantidad;
                    $valor += $iva_aux;
                }
            ?>
            <td>{{"$ ".number_format($valor,2,',','.')}}</td>
            <td>{{"$ ".number_format(($valor * $pr->cantidad),2,',','.')}}</td>
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div>

<div class="col s12 right-align">
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="subtotal_remision">{{"$ ".number_format(round($subtotal),0,',','.')}}</span></p>
    </div>
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">IVA:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="iva_remision">{{"$ ".number_format(round($iva),0,',','.')}}</span></p>
    </div>
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">Total a pagar:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="total_remision">{{"$ ".number_format(round($subtotal+$iva),0,',','.')}}</span></p>
    </div>
</div>


<?php
$admin =  $usuario->userAdminId();
?>
@else
<p class="col s12 center">Sin resultados</p>
@endif

<div class="col s12 center margin-top-40" id="">
@if($facturar)
<a class="btn waves-effect waves-light blue-grey darken-2" onclick='facturarRemision()'>Facturar</a>
@endif
</div>
</div>
</div>

@if($facturar)
    <div id="modal-pagar-remision" class="modal modal-fixed-footer modal-sm" style="height: 400px !important;min-height: 400px;">
        <div class="modal-content">
            <p class="titulo-modal">
                Pagar
            </p>

            <div class="row">
                @include("templates.mensajes",["id_contenedor"=>"modal-pagar"])
                <ul class="collection">
                @foreach($productos as $p)
                    <li class="collection-item no-margin no-padding">
                        <div class="row no-margin no-padding">
                            <?php
                                $valor = 0;
                                $valor = $p->precio_costo;
                                $valor += ($valor * $p->utilidad)/100;
                                $valor += ($valor * $p->iva)/100;
                                $total += $valor * $p->cantidad;
                            ?>

                            <p class="col s8 m10 no-margin no-padding">{{$p->nombre}}</p>
                            <input class="col s4 m2 no-margin no-padding info-producto num-real" data-producto="{{$p->producto_id}}" data-valor="{{$valor}}" data-cantidad="{{$p->cantidad}}" value="{{$p->cantidad}}" id="cantidad_{{$p->id}}">
                        </div>
                    </li>
                @endforeach
                </ul>
            </div>

        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-pagar-modal">
                <a class="green-text btn-flat" onclick="javascript: guardarFacturaRemision();">Facturar</a>
                <a class="modal-close cyan-text btn-flat">Cancelar</a>
                <strong id="valor-total" class="green-text left" style="font-size: large;line-height: 45px;">$ {{number_format($total,2,',','.')}}</strong>
            </div>

            <div class="progress hide" id="progress-pagar-modal">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

@endsection



@section('js')
@parent

<script src="{{asset('js/remisionAction.js')}}"></script>
@stop
