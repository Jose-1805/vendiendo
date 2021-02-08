{!! Form::open([ 'url' => 'proveedor/store/' ,'data-toggle'=>'validator', 'class' => 'form-inline','role'=> 'form','method' => 'POST', 'novalidate', 'id' => 'form-proveedor',  'autocomplete' =>'off'] ) !!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre del proveedor","maxlength"=>"30"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("nit","NIT")!!}
        {!!Form::text("nit",null,["id"=>"nit","placeholder"=>"Ingrese el nit del proveedor","maxlength"=>"45"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("contacto","Contacto")!!}
        {!!Form::text("contacto",null,["id"=>"contacto","placeholder"=>"Ingrese el contacto","maxlength"=>"30"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("direccion","Dirección")!!}
        {!!Form::text("direccion",null,["id"=>"direccion","placeholder"=>"Ingrese la dirección del proveedor","maxlength"=>"50"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("telefono","Teléfono")!!}
        {!!Form::text("telefono",null,["id"=>"telefono","placeholder"=>"Ingrese el teléfono del proveedor","maxlength"=>"10","class"=>"num-tel"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("correo","Correo")!!}
        {!!Form::text("correo",null,["id"=>"correo","placeholder"=>"Ingrese el correo del proveedor","maxlength"=>"80"])!!}
    </div>


    <div class="col s12 center" id="contenedor-action-form-proveedor" style="margin-top: 30px;">
        <a class="btn cyan waves-effect waves-light" id="btn-action-form-proveedor">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-proveedor" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>

</div>
{!!Form::close()!!}

@section('js')
    @parent
    <script src="{{asset('js/proveedorAction.js')}}"></script>
@stop
