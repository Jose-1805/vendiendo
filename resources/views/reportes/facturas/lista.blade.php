<table class="table centered highlight" id="t_rep_facturas" width="100%">
    <thead>
        <th>{{\App\TildeHtml::TildesToHtml('Número')}}</th>
        <th>Estado</th>
        <th>Subtotal</th>
        <th>Iva</th>
        <th>Total</th>
        <th>Vlr. Puntos</th>
        <th>Vlr. Medios de pago</th>
        <th>Descuento</th>
        <th>Efectivo</th>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
        <tr>
            <th class="center-align" colspan="2">TOTAL</th>
            <td id="t_footer_subtotal"></td>
            <td id="t_footer_iva"></td>
            <td id="t_footer_facturas"></td>
            <td id="t_footer_valor_puntos"></td>
            <td id="t_footer_valor_medios_pago"></td>
            <td id="t_footer_descuento"></td>
            <td id="t_footer_efectivo"></td>
        </tr>
    </tfoot>
</table>

<script type="text/javascript">
    $(document).ready(function(){
        cargaTablaReporteFacturas();
    });
</script>