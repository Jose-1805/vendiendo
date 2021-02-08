<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
    <p class="titulo">Busqueda por proveedores</p>
    <div class="input-field col m6 l4 right hide-on-small-only" style="margin-top: -70px;">
        <input type="text" name="busqueda" id="busqueda_pr" placeholder="Buscar" value="{{$filtro}}" style="border: none !important;">
        <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar_pr" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
    </div>

    <div class="input-field col s12 hide-on-med-and-up" >
       <input type="text" name="busqueda2" id="busqueda_pr_2" placeholder="Buscar" value="{{$filtro}}">
        <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar_pr" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
    </div>


    @if($promocionHoy)
        <a href="#" class="row"><span class="badge-vendiendo show-producto-proveedor col s12" data-producto="{{$promocionHoy->producto->id}}" style="right: auto !important; cursor:pointer;padding: 5px 5px !important;"><strong>Promoción: </strong>{{$promocionHoy->descripcion}}</span></a>
        <?php
            $prod = $promocionHoy->producto;
        ?>
        <div id="info-prod-{{$prod->id}}" class="hide">
            <p class="titulo">{{$prod->nombre}}</p>
            <div class="col s12 center-align">
                @if($prod->imagen !='')
                    {{-- Html::image(url("/app/public/img/productos/".$producto->id."/".$producto->imagen), $alt="", $attributes = array('style'=>'max-height: 200px;')) --}}
                    {!! Html::image(url("/img/productos/".$prod->id."/".$prod->imagen), $alt="", ["style"=>"max-height:280px !important;"]) !!}
                @endif
            </div>
            <p class="col s12">{{$prod->descripcion}}</p>
            @if($prod->tienePromocionFecha(date("Y-m-d")))
                <p class="col s12" ><strong>Promoción: </strong>{{$prod->promocionHoy()->descripcion}}</p>
                <p class="col s12" style="margin-top: -10px;"><strong>Valor: </strong>$ {{number_format($prod->promocionHoy()->valor_actual,2,',','.')}}</p>
                <p class="col s12" style="margin-top: -10px;"><strong>Hoy: </strong>$ {{number_format($prod->promocionHoy()->valor_con_descuento,2,',','.')}}</p>

            @endif
            <p class="col s12"><strong>Proveedor: </strong>{{$prod->usuarioProveedor->nombres." ".$prod->usuarioProveedor->apellidos}}</p>
            @if(!$prod->tienePromocionFecha(date("Y-m-d")))
                <p class="col s12" style="margin-top: -10px;"><strong>Precio: </strong>$ {{number_format($prod->precio_costo,2,',','.')}}</p>
            @endif
            <p class="col s12" style="margin-top: -10px;"><strong>Unidad:</strong> {{$prod->unidad->nombre}}</p>
            <p class="col s12" style="margin-top: -10px;"><strong>Medida de venta: </strong> {{$prod->medida_venta}}</p>
        </div>
    @endif

    @include("templates.mensajes",["id_contenedor"=>"producto"])
    <div class="row padding-bottom-50">
        <div id="contenedor-lista-productos" class="col s12 content-table-slide">
            @if(count($productos))
                @include('productos.lista_busqueda_proveedor')
            @endif
        </div>
        @if(count($productos))
            <p class="col s12 center-align hide" id="busqueda-proveedor-sin-resultados">No se han encontrado productos relacionados con su categoría objetivo</p>
        @else
            <p class="col s12 center-align" id="busqueda-proveedor-sin-resultados">No se han encontrado productos relacionados con su categoría objetivo</p>
        @endif
    </div>

</div>
<div style="position: fixed; right: 10px; bottom: 10px; z-index: 1000;">
    <a href="#" class="btn blue-grey darken-2" onclick="cargarProductos();">Cargar más</a>
    <a href="#" class="btn blue-grey darken-2" onclick="moveTop();">Subir</a>
</div>
<div id="modal-info-producto-proveedor" class="modal modal-fixed-footer">
    <div class="modal-content scroll-style" id="info-producto-proveedor">
    </div>
    <div class="modal-footer" style="height: auto;">
            <a href="#!" class="modal-action modal-close btn-flat right">Cerrar</a>
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pedido","Búsqueda en proveedor","inicio"))
                {!! Form::text("cantidad",1,["id"=>"cantidad","placeholder"=>"Cantidad","class"=>"left num-entero","style"=>"width:100px;margin-left:30px;"]) !!}
                <a href="#!" class="left btn blue-grey darken-2" onclick="agregarProductoCarrito(this)"><i class="fa fa-cart-plus"></i></a>
            @endif

    </div>
</div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/productos/busqueda_proveedor.js')}}"></script>
    @if(isset($skip))
        <script>
            skip = {{$skip}};
        </script>
    @endif
@stop