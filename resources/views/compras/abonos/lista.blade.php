<?php $total_abonos = 0;?>

<input type="hidden" id="efectivo_caja" value="{{ $efectivo_caja->efectivo_final }}">
<div class="modal-content">
    <h5 class="titulo-modal right-align"><strong>Compra No. {{$compra->numero}}</strong>
        <a href="#!" class="tooltipped" data-tooltip="Ver historial de abonos" onclick="verHistorialAbonos()"><i class="fa fa-list-alt fa-2x"></i></a>
    </h5>
    <div id='mensaje-confirmacion-abonos-compra'>
    </div>
<?php $num_abonos = count($abonos);?>

    <div id="div-listado-abonos" class="row hide" >
    @if(count($abonos)>0)
            <strong>Historial de abonos</strong>
            <input type="hidden" id="numero_abonos" value="{{$num_abonos}}">
            <ul class="collapsible" data-collapsible="accordion">
                @foreach($abonos as $abono)
                    <li>
                        <div class="collapsible-header text-left"><i class="fa fa-angle-down"> </i> Abono #{{ $num_abonos }}</div>
                        <div class="collapsible-body center-block padding-10">
                            <table class="table">
                                <tr>
                                    <td width="50%"><b>Valor abono:</b> {{ $abono->valor }}</td>
                                    <td width="50%"><b>Fecha abono:</b> {{ $abono->fecha }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" class="text-center"> <b>Nota:</b> {{ $abono->nota }}</td>
                                </tr>
                            </table>
                        </div>
                    </li>
                    <?php
                        $total_abonos += $abono->valor;
                        $num_abonos--;
                    ?>
                @endforeach
            </ul>
        @else
            <div class="text-center">
                <strong class="titulo-modal">Aún no ha realizado ningún abono a la compra</strong>
            </div>
        @endif
    </div>
    <?php $num_abonos = count($abonos); ?>
    <div id="div-form-abono" style="display: block">
        <?php  /*$saldo = $compra->valor - $total_abonos;*/?>
            @if($num_abonos < config('options.numero_abonos') && $saldo > 0 && $compra->estado_pago != 'Pagada')
            <strong class="titulo-modal">Saldo actual: {{"$ ".number_format($saldo,2,',','.')}}</strong>
            <input type="hidden" id="numero-abonos-hechos" value="{{ $num_abonos }}">

            <div class="col s12" id="contenido-abonos-compra" style="width: 100%; margin-top: 25px;">
                {!! Form::open(["id"=>"form-abonos-pago","class"=>"row"]) !!}
                <div class="col s12 m6 input-field">
                    {!! Form::text("valor",0,["id"=>"valor","class"=>"num-entero"]) !!}
                    {!! Form::label("valor","Valor del abono",["class"=>"active"]) !!}
                </div>
                <div class="col s12 m6 input-field">
                    {!! Form::date("fecha",date("Y-m-d"),["id"=>"fecha"]) !!}
                    {!! Form::label("fecha","Fecha",["class"=>"active"]) !!}
                </div>
                <div class="col s12 input-field">
                    <i class="material-icons prefix">mode_edit</i>
                    {!! Form::textarea("nota",'',["id"=>"nota","class"=>'materialize-textarea']) !!}
                    {!! Form::label("nota","Nota",["class"=>"active"]) !!}

                    <input type="hidden" name="tipo_abono_id" id="tipo_abono_id" value="{{$compra->id}}">
                    <input type="hidden" name="estado_pago" id="estado_pago" value="{{$compra->estado_pago}}">

                </div>
                <div class="col s12 center margin-top-40" id="contenedor-boton-realizar-abono">
                    <a class="btn waves-effect waves-light blue-grey darken-2" id="btn-action-form-abonos-pago" onclick="abonar('{{$saldo}}')" >Realizar abono</a>
                </div>
                {!! Form::close() !!}
            </div>
            @else
                <div class="text-center">
                    <strong class="titulo-modal">Ya no puede realizar abonos a esta cuenta</strong>
                </div>
            @endif
    </div>

</div>

<script type="application/javascript">
    $(document).ready(function(){
        $('.collapsible').collapsible({
            accordion : false // A setting that changes the collapsible behavior to expandable instead of the default accordion style
        });
    });
</script>