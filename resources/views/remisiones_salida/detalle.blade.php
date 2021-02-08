<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div id="contenedor-detalle-compra" class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        @include('remisiones_salida.detalles.index')
    </div>
@endsection

@section('js')
    @parent
    <script type="application/javascript" src="{{asset('js/compras/funciones.js')}}"></script>
    <script src="{{asset('js/compras/devolutionAction.js')}}"></script>

@endsection