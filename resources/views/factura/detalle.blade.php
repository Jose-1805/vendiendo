<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <?php $color = "red-text"; ?>
    @if($factura->estado == "Pagada")
        <?php $color = "green-text"; ?>
    @endif
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Detalle factura - {{$factura->numero}}<i class="font-medium {{$color}}"> ({{"Estado: ".$factura->estado}})</i>
            @if($factura->dias_credito > 0)<i class="fa fa-list-alt waves-effect waves-light black-text right tooltipped" id="btn-historial-abonos" data-position="bottom" data-delay="50" data-tooltip="Historial de abonos" style="cursor: pointer;margin-top: -5px;margin-left: 10px;line-height: 50px;" onclick="abrirModalAbonos('{{$factura->id }}', '{{$factura->estado}}')"></i>@endif
        </p>
        @include("templates.mensajes",["id_contenedor"=>"detalle-factura"])
        {!! Form::hidden("id",$factura->id,["id"=>"id"]) !!}
        <div id="info-cliente" class="col s12 m4 l3 scroll-style" style="border: 2px solid rgba(0,0,0,.3);height: 100%;overflow-y: auto; font-size: small !important;">
            <p class="titulo-modal">Datos del cliente</p>
            <div class="col s12 m12 no-padding">
                <p class="no-margin" style="width: 150px;display: inline-block;"><strong>Nombre: </strong></p>
                <p class="no-margin" style="display: inline-block;">{{$factura->cliente->nombre}}</p>
            </div>

            <div class="col s12 m12 no-padding hide">
                <p class="no-margin" style="width: 150px;display: inline-block;"><strong>Tipo identificación: </strong></p>
                <p class="no-margin" style="display: inline-block;">{{$factura->cliente->tipo_identificacion}}</p>
            </div>

            <div class="col s12 m12 no-padding">
                <p class="no-margin " style="width: 150px;display: inline-block;"><strong>Identificación: </strong></p>
                <p class="no-margin" style="display: inline-block;">{{$factura->cliente->identificacion}}</p>
            </div>

            <div class="col s12 m12 no-padding">
                <p class="no-margin" style="width: 150px;display: inline-block;"><strong>Teléfono: </strong></p>
                <p class="no-margin" style="display: inline-block;">{{$factura->cliente->telefono}}</p>
            </div>

            <div class="col s12 m12 no-padding">
                <p class="no-margin" style="width: 150px;display: inline-block;"><strong>Correo: </strong></p>
                <p class="no-margin" style="display: inline-block;">{{$factura->cliente->correo}}</p>
            </div>

            <div class="col s12 m12 no-padding">
                <p class="no-margin" style="width: 150px;display: inline-block;"><strong>Dirección: </strong></p>
                <p class="no-margin" style="display: inline-block;">{{$factura->cliente->direccion}}</p>
            </div>
             @if($factura->observaciones)
                    <div class="col s12 m12 no-padding" >
                        <strong>Observaciones</strong>
                        <p>{{$factura->observaciones}}</p>
                    </div>
                @endif
            
            <?php
                $admin =  \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
                ?>
                @if($admin->datos_cliente_vendedor == "si")
                    <div class="divider col s12 grey lighten-1" style="/*margin-top: -30px !important;*/"></div>
                    <div class="col s12 m12 no-padding">
                        <p class="" style="width: 150px;display: inline-block;"><strong>Atendido por:</strong></p>
                        <p class="" style="display: inline-block;">{{$factura->usuarioCreador->nombres ." ". $factura->usuarioCreador->apellidos}}</p>
                    </div>
                @endif

                <div class="divider col s12 grey lighten-1" style="/*margin-top: -30px !important;*/"></div>
                <div class="col s12 right-align">
                    <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
                    <p class="no-margin" style="display: inline-block !important; width: 150px;">{{"$ ".number_format(round($factura->subtotal),0,',','.')}}</p>
                </div>
                <div class="col s12 right-align">
                    <strong class="no-margin" style="display: inline-block !important;">IVA:</strong>
                    <p class="no-margin" style="display: inline-block !important; width: 150px;">{{"$ ".number_format(round($factura->iva),0,',','.')}}</p>
                </div>
                @if($factura->descuento > 0)
                    <div class="col s12 right-align">
                        <strong class="no-margin col s12 m6" style="">Descuento:</strong>
                        <p class="no-margin col s12 m6" style=" ">{{"$ ".number_format(round($factura->descuento),0,',','.')}}</p>
                    </div>
                    <div class="col s12 right-align margin-bottom-40">
                        <strong class="no-margin" style="display: inline-block !important;">TOTAL:</strong>
                        <p class="no-margin" style="display: inline-block !important; width: 150px;">{{"$ ".number_format(round($factura->iva + $factura->subtotal - $factura->descuento),0,',','.')}}</p>
                    </div>
                @else
                    <div class="col s12 right-align margin-bottom-40">
                        <strong class="no-margin" style="display: inline-block !important;">TOTAL:</strong>
                        <p class="no-margin" style="display: inline-block !important; width: 150px;">{{"$ ".number_format(round($factura->iva + $factura->subtotal),0,',','.')}}</p>
                    </div>
                @endif



                @if($valor_puntos>0)
                        <div class="col s12 right-align">
                            <strong class="no-margin col s12 m6" style="">Vlr. Puntos:</strong>
                            <p class="no-margin col s12 m6" style=" ">{{"$ ".number_format(round($valor_puntos),0,',','.')}}</p>
                        </div>
                @endif

                <?php
                    $valor_medios_pago = 0;
                    $medios_pago = $factura->tiposPago()->select('facturas_tipos_pago.*','tipos_pago.nombre')->get();
                ?>

                @foreach($medios_pago as $mp)
                    <div class="col s12 right-align">
                        <strong class="no-margin col s12 m6 truncate"
                                title="{{$mp->nombre}}
                                    @if($mp->codigo_verificacion)
                                     {{' ('.$mp->codigo_verificacion.')'}}
                                    @endif">
                                        {{$mp->nombre}}
                                        @if($mp->codigo_verificacion)
                                            {{' ('.$mp->codigo_verificacion.')'}}
                                        @endif
                            :</strong>
                        <p class="no-margin col s12 m6" style=" ">{{"$ ".number_format(round($mp->valor),0,',','.')}}</p>
                        <?php $valor_medios_pago += $mp->valor; ?>
                    </div>
                @endforeach

                @if(round(($factura->subtotal+$factura->iva) - ($factura->descuento + $valor_puntos + $valor_medios_pago)) > 0)
                    <div class="col s12 right-align">
                        <strong class="no-margin col s12 m6" style="">Efectivo:</strong>
                        <p class="no-margin col s12 m6" style=" ">{{"$ ".number_format(round(($factura->subtotal+$factura->iva) - ($factura->descuento + $valor_puntos + $valor_medios_pago)),0,',','.')}}</p>
                    </div>
                @endif
                @if($factura->descuento > 0)
                    <div class="col s12 right-align margin-bottom-40">
                        <strong class="no-margin col s12 m6">Total recibido:</strong>
                        <p class="no-margin col s12 m6">{{"$ ".number_format(round($factura->iva + $factura->subtotal - $factura->descuento),0,',','.')}}</p>
                    </div>
                @else
                    <div class="col s12 right-align margin-bottom-40">
                        <strong class="no-margin col s12 m6">Total recibido:</strong>
                        <p class="no-margin col s12 m6">{{"$ ".number_format(round($factura->iva + $factura->subtotal),0,',','.')}}</p>
                    </div>
                @endif

                <div class="divider col s12 grey lighten-1" style="/*margin-top: -30px !important;*/"></div>
                <div class="col s12 center margin-top-40" id="">
                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Anular","facturas","inicio") && ($factura->estado == "Pagada" || $factura->estado == "Pendiente por pagar") && \Illuminate\Support\Facades\Auth::user()->cajaAsignada())
                    <a class="btn waves-effect waves-light blue-grey darken-2 btn-small margin-top-10 modal-trigger" href="#modal-anular-factura">Anular</a>
                @endif
                <a class="btn waves-effect waves-light blue-grey darken-2 btn-small margin-top-10" onclick='javascript:imprimir("factura-pos")' id="btn-imprimir-pos">Imprimir</a>
                <a class="btn waves-effect waves-light blue-grey darken-2 btn-small margin-top-10" onclick='javascript:window.open("{{url("/factura/imprimir/".$factura->id)}}", "_blank", "menubar=no");'>Imprimir a color</a>
                <a class="btn waves-effect waves-light blue-grey darken-2 btn-small margin-top-10" href="{{url("/factura/create")}}">Terminar</a>
            </div>
        </div>

       <div class="col s12 m8 l9 scroll-style" id="content-detalle-factura" style="border: 2px solid rgba(0,0,0,.3);height: 100%;overflow-y: auto;">
            <p class="titulo-modal margin-top-20">Detalles de la factura</p>
            @if(
                (Auth::user()->bodegas == 'no' && $factura->productosHistorial->count())
            ||  (Auth::user()->bodegas == 'si' && $factura->productosHistorialUtilidad->count())
            )
               <div class="content-table-slide col s12">
                    <table class="table highlight centered margin-bottom-40" style="min-width: 500px;">
                        <thead>
                        <th>Producto</th>
                        <th>Unid.</th>
                        <th>Cant.</th>
                        <th>Vlr unit</th>
                        <th>Subtotal</th>
                        </thead>

                        <tbody>
                        <?php
                            if(Auth::user()->bodegas == 'si'){
                                $historiales = $factura->productosHistorialUtilidad()->select("historial_utilidad.*","facturas_historial_utilidad.cantidad")->get();
                            }else{
                                $historiales = $factura->productosHistorial()->select("productos_historial.*","facturas_productos_historial.cantidad")->get();
                            }
                        ?>
                        @foreach($historiales as $historial)
                            <tr>
                                <td>{{$historial->producto->nombre}}</td>
                                <td>{{substr($historial->producto->unidad->sigla,0,5)}}</td>
                                <td>{{$historial->cantidad}}</td>
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
                                <td>{{"$ ".number_format($valor,2,',','.')}}</td>
                                <td>{{"$ ".number_format(($valor * $historial->cantidad),2,',','.')}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>





            @else
                <p class="col s12 center">Sin resultados</p>
            @endif


            <div class="progress hide" id="progress-detalle-factura">
                <div class="indeterminate"></div>
            </div>
        </div>
    </div>
    @include('factura.factura_carta')
    @include('factura.factura_pos')

@endsection

@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Anular","facturas","inicio") && \Illuminate\Support\Facades\Auth::user()->cajaAsignada())
    <div id="modal-anular-factura" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Anular</p>
            @include("templates.mensajes",["id_contenedor"=>"anular-factura"])
            <p>¿Está seguro de anular esta factura?</p>
            <p>Si la factura es anulada, no podrá cambiar su estado posteriormente.</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-anular-factura">
                <button class="red-text btn-flat" onclick="javascript: anular()">Aceptar</button>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-anular-factura">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>

    <div id="modal-abonos" class="modal modal-fixed-footer">
        <div class="modal-content">
            <p class="titulo-modal">Abonos</p>
            <div class="" id="load-info-abonos">
                <p class="center-align">Cargando información<i class="fa fa-spin fa-spinner text-cyan"></i></p>
            </div>
            <div class="row hide" id="info-abonos">
            </div>
        </div>

        <div class="modal-footer">
            <div class="col s12 hide" id="contenedor-botones-abonos">
                <a href="#!" class="green-text btn-flat" onclick="javascript: guardarAbono()">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-abonos">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif

@section('js')
    @parent
    <script src="{{asset('js/facturaAction.js')}}"></script>
    <script type="text/javascript">
        ConfirmacionRecarga=false;
        $(document).ready(function(){

            //PARA BLOQUEAR EL BOTON ATRAS DEL NAVEGADOR
            var urlCrearFactura = '{{url('factura')}}';
            var inicio = '{{url('/')}}';
            window.onload = function () {
                if (typeof history.pushState === "function") {
                    history.pushState("jibberish", null, null);
                    if ($("#menu-desplegable").attr('href') === "#")
                        history.pushState("jibberish", null, null);
                    window.onpopstate = function () {
                        history.pushState('newjibberish', null, null);
                        window.location.href = urlCrearFactura;
                    };
                }else {
                    var ignoreHashChange = true;
                    window.onhashchange = function () {
                        if (!ignoreHashChange) {
                            ignoreHashChange = true;
                            window.location.hash = Math.random();
                        }
                        else {
                            ignoreHashChange = false;
                        }
                    };
                }
            }
        });
    </script>
@stop