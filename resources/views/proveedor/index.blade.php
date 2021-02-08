<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
@if(!isset($filtro))
<?php $filtro = ""; ?>
@endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
     @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","proveedores","configuracion") && (Auth::user()->plan()->n_proveedores == 0 || Auth::user()->plan()->n_proveedores > Auth::user()->countProveedoresAdministrador()))
        <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/proveedor/create')}}"><i class="fa fa-plus"></i></a>
     @endif
     <p class="titulo">Proveedores</p>

     @if(!(Auth::user()->plan()->n_proveedores == 0 || Auth::user()->plan()->n_proveedores > Auth::user()->countProveedoresAdministrador()))
             <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                 <i class='fa fa-close btn-cerrar-confirmacion'></i>
                 <ul>
                     <li>No es posible crear más proveedores, su plan alcanzó el tope máximo de proveedores permitidas.</li>
                 </ul>
             </div>
     @endif

     <div class="input-field col s12 hide-on-med-and-up" >
         <input type="text" name="busqueda2" id="busqueda2" placeholder="Buscar" value="{{$filtro}}">
         <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
     </div>

     <div id="contenedor-lista-proveedores" class="col s12 content-table-slide">
     @include('proveedor.lista')
     </div>
</div>
@stop

@section('js')
@parent
    <script src="{{asset('js/proveedorAction.js')}}"></script>
@stop