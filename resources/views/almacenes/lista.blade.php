<table class="bordered highlight centered" id="tabla_almacenes" style="width: 100%;">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Dirección</th>
        <th>Teléfono</th>
        <th>Latitud</th>
        <th>Longitud</th>
        <th>Administrador</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","almacenes","inicio"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
</table>