<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s10 {{$size_medium}} white" style="margin-top: 85px;">
        <p class="titulo">Tipos de productos</p>
    <table>
        <tr>
            <td>
                <div class="live-tile green" data-mode="none" data-speed="750" data-delay="3000" data-link="{{url('/productos/lista')}}">
                    <span class="tile-title">Productos terminado</span>
                    <div><i class="fa fa-thumb-tack fa-5x" aria-hidden="true"></i></div>
                </div>
            </td>
            <td>
                <div class="live-tile red" data-mode="none" data-speed="750" data-delay="3000" data-link="{{url('/productos/lista')}}">
                    <span class="tile-title">Productos compuestos</span>
                    <div><i class="fa fa-thumb-tack fa-5x" aria-hidden="true"></i></div>
                </div>
            </td>
            <td>
                <div class="live-tile blue" data-mode="none" data-speed="750" data-delay="3000" data-link="{{url('/productos/lista')}}">
                    <span class="tile-title">Productos preparados</span>
                    <div><i class="fa fa-thumb-tack fa-5x" aria-hidden="true"></i></div>
                </div>
            </td>
        </tr>
    </table>
</div>
@endsection