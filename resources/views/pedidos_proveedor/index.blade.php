<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    @if(!isset($filtro))
        <?php $filtro=""; ?>
    @endif
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px">
    <p class="titulo">Pedidos</p>

    @include("templates.mensajes",["id_contenedor"=>"pedidos"])
    <div id="contenedor-lista-pedidos" class="col s12 content-table-slide">
        <table class="bordered highlight centered">
            <thead>
            <tr>
                <th>Consecutivo</th>
                <th>Valor</th>
                <th>Usuario</th>
                <th>Fecha</th>
                <th>Estado</th>
                <th>Cambiar a</th>
                <th>Detalle</th>
            </tr>
            </thead>
            <tbody id="datos">
            @if(count($pedidos))
                @foreach($pedidos as $pedido)
                    <?php
                        $nuevosEstados = [];
                            switch ($pedido->estado){
                                case 'sin procesar': $nuevosEstados = [["revisado","green"]];
                                    break;
                                case 'revisado': $nuevosEstados = [["aprobado","green"],["rechazado","red"]];
                                    break;
                                case 'aprobado': $nuevosEstados = [["enviado","green"],["cancelado","red"]];
                                    break;
                                case 'enviado': $nuevosEstados = [["recibido","green"]];
                                    break;
                            }
                    ?>
                    <tr data-id="{{$pedido->id}}">
                        <td class="lef">00{{$pedido->consecutivo}}</td>
                        <td>$ {{number_format($pedido->valor_total, 0, "," , "." )}}</td>
                        <td>{{$pedido->administrador->nombres." ".$pedido->administrador->apellidos}}</td>
                        <td>{{date("Y-m-d",strtotime($pedido->created_at))}}</td>
                        <td>{{$pedido->estado}}</td>
                        <td style="min-width: 100px;">
                            @foreach($nuevosEstados as $estado)
                                <div class="col s12">
                                    <a class="badge-vendiendo waves-effect waves-light {{$estado[1]}}" onclick="cambiarEstado('{{$estado[0]}}',{{$pedido->id}})" style="width:80px !important;cursor:pointer; padding: 2px 10px; border-radius: 3px;">{{$estado[0]}}</a>
                                </div>
                            @endforeach
                        </td>
                        <td><a href="#" onclick="showDetallePedido({{$pedido->id}})"><i class="fa fa-list"></i></a></td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" class="center"><p>Sin resultados</p></td>
                </tr>
            @endif
            </tbody>

        </table>
        {!! $pedidos->render() !!}


        <div id="modal-detalle" class="modal modal-fixed-footer" style="width: 70%;">
            <div class="modal-content scroll-style">
                <p class="titulo-modal">Detalle del pedido</p>
                <div class="col s12" id="contenido-detalle">

                </div>
            </div>
            <div class="modal-footer" style="height: auto;">
                <a href="#!" class="modal-action modal-close btn-flat right">Cerrar</a>
            </div>
        </div>
    </div>

</div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/productos/pedidosProveedor.js')}}"></script>
@stop