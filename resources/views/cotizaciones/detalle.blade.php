<?php
    $usuario = \Illuminate\Support\Facades\Auth::user();
    $subtotal = 0;
        $iva = 0;
        $facturar = false;
    if($cotizacion->estado == "generada" && $usuario->permitirFuncion("Editar","cotizaciones","inicio") && $usuario->permitirFuncion("Crear","facturas","inicio"))
        $facturar = true;
        $editar = false;
    if($cotizacion->estado == "generada" && $usuario->permitirFuncion("Editar","cotizaciones","inicio"))
        $editar = true;

        $historiales = $cotizacion->historial()->orderBy("created_at","DESC")->get();
        //dd($cotizacion->getDatosValor());
?>
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Detalle cotización - {{$cotizacion->numero}}<i class="font-medium"> ({{"Estado: ".$cotizacion->estado}})</i> <span><a href="#modal-historial" class="modal-trigger" ><i style="line-height: 40px;" class="fa fa-list-alt right blue-grey-text text-darken-2"></i></a></span></p>
        @include("templates.mensajes",["id_contenedor"=>"detalle-cotizacion"])
        {!! Form::hidden("id",$cotizacion->id,["id"=>"id"]) !!}
        <div class="col s12 m10 offset-m1">
            <p class="titulo-modal">Datos del cliente</p>
            <div class="col s12 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Nombre: </strong></p>
                <p class="" style="display: inline-block;">{{$cotizacion->cliente->nombre}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Tipo identificación: </strong></p>
                <p class="" style="display: inline-block;">{{$cotizacion->cliente->tipo_identificacion}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Identificación: </strong></p>
                <p class="" style="display: inline-block;">{{$cotizacion->cliente->identificacion}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Teléfono: </strong></p>
                <p class="" style="display: inline-block;">{{$cotizacion->cliente->telefono}}</p>
            </div>

            <div class="col s12 m6 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Correo: </strong></p>
                <p class="" style="display: inline-block;">{{$cotizacion->cliente->correo}}</p>
            </div>

            <div class="col s12 no-padding">
                <p class="" style="width: 150px;display: inline-block;"><strong>Dirección: </strong></p>
                <p class="" style="display: inline-block;">{{$cotizacion->cliente->direccion}}</p>
            </div>
        </div>

        <div class="col s12 m10 offset-m1">
            <p class="titulo-modal margin-top-40">Detalles de la cotización @if($cotizacion->estado == "generada") - <span style="font-size: small;color: #5a5a5a;">Vencimiento: {{date("Y-m-d H:i",strtotime("+".$cotizacion->dias_vencimiento." days",strtotime($cotizacion->created_at)))}}</span>@endif</p>
            @if($cotizacion->productosHistorial->count())
                <div class="content-table-slide col s12">
                    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
                        <thead>
                            <th>Producto</th>
                            <th>Unidad</th>
                            <th>Cantidad</th>
                            <th>Valor unitario</th>
                            <th>Subtotal</th>
                            @if($editar)
                                <th>Eliminar</th>
                            @endif
</thead>

<tbody>
    @foreach($cotizacion->productosHistorial()->select("productos_historial.*","cotizaciones_productos_historial.cantidad","cotizaciones_productos_historial.id as relacion")->get() as $historial)
        <tr id="elemento_{{$historial->relacion}}">
            <td>{{$historial->producto->nombre}}</td>
            <td>{{substr($historial->producto->unidad->sigla,0,5)}}</td>
            <td>{{$historial->cantidad}}</td>
            <?php
                $valor =  $historial->precio_costo_nuevo+(($historial->precio_costo_nuevo*$historial->utilidad_nueva)/100);
                $subtotal += $valor * $historial->cantidad;
                $iva_aux = (($valor * $historial->iva_nuevo)/100);
                $iva += $iva_aux * $historial->cantidad;
                $valor += $iva_aux;
            ?>
            <td>{{"$ ".number_format($valor,2,',','.')}}</td>
            <td>{{"$ ".number_format(($valor * $historial->cantidad),2,',','.')}}</td>
            @if($editar)
                <td><a style="cursor: pointer;" onclick="quitarProductoDetalle({{$cotizacion->id.",".$historial->relacion}},this)"><i class="fa fa-trash red-text"></i></a></td>
            @endif
</tr>
@endforeach
</tbody>
</table>
</div>

<div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div>

<div class="col s12 right-align">
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="subtotal_cotizacion">{{"$ ".number_format(round($subtotal),0,',','.')}}</span></p>
    </div>
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">IVA:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="iva_cotizacion">{{"$ ".number_format(round($iva),0,',','.')}}</span></p>
    </div>
    <div class="col s12">
        <strong class="no-margin" style="display: inline-block !important;">Total a pagar:</strong>
        <p class="no-margin" style="display: inline-block !important; width: 150px;"><span id="total_cotizacion">{{"$ ".number_format(round($subtotal+$iva),0,',','.')}}</span></p>
    </div>
</div>

@if($cotizacion->observaciones != "")
    <div class="col s12">
        {!! Form::label("observaciones","Observaciones") !!}
        <p>{!!$cotizacion->observaciones !!}</p>
    </div>
@endif

<?php
$admin =  $usuario->userAdminId();
?>
@else
<p class="col s12 center">Sin resultados</p>
@endif

<div class="col s12 center margin-top-40" id="">
@if($facturar)
<a class="btn waves-effect waves-light blue-grey darken-2" onclick='facturarCotizacion()'>Facturar</a>
@endif
<a class="btn waves-effect waves-light blue-grey darken-2" onclick='javascript:window.open("{{url("/cotizacion/imprimir/".$cotizacion->id)}}", "_blank", "menubar=no");'>Imprimir</a>
</div>
</div>
</div>
@include('cotizaciones.factura_carta')

@if($editar)
    <div id="modal-eliminar-producto-detalle" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este producto de la cotización?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-producto-detalle">
                <a href="#!" class="red-text btn-flat" onclick="javascript: quitarProductoDetalle(null,null,false)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>
        </div>
    </div>

    <div id="modal-crear-historial" class="modal modal-fixed-footer modal-small" style="min-height: 60%;">
        <div class="modal-content">
            <p class="titulo-modal">Crear Historial</p>
            @include('templates.mensajes',["id_contenedor"=>"historial"])
            <div class="row">
                @include('cotizaciones.form_historial',["cotizacion",$cotizacion])
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-producto-detalle">
                <a href="#!" class="cyan-text btn-flat" onclick="guardarHistorial();" >Guardar</a>
                <a href="#!" class="modal-close btn-flat">Cancelar</a>
            </div>
        </div>
    </div>
@endif


@if($facturar)
    <div id="modal-pagar-cotizacion" class="modal modal-fixed-footer modal-sm" style="height: 400px !important;min-height: 400px;">
        <div class="modal-content">
            <p class="titulo-modal">
                Pagar
                @if(\Illuminate\Support\Facades\Auth::user()->plan()->puntos == "si")
                    <a href="#" class="tooltipped" data-position="bottom" data-delay="50" data-tooltip="Redimir puntos" onclick="javascript: showPuntosClienteCotizacion();" style="display: inline;margin-left: 32em;" id="btn-redimir-puntos"><i class="fa fa-credit-card-alt green-text fa-2x" aria-hidden="true"></i></a>
                @endif
            </p>
            <div class="row">
                @include("templates.mensajes",["id_contenedor"=>"modal-pagar"])
                <div class="col s12 m6">
                    <strong>Total factura </strong>
                    <p id="total-pagar-modal-cotizacion">{{"$ ".number_format(round($subtotal+$iva),0,',','.')}}</p>
                </div>
                <div id="puntos-redimidos" class="col s12 m6 hide">
                    <strong>Puntos redimidos </strong><br>
                    <p id="total-puntos-modal-cotizacion">$ 0</p>
                </div>
                <div class="col s12 m6">
                    <strong>Total a pagar </strong>
                    <p id="total-pagar-neto-cotizacion">{{"$ ".number_format(round($subtotal+$iva),0,',','.')}}</p>
                </div>
                <div class="col s12 m6">
                    <strong>Efectivo </strong>
                    {!!Form::text("efectivo-modal-cotizacion",null,["id"=>"efectivo-modal-cotizacion","maxlength"=>"10","class"=>"num-entero"])!!}
                </div>

                <div class="col s12 m6">
                    <div class="col s12 m6">
                        <strong>Regreso</strong>
                        <p id="regreso-modal-cotizacion">$ 0</p>
                    </div>
                    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Descuentos","facturas","inicio"))
                        <div class="col s12 m6">
                            <strong class="red-text">Descuento</strong>
                            <p id="descuento-modal-cotizacion">$ {{$subtotal+$iva}}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-pagar-modal">
                <a class="green-text btn-flat" onclick="javascript: validPagarCotizacion();">Realizar pago</a>
                <a class="green-text btn-flat" onclick="javascript: guardarFacturaCotizacion()">Omitir calculo</a>
                <a class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-pagar-modal">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>

    <div id="modal-puntos-cotizacion" class="modal modal-fixed-footer modal-sm" style="height: 400px !important;min-height: 400px;">
        <div class="modal-content">
            <p class="titulo-modal">Estado de puntos del cliente</p>
            <div class="col s12 cyan white-text">
                Por favor exija el documento de identidad original y cotege los datos correspondientes
            </div>
            <div class="col s12">
                <p id="texto-puntos"><strong>Cliente: </strong> {{ $cotizacion->cliente->nombre . " - " . $cotizacion->cliente->tipo_identificacion .": ". $cotizacion->cliente->identificacion}}</p>
                <p id="texto-total-factura"><strong>Total factura: </strong>$ {{number_format(round($subtotal+$iva),0,',','.')}}</p>
                <p id="texto-valor-puntos"><strong>Valor puntos: </strong>$ {{number_format($cotizacion->cliente->valor_puntos,0,',','.')}}</p>
            </div>

            <div class="row">
                <div class="col s12 m6 input-field">
                    {!! Form::select("cotizacion-redimir",["1"=>"Todo","2"=>"Parcial"],null,["id"=>"cotizacion-redimir"]) !!}
                    {!! Form::label("cotizacion-redimir","Redimir") !!}
                </div>
                <div class="col s12 m6 input-field">
                    <?php
                        $max_redimir = 0;
                        if(($subtotal + $iva) > $cotizacion->cliente->valor_puntos){
                            $max_redimir = $cotizacion->cliente->valor_puntos;
                        }else{
                            $max_redimir = $subtotal + $iva;
                        }
                    ?>
                    {!! Form::text("valor",$max_redimir,["id"=>"valor","class"=>"num-real","readonly"=>"readonly"]) !!}
                    {!! Form::label("valor","Valor a redimir",["class"=>"active"]) !!}
                </div>
            </div>

        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-pagar-modal">
                <a class="green-text btn-flat" onclick="redimirEnFacturacion()">Redimir</a>
                <a class="btn-flat" onclick="javascript:$('#modal-puntos').closeModal();$('#modal-pagar').openModal();">Cancelar</a>
            </div>
        </div>
    </div>

    <div id="datos_token_cotizacion" class="hide" style="display: none;">
        <p style="text-align: center !important;width: 100% !important;font-weight: bold;" id="nombre_negocio"></p>
        <p style="text-align: center !important;width: 100% !important;">_______________________</p>
        <p><strong>FECHA DE SOLICITUD: </strong> <span id="fecha">18/05/1993</span></p>
        <p><strong>TOKEN: </strong> <span id="token">40890894984-408</span></p>
        <p><strong>VALOR: </strong> <span id="valor">$ 5.000</span></p>
        <p><strong>VÀLIDO HASTA: </strong> <span id="valido">18/05/1993 21:00</span></p>
        <p><strong>FIRMA: </strong>____________________</p>
    </div>
@endif

<div id="modal-historial" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Historial</p>
        @include('cotizaciones.lista_historial',["historiales"=>$historiales])
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-eliminar-producto-detalle">
            @if($editar)
                <a href="#modal-crear-historial" class="cyan-text btn-flat modal-trigger" >Agregar</a>
            @endif
            <a href="#!" class="modal-close btn-flat">Cerrar</a>
        </div>
    </div>
</div>
@endsection



@section('js')
@parent

<script src="{{asset('js/cotizacionAction.js')}}"></script>

    <script>
        $(function () {
            setTotalCotizacion({{$subtotal+$iva}})
            setCliente({{$cotizacion->cliente->id}});
            setPuntosCliente({{$cotizacion->cliente->valor_puntos}});
            setCotizacion({{$cotizacion->id}});
        })
    </script>
@stop
