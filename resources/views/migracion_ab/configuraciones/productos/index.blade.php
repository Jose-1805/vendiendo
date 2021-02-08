<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>
@extends("templates.master")

@section('titulo')
    Vendiendo.co - Inicio
@stop

@section('css')
    @parent
@stop

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Configuración de productos</p>
        <div id="contenedor-lista-productos" class="col s12">
            @include("templates.mensajes",["id_contenedor"=>"productos"])
            <div class="col s12 grey lighten-3">
                <p class="font-small blue-text text-accent-2"><strong>Importante! </strong> La informacón que se registre no podrá ser editada nuevamente durante el proceso de configuración, si selecciona la casilla
                    de bodega indicará al sistema que todas las unidades existentes del producto relacionado de encuentran en bodega, de lo contrario, ingrese el stock existente en cada uno de los almacenes y/o el nuevo precio de venta (si es necesario). Si no se envia el stock total a los almacenes el stock restante será almacenado en bodega.</p>
            </div>
            {!! Form::open(['id'=>'form-lista-productos']) !!}
                <div id="lista-productos" class="content-table-slide col s12">
                <table class="bordered highlight centered no-material-select" id="tabla_productos">
                    <thead>
                    <tr>
                        <th >Nombre</th>
                        <th >Stock</th>
                        <th >Precio costo</th>
                        <th >Iva</th>
                        <th >Bodega</th>
                        @foreach($almacenes as $a)
                            <th width="200">{{$a->nombre}}<p class="font-small center-align">Stock/Precio venta</p></th>
                        @endforeach
                    </tr>
                    </thead>
                </table>
            </div>
            {!! Form::close() !!}
            <div class="col s12 padding-top-20 grey lighten-3">
                <div class="col s12 m6 l8 blue-text text-accent-2">
                    <p class="font-small"><strong>Nota: </strong>
                    A continuación encuentra un elemento ayudante para selección rápida de todas las bodegas, acompañado del botón de acción
                    para guardar la información seleccionada en la tabla de productos
                    </p>
                </div>
                <div class="col s12 m3 l2">
                    <p class='center-align'><input type='checkbox' id='check_bodega_global' /><label for='check_bodega_global'>Todo en bodega</label></p>
                </div>
                <div class="col s12 m3 l2 padding-top-10">
                    <a class="btn blue-grey darken-2" id="btn-guardar">Guardar</a>
                </div>
            </div>
        </div>

    </div>
@stop

@section('js')
    @parent
    <script src="{{asset('js/migracion_ab/productos.js')}}"></script>
    <script>
        $(function () {
            var columnas = [
                {"data":"nombre", 'className': "text-center"},
                {"data":"stock", 'className': "text-center"},
                {"data":"precio_costo", 'className': "text-center"},
                {"data":"iva", 'className': "text-center"},
                {"data":"bodega", 'className': "text-center"},
            ];
            @foreach($almacenes as $a)
                columnas.push({"data":"almacen_"+'{{$a->id}}', 'className': "text-center"});
            @endforeach
            cargarTablaProductos(columnas);
        })
    </script>
@endsection
