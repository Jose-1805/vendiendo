@include('templates.no_cache')
<?php
    $admin = \App\User::find(Auth::user()->userAdminId());
    if($cliente_ && \Illuminate\Support\Facades\Auth::user()->plan()->cliente_predeterminado == "si"){
        $data[0] = $cliente_->nombre;
        $data[1] = "";
        $data[3] = $cliente_->id;
    }else{
        $data[0] = "No establecido ";
        $data[1] = "No establecido ";
        $data[3] = null;
    }
?>

<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
@section('css')
    @parent
    <style>
        #datos-cliente #info-cliente p{
            margin: 0px !important;
            padding: 0px !important;
            padding: 0px !important;
        }

        #tabla-detalle-producto tbody input[type=text]{
            margin: 0 0 0 0;
        }
    </style>
@endsection
@section('contenido')
        <div class="col s12 {{$size_medium}} white no-padding padding-bottom-30" style="margin-top: 85px">
            @if(\App\Models\Resolucion::getActiva())
                @include("templates.mensajes",["id_contenedor"=>"detalle-factura"])
                <div class="col s12 m4 l3 scroll-style" style="border: 2px solid rgba(0,0,0,.3);height: 100%;overflow-y: auto;">

                        <p class="titulo-modal">Crear factura</p>
                        <div class="col s12" id="datos-cliente">
                            <p class="titulo-modal"><i class="fa fa-angle-double-up cyan white-text waves-effect waves-light btn-toggle-datos-cliente" style="cursor: pointer; border-radius: 80px !important;width: 17px;text-align: center;"></i> Datos del cliente</p>

                            <div class="input-field col m6 l4 right hide-on-small-only buscar-cliente" style="margin-top: -64px;">
                                {{-- <input type="text" name="busqueda" id="busqueda-cliente" placeholder="Buscar" value="" style="border: none !important;"> --}}
                                <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar-cliente" style="float: right;margin-top: 10px;padding: 0px 5px !important;"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
                            </div>

                            <div class="input-field col s12 hide-on-med-and-up buscar-cliente" >
                                {{-- <input type="text" name="busqueda2" id="busqueda-cliente-2" placeholder="Buscar" value=""> --}}
                                <button class="btn blue-grey darken-2 waves-effect waves-light btn-buscar-cliente" style="float: right;margin-top: -75px;/*padding: 0px 5px !important;*/"><i class="fa fa-search icono-buscar" style=""></i><i class="fa fa-spin fa-spinner hide icono-load-buscar" style=""></i></button>
                            </div>

                            <div class="col s12 no-padding" style="">
                                <p class="" style="width: 100px;display: inline-block;padding: 0px;margin: 0px;"><strong>Nombre: </strong></p>
                                <p class="dato-cliente grey-text text-darken-1" style="padding: 0px;margin: 0px;display: inline-block;" id=""><span id="txt-nombre">{{$data[0]}}</span>
                                    <span id="contenedor-botones-cliente-up" class="hide">
                                        <i class="cyan-text hide fa fa-edit btn-editar-cliente fa-1x" title="Editar cliente seleccionado" onclick="cargarEditarCliente();" style="cursor: pointer;"><i class="fa fa-spin fa-spinner hide"></i></i>
                                        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","clientes","inicio"))
                                            <i class="cyan-text modal-trigger fa fa-plus-square-o btn-crear-cliente fa-1x" title="Crear un nuevo cliente" href="#modal-crear-cliente" style="cursor: pointer;"></i>
                                        @endif
                                    </span>
                                </p>
                            </div>

                            <div id="info-cliente">

                                <div class="col s12 no-padding" style="">
                                    <p class="" style="width: 100px;display: inline-block;"><strong>Identificación: </strong></p>
                                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-identificacion">{{$data[1]}} </p>
                                </div>

                                <div class="col s12 no-padding" style="">
                                    <p class="" style="width: 100px;display: inline-block;"><strong>Teléfono: </strong></p>
                                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-telefono">{{$data[1]}}</p>
                                </div>

                                <div class="col s12 no-padding" style="">
                                    <p class="col s12" style="width: 100px;display: inline-block;"><strong>Dirección: </strong></p>
                                    <p class="dato-cliente grey-text text-darken-1" style="display: inline-block;" id="txt-direccion">{{$data[1]}}</p>
                                </div>

                                    <div class="col s12 no-padding">
                                        <input type="hidden" value="{{$data[1]}}" id="tipo-cliente">
                                    </div>

                                <div class="col s12 center-align margin-top-10" id="contenedor-botones-cliente">
                                    <i class="fa fa-spin fa-spinner hide"></i><a class="btn blue-grey darken-2 waves-effect waves-light hide btn-editar-cliente btn-small" onclick="cargarEditarCliente();">Editar</a>
                                    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","clientes","inicio"))
                                        <a class="btn blue-grey darken-2 waves-effect waves-light modal-trigger btn-crear-cliente btn-small" href="#modal-crear-cliente">Crear</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col s12" id="contenedor-estado-factura" style="margin-top: 30px;">
                        <div class="" id="estados-factura" style="display: block;">
                            <div class="input-field " style="padding: 0px;margin-bottom: 1pt" id="estado-factura">
                                {!! Form::select("estado",['Pagada'=>'Pagada','Pendiente por pagar'=>'Pendiente por pagar'],'Pagada',["id"=>"estado","class"=>"","disabled"=>"disabled"]) !!}
                                {!! Form::label("estado","Seleccionar estado de la factura") !!}
                            </div>
                        </div>
                    </div>

                    <div class="input-field col s12">
                        {!! Form::label("observaciones","Observaciones",["class"=>"active"]) !!}
                        {!! Form::textarea("observaciones",null,["id"=>"observaciones","class"=>"materialize-textarea","placeholder"=>"Observaciones de la factura"]) !!}
                    </div>


                    {{--LEO--}}
                    <div class="col s12 ">
                        <strong class="no-margin" style="display: inline-block !important; font-size: small;">Subtotal:</strong>
                        <p class="no-margin" style="display: inline-block !important; font-size: small; width: 150px;" id="txt-subtotal">$ 0,00</p>
                    </div>
                    <div class="col s12 ">
                        <strong class="no-margin" style="display: inline-block !important; font-size: small;">IVA:</strong>
                        <p class="no-margin" style="display: inline-block !important; font-size: small; width: 150px;" id="txt-iva">$ 0,00</p>
                    </div>
                    <div class="col s12 ">
                        <strong class="no-margin" style="display: inline-block !important; font-size: small;">Total a pagar:</strong>
                        <p class="no-margin" style="display: inline-block !important; font-size: small; width: 150px;" id="txt-total-pagar">$ 0,00</p>
                    </div>

                    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","facturas","inicio"))
                        <div class="col s12 center margin-top-40 no-padding padding-bottom-30" id="contenedor-boton-facturar" >
                            <a class="btn waves-effect waves-light blue-grey darken-2 btn-small margin-top-10" onclick="pagar()" >Facturar</a>
                            <a class="btn waves-effect waves-light blue-grey darken-2 tooltipped targetclass btn-small margin-top-10"  data-position="top" data-delay="50" data-tooltip="Abre una nueva pestaña para facturar, dejando la actual en espera." onclick="abrirPestana()">Aplazar venta</a>
                        </div>
                    @endif
                </div>

                <div class="col s12 m8 l9 scroll-style" id="content-detalle-factura" style="border: 2px solid rgba(0,0,0,.3);height: 100%;overflow-y: auto;">

                    <p class="titulo-modal ">Detalles de la factura <i id="muestra_div" class="fa fa-low-vision hide" title="Muestra el recuadro con el total de la factura" aria-hidden="true" style="float:right;cursor: pointer; color:rgb(140, 189, 83); font-size:24px; " onclick="oculta_div_flotante();"></i></p>
                    <div class="content-table-slide col s12">
                        @include('factura.detalle_factura')
                        {!! Form::hidden("cliente",$data[3],["id"=>"cliente"]) !!}
                    </div>
                </div>
            @else
                <p class="center">No existe ninguna resolución activa relacionada con su usuario. <a href="{{url('/facturacion')}}">Click aquí</a> para crear una resolución.</p>
            @endif
        </div>

        <div class="div_flotate" style="padding:10px; font-size:16px;">
            <i class="fa fa-minus-circle" aria-hidden="true" style="float: right;cursor:pointer; padding-top: 3px;" onclick="oculta_div_flotante()"></i>

            {{--  <div class="divider col s12 grey lighten-1" style="margin-top: -30px !important;"></div> --}}
            <div class="col s12 " style="width:80%">
                <strong class="no-margin" style="display: inline-block !important; font-weight: 300;">Total a pagar:</strong>
                <p class="no-margin" style="display: inline-block !important; word-break: break-word;" id="txt-total-pagar-flotante">$0,00</p>
                @if(count(explode('mini',strtolower(Auth::user()->ultimoPlan()->nombre))) > 1)
                    <br>
                    <p class="no-margin" style="display: inline-block !important; word-break: break-word;" id="txt-total-pagar-flotante">
                        Factura: {{(Auth::user()->countFacturasAdministrador()+1).'/'.Auth::user()->ultimoPlan()->n_facturas}}
                    </p>
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

