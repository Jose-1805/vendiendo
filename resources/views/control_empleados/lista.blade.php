@include("templates.mensajes",["id_contenedor"=>"lista-productos"])
<?php
    $numColumns = 3;
?>
<table id="tabla_control_empleados" class="bordered highlight centered" width="100%">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Cedula</th>
            <th>Estado empleado</th>
            <th>Ultima fecha de ingreso</th>
            <th>Ultima fecha de salida</th>
            <th>Ver</th>
             @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Empleados","configuracion"))
                <th >Editar</th>
            @else
                <th class="hide"></th>
            @endif
           @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Cerrar sesion","Empleados","configuracion"))
                <th>Estado sesión<br><label style="font-size: 10px">Cerrar sesión</label></th>
                @else
                <th class="hide"></th>
            @endif
        </tr>
    </thead>
</table>
