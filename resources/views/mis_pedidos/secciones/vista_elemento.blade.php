<?php
    if(!isset($validacion_stock)){
        $admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
        $plan = $admin->plan();
        if($plan->validacion_stock == "si")$validacion_stock = true;
        else $validacion_stock = false;
    }

    if(!isset($vista_buscar))$vista_buscar = false;
    $stock = $pr->stock;
    if($pr->tipo_producto == 'Compuesto' && $pr->omitir_stock_mp == 'si'){
        $stock = $pr->DisponibleOmitirStockMp();
    }
?>
@if($vista_buscar)
    <li class="elemento-buscar" id="producto-buscar-{{$pr->id}}" data-producto="{{$pr->id}}">
        <p class="font-small">
            <span class="font-small circle waves-effect white-text center align cantidad-buscar {{$class_color}}" >0</span>
            {{$pr->nombre}}
            <span class="circle waves-effect waves-light right white-text center align pink darken-1 menos-buscar" >-</span>
            <span class="circle waves-effect waves-light right white-text center align green darken-2 mas-buscar" >+</span>
        </p>
        <?php
            if(Auth::user()->bodegas == 'si'){
                $almacen = Auth::user()->almacenActual();
                $historial = $pr->ultimoHistorialUtilidadAlmacen($almacen->id);
                $precio_venta = $pr->precio_costo + (($pr->precio_costo * $pr->iva)/100);
                $precio_venta = $precio_venta + ($precio_venta * $historial->utilidad)/100;
            }else{

                $precio_venta = $pr->precio_costo + (($pr->precio_costo * $pr->iva)/100);
                $precio_venta = $precio_venta + ($precio_venta * $pr->utilidad)/100;
            }
        ?>
        <input type="hidden" class="pr_id" value="{{$pr->id}}">
        <input type="hidden" class="pr_nombre" value="{{$pr->nombre}}">
        <input type="hidden" class="pr_stock" value="{{$stock}}">
        <input type="hidden" class="pr_precio" value="{{$precio_venta}}">
    </li>
