{!! Form::open([ 'url' => 'unidades/store/' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-unidad',  'autocomplete' =>'off'] ) !!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre de la unidad","maxlength"=>"30"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("sigla","Sigla")!!}
        {!!Form::text("sigla",null,["id"=>"sigla","placeholder"=>"Ingrese la sigla de la unidad","maxlength"=>"45"])!!}
    </div>
    {!!Form::hidden("configuracion_unidades","configuracion-unidades")!!}
    <div class="col s12 center" id="contenedor-action-form-unidad" style="margin-top: 30px;">
        <a class="btn cyan waves-effect waves-light" id="btn-action-form-unidad">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-unidad" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>
</div>
{!!Form::close()!!}
@section('js')
    @parent
    <script src="{{asset('js/unidades/unidadesAction.js')}}"></script>
@stop