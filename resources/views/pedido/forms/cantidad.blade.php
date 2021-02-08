<div class="row">
    <form class="col s12">
        <div class="row">
            <div class="input-field col s6">
                <input id="cantidad-{{$pbc->id}}" type="text" class="cantidad-numerico">
                <label for="icon_prefix" class="active white-text cantidad-pedido">Cantidad</label>
            </div>
            <div class="input-field col s6 text-right">
                <a class="waves-effect waves-teal btn-flat right tooltipped" data-position="bottom" data-delay="50" data-tooltip="Agregar al carrito" onclick="agregarACarrito('{{$pbc->id}}','{{$pbc->nombre}}','{{$pbc->precio_costo}}','{{$pbc->unidad->sigla}}','{{$pbc->iva}}','{{$pbc->utilidad}}','{{$pbc->stock}}')"><i id="btn-agregar-carrito-{{$pbc->id}}" class="fa fa-cart-plus fa-5x" aria-hidden="true" style="font-size: 40px; color: white;"></i></a>
            </div>
            <div class="col s12">
                <p class="range-field">
                    <input type="range" class="input-range" id="cantidad-slider-{{$pbc->id}}" min="1" max="'{{$pbc->stock}}'" />
                </p>
            </div>
        </div>
        <div class="contenedor-errores s7 " id="mensaje-{{$pbc->id}}" style="display:none"></div>
        <div class="contenedor-confirmacion s7 " id="mensaje-confirmacion-{{$pbc->id}}" style="display:none"></div>
    </form>
</div>

<script>
    $(document).ready(function (){
        var aux=0;
        var stock = <?php echo $pbc->stock; ?>;

        $("#cantidad-slider-<?php echo $pbc->id?>").attr({
            "max" : stock,
            "min" : 1
        });

        $("#cantidad-slider-<?php echo $pbc->id?>").val(1);
        $("#cantidad-<?php echo $pbc->id?>").val(1);

        $('.cantidad-numerico').keyup(function (){
            this.value = (this.value + '').replace(/[^0-9]/g, '');
        });

        $("#cantidad-<?php echo $pbc->id?>").focusout(function () {
            var cantidadNew = $("#cantidad-<?php echo $pbc->id?>").val();
            var existencia = <?php echo $pbc->stock; ?>;

            if(cantidadNew > existencia){
                alert("La cantidad requerida supera la existencia del producto, debes seleccionar una cantidad menor o igual a la existencia");
                $("#cantidad-<?php echo $pbc->id?>").val(1);
                $("#cantidad-slider-<?php echo $pbc->id?>").val(1);
            }else{
                $("#cantidad-<?php echo $pbc->id?>").val(cantidadNew);
            }
        });

        $("#cantidad-slider-<?php echo $pbc->id?>").on('change input', function(){
            $("#cantidad-<?php echo $pbc->id?>").val($(this).val());
            $("#cantidad-<?php echo $pbc->id?>").focus();
        });
        $("#cantidad-<?php echo $pbc->id?>").on('keyup', function(){
            $("#cantidad-slider-<?php echo $pbc->id?>").val($(this).val());
        });
        /*$("#cantidad-slider-<?php echo $pbc->id?>").on('input', function(){
            console.log("value changed to", this.value);
        });*/

    });

</script>