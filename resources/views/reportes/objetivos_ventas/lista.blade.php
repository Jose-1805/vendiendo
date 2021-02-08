<table class="table centered highlight" id="t_rep_objetivo_ventas" width="100%">
    <thead>
        @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')
            <th>Almacén</th>
        @else
            <th class="hide"></th>
        @endif
        <th>Fecha</th>
        <th>Valor fijado</th>
        <th>Valor acumulado</th>
        <th>Cumplimiento</th>
    </thead>
    <tbody>
    </tbody>
</table>
<script type="text/javascript">
    $(document).ready(function(){
        cargaTablaReporteObjetivosVentas();
    });
</script>