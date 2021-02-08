<?php
    $remision_productos = $remision->productos()
        ->select('productos.*','remisiones_salida_productos.cantidad')->get();

    $remision_materias_p = $remision->materiasPrimas()
        ->select('materias_primas.*','remisiones_salida_materias_primas.cantidad')->get();
?>

    @include("templates.mensajes",["id_contenedor"=>"detalle-remision"])
    <div class="col s12 m10 offset-m1">
        <p class="titulo-modal">Datos de la remisión de salida</p>

        <div class="col s12 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Numero remisión: </strong></p>
            <p class="" style="display: inline-block;">{{$remision->numero}}</p>
        </div>

        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Fecha de remisión: </strong></p>
            <p class="" style="display: inline-block;">{{$remision->created_at}}</p>
        </div>
        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Usuario: </strong></p>
            <p class="" style="display: inline-block;">{{$remision->usuarioCreador->nombres." ".$remision->usuarioCreador->apellidos}}</p>
        </div>
    </div>
    <div id="contenedor-detalle-productos" class="col s12 m10 offset-m1">
        <p class="titulo-modal margin-top-40">Detalles de productos de la remisión</p>
        @if(count($remision_productos))
            @include('remisiones_salida.detalles.detalle_productos')
        @else
            <p class="col s12 center">Sin resultados</p>
        @endif
    </div>
    <div id="contenedor-detalle-materias" class="col s12 m10 offset-m1">
        <p class="titulo-modal margin-top-40">Detalles materias primas de la remisión</p>
        @if(count($remision_materias_p))
            @include('remisiones_salida.detalles.detalle_materias_primas')
        @else
            <p class="col s12 center">Sin resultados</p>
        @endif
    </div>