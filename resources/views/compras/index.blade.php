<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","compras","inicio") && \App\Models\Caja::abierta())
            @if(Auth::user()->plan()->n_compras == 0 || Auth::user()->plan()->n_compras > Auth::user()->countComprasAdministrador())
                @if(Auth::user()->bodegas == 'si' && !\App\Models\Bodega::permitidos()->get()->count())
                    <p><strong>Nota: </strong>para crear compras primero debe registrar una bodega</p>
                @else
                    <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/compra/create')}}"><i class="fa fa-plus"></i></a>
                @endif
            @endif
        @endif
        <p class="titulo">Compras</p>
        @if(!(Auth::user()->plan()->n_compras == 0 || Auth::user()->plan()->n_compras > Auth::user()->countComprasAdministrador()))

            <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                <i class='fa fa-close btn-cerrar-confirmacion'></i>
                <ul>
                    <li>No es posible crear más compras, su plan alcanzó el tope máximo de compras permitidas.</li>
                </ul>
            </div>
        @endif
        <div class="input-field col m6 l4 right hide" style="margin-top: -70px;">
            <input type="text"  name="busqueda" id="busqueda" placeholder="Buscar" value="{{$filtro}}" style="border: none !important;">
            <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
        </div>

        <div class="input-field col s12 hide-on-med-and-up hide" >
            <input type="text" name="busqueda2" id="busqueda2" placeholder="Buscar" value="{{$filtro}}">
            <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
        </div>
        <div id="contenedor-lista-facturas" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"compras"])
            <div id="lista-compras">
                @include('compras.lista')
            </div>
        </div>
        <input type="hidden" id="efectivo_caja" value="{{ $efectivo_caja->efectivo_final }}">

    </div>
@endsection


@section('js')
    @parent

    <script>
        @if(Auth::user()->permitirFuncion("Editar","compras","inicio") && \App\Models\Caja::abierta())
            setPermisoEditarCompra(true);
        @else
            setPermisoEditarCompra(false);
        @endif
    </script>
@stop