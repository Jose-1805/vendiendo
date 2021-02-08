<div class="row no-padding">
    <p class="col s12 m6 no-padding"><strong>Efectivo inicial: $ </strong>{{number_format($caja->efectivo_inicial,0,'','.')}}</p>
    <p class="col s12 m6 no-padding"><strong>Efectivo actual: $ </strong>{{number_format($caja->efectivo_final,0,'','.')}}</p>
</div>
<div class="input-field">
    {!! Form::label("cantidad","Cantidad") !!}
    {!! Form::text("cantidad",null,["id"=>"cantidad","class"=>"num-entero"]) !!}
</div>