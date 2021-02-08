<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('js')
    @parent
    <script src="{{asset('js/categoriaAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Categorias","configuracion"))
            setPermisoEditar(true);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Categorias","configuracion"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop

@section('contenido')
@if(!isset($filtro))
<?php $filtro=""; ?>
@endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","Categorias","configuracion"))
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped modal-trigger agregar-elemento-tabla" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="#modal-accion-categoria"><i class="fa fa-plus"></i></a>
    @endif
            <p class="titulo">Categorías</p>
            <div id="contenedor-lista-productos" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"categorias"])
            <div id="lista-categorias" class="content-table-slide col s12">
                @include('categoria.lista')
            </div>
        </div>

    </div>

<div id="modal-accion-categoria" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <div id="contenido-accion-categoria">
            @include('categoria.form')
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-accion-categoria">
            <a href="#!" class="btn-flat waves-effect waves-block" id="btn-accion-categoria">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cancelar</a>
        </div>
        <div class="progress hide" id="progres-accion-categoria">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>
@endsection

