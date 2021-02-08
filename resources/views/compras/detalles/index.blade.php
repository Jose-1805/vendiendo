 <?php
    if(Auth::user()->bodegas == 'si'){
        $compra_producto_detalles = $compra->historialCostos()
                ->join("productos","historial_costos.producto_id","=","productos.id")
                ->select('historial_costos.*','compras_historial_costos.cantidad')->get();
    }else{
        $compra_producto_detalles = $compra->productosHistorial()
            ->join("productos","productos_historial.producto_id","=","productos.id")
            ->select('productos_historial.*','compras_productos_historial.cantidad')->get();
    }
$compra_metaria_detalle = $compra->materias_primas_historial()
        ->join("materias_primas","materias_primas_historial.materia_prima_id","=","materias_primas.id")
        ->select('materias_primas.*','compras_materias_primas_historial.cantidad','materias_primas_historial.precio_costo_nuevo as valor','materias_primas_historial.id as historial')->get();
?>

    @include("templates.mensajes",["id_contenedor"=>"detalle-compras"])
    <div class="col s12 m10 offset-m1">
        <p class="titulo-modal">Datos de la compra
            @if($compra->dias_credito > 0)<i class="fa fa-list-alt waves-effect waves-light black-text right tooltipped" id="btn-historial-abonos" data-position="bottom" data-delay="50" data-tooltip="Historial de abonos" style="cursor: pointer;margin-top: -5px;margin-left: 10px;line-height: 50px;" onclick="detalleAbono('{{$compra->id }}')"></i>@endif
        </p>
        @if(count($lista_devoluciones))
            <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-cart-arrow-down fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Lista de devoluciones" style="cursor: pointer;margin-top: 10px;" onclick="listarDevolucionesCompra({{$compra->id}})"></i></div>
        @endif
        <div class="col s12 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Numero factura: </strong></p>
            <p class="" style="display: inline-block;">{{$compra->numero}}</p>
        </div>

        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Fecha compra: </strong></p>
            <p class="" style="display: inline-block;">{{$compra->created_at}}</p>
        </div>

        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Proveedor: </strong></p>
            <p class="" style="display: inline-block;">{{$compra->proveedor->nombre}}</p>
        </div>

        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Estado compra: </strong></p>
            <p class="" style="display: inline-block;">{{$compra->estado}}</p>
        </div>
        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Estado pago: </strong></p>
            <p class="" style="display: inline-block;">{{$compra->estado_pago}}</p>
        </div>
        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Usuario: </strong></p>
            <p class="" style="display: inline-block;">{{$compra->usuarioCreador->nombres." ".$compra->usuarioCreador->apellidos}}</p>
        </div>
        <div class="col s12 m6 no-padding">
            <p class="" style="width: 150px;display: inline-block;"><strong>Valor compra: </strong></p>
            <p class="" style="display: inline-block;">{{"$ ".number_format($compra->valor,2,',','.')}}</p>
        </div>
    </div>
    <div id="contenedor-detalle-productos" class="col s12 m10 offset-m1">
        <p class="titulo-modal margin-top-40">Detalles de productos de la compra</p>
        @if(count($compra_producto_detalles))
            @include('compras.detalles.detalle_productos')
        @else
            <p class="col s12 center">Sin resultados</p>
        @endif
    </div>
    <div id="contenedor-detalle-materias" class="col s12 m10 offset-m1">
        <p class="titulo-modal margin-top-40">Detalles materias primas de la compra</p>
        @if(count($compra_metaria_detalle))
            @include('compras.detalles.detalle_materias_primas')
        @else
            <p class="col s12 center">Sin resultados</p>
        @endif
    </div>

    @if(isset($lista_devoluciones) && count($lista_devoluciones))
        <div id="" class="col s12 m10 offset-m1">
            <p class="titulo-modal">Devoluciones</p>
            @include('compras.devoluciones.index',["estado"=>false])
        </div>
    @endif



<div id="modal-lista-devoluciones-compra" class="modal modal-fixed-footer" style="width: 90%">
    <div class="modal-content">
        <div class="titulo-modal">Listado de devoluciones compra</div>
        <div id="listado-devoluciones"></div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat" onclick="javascript:window.location.reload()">Cerrar</a>
    </div>
</div>
