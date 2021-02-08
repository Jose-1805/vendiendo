<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-50" style="margin-top: 85px">
        <p class="titulo">{{$action}} almacén</p>
        @include('almacenes.form',['action'=>$action])
    </div>
@endsection

@section('js')
    @parent
    <script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?v=3&key=AIzaSyBJWGjtzGlOPTzVUETckRMHxqbOHNN8Oz4"></script>
    <script src="{{asset('js/almacenes/action.js')}}"></script>
    <script>
        $(function () {

            setAction('{{$action}}');
            @if(isset($almacen))
                setLatitud({{$almacen->latitud}});
                setLongitud({{$almacen->longitud}});
            @endif
            init();
        })
    </script>
@stop