<table class="bordered highlight centered" id="tabla_cuentas_bancarias" style="width: 100%;">
    <thead>
    <tr>
        <th>Banco</th>
        <th>Titular</th>
        <th>Número</th>
        <th>Saldo</th>
        <th>Usuario</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Consignar","cuentas bancarias","configuracion"))
            <th>Consignar</th>
            <th>Historial</th>
        @else
            <th class="hide"></th>
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
</table>

