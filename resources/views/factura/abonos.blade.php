<p class="col s12 right-align" style="margin-top: -45px;"><strong>Factura:</strong> {{$factura->numero}} @if($factura->estado == 'Pendiente por pagar')<i class="fa fa-list-alt fa-2x waves-effect waves-light black-text tooltipped" id="btn-historial-abonos" data-position="bottom" data-delay="50" data-tooltip="Historial de abonos" style="cursor: pointer;margin-top: -5px;margin-left: 10px;" onclick="$('#contenedor-historial-abonos').slideToggle(500);"></i>@endif</p>
<div id="contenedor-historial-abonos" @if($factura->estado == 'Pendiente por pagar')style="display: none;"@endif>
    <strong class="col s12">Historial de abonos</strong>

    @if(count($abonos))
    <div class="col s12">
    <ul class="collapsible" data-collapsible="accordion">
    <?php $i = count($abonos)+1; ?>
    @foreach($abonos as $abono)
        <?php $i--; ?>
        <li>
            <div class="collapsible-header"><i class="fa fa-angle-double-down"> </i> Abono #{{$i}}</div>
            <div class="collapsible-body row">
                <div class="col s12 ">
                    <?php
                        $nota = "<i style='font-size: small;'>No se ha registrado ninguna nota</i>";
                        if($abono->nota != "")$nota = $abono->nota;
                    ?>
                        <p style="padding: 5px;" class="col s12"><strong>Usuario: </strong>{{$abono->usuario->nombres." ".$abono->usuario->apellidos}}</p>
                        <p style="padding: 5px;" class="col s12 m4"><strong>Fecha: </strong>{{$abono->fecha}}</p>
                        <p style="padding: 5px;" class="col s12 m4"><strong>Caja: </strong>{{$abono->caja()->nombre." - ".$abono->caja()->prefijo}}</p>
                        <p style="padding: 5px;" class="col s12 m4"><strong>Valor: </strong>$ {{number_format($abono->valor,2,',','.')}}</p>
                        <p style="padding: 5px;" class="col s12"><strong>Nota: </strong>{!! $nota !!}</p>
                </div>
            </div>
        </li>
    @endforeach
    </ul>
    </div>
    @else
        <p>No se han registrado abonos anteriormente.</p>
    @endif
    <div class="col s12 divider grey lighten-1" style="margin-top: 20px;margin-bottom: 20px;"></div>
</div>

@if($factura->estado == 'Pendiente por pagar')
    <div class="row">
        @include("templates.mensajes",["id_contenedor"=>"abonos"])
        <strong class="col s12">Registrar abono # {{count($abonos)+1}} de <div style="display: inline" id="num-coutas">{{$factura->numero_cuotas}}</div> - Saldo ($ {{number_format($factura->getSaldo(),2,',','.')}})</strong>
        {!! Form::open(["id"=>"form-abono","class"=>"col s12","style"=>"margin-top:15px;"]) !!}
            <div class="col s6 input-field">
                {!! Form::date("fecha",date("Y-m-d"),["id"=>"fecha"]) !!}
                {!! Form::label("fecha","Fecha",["class"=>"active"]) !!}
            </div>
            <div class="col s6 input-field">
                {!! Form::text("valor",null,["id"=>"valor","class"=>"num-real"]) !!}
                {!! Form::label("valor","Valor",["class"=>"active"]) !!}
            </div>
            <div class="col s12 input-field">
                {!! Form::textarea("nota",null,["id"=>"nota","class"=>"materialize-textarea"]) !!}
                {!! Form::label("nota","Nota",["class"=>"active"]) !!}
            </div>
            {!! Form::hidden("factura",$factura->id) !!}
        {!! Form::close() !!}
    </div>
@endif