<div id="modal-pagar" class="modal modal-fixed-footer modal-sm" style="height: 400px !important;min-height: 400px;">
    <div class="modal-content">
        <p class="titulo-modal">
            Pagar
            @if(\Illuminate\Support\Facades\Auth::user()->plan()->puntos == "si")
                <a href="#" class="margin-right-10 right tooltipped" data-position="bottom" data-delay="50" data-tooltip="Redimir puntos" onclick="javascript: showPuntosCliente();" id="btn-redimir-puntos"><i class="fa fa-credit-card-alt green-text " aria-hidden="true"></i></a>
            @endif
            <a href="#" class="margin-right-10 right tooltipped" data-position="bottom" data-delay="50" data-tooltip="Medios de pago" onclick="javascript: showMediosPago();" id="btn-medios-pago"><i class="fa fa-handshake-o green-text " aria-hidden="true"></i></a>
        </p>
        <div class="row">
           <div class="col s12 m6">
               <strong>Total factura </strong>
               <p id="total-pagar-modal">$ 0</p>
           </div>
            <div id="puntos-redimidos" class="col s12 m6 hide">
                <strong>Puntos redimidos </strong><br>
                <p id="total-puntos-modal">$ 0</p>
            </div>
            <div id="medios-pago" class="col s12 m6 hide">
                <strong>Medios de pago</strong><br>
                <p id="total-medios-pago">$ 0</p>
            </div>
            <div class="col s12 m6">
                <strong>Total a pagar </strong>
                <p id="total-pagar-neto">$ 0</p>
            </div>
            <div class="col s12 m6">
                <strong>Efectivo </strong>
                {!!Form::text("efectivo-modal",null,["id"=>"efectivo-modal","maxlength"=>"10","class"=>"num-entero focus-tecla tab-index","tabindex"=>"1"])!!}
            </div>
            <div class="col s12 m6">
                <div class="col s12 m6">
                    <strong>Regreso</strong>
                    <p id="regreso-modal">$ 0</p>
                </div>
                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Descuentos","facturas","inicio"))
                    <div class="col s12 m6">
                        <strong class="red-text">Descuento</strong>
                        <p id="descuento-modal">$ 0</p>
                    </div>
                @endif
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <div class="col s12 right-align" id="contenedor-botones-pagar-modal">
            <button style="float: none;" class="modal-close cyan-text focus-tecla btn-flat tab-index" tabindex="2">Cancelar</button>
            <button style="float: none;" class="green-text focus-tecla btn-flat tab-index" onclick="javascript: facturar(true)" tabindex="3">Omitir calculo</button>
            <button style="float: none;" class="green-text focus-tecla btn-flat tab-index" onclick="javascript: validPagar();" tabindex="4">Realizar pago</button>
        </div>

        <div class="progress hide" id="progress-pagar-modal">
            <div class="indeterminate cyan"></div>
        </div>
    </div>
