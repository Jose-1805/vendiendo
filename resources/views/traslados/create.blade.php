@include('templates.no_cache')
<?php
    $data[0] = "No establecido ";
    $data[1] = "No establecido ";
    $data[3] = null;
    $bodega = [];
    if(Auth::user()->admin_bodegas == 'no')
        $bodega = ['bodega'=>'Bodega'];

    $texto_select = 'Seleccione un almacén'.(Auth::user()->admin_bodegas == 'no'?' o una bodega':'');
?>

<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Crear traslado</p>
            @include("templates.mensajes",["id_contenedor"=>"detalle-traslado"])

        <p class="text-info"><strong>Nota: </strong>recuerde cambiar el precio de venta en la tabla de productos seleccionados para establecer la utilidad de cada producto en relación al almacén seleccionado.</p>
            <div class="col s12 m10 offset-m1 margin-top-30" style="margin-top: -30px;">
                <p class="titulo-modal margin-top-20">Almacèn {!! Auth::user()->admin_bodegas == 'no'?'o bodega':''; !!}</p>
                {!! Form::select('select_almacen',[''=>$texto_select]+$bodega+\App\Models\Almacen::permitidos(false)->lists('nombre','id'),null,['id'=>'select_almacen']) !!}
            </div>
            <div class="col s12 m10 offset-m1 margin-top-30" id="content-detalle-traslado" style="margin-top: -30px;">
                <p class="titulo-modal margin-top-20">Detalles de la traslado <i id="muestra_div" class="fa fa-low-vision hide" title="Muestra el recuadro con el valor total de el traslado" aria-hidden="true" style="float:right;cursor: pointer; color:rgb(140, 189, 83); font-size:24px; " onclick="oculta_div_flotante();"></i></p>
                <div class="content-table-slide col s12">
                    @include('traslados.detalle_traslado')
                    {!! Form::hidden("cliente",$data[3],["id"=>"cliente"]) !!}
                </div>
                {{--LEO--}}
                <div class="col s12 ">
                    <strong class="no-margin" style="display: inline-block !important;">Subtotal:</strong>
                    <p class="no-margin" style="display: inline-block !important; width: 150px;" id="txt-subtotal">$ 0,00</p>
                </div>
                <div class="col s12 ">
                    <strong class="no-margin" style="display: inline-block !important;">IVA:</strong>
                    <p class="no-margin" style="display: inline-block !important; width: 150px;" id="txt-iva">$ 0,00</p>
                </div>
                <div class="col s12 ">
                    <strong class="no-margin" style="display: inline-block !important;">Total a pagar:</strong>
                    <p class="no-margin" style="display: inline-block !important; width: 150px;" id="txt-total-pagar">$ 0,00</p>
                </div>
                <div class="div_flotate" style="padding:10px; font-size:16px;">
                    <i class="fa fa-minus-circle" aria-hidden="true" style="float: right;cursor:pointer; padding-top: 3px;" onclick="oculta_div_flotante()"></i>

                    {{--  <div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div> --}}
                    <div class="col s12 " style="width:80%">
                        <strong class="no-margin" style="display: inline-block !important; font-weight: 300;">Total a pagar:</strong>
                        <p class="no-margin" style="display: inline-block !important; word-break: break-word;" id="txt-total-pagar-flotante">$0,00</p>
                    </div>
                </div>

                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","traslados","inicio"))
                    <div class="col s12 center margin-top-40" id="" >
                        <a class="btn waves-effect waves-light blue-grey darken-2" onclick="guardarTraslado();" >Guardar traslado</a>
                    </div>
                @endif
            </div>
    </div>
@endsection

<div id="modal-detalles-factura" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal">Productos</p>

       {{--  <i class="fa fa-filter right waves-effect waves-light" title="Filtros de busqueda" style="cursor: pointer;margin-top: -40px;" onclick="javascript: $('#contenedor-filtros-detalles-factura').slideToggle(500)"></i> --}}
        
        <div id="contenedor-productos-modal" class="col s12"></div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-detalles-factura-modal">
            <a class="green-text btn-flat" id="btnSeleccionProducto">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-detalles-factura-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>


@section('js')
    @parent
    <script src="{{asset('js/trasladoAction.js')}}"></script>

    <script>
        var existe_informacion = false;
        $(function(){
            window.onbeforeunload = confirmaSalida;


            if(localStorage.loadPedido){
                localStorage.setItem("deletePedido",true);
                loadPedido();
            }else{
                if(localStorage.deletePedido){
                    localStorage.removeItem("deletePedido");
                }
            }
            if (sessionStorage.lista_factura_abierta){
                $("#contenedor-boton-facturar").append("<a class='btn waves-effect waves-light blue-grey darken-2' onclick='borrarFacturaAbierta()' >Cancelar</a>");
                loadPedidoFacturaAbierta();
            }
            //alert($(".id-pr").length);

            $(".btn-toggle-datos-cliente").eq(0).click();
        });
        function abrirPestana() {
            localStorage.aux_lista_factura = sessionStorage.lista_factura_abierta;
            sessionStorage.clear();
            var win = window.open('{{url('factura/create')}}', '_blank');
            sessionStorage.lista_factura_abierta = localStorage.aux_lista_factura;
            localStorage.removeItem('aux_lista_factura');
        }
        function confirmaSalida(){
            if ((sessionStorage.lista_factura_abierta || productos.length > 0 ||  $("#cliente").val() != '') && !ConfirmacionRecarga )
                return "Vas a abandonar esta pagina. Si has hecho algun cambio sin grabar vas a perder todos los datos.";
        }
    </script>
@stop
