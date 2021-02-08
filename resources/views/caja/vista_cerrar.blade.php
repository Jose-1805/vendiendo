<div class="input-field col m6">
    {!! Form::text(null,"$ ".number_format($relacion->valor_final,0,',','.'),["readonly"=>"readonly"])!!}
    {!! Form::hidden(null,$relacion->valor_final,["id"=>"efectivo_calculado"]) !!}
    {!! Form::label(null,"Efectivo calculado",["class"=>"active"]) !!}
</div>

<div class="input-field col m6">
    {!! Form::text("efectivo_real",0,["id"=>"efectivo_real","class"=>"num-entero"])!!}
    {!! Form::label("efectivo_real","Efectivo real",["class"=>"active"]) !!}
</div>

<p class="col m6 green-text text-darken-2"><strong>Restante: </strong>$ <span id="restante">0</span></p>
<p class="col m6 red-text text-darken-2"><strong>Faltante: </strong>$ <span id="faltante">{{number_format($relacion->valor_final,0,',','.')}}</span></p>

<?php
    $admin = \App\User::find(Auth::user()->userAdminid());
    $tipos_pago = $admin->tiposPago;
?>
@if(count($tipos_pago))
    <p class="titulo-modal">Medios de pago</p>
    <table class="hoverable">
        <thead>
            <th class="text-center">Tipo de pago</th>
            <th class="text-center">Valor</th>
        </thead>
        @foreach($tipos_pago as $tp)
            <tr>
                <td class="text-center">
                    <strong>{{$tp->nombre}}</strong>
                </td>
                <td class="text-center">
                    $ {{number_format(\App\Models\Factura::getValorCajaUsuarioTipoPago($relacion->id,$tp->id),2,',','.')}}
                </td>
            </tr>
        @endforeach
    </table>

@endif


