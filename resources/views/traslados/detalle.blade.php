<?php
    $usuario = \Illuminate\Support\Facades\Auth::user();
    $subtotal = 0;
    $iva = 0;
    $total = 0;

    $productos = $traslado->precios()
        ->join('historial_costos','traslados_precios.historial_costo_id','=','historial_costos.id');
    if($traslado->almacen_id)
        $productos = $productos->join('historial_utilidad','traslados_precios.historial_utilidad_id','=','historial_utilidad.id');

    $productos = $productos->join('productos','historial_costos.producto_id','=','productos.id')
        ->join('vendiendo.unidades','productos.unidad_id','=','unidades.id');

    if($traslado->almacen_id)
        $productos = $productos->select("productos.id as id_producto","productos.nombre","unidades.sigla as unidad","historial_costos.precio_costo_nuevo","historial_costos.iva_nuevo","historial_utilidad.utilidad","historial_utilidad.actualizacion_utilidad","traslados_precios.*")->get();
    else
        $productos = $productos->select("productos.id as id_producto","productos.nombre","unidades.sigla as unidad","historial_costos.precio_costo_nuevo","historial_costos.iva_nuevo","traslados_precios.*")->get();
        //dd($traslado->getDatosValor());

?>

<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Detalle traslado - {{$traslado->numero}}<i class="font-medium"> ({{"Estado: ".$traslado->estado}})</i> </p>
        @include("templates.mensajes",["id_contenedor"=>"detalle-traslado"])
        {!! Form::hidden("id",$traslado->id,["id"=>"id"]) !!}
        <div class="col s12 m10 offset-m1">
            @if($traslado->almacen_remitente_id)
                <p class="titulo-modal">Almacén remitente</p>
                <div class="col s12 no-padding">
                    <p class="" style="display: inline-block;">{{$traslado->almacenRemitente->nombre}}</p>
                </div>
            @else

                <p class="titulo-modal">Almacén</p>
                <div class="col s12 no-padding">
                    <p class="" style="display: inline-block;">{{$traslado->almacen->nombre}}</p>
                </div>
            @endif
        </div>

        <div class="col s12 m10 offset-m1">
            <p class="titulo-modal margin-top-40">Detalles del traslado</p>
            @if($productos->count())
                <div class="content-table-slide col s12">
                    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
                        <thead>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cant. Trasladada</th>
                            @if((Auth::user()->admin_bodegas == 'no' && $traslado->estado == 'enviado') || $traslado->estado == 'recibido')
                                <th>Cant. Recibida</th>
                            @endif
                            <th>Precio venta</th>
                            <th>Subtotal</th>
                            @if((Auth::user()->admin_bodegas == 'no' || (Auth::user()->admin_bodegas == 'si' && !$traslado->almacen_id)) && $traslado->estado == 'enviado')
                                <th>Procesar</th>
                            @endif

                            @if($traslado->estado == 'recibido')
                                <th>Observaciones</th>
                            @endif
</thead>

<tbody>
    {!! Form::open(['id'=>'form-procesar-traslado']) !!}
    {!! Form::hidden('traslado',$traslado->id) !!}
    @foreach($productos as $pr)
        <?php
          //dd($pr);
        ?>
        <?php
            $p = \App\Models\ABProducto::find($pr->id_producto);
        ?>
        <tr id="">
            <td>{{$pr->nombre}}</td>
            <td>{{substr($pr->unidad,0,5)}}</td>
            <td>{{$pr->cantidad}}</td>

            @if(\Illuminate\Support\Facades\Auth::user()->admin_bodegas == 'no' && $traslado->estado == 'enviado')
                <td><p id="cant_recibida_{{$p->id}}">{{$pr->cantidad}}</p></td>
            @endif

            @if($traslado->estado == 'recibido')
                <td>{{$pr->cantidad_recibida}}</td>
            @endif

            <?php
                if($traslado->estado == 'recibido')$utilidad = $pr->utilidad;
                else $utilidad = $pr->actualizacion_utilidad;
                $valor =  $pr->precio_costo_nuevo+(($pr->precio_costo_nuevo*$utilidad)/100);
                $cant_recibida = $pr->cantidad;
                if($pr->cantidad_recibida)$cant_recibida = $pr->cantidad_recibida;
                $cant_devuelta = $pr->cantidad - $cant_recibida;
                $subtotal += $valor * ($pr->cantidad - $cant_devuelta);
                $iva_aux = (($valor * $pr->iva_nuevo)/100);
                $iva += $iva_aux * ($pr->cantidad-$cant_devuelta);
                $valor += $iva_aux;

            ?>
            <td>{{"$ ".number_format($valor,2,',','.')}}</td>
            <td>{{"$ ".number_format(($valor * ($pr->cantidad-$cant_devuelta)),2,',','.')}}</td>

            @if((Auth::user()->admin_bodegas == 'no' || (Auth::user()->admin_bodegas == 'si' && !$traslado->almacen_id)) && $traslado->estado == 'enviado')
                <td>
                    <a href="#modal-producto-{{$p->id}}" class="modal-trigger"><i class="fa fa-eye"></i></a>
                </td>

                <div id="modal-producto-{{$p->id}}" class="modal modal-fixed-footer modal-small" style="min-height: 70%;">
                    <div class="modal-content">
                        <p class="titulo-modal">{{$p->nombre}}</p>
                        <p><strong>Cantidad trasladada: </strong> {{$pr->cantidad}}</p>
                        <div class="form-control">
                            {!! Form::label('recibido_'.$p->id,'Recibido') !!}
                            {!! Form::text('recibido_'.$p->id,$pr->cantidad,['id'=>'recibido_'.$p->id,'class'=>'num-entero recibido','data-producto'=>$p->id]) !!}
                        </div>
                        <div class="form-control">
                            {!! Form::label('observaciones_'.$p->id,'Observaciones') !!}
                            {!! Form::textarea('observaciones_'.$p->id,null,['id'=>'recibido_'.$p->id,'class'=>'materialize-textarea']) !!}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat ">Continuar</a>
                    </div>
                </div>
            @endif

            @if($traslado->estado == 'recibido')
                <td>{{$pr->observaciones}}</td>
            @endif
</tr>
@endforeach
{!! Form::close() !!}
</tbody>
</table>
</div>

<div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div>

<div class="col s12 right-align">
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="subtotal_traslado">{{"$ ".number_format(round($subtotal),0,',','.')}}</span></p>
    </div>
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">IVA:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="iva_traslado">{{"$ ".number_format(round($iva),0,',','.')}}</span></p>
    </div>
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">Total a pagar:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="total_traslado">{{"$ ".number_format(round($subtotal+$iva),0,',','.')}}</span></p>
    </div>
</div>


<?php
$admin =  $usuario->userAdminId();
?>
        @if((Auth::user()->admin_bodegas == 'no' || (Auth::user()->admin_bodegas == 'si' && !$traslado->almacen_id)) && $traslado->estado == 'enviado')
            <div class="col s12 center-align margin-top-50">
                <a id="btn-procesar-traslado" href="#!" class="btn blue-grey darken-2 waves-effect waves-light">Procesar</a>
            </div>
        @endif
@else
<p class="col s12 center">Sin resultados</p>
@endif

<div class="col s12 center margin-top-40" id="">

</div>
</div>
</div>


@endsection



@section('js')
@parent

<script src="{{asset('js/trasladoAction.js')}}"></script>
@stop
