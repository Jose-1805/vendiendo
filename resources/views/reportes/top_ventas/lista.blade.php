<table class="table centered highlight" id="t_rep_top_ventas" width="100%">
    <thead>
        <th>Nombre</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Descripción') !!}</th>
        <th>{!! \App\TildeHtml::TildesToHtml('Categoría') !!}</th>
        <th>Cantidad vendida</th>
        <th>Stock</th>
    </thead>
    <tbody>
    </tbody>
</table>
<script type="text/javascript">
   $(document).ready(function(){
        cargaTablaReporteTopVentas();
   });
</script>