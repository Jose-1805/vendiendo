<?php
    $usuario_actual = $caja->relacionUsuarioActual();
?>
<p class="col s12 m6 l4"><strong>Caja: </strong>{{$caja->nombre}}</p>
<p class="col s12 m6 l4"><strong>Prefijo: </strong>{{$caja->prefijo}}</p>
<p class="col s12 m6 l4"><strong>Estado: </strong>{{$caja->estado}}</p>
@if($usuario_actual)
    <p class="col s12 m6 l4"><strong>Cajero actual: </strong>{{$usuario_actual->nombres." ".$usuario_actual->apellidos}}</p>
    <p class="col s12 m6 l4"><strong>Valor inicial: </strong>$ {{number_format($usuario_actual->valor_inicial,2,',','.')}}</p>
    <p class="col s12 m6 l4"><strong>Valor actual: </strong>$ {{number_format($usuario_actual->valor_final,2,',','.')}}</p>
@endif
<div class="content-table-slide col s12">
<table class="bordered highlight centered" id="tabla_lista_historial" style="width: 100%;">
    <thead>
        <tr>
            <th></th>
            <th>Cajero</th>
            <th>Valor inicial</th>
            <th>Valor final (Calculado)</th>
            <th>Valor final (Real)</th>
            <th>Fecha asignación</th>
            <th>Fecha cierre</th>
        </tr>
    </thead>
</table>
</div>