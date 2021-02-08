
@foreach($productos as $p)
    <div class="col s12 m6 l4 padding-30">
        <div class="card small hoverable" style="height: 250px !important;">
            <div class="card-image waves-effect waves-block waves-light" style="max-height: 70%;min-height: 70%;height: 70%;">
                @if($p->imagen !='')
                    {{-- Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) --}}
                    {!! Html::image(url("/img/productos/".$p->id."/".$p->imagen), $alt="", ["class"=>"show-producto-proveedor","data-producto"=>$p->id]) !!}
                @else
                    {{-- Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) --}}
                    {!! Html::image(url("/img/sistema/LogoVendiendo.png"), $alt="", ["class"=>"show-producto-proveedor","data-producto"=>$p->id]) !!}
                @endif
            </div>
            <div class="card-content" style="max-height: 30%;min-height: 30%;height: 30%; padding-top: 5px !important;">
                <span class="card-title grey-text text-darken-4" style="line-height: 25px !important;font-size: 18px !important;font-weight: 400;">{{$p->nombre}}<i class="material-icons right show-producto-proveedor" style="cursor: pointer;" data-producto="{{$p->id}}">more_vert</i></span>
                <p style="font-size: 12px !important;">$ {{number_format($p->precio_costo,2,',','.')}}
                    @if($p->tienePromocionFecha(date("Y-m-d")))
                            <span class="badge-vendiendo right" ><strong>Hoy: </strong>$ {{number_format($p->promocionHoy()->valor_con_descuento,2,',','.')}}</span>
                    @endif
                </p>
                <p style="font-size: 12px !important;">{{$p->usuarioProveedor->nombres." ".$p->usuarioProveedor->apellidos." (".$p->usuarioProveedor->municipio->nombre." - ".$p->usuarioProveedor->municipio->departamento->nombre.")"}}</p>
            </div>
            <div id="info-prod-{{$p->id}}" class="hide">
                <p class="titulo">{{$p->nombre}}</p>
                <div class="col s12 center-align">
                    @if($p->imagen !='')
                        {{-- Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) --}}
                        {!! Html::image(url("/img/productos/".$p->id."/".$p->imagen), $alt="", ["style"=>"max-height:280px !important;"]) !!}
                    @endif
                </div>
                <p class="col s12">{{$p->descripcion}}</p>

                @if($p->tienePromocionFecha(date("Y-m-d")))
                    <p class="col s12" ><strong>Promoción: </strong>{{$p->promocionHoy()->descripcion}}</p>
                    <p class="col s12" style="margin-top: -10px;"><strong>Valor: </strong>$ {{number_format($p->promocionHoy()->valor_actual,2,',','.')}}</p>
                    <p class="col s12" style="margin-top: -10px;"><strong>Hoy: </strong>$ {{number_format($p->promocionHoy()->valor_con_descuento,2,',','.')}}</p>
                @endif
                <p class="col s12"><strong>Proveedor: </strong>{{$p->usuarioProveedor->nombres." ".$p->usuarioProveedor->apellidos}}</p>
                <p class="col s12" style="margin-top: -10px;"><strong>Celular: </strong>{{$p->usuarioProveedor->telefono}}</p>
                <p class="col s12" style="margin-top: -10px;"><strong>Email: </strong>{{$p->usuarioProveedor->email}}</p>
                <p class="col s12" style="margin-top: -10px;margin-bottom: 40px;"><strong>Ubicación: </strong>{{" (".$p->usuarioProveedor->municipio->nombre." - ".$p->usuarioProveedor->municipio->departamento->nombre.")"}}</p>
                @if(!$p->tienePromocionFecha(date("Y-m-d")))
                    <p class="col s12" style="margin-top: -10px;"><strong>Precio: </strong>$ {{number_format($p->precio_costo,2,',','.')}}</p>
                @endif
                <p class="col s12" style="margin-top: -10px;"><strong>Unidad:</strong> {{$p->unidad->nombre}}</p>
                <p class="col s12" style="margin-top: -10px;"><strong>Medida de venta: </strong> {{$p->medida_venta}}</p>
            </div>
        </div>
    </div>
@endforeach