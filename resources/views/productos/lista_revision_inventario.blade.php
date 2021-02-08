<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
        <p class="titulo">Inventario productos</p>
     
        @include("templates.mensajes",["id_contenedor"=>"inventario-productos"])
        <div id="contenedor-lista-productos" class="col s12 content-table-slide">
            <table class="table centered " id="t_revision_inventario" width="100%">
                <thead>
                    <th>Barcode</th>
                    <th>Nombre</th>
                    <th>Stock</th>
                    <th>Medida venta</th>
                    <th>Unidad</th>
                    <th>Categoria</th>
                    <th>Revisar</th>
                    <th>Eliminar</th>
                </thead>
                <tbody>
                </tbody>
            </table>


        </div>
        <div class="col s12 center-align paginate-productos-inventario">{!! $productos->render() !!}</div>
    </div>

    <div id="modal-aprobar-producto-inventario" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Aprobar</p>
            <p>¿Está seguro de aprobar las ediciones realizadas en este producto?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-aprobar-producto-inventario">
                <a href="#!" class="cyan-text btn-flat" onclick="aprobarEdiciones()">Aceptar</a>
                <a href="#!" class="modal-close btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-aprobar-producto-inventario">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>

     <div id="modal-eliminar-producto-inventario" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este producto de la lista?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="">
                <a href="#!" class="red-text btn-flat" onclick="elminarProductoInventario()">Aceptar</a>
                <a href="#!" class="modal-close btn-flat">Cancelar</a>
            </div>

        </div>
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/productos/productosAction.js')}}"></script>    
    <script type="text/javascript">
        $(document).ready(function(){     
            cargaTablaRevisionInventario();
        });
    </script>
@stop