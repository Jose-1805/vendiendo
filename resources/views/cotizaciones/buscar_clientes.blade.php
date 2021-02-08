
<table class="bordered highlight centered" id="tabla_lista_clientes" width="100%">
    <thead>
        <tr>
            <th width="10%"></th>
            <th width="20%">Identificación</th>
            <th width="30%">Nombre</th>
            <th width="40%">Correo</th>
        </tr>
    </thead>
</table>

<script type="text/javascript">
    $(document).ready(function(){
        cargatablaListaClientes();
    });
    $(document).keypress(function(e) {
         if(e.which == 13) {
               if($('.radio_producto input:radio').is(':focus')){
                $('.radio_producto input:radio:focus').attr("checked", "checked");
                $('.radio_producto input:radio:focus').click();
               }
            }
    });
</script>