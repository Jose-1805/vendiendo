{!! Form::open(["id"=>"form-historial"]) !!}
    {!! Form::hidden("cotizacion",$cotizacion->id) !!}
    <div class="input-field col s12">
        {!! Form::label("observacion","Observación",["class"=>"active"]) !!}
        {!! Form::textarea("observacion",null,["id"=>"observacion","class"=>"materialize-textarea"]) !!}
    </div>
{!! Form::close() !!}