</div>

<div id="modal-puntos" class="modal modal-fixed-footer modal-sm" style="height: 400px !important;min-height: 400px;">
    <div class="modal-content">
        <p class="titulo-modal">Estado de puntos del cliente</p>
        <div class="col s12 cyan white-text">
            Por favor exija el documento de identidad original y cotege los datos correspondientes
        </div>
        <div class="col s12">
            <p id="texto-puntos"></p>
            <p id="texto-total-factura"></p>
            <p id="texto-valor-puntos"></p>
        </div>

        <div class="row">
            <div class="col s12 m6 input-field">
                {!! Form::select("redimir",["1"=>"Todo","2"=>"Parcial"],null,["id"=>"redimir"]) !!}
                {!! Form::label("redimir","Redimir") !!}
            </div>
            <div class="col s12 m6 input-field">
                {!! Form::text("valor",null,["id"=>"valor","class"=>"num-real","readonly"=>"readonly"]) !!}
                {!! Form::label("valor","Valor a redimir",["class"=>"active"]) !!}
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-pagar-modal">
            <a class="green-text btn-flat" onclick="redimir()">Redimir</a>
            <a class="btn-flat" onclick="javascript:$('#modal-puntos').closeModal();$('#modal-pagar').openModal();">Cancelar</a>
        </div>
    </div>
