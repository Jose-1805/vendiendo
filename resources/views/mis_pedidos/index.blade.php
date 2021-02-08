@extends('templates.pedidos')
<?php
    if(!isset($class_color))$class_color = "blue-grey darken-2";
    if(!isset($class_color_text))$class_color_text = "blue-grey-text";
    $admin = \App\User::find(\Illuminate\Support\Facades\Auth::user()->userAdminId());
    $plan = $admin->plan();
    if($plan->validacion_stock == "si")$validacion_stock = true;
    else $validacion_stock = false;
?>
@section('contenido')
    <div id="encabezado" class="row {{$class_color}}" >
        @include('mis_pedidos.secciones.encabezado')
    </div>
    @include('templates.teclado_numerico',['class_color'=>$class_color,'class_color_text'=>$class_color_text])
    <div class="row" id="contenido-principal">
        @include('mis_pedidos.secciones.contenido_principal')
    </div>

    <div id="modal-mas-informacion" class="modal modal-fixed-footer modal-sm modal-small" style="height: 70% !important;min-height: 70%;">
        <div class="modal-content">
            <p class="titulo-modal nombre">Nombre</p>
            <div class="col s12 contenido">
                <p class="descripcion"></p>
                <p class="valor font-large"></p>
            </div>

        </div>

        <div class="modal-footer">
                <a class="btn-flat modal-close">Cancelar</a>
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
                <a class="btn-flat" onclick="javascript:$('#modal-puntos').closeModal();">Cancelar</a>
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
                <a class="btn-flat" onclick="javascript:$('#modal-medios-pago').closeModal();actualizarDatosPrecios();">Cerrar</a>
            </div>
        </div>
    </div>

@endsection
@section('js')
    @parent
    <script>

        $( document ).ready(function(){

//            $(".button-collapse").sideNav();
            var margen=0;
            var myElement = document.getElementById('div-lista-categorias');
            var hammertime = new Hammer(myElement);
            hammertime.on('panup pandown tap press', function(ev) {

                if(ev.type=='panleft' || ev.type=='panright'){
                    document.getElementById("Listado").style.margin = "0px 0px 0px "+margen+"px";
                    console.log(margen);
                    if(ev.type=='panleft' )
                        margen-=3;
                    if(ev.type=='panright' )
                        margen+=3;
                    if(margen>0)
                        margen=0;
                }
            });
            setClassColor('{{$class_color}}');
            setClassColorText('{{$class_color_text}}');
            setValidacionStock({{$validacion_stock}});
        })

    </script>
@stop