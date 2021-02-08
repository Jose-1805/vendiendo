@if(isset($resolucion))
    {!! Form::model($resolucion,["id"=>"form-editar-resolucion"]) !!}
    <?php $accion = "editar" ?>
@else
    {!! Form::open(["id"=>"form-agregar-resolucion"]) !!}
    <?php $accion = "agregar" ?>
@endif


@include("templates.mensajes",["id_contenedor"=>$accion."-resolucion"])

<div class="col s12 m6 input-field">
    {!! Form::text("numero",null,["id"=>"numero"]) !!}
    {!! Form::label("numero","Número de resolución",["class"=>"active"]) !!}
</div>

<div class="col s12 m6 input-field">
    {!! Form::text("prefijo",null,["id"=>"prefijo"]) !!}
    {!! Form::label("prefijo","Prefijo",["class"=>"active"]) !!}
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

@if(!(\App\Models\Resolucion::permitidos()->get()->count() > 1 && $accion == "editar"))
<div class="col s12 m6 input-field">
    {!! Form::text("inicio",null,["id"=>"inicio","class"=>"num-entero"]) !!}
    {!! Form::label("inicio","Número inicial de factura",["class"=>"active"]) !!}
</div>

<div class="col s12 m6 input-field">
    {!! Form::text("fin",null,["id"=>"fin","class"=>"num-entero"]) !!}
    {!! Form::label("fin","Número final de factura",["class"=>"active"]) !!}
</div>
@endif
@if(!isset($resolucion))
<div class="col s12 center-align margin-top-20" id="contenedor-botones-{{$accion}}-resolucion">
    <a class="btn blue-grey darken-2 waves-effect waves-light" onclick="agregarResolucion();">Guardar</a>
</div>

<div class="col s12 progress margin-bottom-10 hide" id="progress-{{$accion}}-resolucion">
    <div class="indeterminate"></div>
</div>
@else
    {!! Form::hidden("id",$resolucion->id) !!}
@endif
{!! Form::close() !!}