</div>

<div id="modal-medios-pago" class="modal modal-fixed-footer modal-sm" style="height: 400px !important;min-height: 400px;">
    <div class="modal-content">
        <p class="titulo-modal">Medios de pago</p>
        <div class="row">
            @include('templates.mensajes',['id_contenedor'=>'medios-pago'])
        </div>
        <div class="row">
            @forelse($admin->tiposPago as $tp)
                <div class="col s12 m4 l3 input-field">
                    <strong>{{$tp->nombre}}</strong>
                </div>
                <div class="col s12 m4 l5 input-field">
                    @if($tp->nombre == 'Tarjeta crédito' || $tp->nombre == 'Tarjeta debito')
                        <a class="valor-total-tipo-pago fa fa-usd waves-effect waves-light blue-grey darken-2 white-text tooltipped agregar-elemento-tabla" data-position="right" data-delay="50" data-tooltip="Valor total"
                           style="
                            margin-bottom: -30px;
                            margin-top: 16px;
                            margin-left: -40px !important;
                            margin-right: 30px;
                            padding: 5px;
                            font-size: smaller;
                            border-radius: 15px;
                            width: 22px;
                            height: 22px;
                            text-align: center;
                            cursor: pointer;
                        "></a>
                    @endif
                    {!! Form::text("valor_tipo_pago_".$tp->id,null,["id"=>"valor_tipo_pago_".$tp->id,"class"=>"num-entero valor-medio-pago","data-tipo-pago"=>$tp->id]) !!}
                    {!! Form::label("valor_tipo_pago_".$tp->id,"Valor",["class"=>"active"]) !!}
                </div>
                <div class="col s12 m4 l4 input-field">
                    {!! Form::text("codigo_tipo_pago_".$tp->id,null,["id"=>"codigo_tipo_pago_".$tp->id,"class"=>""]) !!}
                    {!! Form::label("codigo_tipo_pago_".$tp->id,"Código de verificación",["class"=>"active"]) !!}
                </div>
            @empty
                <p class="center-align">No existen medios de pago habilitados.</p>
            @endforelse
        </div>

    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-pagar-modal">
            <a class="btn-flat" onclick="javascript:$('#modal-medios-pago').closeModal();$('#modal-pagar').openModal();pagar();">Cerrar</a>
        </div>
    </div>
