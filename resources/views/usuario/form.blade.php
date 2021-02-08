<?php
$url = url("/usuario/store");
if($usuario->exists){
    $url = url("/usuario/update/".$usuario->id);
}
$user = \Illuminate\Support\Facades\Auth::user();
$hide = "hide";
$hideProveedor = "hide";
if(isset($usuario) && $usuario->exists){
    if($usuario->perfil->nombre == "proveedor"){
        $hideProveedor = "";
    }else if($usuario->perfil->nombre == "administrador"){
        $hide = "";
    }
}
?>
@include("templates.mensajes",["id_contenedor"=>"usuario"])

{!!Form::model($usuario,["id"=>"form-usuario","url"=>$url])!!}
<div class="col s12" style="padding: 20px;">
    @if($user->perfil->nombre == "superadministrador")
        <div class="input-field col s12 m6 {{$hide}}" id="contenedor-nombre-negocio">
            {!!Form::label("nombre_negocio","Nombre negocio")!!}
            {!!Form::text("nombre_negocio",null,["id"=>"nombre_negocio","placeholder"=>"Ingrese el nombre del negocio","maxlength"=>"200"])!!}
        </div>

        <div class="input-field col s12 m6 {{$hide}}" id="contenedor-categoria">
            {!!Form::label("categoria","Categoria",["class"=>"active"])!!}
            <select id="categoria" name="categoria" class="col s8 m10 l11">
                <option disabled selected>Seleccione una categoria</option>

                @foreach(\App\Models\Categoria::where("negocio","si")->get() as $c)
                    @if(isset($usuario) && $usuario->exists && $usuario->categoria_id == $c->id)
                        <option value="{{$c->id}}" selected>{{$c->nombre}}</option>
                    @else
                        <option value="{{$c->id}}">{{$c->nombre}}</option>
                    @endif
                @endforeach
            </select>
            <a class="s4 m2 l1 tooltipped modal-trigger" href="#modal-accion-categoria" data-position="bottom" data-delay="50" data-tooltip="Crear categoria"><i class="fa fa-plus" style="margin:10px;margin-top: 25px;"></i></a>
        </div>



            <div class="input-field col s12 m6 {{$hideProveedor}}" id="contenedor-categorias-proveedor">
                {!!Form::label("categorias","Categorias de negocio objetivo",["class"=>"active"])!!}
                <select id="categorias" name="categorias[]" class="col s8 m10 l11" multiple="">
                    <option disabled selected>Seleccione las categorias</option>
                    <?php
                        $categorias = $usuario->categorias;
                    ?>
                    @foreach(\App\Models\Categoria::where("negocio","si")->get() as $c)
                                <option value="{{$c->id}}"
                                @foreach($categorias as $cat)
                                        @if($cat->id == $c->id)
                                            selected
                                        @endif
                                @endforeach
                                >{{$c->nombre}}</option>
                    @endforeach
                </select>
                <a class="s4 m2 l1 tooltipped modal-trigger" href="#modal-accion-categoria" data-position="bottom" data-delay="50" data-tooltip="Crear categoria"><i class="fa fa-plus" style="margin:10px;margin-top: 25px;"></i></a>
            </div>
        @endif

    <div class="input-field col s12 m6">
        {!!Form::label("nombres","Nombres")!!}
        {!!Form::text("nombres",null,["id"=>"nombres","placeholder"=>"Ingrese los nombres del usuario","maxlength"=>"100"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("apellidos","Apellidos")!!}
        {!!Form::text("apellidos",null,["id"=>"apellidos","placeholder"=>"Ingrese los apellidos del usuario","maxlength"=>"100"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("email","Email")!!}
        {!!Form::text("email",null,["id"=>"email","placeholder"=>"Ingrese el email del usuario","maxlength"=>"80"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("telefono","Teléfono")!!}
        {!!Form::text("telefono",null,["id"=>"telefono","placeholder"=>"Ingrese el teléfono del usuario","maxlength"=>"10","class"=>"num-tel"])!!}
    </div>

    <div class="input-field col s12 m6">
        {!!Form::label("alias","Seudónimo")!!}
        {!!Form::text("alias",null,["id"=>"correo","placeholder"=>"Ingrese el nombre de usuario","maxlength"=>"30"])!!}
    </div>

    @if($user->perfil->nombre == "superadministrador")
        <div class="input-field col s12 m6 {{$hideProveedor}}" id="contenedor-departamentos">
            {!!Form::label("departamento","Departamento",["class"=>"active"])!!}
            <select id="departamento" >
                <option disabled selected>Seleccione un departamento</option>
                @foreach(\App\Models\Departamento::all() as $d)
                    @if(isset($usuario) && $usuario->exists && $usuario->municipio && $usuario->municipio->departamento->id == $d->id)
                        <option value="{{$d->id}}" selected>{{$d->nombre}}</option>
                    @else
                        <option value="{{$d->id}}">{{$d->nombre}}</option>
                    @endif
                @endforeach
            </select>
        </div>
        <div class="input-field col s12 m6 {{$hideProveedor}}" id="contenedor-municipios">
            {!!Form::label("municipio","Municipio",["class"=>"active"])!!}
            <select id="municipio" name="municipio">
                <option disabled selected>Seleccione un municipio</option>
                @if(isset($usuario) && $usuario->exists && $usuario->municipio)
                    @foreach(\App\Models\Municipio::where("departamento_id",$usuario->municipio->departamento_id)->get() as $m)
                        @if($usuario->municipio_id == $m->id)
                            <option value="{{$m->id}}" selected>{{$m->nombre}}</option>
                        @else
                            <option value="{{$m->id}}">{{$m->nombre}}</option>
                        @endif
                    @endforeach
                @endif
            </select>
        </div>
    @endif
    @if($funcion == "Crear" && ($user->perfil->nombre == "superadministrador" || $user->perfil->nombre == "administrador" ))
        @if($user->perfil->nombre == "superadministrador")
            <div class="input-field col s12 m6">
                {!!Form::label("perfil","Perfil",["class"=>"active"])!!}
                <select id="perfil" name="perfil">
                    <option disabled selected>Seleccione un perfil</option>
                    @foreach(\App\Models\Perfil::all() as $p)
                        @if($p->nombre != "usuario")
                            <option value="{{$p->id}}">{{$p->nombre}}</option>
                        @endif
                    @endforeach
                </select>
            </div>
        @elseif($user->perfil->nombre == "administrador" && $user->bodegas == 'si')
            <div class="input-field col s12 m6">
                {!!Form::label("perfil","Perfil",["class"=>"active"])!!}
                <select id="perfil" name="perfil">
                    <option disabled selected>Seleccione un perfil</option>
                    @foreach(\App\Models\Perfil::admin_user() as $p))
                    <option value="{{$p->id}}">{{$p->nombre}}</option>
                    @endforeach
                </select>
            </div>

            <div class="input-field col s12 m6 hide" id="contenedor-select-almacen">
                {!!Form::label("almacen","Almacén",["class"=>"active"])!!}
                <select id="almacen" name="almacen">
                    <option disabled selected>Seleccione un almacén</option>
                    @foreach(\App\Models\Almacen::permitidos()->get() as $a))
                    <option value="{{$a->id}}">{{$a->nombre}}</option>
                    @endforeach
                </select>
            </div>
        @endif
        <div class="input-field col s12 m6 hide" id="contenedor-check-bodegas">
            <p style="margin-top: 0px;">
                <input type="checkbox" id="bodegas" name="bodegas" value="si"/>
                <label for="bodegas">Bodegas y almacenes</label>
            </p>
        </div>

        <div class="input-field col s12 m6 hide" id="contenedor-nit">
            {!!Form::label("nit","NIT")!!}
            {!!Form::text("nit",null,["id"=>"nit","placeholder"=>"Ingrese el nit del administrador","maxlength"=>"30"])!!}
        </div>

        <div class="input-field col s12 m6 hide" id="contenedor-regimen">
            {!!Form::label("","Régimen",["class"=>"active"])!!}
            <p style="display: inline-block;">
                <input name="regimen" type="radio" id="comun" value="común" />
                <label for="comun">Común</label>
            </p>
            <p style="display: inline-block;margin-left: 40px;">
                <input name="regimen" type="radio" id="simplificado" value="simplificado" />
                <label for="simplificado">Simplificado</label>
            </p>
        </div>
        <div class="row"></div>
        <div class="input-field col s12 m6 hide" id="contenedor-plan">
            {!!Form::label("plan","Plan",["class"=>"active"])!!}
            {!!Form::select("plan",[""=>"Seleccione un plan"]+\App\Models\Plan::lista(),null,["id"=>"plan"])!!}
        </div>
    @endif

    @if($usuario->exists)
        {!! Form::hidden("perfil",$usuario->perfil->id) !!}
    @endif

    @if($funcion == "Crear")
        <div class="col s12">
        </div>
        <div class="input-field col s12 m6">
            {!!Form::label("password","Contraseña")!!}
            {!!Form::password("password",null,["id"=>"password","placeholder"=>"Ingrese la contraseña"])!!}
        </div>

        <div class="input-field col s12 m6">
            {!!Form::label("password_confirm","Verificación contraseña")!!}
            {!!Form::password("password_confirm",null,["id"=>"password_confirm","placeholder"=>"Ingrese nuevamente la contraseña"])!!}
        </div>
    @endif

    @if($funcion == "Editar" && $user->perfil->nombre == "superadministrador" && ($usuario->perfil->nombre == "administrador" || $usuario->perfil->nombre == "proveedor"))
        <div class="input-field col s12 m6" id="contenedor-nit">
            {!!Form::label("nit","NIT")!!}
            {!!Form::text("nit",null,["id"=>"nit","placeholder"=>"Ingrese el nit del administrador","maxlength"=>"30"])!!}
        </div>

        <div class="input-field col s12 m6" id="contenedor-regimen">
            {!!Form::label("","Régimen",["class"=>"active"])!!}
            <p style="display: inline-block;">
                <?php $check = ''; ?>
                @if($usuario->regimen == "común")
                    <?php $check = 'checked="true"'; ?>
                @endif
                <input name="regimen" {{$check}} type="radio" id="comun" value="común" />
                <label for="comun">Común</label>
            </p>
            <p style="display: inline-block;margin-left: 40px;">
                <?php $check = ''; ?>
                @if($usuario->regimen == "simplificado")
                    <?php $check = 'checked="true"'; ?>
                @endif
                <input name="regimen" {{$check}} type="radio" id="simplificado" value="simplificado" />
                <label for="simplificado">Simplificado</label>
            </p>
        </div>

        @if($usuario->perfil->nombre == "administrador")
            <div class="input-field col s12 m6" id="contenedor-plan">
                @if(!$usuario->plan())
                    <p class="red-text" style="font-size: 12px">Usuario con plan vencido (guardar la información de este usuario activará nuevamente el plan seleccionado)</p>
                @endif
                {!!Form::label("plan","Plan",["class"=>"active"])!!}
                {!!Form::select("plan",[""=>"Seleccione un plan"]+\App\Models\Plan::lista(),$usuario->ultimoPlan()->id,["id"=>"plan"])!!}
            </div>

            @if($usuario->plan())
                <?php
                $planUsuario = \App\Models\PlanUsuario::where("usuario_id",$usuario->userAdminId())->where("plan_id",$usuario->plan()->id)->where("estado","activo")->first();
                ?>
                <div class="input-field col s12 m6">
                    {!!Form::label("nueva_fecha","Fecha finalización plan",["class"=>"active"])!!}
                    {!!Form::text("nueva_fecha",$planUsuario->hasta,["id"=>"nueva_fecha","class"=>"flatpickr-input active", "data-enable-time"=>"true", "data-time_24hr"=>"true","readonly"=>"readonly"])!!}
                </div>

                <script>
                    $(function(){
                        document.getElementById("nueva_fecha").flatpickr({minDate: "today"});
                    })
                </script>
            @endif
        @endif
    @endif


    <div class="col s12 center" id="contenedor-action-form-usuario" style="margin-top: 30px;">
        @if($funcion == "Editar" && $user->perfil->nombre == "superadministrador" && $usuario->bodegas == 'no' && $usuario->perfil->nombre == 'administrador')
            <a class="btn blue-grey darken-2 waves-effect waves-light modal-trigger" href="#modal-version-categorias" >V. almacenes/bodegas</a>
        @endif
        <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-usuario">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-usuario" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>
</div>
{!! Form::hidden("id",$usuario->id) !!}
{!!Form::close()!!}
<div id="modal-accion-categoria" class="modal modal-fixed-footer ">
    <div class="modal-content">
        <div id="contenido-accion-categoria">
            @include('categoria.form',["categoria"=> new \App\Models\Categoria(),"accion"=>"Agregar","negocio"=>"si"])
        </div>
    </div>

    <div class="modal-footer">
        <div class="col s12" id="contenedor-botones-accion-categoria">
            <a href="#!" class="btn-flat waves-effect waves-block" id="btn-accion-categoria">Guardar</a>
            <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
        </div>
        <div class="progress hide" id="progres-accion-categoria">
            <div class="indeterminate"></div>
        </div>
    </div>
</div>
@section('js')
    @parent
    <script src="{{asset('js/categoriaAction.js')}}"></script>
    <script src="{{asset('js/usuarioAction.js')}}"></script>
    <script>
        $(function(){
            setPefilAuth('{{Auth::user()->perfil->nombre}}');
        })
    </script>
@stop