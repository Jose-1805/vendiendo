<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('js')
    @parent
    <script src="{{asset('js/materiasPrimasAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","materias primas","inicio"))
            setPermisoEditar(true);
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","materias primas","inicio"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop
@section('contenido')
@if(!isset($filtro))
<?php $filtro = ""; ?>
@endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
     @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","materias primas","inicio"))
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/materia-prima/create')}}"><i class="fa fa-plus"></i></a>
     @endif
        <a style="margin-top: 50px;" class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Importación de materias primas" href="{{url('/materia-prima/importacion')}}"><i class="fa fa-cloud"></i></a>
     <p class="titulo">Materias primas</p>

     <!--<div class="input-field col m6 l4 right hide-on-small-only" style="margin-top: -70px;">
         <input type="text" name="busqueda" id="busqueda" placeholder="Buscar" value="{{$filtro}}" style="border: none !important;">
         <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
     </div>

     <div class="input-field col s12 hide-on-med-and-up" >
         <input type="text" name="busqueda2" id="busqueda2" placeholder="Buscar" value="{{$filtro}}">
         <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
     </div>-->

     <div id="contenedor-lista-materias-primas" class="col s12 content-table-slide">
     @include('materias_primas.lista')
     </div>
</div>

<div id="modal-detalle-proveedor" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal">Detalle</p>
        <div id="contenido-proveedor" style="width: 100%">

        </div>

    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-detalle-proveedor">
            <!--<a href="#!" class="red-text btn-flat" onclick="javascript: detalleProducto(id_select)">Aceptar</a>-->
            <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
        </div>
    </div>
</div>
@stop
