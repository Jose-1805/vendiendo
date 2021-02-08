<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","almacenes","inicio") && (Auth::user()->plan()->n_almacenes == 0 || Auth::user()->plan()->n_almacenes > Auth::user()->countAlmacenesAdministrador()))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/almacen/crear')}}"><i class="fa fa-plus"></i></a>
        @endif

        <p class="titulo">Almacenes</p>
        @if(!(Auth::user()->plan()->n_almacenes == 0 || Auth::user()->plan()->n_almacenes > Auth::user()->countAlmacenesAdministrador()))
                <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                    <i class='fa fa-close btn-cerrar-confirmacion'></i>
                    <ul>
                        <li>No es posible crear más almacenes, su plan alcanzó el tope máximo de almacenes permitidos.</li>
                    </ul>
                </div>
        @endif
        <div id="mapas" class="col s6"></div>
        <div id="contenedor-lista-almacenes" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"almacenes"])
            <div id="lista-almacenes" class="col s12 content-table-slide">
                @include('almacenes.lista')
            </div>
        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/almacenes/index.js')}}"></script>

    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","almacenes","inicio"))
        <script>
            setPermisoEditar(true);
        </script>
    @endif
@stop