@else
    <?php
        $new_class_color = "grey";
        $class_elemento_cantidad = "";
        if($stock > 0 || !$validacion_stock){
            $new_class_color = $class_color;
            $class_elemento_cantidad = "trigger-teclado-numerico elemento-cantidad";
        }
    ?>
    <div class="elemento col s12 m12 l6">
    @if($stock <= 0 && $validacion_stock)
        <div class="col s12 agotado valign-wrapper" style=""><p class="valign ">AGOTADO</p></div>
    @endif
    <div class="card col s12 contenido-elemento" id="elemento-{{$pr->id}}" data-producto="{{$pr->id}}">
        <div style="width: 35px; height: 100%; float: right; ">
            <div class="{{$new_class_color}} {{$class_elemento_cantidad}}" style="color:white; width: 35px; height: 35px; border-radius: 35px; position: absolute; text-align: center; margin:5px 5px 0px -5px; border:1px solid white; padding-top: 6px; float: right;">0</div>
        </div>

        <div class="col s12 m12 l6 info-producto">
            <strong class="no-margin nombre truncate {{$class_color_text}}" title="{{$pr->nombre .' ************** '. $pr->descripcion}}">{{$pr->nombre}}</strong>
            <p class="no-margin descripcion font-small truncate margin-top-5" title="{{$pr->nombre .' ************** '. $pr->descripcion}}">{{$pr->descripcion}}</p>
            <p class="margin-top-10 col s12 no-padding blue-text mas-informacion" style="cursor: pointer;"><i class="fa fa-info-circle"></i><span style="font-size: 12px; margin-left: 5px;">Más info...</span></p>
            <?php
                if(Auth::user()->bodegas == 'si'){
                    $almacen = Auth::user()->almacenActual();
                    $historial = $pr->ultimoHistorialUtilidadAlmacen($almacen->id);
                    $precio_venta = $pr->precio_costo + (($pr->precio_costo * $pr->iva)/100);
                    $precio_venta = $precio_venta + ($precio_venta * $historial->utilidad)/100;
                }else{
                    $precio_venta = $pr->precio_costo + (($pr->precio_costo * $pr->iva)/100);
                    $precio_venta = $precio_venta + ($precio_venta * $pr->utilidad)/100;
                }
            ?>
            <p class="col s12 no-margin no-padding {{$class_color_text}} margin-top-20 margin-bottom-20 truncate font-large valor" style="line-height: 25px;">$ {{number_format($precio_venta,2,',','.')}}</p>
            @if($stock > 0 || !$validacion_stock)
                <input type="hidden" class="pr_id" value="{{$pr->id}}">
                <input type="hidden" class="pr_nombre" value="{{$pr->nombre}}">
                <input type="hidden" class="pr_stock" value="{{$stock}}">
                <input type="hidden" class="pr_precio" value="{{$precio_venta}}">
            @endif
        </div>
        <div class="col s12 m12 l6 contenedor-img-producto center-align" style="word-wrap: break-word;overflow: hidden;border-right: 7px solid white;">
            @if($pr->imagen !='')
                <img class="img-producto" style="max-height: 180px; height: 180px;" src="{{url(/*/app/public/*/'img/productos/'.$pr->id.'/'.$pr->imagen)}}">
            @else
                <img class="img-producto" style="max-height: 180px; height: 180px;opacity: .25" src="{{url(/*/app/public/*/'img/sistema/LogoVendiendo.png')}}">
            @endif
        </div>

        <div class="hide-on-small-only col s12 padding-top-20 botones-control-pedidos" style="" >
            <div class="botones-elemento {{$new_class_color}} col s8 offset-s2 m6 offset-m3 l4 offset-l4">
                @if($stock > 0 || !$validacion_stock)
                    <a class="col s6 white-text no-padding waves-effect waves-light menos"><span class="left">-</span></a>
                    <a class="col s6 white-text no-padding waves-effect waves-light mas"><span class="right">+</span></a>
                @else

                    <a class="col s6 white-text no-padding " style="cursor: no-drop;"><span class="left">-</span></a>
                    <a class="col s6 white-text no-padding " style="cursor: no-drop;"><span class="right">+</span></a>
                @endif
            </div>
            <div class="col s12">
                <div class="logo-vendiendo-elemento {{$new_class_color}}" >

                    <svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                         viewBox="0 0 90 90" style="enable-background:new 0 0 90 90; margin-left: -1px;" xml:space="preserve">
                                <style type="text/css">
                                    .st0{fill:#FFFFFF;}
                                    .st1{opacity:0.3;fill:#FFFFFF;enable-background:new    ;}
                                </style>
                        <g>
                            <g>
                                <g>
                                    <path class="st0" d="M74.4,38.7c0,0-0.9,2.2-2.3,4.1c-0.9,1.3-3.9,3.9-8.9,3.9H38.4c0,0-5.3-17,3.8-17c5.8,0,9.2,11.7,17.2,11.7 c6.1,0,8.8-2.7,9.7-6.3l3-11.7H26.7l8.8,31.1c0.3,0.6,1.1,1.7,2.5,1.7h31.7c0,6.3-4.2,6.3-7,6.3H37.3c-6.6,0-7.7-6.4-7.7-6.4 l-10-35.8h-4.5c-3,0-5.5-1.7-5.5-6.4L20.7,14c3,0,3.9,2.5,3.9,2.5l0.2,0.8h55.6L74.4,38.7z"/>
                                    <path class="st1" d="M74.4,38.7c0,0-0.9,2.2-2.3,4.1c-0.9,1.3-3.9,3.9-8.9,3.9H38.4c0,0-5.3-17,3.8-17c5.8,0,9.2,11.7,17.2,11.7 c6.1,0,8.8-2.7,9.7-6.3l3-11.7H26.7l8.8,31.1c0.3,0.6,1.1,1.7,2.5,1.7h31.7c0,6.3-4.2,6.3-7,6.3H37.3c-6.6,0-7.7-6.4-7.7-6.4 l-10-35.8h-4.5c-3,0-5.5-1.7-5.5-6.4L20.7,14c3,0,3.9,2.5,3.9,2.5l0.2,0.8h55.6L74.4,38.7z"/>
                                </g>
                                <g>
                                    <ellipse class="st0" cx="69.2" cy="70.4" rx="5" ry="4.8"/>
                                    <ellipse class="st1" cx="69.2" cy="70.4" rx="5" ry="4.8"/>
                                </g>
                                <g>
                                    <ellipse class="st0" cx="39" cy="70.4" rx="5" ry="4.8"/>
                                    <ellipse class="st1" cx="39" cy="70.4" rx="5" ry="4.8"/>
                                </g>
                            </g>
                        </g>
                                </svg>


                </div>

                <!--<div style="margin: 0 auto; width: 48px;" class="blue">
                    <img style="margin-left: -51px;margin-top: -50px;" src="{{asset('img/sistema/SombraBtn.png')}}">
                </div>-->
            </div>
        </div>
    </div>
</div>
@endif