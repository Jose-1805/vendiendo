<ul class="col s12 padding-left-30 grey-text text-darken-1">
    <li style="list-style-type: disc;">Agregue las resoluciones en orden secuencial y cronológico.</li>
</ul>

{!! Form::open(["id"=>"form-agregar-resolucion"]) !!}
<div class="col s12 m6 input-field">
    {!! Form::text("numero",null,["id"=>"numero"]) !!}
    {!! Form::label("numero","Número de resolución",["class"=>"active"]) !!}
</div>

<div class="col s12 m6 input-field">
    {!! Form::date("fecha",null,["id"=>"fecha","class"=>"datepicker"]) !!}
    {!! Form::label("fecha","Fecha emisión resolución",["class"=>"active"]) !!}
</div>

<div class="col s12 m6 input-field">
    {!! Form::date("fecha_vencimiento",null,["id"=>"fecha_vencimiento","class"=>"datepicker"]) !!}
    {!! Form::label("fecha_vencimiento","Fecha de vencimiento",["class"=>"active"]) !!}
</div>

<div class="col s12 m6 input-field">
    {!! Form::date("fecha_notificacion",null,["id"=>"fecha_notificacion","class"=>"datepicker"]) !!}
    {!! Form::label("fecha_notificacion","Fecha para notificación de vencimiento",["class"=>"active"]) !!}
</div>

<div class="col s12 m6 input-field">
    {!! Form::text("numero_notificacion",null,["id"=>"numero_notificacion","class"=>"num-entero"]) !!}
    {!! Form::label("numero_notificacion","Número para notificación de vencimiento",["class"=>"active"]) !!}
</div>

@if(!(\App\Models\Resolucion::permitidos()->get()->count() > 1))
    <div class="col s12 m6 input-field">
        {!! Form::text("inicio",null,["id"=>"inicio","class"=>"num-entero"]) !!}
        {!! Form::label("inicio","Número inicial de factura",["class"=>"active"]) !!}
    </div>

    <div class="col s12 m6 input-field">
        {!! Form::text("fin",null,["id"=>"fin","class"=>"num-entero"]) !!}
        {!! Form::label("fin","Número final de factura",["class"=>"active"]) !!}
    </div>
@endif

    <div class="col s12 center-align margin-top-20" id="contenedor-botones-agregar-resolucion">
        <a class="btn cyan waves-effect waves-light" onclick="agregarResolucion();">Guardar</a>
    </div>

    <div class="col s12 progress margin-bottom-10 hide" id="progress-agregar-resolucion">
        <div class="indeterminate"></div>
    </div>

{!! Form::close() !!}
@section('js')
    @parent
    <script src="{{asset('js/facturacionAction.js')}}"></script>
@stop