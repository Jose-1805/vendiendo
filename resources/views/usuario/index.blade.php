<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>
@extends('templates.master')

@section('js')
    @parent
    <script src="{{asset('js/usuarioAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","usuarios","configuracion"))
            setPermisoEditar(true);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","usuarios","configuracion"))
            setPermisoEliminar(true);
        @endif
    </script>
@stop

@section('contenido')
@if(!isset($filtro))
<?php $filtro = ""; ?>
@endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
     @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","usuarios","configuracion"))
         @if(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "superadministrador" || \Illuminate\Support\Facades\Auth::user()->plan()->n_usuarios == 0 || \Illuminate\Support\Facades\Auth::user()->plan()->n_usuarios > \Illuminate\Support\Facades\Auth::user()->countUsuariosAdministrador())
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/usuario/create')}}"><i class="fa fa-plus"></i></a>
         @endif
     @endif
     <p class="titulo">Usuarios</p>

     @if(!(\Illuminate\Support\Facades\Auth::user()->perfil->nombre == "superadministrador" || \Illuminate\Support\Facades\Auth::user()->plan()->n_usuarios == 0 || \Illuminate\Support\Facades\Auth::user()->plan()->n_usuarios > \Illuminate\Support\Facades\Auth::user()->countUsuariosAdministrador()))
         <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
                 <i class='fa fa-close btn-cerrar-confirmacion'></i>
                 <ul>
                     <li>No es posible crear más usuarios, su plan alcanzó el tope máximo de usuarios permitidos.</li>
                 </ul>
         </div>
     @endif

     <div id="contenedor-lista-proveedores" class="col s12 content-table-slide">
     @include('usuario.lista')
     </div>
</div>
@stop