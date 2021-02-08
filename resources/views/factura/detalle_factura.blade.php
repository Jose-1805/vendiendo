@include("templates.mensajes",["id_contenedor"=>"detalles-factura"])

<table class="table highlight centered" style="min-width: 500px;" id="tabla-detalle-producto">
    <thead>
    <th><i class="fa fa-barcode"></i> Código</th>
    <th>Producto</th>
    <th>Unid.</th>
    <th>Cant.</th>
    <th>Vlr. Unit.</th>
    <th>Subtotal</th>
    <th></th>
    </thead>

    <tbody>
    <tr class="filaProductos" id="filaProd_0">
        <td>
            <input type="text" class="barcode" id="barCodeProducto" onchange="seleccionProducto('barCode', 'filaProd_0', this.id,event); obtenerMaximoTotalStockProductos('', 0 )"  placeholder="Codigo de barras">
        </td>
        <td>
            <input type="text" class="nombre" placeholder="Click aquí">
            <i class="fa fa-spin fa-spinner hide" style="margin-top: -40px;"></i>
            <input type="hidden" class="id-pr">
        </td>

        <td>
            <p class="unidad"></p>
        </td>

        <td>
            <input type="text" value="1" min="1" class="excepcion num-real center-align cantidad" onblur="obtenerMaximoTotalStockProductos(this.id)">
        </td>

        <td>
            <p style="padding-top: 15px;white-space: nowrap;" class="vlr-unitario">$ 0</p>
        </td>

        <td>
            <p style="padding-top: 15px;white-space: nowrap;" class="vlr-total">$ 0</p>
        </td>


        <td>
            <i class="fa fa-trash red-text text-darken-1 waves-effect waves-light" title="Eliminar elemento" style="cursor: pointer;"></i>
        </td>
    </tr>
    </tbody>
</table>
<div class="contenedor-btn-add-elemento margin-bottom-50 right-align margin-top-20">
    <button class="waves-effect waves-light blue-grey darken-2 btn" onclick="agregarElementoFactura()" >Agregar Elemento</button>
    <a class="btn-floating waves-effect waves-light blue-grey darken-2 tooltipped" data-position="bottom" data-delay="50" data-tooltip="Limpiar lista de productos" href="#" id="btn-limpiar-lista-productos"><i class="fa fa-minus-circle"></i></a>
</div>

<script type="text/javascript">
    $(function () {
        $('input:enabled.barcode').first().focus();

        ConfirmacionRecarga = false;
        var minlength = 3;
        $("#search_disponibles").keyup(function () {
            var that = this,
            value = $(this).val();
            if (value.length >= minlength) {
                $('#licencias_asignar option').each(function () {
                    if ($(this).val() != "l" && $(this).val() != "lm" && $(this).val() != "lmm")
                        $(this).addClass("hidden");
                });
                $('#licencias_asignar').find('option:contains("' + value + '")').removeClass("hidden");
            } else if(value.length == 0) {
                $('#licencias_asignar option').each(function () {
                    $(this).removeClass("hidden");
                });
            }
        });
    });
</script>