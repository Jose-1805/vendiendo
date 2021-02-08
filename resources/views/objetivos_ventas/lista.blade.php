<?php
$meses = [
        1 => "Enero",
        2 => "Febrero",
        3 => "Marzo",
        4 => "Abril",
        5 => "Mayo",
        6 => "Junio",
        7 => "Julio",
        8 => "Agosto",
        9 => "Septiembre",
        10 => "Octubre",
        11 => "Noviembre",
        12 => "Diciembre",
];
?>
<table class="bordered highlight centered" id="tabla_objetivos_ventas">
    <thead>
    <tr>
        <th>Valor</th>
        <th>Fecha</th>
        @if(Auth::user()->bodegas == 'si')
            <th>Almacén</th>
        @else
            <th class="hide"></th>
        @endif
        <th>Editar</th>
        <th>Eliminar</th>
    </tr>
    </thead>
</table>


