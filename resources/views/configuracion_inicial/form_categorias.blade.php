{!! Form::open([ 'url' => 'categoria/store/' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-categoria',  'autocomplete' =>'off'] ) !!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!! Form::text("nombre",null,["id"=>"nombre_categoria","placeholder"=>"Ingrese el nombre de la categoría"]) !!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("descripcion","Descripción")!!}
        {!! Form::textarea("descripcion",null,["id"=>"descripcion","class"=>"materialize-textarea","placeholder"=>"Describa brevemente la categoría"]) !!}
    </div>

    <div class="col s12 center" id="contenedor-action-form-categoria" style="margin-top: 30px;">
        <a class="btn cyan waves-effect waves-light" id="btn-action-form-categoria-configuracion">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-categoria" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>
</div>
{!!Form::close()!!}
@section('js')
    @parent
    <script src="{{asset('js/categoriaAction.js')}}"></script>
@stop