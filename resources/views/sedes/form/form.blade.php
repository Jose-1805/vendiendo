<?php
    $url = url("/sede/store/");
    $municipio = [''];
    $departamento_id = "";
    $usuario_id = "";
    $municipio_id = 0;
    $disabled = "disabled";
    if($sede->exists){
        $url = url("/sede/update/".$sede->id);
        $disabled = "";
        $departamento_id = $sede->municipio->departamento->id;
        $municipio_id = $sede->municipio_id;
        $municipio = \App\models\Municipio::select('nombre','id')->where('departamento_id',$sede->municipio->departamento->id)->lists('nombre','id');
    }
?>
@include("templates.mensajes",['id_contenedor'=>'sede-form'])
{!!Form::model($sede,["id"=>"form-sede","url"=>$url])!!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre de la sede","maxlength"=>"30"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("direccion","Direccion")!!}
        {!!Form::text("direccion",null,["id"=>"direccion","placeholder"=>"Ingrese la dirección de la sede","maxlength"=>"45"])!!}
    </div>
    <div class="input-field col s12 m6">
        {!! Form::select('departamento', $departamentos,$departamento_id, array('onchange'=> 'traerMunicipios(this.value)', 'required', 'placeholder' => 'Seleccione departamento' )) !!}
        {!!Form::label("departamento","Departamento")!!}

    </div>
    <div class="col-md-" id="DIV_LISTA_MUNICIPIOS_ERROR" style="display: none;margin-top: 25px"></div>
    <div class="input-field col s12 m6">
        {!! Form::select('municipio_id', $municipio,$municipio_id, array('id'=>'LISTA_MUNICIPIOS', 'required', 'placeholder' => 'Seleccione municipio',$disabled)) !!}
        {!!Form::label("municipio_id","Municipio")!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("latitud","Latitud")!!}
        {!!Form::text("latitud",null,["id"=>"latitud","placeholder"=>"Ingrese la latitud de la sede","maxlength"=>"30"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("longitud","Longitud")!!}
        {!!Form::text("longitud",null,["id"=>"longitud","placeholder"=>"Ingrese la longitud de la sede","maxlength"=>"45"])!!}
    </div>
    <div class="input-field col s12 m6">
        {!!Form::label("descripcion","Descripción")!!}
        {!! Form::textarea("descripcion",null,["id"=>"descripcion","class"=>"materialize-textarea","placeholder"=>"Describa brevemente la ubicación de la sede"]) !!}

    </div>
    <div class="input-field col s12 m6">
        {!! Form::select('usuario_id', $negocios,$usuario_id, array('required', 'placeholder' => 'Seleccione negocio' )) !!}
        {!!Form::label("usuario_id","Negocios")!!}

    </div>

    <div class="col s12 center" id="contenedor-action-form-sede" style="margin-top: 30px;">
        <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-sede">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-sede" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>
</div>
{!!Form::close()!!}
@section('js')
    @parent
    <script src="{{asset('js/sedes/sedesAction.js')}}"></script>
@stop