</div>

<div id="datos_token" class="hide" style="display: none;">
    <p style="text-align: center !important;width: 100% !important;font-weight: bold;" id="nombre_negocio"></p>
    <p style="text-align: center !important;width: 100% !important;">_______________________</p>
    <p><strong>FECHA DE SOLICITUD: </strong> <span id="fecha">18/05/1993</span></p>
    <p><strong>TOKEN: </strong> <span id="token">40890894984-408</span></p>
    <p><strong>VALOR: </strong> <span id="valor">$ 5.000</span></p>
    <p><strong>VÀLIDO HASTA: </strong> <span id="valido">18/05/1993 21:00</span></p>
    <p><strong>FIRMA: </strong>____________________</p>
</div>

<div id="modal-pendiente-pagar" class="modal modal-fixed-footer modal-small" style="height: 400px !important;min-height: 350px;">
    <div class="modal-content">
        <p class="titulo-modal">Información de pagos y notificaciones</p>
        @include('templates.mensajes',["id_contenedor"=>"pendiente-pagar"])
        {!! Form::open(["id"=>"form-datos-pago","class"=>"row"]) !!}
            <div class="col s12 m12">
                {!! Form::label("dias_credito","Dias de credito",["class"=>"active"]) !!}
                {!! Form::text("dias_credito",30,["id"=>"dias_credito","class"=>"num-entero"]) !!}
            </div>
            <div class="col s12 m6 input-field">
                {!! Form::text("numero_cuotas",1,["id"=>"numero_cuotas","class"=>"num-entero"]) !!}
                {!! Form::label("numero_cuotas","Número de cuotas",["class"=>"active"]) !!}
            </div>
            <div class="col s12 m6 input-field">
                {!! Form::select("tipo_periodicidad_notificacion",[""=>"Seleccione","quincenal"=>"Quincenal","mensual"=>"Mensual","nunca"=>"Nunca"],null,["id"=>"tipo_periodicidad_notificacion"]) !!}
                {!! Form::label("tipo_periodicidad_notificacion","Periodicidad de la notificación",["class"=>"active","style"=>"margin-top:25px !important;"]) !!}
            </div>
            <div class="hide" id="periodicidad">
            <div class="col s12 m6 input-field">
                {!! Form::date("fecha_primera_notificacion",date("Y-m-d"),["id"=>"fecha_primera_notificacion"]) !!}
                {!! Form::label("fecha_primera_notificacion","Fecha primera notificacion",["class"=>"active"]) !!}
            </div>
            </div>
        {!! Form::close() !!}

    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-pendiente-pagar-modal">
            <a class="green-text btn-flat" onclick="javascript: facturar(false,true)">Aceptar</a>
            <a class="modal-close cyan-text btn-flat">Cancelar</a>
        </div>

        <div class="progress hide" id="progress-pendiente-pagar-modal">
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
    <script src="{{asset('js/facturaAction.js')}}"></script>

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

    @if($cliente_ && \Illuminate\Support\Facades\Auth::user()->plan()->cliente_predeterminado == "si")
        <script>
            $(function () {
                $(".btn-editar-cliente").removeClass("hide");
                //$(".btn-crear-cliente").addClass("hide");
                if($(".btn-toggle-datos-cliente").eq(0).hasClass("fa-angle-double-up")){
                    $(".btn-toggle-datos-cliente").eq(0).click();
                }else{
                    $("#contenedor-botones-cliente-up").removeClass("hide");
                    $("#contenedor-botones-cliente").addClass("hide");
                }
                //$(".btn-toggle-datos-cliente").click();
            })
        </script>
    @endif

    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Descuentos","facturas","inicio"))
        <script>
            $(function(){
                setAplicaDescuentos(true);
            })
        </script>
    @endif
@stop
