<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('js')
    @parent
    <script src="{{asset('js/unidades/unidadesAction.js')}}"></script>

    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Unidades","configuracion"))
            setPermisoEditar(true);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Unidades","configuracion"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop

@section('contenido')
@if(!isset($filtro))
<?php $filtro = ""; ?>
@endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Unidades","configuracion"))
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/unidades/create')}}"><i class="fa fa-plus"></i></a>
    @endif

    <p class="titulo">Unidades</p>

    <div id="contenedor-lista-unidades" class="col s12 content-table-slide">
        @include('unidades.lista')
    </div>
</div>

@endsection
