<table class="bordered highlight centered" id="tabla_bodegas" style="width: 100%;">
    <thead>
    <tr>
        <th>Nombre</th>
        <th>Dirección</th>
        <th>Creador</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","bodegas","inicio"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
</table>