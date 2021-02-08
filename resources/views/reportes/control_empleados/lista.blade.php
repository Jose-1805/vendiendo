<table class="table centered highlight"  width="100%" id="tabla_reporte_control_empleados">
    <thead>
        <th>Fecha</th>
        <th>Nombre</th>
        <th>Cedula</th>
        <th>Fecha llegada</th>
        <th>Fecha salida</th>
        @if(Auth::user()->bodegas == 'si')
            <th>Lugar</th>
        @else
            <th class="hide"></th>
        @endif
    </thead>
    <tbody>
    </tbody>
</table>
