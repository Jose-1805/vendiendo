@include("templates.mensajes",["id_contenedor"=>"lista-productos"])
<?php
    $numColumns = 3;
?>
<table id="tabla_inicio_control_empleados" class="bordered highlight centered">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Fecha de ingreso</th>
            <th>Fecha de salida</th>
            @if(Auth::user()->bodegas == 'si')
                <th>Lugar</th>
            @else
                <th class="hide"></th>
            @endif
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cambiar estado del empleado","Empleados","configuracion"))
                <th>Estado sesión<br><label style="font-size: 10px">Cerrar sesión</label></th>
                @else
                <th class="hide"></th>
            @endif
        </tr>
    </thead>
</table>


