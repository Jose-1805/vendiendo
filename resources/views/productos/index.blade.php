<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","productos","inicio") && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador()))
        @if(\App\Models\Categoria::permitidos()->get()->count() !=0 && \App\Models\Unidad::unidadesPermitidas()->get()->count() !=0)
            <a class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar" href="{{url('/productos/create')}}"><i class="fa fa-plus"></i></a>
        @endif
    @endif
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Revision inventario","productos","inicio") && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador()))
        <a style="margin-top: 50px;" class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Revisión entrada inventario" href="{{url('/productos/revision-inventario')}}"><i class="fa fa-list-alt"></i></a>
    @endif
    @if(\Illuminate\Support\Facades\Auth::user()->plan()->importacion_productos == "si" && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador()))
        <a style="margin-top: 100px;" class="btn-floating waves-effect waves-light blue-grey darken-2 agregar-elemento-tabla tooltipped" data-position="bottom" data-delay="50" data-tooltip="Importación de productos" href="{{url('/productos/importacion')}}"><i class="fa fa-cloud"></i></a>
    @endif
    <p class="titulo">Productos</p>

    @if(!(Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador()))
        <div class="col s12 contenedor-confirmacion blue lighten-5 blue-text" id="contenedor-confirmacion-n_usuarios">
            <i class='fa fa-close btn-cerrar-confirmacion'></i>
            <ul>
                <li>No es posible crear más productos, su plan alcanzó el tope máximo de productos permitidos.</li>
            </ul>
        </div>
    @endif

    <div class="input-field col s12 hide-on-med-and-up" >
       <input type="text" name="busqueda2" id="busqueda2" placeholder="Buscar" value="{{$filtro}}">
        <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar" style="float: right;margin-top: -55px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
    </div>
        @include("templates.mensajes",["id_contenedor"=>"productoIndex"])
    <div id="contenedor-lista-productos" class="col s12 content-table-slide">
        @if(\App\Models\Categoria::permitidos()->get()->count() != 0 && \App\Models\Unidad::unidadesPermitidas()->get()->count() !=0 && (\App\Models\Proveedor::permitidos()->get()->count() !=0 || \App\Models\MateriaPrima::materiasPrimasPermitidas()->get()->count() !=0))
            @include('productos.lista')
        @else
            <p style="text-align: center; background-color: #80d8ff; color: #0D47A1; padding: 10px;border-radius: 3px;">Para la creación de un producto se requiere que haya registrado unidades y categorías, además de proveedores y/o materias primas según sea el caso</p>
        @endif
    </div>

</div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/productos/productosAction.js')}}"></script>
<script>
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","productos","inicio"))
            setPermisoEditarProducto(true);
        @else
            setPermisoEditarProducto(false);
    @endif

    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Estado","productos","inicio"))
        setPermisoEstadoProductos(true);
    @else
        setPermisoEstadoProductos(false);
    @endif


    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","productos","inicio"))
        setPermisoEliminar(true);
    @endif
</script>
@stop