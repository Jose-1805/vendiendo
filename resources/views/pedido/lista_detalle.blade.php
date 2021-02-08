<?php $bandera = 0?>
<div class="row">
    <p class="titulo">{{$categoria->nombre}}</p>
    @if(count($productosByCategoria))
        @foreach ($productosByCategoria as $pbc)
            <?php $bandera++;?>
            @if($pbc->estado_producto !='Inactivo')
                <div class="col s12 m6 l4 white">
                    <div id="div-contenedor-producto-card-{{$pbc->id}}">
                        <div class="card blue-grey darken-1 producto_card" onclick="agregarCantidadProductoDiv('{{$pbc->id}}')" style="cursor: pointer;">
                            <div class="right-align ">
                                @if($pbc->stock >0)
                                    <div class="hide">
                                        <input type="checkbox" class="filled-in" id="{{$pbc->id}}" onclick="agregarCantidadProducto(this.id)" />
                                        <label for="{{$pbc->id}}" style="margin-right: 20px; padding-left: 20px; border: 3px solid #f5f2f2 !important;"></label>
                                    </div>
                                @endif
                            </div>
                            <div class="card-content white-text">
                                <span class="card-title">{{$pbc->nombre}}</span>
                                <p style="text-align: justify; margin-bottom: 10px;">{{$pbc->descripcion}}</p>
                                @if($pbc->imagen !='')
                                    <div class="card small row" style="max-height: 200px; max-width: 250px; margin:auto;">
                                        {!! Html::image(url("/app/public/img/productos/".$pbc->id."/".$pbc->imagen), $alt="",
                                        $attributes = array('style'=>'max-height: 200px; margin: 0 auto;','class'=>'materialboxed col s12')) !!}
                                    </div>
                                @else
                                    <div class="card small" style="max-height: 200px; max-width: 250px; margin:auto">
                                        {!! Html::image("img/sistema/noaplica.png", $alt="",
                                        $attributes = array('style'=>'max-height: 200px; max-width: 250px; margin: 0 auto;','class'=>'materialboxed col s12')) !!}
                                    </div>
                                @endif
                                <?php
                                $precioV = $pbc->precio_costo + (($pbc->precio_costo * $pbc->utilidad)/100);
                                $strikeIn="";
                                $strikeEnd="";
                                $mensaje ="";
                                if($pbc->stock ==0){
                                    $strikeIn ="<s>";
                                    $strikeEnd ="</s>";
                                    $mensaje = "Producto agotado";
                                }
                                ?>
                                <div class="row" style="margin-top: 10px;">
                                    <div class="col s6">
                                        <p>{!! $strikeIn !!}Precio: ${{number_format($precioV + (($precioV * $pbc->iva)/100) , 2,',','.')}}{!! $strikeEnd !!}</p>
                                    </div>
                                    <div class="col s6">
                                        <p><font SIZE=2 COLOR="white">Stock: {{$pbc->stock}} {{$pbc->unidad->sigla}}</font></p>
                                    </div>
                                    @if($pbc->stock ==0)
                                        <div class="col s12" style="position: absolute">
                                            {!! Html::image("img/sistema/agotado.png", $alt="", $attributes = array('style'=>'height: 100px; height:90px; position:absolute')) !!}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="card blue-grey darken-1 detalle-producto-card" id="detalle-producto-{{$pbc->id}}" style="color: lavenderblush; display: none; margin-top: -16px">
                            @include('pedido.forms.cantidad')
                        </div>
                    </div>
                </div>
                @if($bandera%3 == 0)
                    <div class="col s12 divider white"></div>
                @endif
            @endif
        @endforeach
    @else
    <div>
        No hay productos para esta categoria
    </div>
    @endif

</div>
<script type="application/javascript">
    $(document).ready(function () {
        var alturas_div = $(".producto_card").map(function () {
            return $(this).height();
        }).get();
        altura_max = Math.max.apply(null, alturas_div);

        $(".producto_card").height(altura_max);


        //POSICION ESTATICA DEL BOTON DE REGRESAR A CATEGORIAS
        var offset = $("#undo-categoria").offset();
        var topPadding = 15;
        $(window).scroll(function() {
            if ($("#undo-categoria").height() < $(window).height() && $(window).scrollTop() > offset.top) {
                $("#undo-categoria").stop().animate({
                    marginTop: $(window).scrollTop() - offset.top + topPadding
                });
            } else {
                $("#undo-categoria").stop().animate({
                    marginTop: 0
                });
            };
        });

    });
</script>
