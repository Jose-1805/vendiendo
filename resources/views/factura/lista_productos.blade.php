<div class="content-table-slide">
{!! Form::open(["id"=>"form-select-producto"]) !!}
<table class="bordered highlight centered" style="min-width: 500px;" id="tabla_lista_productos" width="99%">
    <thead>
    <tr>
        <th width="5%"></th>
        <th width="20%">Nombre</th>
        <th width="15%">Valor</th>
        <th width="15%">Stock</th>
        <th width="15%">Umbral</th>
        <th width="15%">Unidad</th>
        <th width="15%">Categoria</th>
    </tr>
    </thead>
      <tbody id="datos_1"></tbody>
</table>



<script type="text/javascript">
    $(document).ready(function(){
        cargatablaListaProductos();

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