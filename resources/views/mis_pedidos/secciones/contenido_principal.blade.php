<div class="col s12 m7 l9 borde-contenedor-items" id="contenedor-elementos">

    <div class="row" >
        @foreach($categorias as $key => $categoria)
            <div class="col s12" id="categoria_{{$categoria->id}}">
                @if(Auth::user()->bodegas == 'si')
                    <?php
                        $almacen = Auth::user()->almacenActual();
                        $prdts = $categoria->productos()->select('productos.*','almacenes_stock_productos.stock as stock')
                            ->join('almacenes_stock_productos','productos.id','=','almacenes_stock_productos.producto_id')
                            ->where('almacenes_stock_productos.almacen_id',$almacen->id)
                            ->take(10)->get()
                    ?>
                @else
                    <?php
                        $prdts = $categoria->productos()->take(10)->get();
                    ?>
                @endif
                @forelse($prdts as $pr)
                    @include('mis_pedidos.secciones.vista_elemento')
                    <script>
                        productos_en_vista.push({{$pr->id}})
                    </script>
                @empty
                    <p class="titulo-modal center-align">Categoria sin productos</p>
                @endforelse
                <div class="col s12 center-align">
                    <a href="#!" data-categoria="{{$categoria->id}}" class="btn-cargar-mas btn waves-effect waves-light {{$class_color}}">Ver más</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
<div class="col s12 m5 l3" id="datos-pedido">
    <p class="titulo-modal no-margin">Productos seleccionados</p>
    <div class="row borde-contenedor-items"  id="lista-pedido">
        <ul class="collection no-margin">
        </ul>
    </div>
    <div class="col s12 center margin-top-30">
        <a href="#!" id="" class="total btn waves-effect waves-light {{$class_color}} tooltipped" data-position="bottom" data-delay="50" data-tooltip="Ingresar pago">Total: $<span class="info-total">0</span></a>
    </div>
    <div class="col s12" id="contenedor-datos-precios">
        <div class="col s12 hide borde-contenedor-items" id="datos-precios">
            <p id="" class="total {{$class_color_text}} tooltipped" data-position="bottom" data-delay="50" data-tooltip="Ver lista" style="margin-bottom: 5px;margin-top: -5px;"><i class="fa fa-arrow-circle-o-left"></i> Total: $<span class="info-total">0</span></p>
            <label>Cliente</label>
            <ul class="collection" id="ul-cliente">
                <li class="collection-item avatar" id="seleccion-cliente" style="cursor: pointer;">
                    <i class="{{$class_color}} circle fa fa-user"></i>
                    <p id="nombre-cliente">Ningún cliente seleccionado</p>
                    <p class="font-small"><strong>Teléfono </strong><span id="telefono-cliente">Sin información</span></p>
                    <p class="font-small"><strong>Dirección </strong><span id="direccion-cliente">Sin información</span></p>
                </li>
            </ul>
            <div class="row">
                <a class='dropdown-button-width btn col s12 waves-effect waves-light {{$class_color}}' href='#!' data-activates='dropdown-medios-pago'>Medios de pago</a>

                <ul id='dropdown-medios-pago' class='dropdown-content'>
                    <li><a href="#!" onclick="javascript: showPuntosCliente();">Puntos</a></li>
                    <li><a href="#modal-medios-pago" class="modal-trigger">Más...</a></li>
                </ul>

            </div>

            <div id="puntos-redimidos" class="col s12 hide">
                <strong>Puntos redimidos </strong><br>
                <p id="total-puntos-redimidos">$ 0</p>
            </div>

            <div id="medios-pago" class="col s12 hide">
                <strong>Medios de pago</strong><br>
                <p id="total-medios-pago">$ 0</p>
            </div>

            <div class="col s12 input-field margin-top-30">
                <input type="text" id="efectivo" class="excepcion num-entero" value="0">
                <label for="efectivo" class="active">Efectivo</label>
            </div>
            <div class="col s12 m12 l6 input-field">
                <label for="cambio" class="active">Cambio</label>
                <p id="cambio">0</p>
            </div>
            <div class="col s12 m12 l6 input-field">
                <label for="descuento" class="active">Descuento</label>
                <p id="descuento">0</p>
            </div>
            <div class="col s12 grey lighten-4">
                <p>
                    <input type="checkbox" id="ver_factura" checked/>
                    <label for="ver_factura">Visualizar factura</label>
                </p>
            </div>
            <div class="col s12 center-align margin-top-20">
                <a class="btn waves-effect waves-light {{$class_color}}" onclick="enviarPedido()">Vender</a>
            </div>
        </div>
    </div>
</div>

<div id="modal-clientes-pedido" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-clientes">Clientes</p>
        <div id="contenedor-clientes" class="content-table-slide"></div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-clientes">
            <a class="modal-close cyan-text btn-flat">Cerrar</a>
        </div>
    </div>
</div>
<div id="contenedor-factura-pos"></div>
@if(\Illuminate\Support\Facades\Auth::user()->plan()->cliente_predeterminado == "si")
    @if(\App\Models\Cliente::permitidos()->where("predeterminado","si")->first())
        <script>
            seleccionarCliente({{\App\Models\Cliente::permitidos()->where("predeterminado","si")->first()->id}});
        </script>
    @endif
@endif