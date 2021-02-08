@include('templates.no_cache')
<?php
    $data[0] = "No establecido ";
    $data[1] = "No establecido ";
    $data[3] = null;
?>

<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Crear remisión</p>
            @include("templates.mensajes",["id_contenedor"=>"detalle-remision"])
            <div class="col s12 m10 offset-m1" id="datos-cliente" style="border-bottom: 1px solid #b3b3b3;padding-bottom: 10px;">
                <p class="titulo-modal"><i class="fa fa-angle-double-up cyan white-text waves-effect waves-light btn-toggle-datos-cliente" style="cursor: pointer; border-radius: 80px !important;width: 17px;text-align: center;"></i> Datos del cliente</p>

                <div class="input-field col m6 l4 right hide-on-small-only buscar-cliente" style="margin-top: -64px;">
                    {{-- <input type="text" name="busqueda" id="busqueda-cliente" placeholder="Buscar" value="" style="border: none !important;"> --}}
                    <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar-cliente" style="float: right;/*margin-top: -55px;padding: 0px 5px !important;*/"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
                </div>

                <div class="input-field col s12 hide-on-med-and-up buscar-cliente" >
                    {{-- <input type="text" name="busqueda2" id="busqueda-cliente-2" placeholder="Buscar" value=""> --}}
                    <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar-cliente" style="float: right;margin-top: -75px;/*padding: 0px 5px !important;*/"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
                </div>

                <div class="col s12 no-padding" style="margin-top: -25px;">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Nombre: </strong></p>
                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id=""><span id="txt-nombre">{{$data[0]}}</span>
                        <span id="contenedor-botones-cliente-up" class="hide">
                            <i class="cyan-text hide fa fa-edit btn-editar-cliente fa-1x" title="Editar cliente seleccionado" onclick="cargarEditarCliente();" style="cursor: pointer;"><i class="fa fa-spin fa-spinner hide"></i></i>
                            <i class="cyan-text modal-trigger fa fa-plus-square-o btn-crear-cliente fa-1x" title="Crear un nuevo cliente" href="#modal-crear-cliente" style="cursor: pointer;"></i>
                        </span>
                    </p>
                </div>

                <div id="info-cliente">
                <div class="col s12 m6 no-padding" style="margin-top: -20px;">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Tipo identificación: </strong></p>
                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-tipo-identificacion">{{$data[1]}}</p>
                </div>

                <div class="col s12 m6 no-padding" style="margin-top: -20px;">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Identificación: </strong></p>
                        <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-identificacion">{{$data[1]}} </p>
                </div>

                <div class="col s12 m6 no-padding" style="margin-top: -20px;">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Teléfono: </strong></p>
                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-telefono">{{$data[1]}}</p>
                </div>

                <div class="col s12 m6 no-padding" style="margin-top: -20px;">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Correo: </strong></p>
                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-correo">{{$data[1]}}</p>
                </div>

                <div class="col s12 no-padding" style="margin-top: -20px;">
                    <p class="" style="width: 150px;display: inline-block;"><strong>Dirección: </strong></p>
                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-direccion">{{$data[1]}}</p>
                </div>

                    <div class="col s12 no-padding">
                        <input type="hidden" value="{{$data[1]}}" id="tipo-cliente">
                    </div>

                <div class="col s12 right-align margin-top-10" id="contenedor-botones-cliente">
                    <i class="fa fa-spin fa-spinner hide"></i><a class="btn blue-grey darken-2 waves-effect waves-light hide btn-editar-cliente" onclick="cargarEditarCliente();">Editar</a>
                    <a class="btn blue-grey darken-2 waves-effect waves-light modal-trigger btn-crear-cliente" href="#modal-crear-cliente">Crear</a>
                </div>
                </div>
            </div>

            <div class="col s12 m10 offset-m1 margin-top-30" id="content-detalle-remision" style="margin-top: -30px;">
                <div class="input-field row">
                    <div class="col s12 m5 l4">
                        {!! Form::date("fecha_vencimiento",date('Y-m-d'),["id"=>"fecha_vencimiento","class"=>""]) !!}
                        {!! Form::label("fecha_vencimiento","Fecha de vencimiento de la remisión",["class"=>"active"]) !!}
                    </div>
                </div>
                <p class="titulo-modal margin-top-20">Detalles de la remisión <i id="muestra_div" class="fa fa-low-vision hide" title="Muestra el recuadro con el valoe total de la remisión" aria-hidden="true" style="float:right;cursor: pointer; color:rgb(140, 189, 83); font-size:24px; " onclick="oculta_div_flotante();"></i></p>
                <div class="content-table-slide col s12">
                    @include('remisiones.detalle_remision')
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


                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","remisiones","inicio"))
                    <div class="col s12 center margin-top-40" id="" >
                        <a class="btn waves-effect waves-light blue-grey darken-2" onclick="guardarRemision();" >Guardar remisión</a>
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

<div id="modal-crear-cliente" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal">Crear cliente</p>
        @include("templates.mensajes",["id_contenedor"=>"modal-crear-cliente"])
        @include("factura.form_cliente")
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-modal-crear-cliente">
            <a class="green-text btn-flat" onclick="guardarCliente();">Guardar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-modal-crear-cliente">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-editar-cliente" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal">Editar cliente</p>
        @include("templates.mensajes",["id_contenedor"=>"modal-editar-cliente"])
        <div id="contenedor-datos-cliente-modal"></div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-modal-editar-cliente">
            <a class="green-text btn-flat" onclick="editarCliente();">Guardar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-modal-editar-cliente">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-clientes" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <p class="titulo-modal" id="titulo-modal-clientes">Clientes</p>
        <div id="contenedor-clientes" class="content-table-slide"></div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-clientes">
            <a class="green-text btn-flat" onclick="javascript: seleccionCliente()">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-clientes">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

@section('js')
    @parent
    <script src="{{asset('js/remisionAction.js')}}"></script>

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
