<?php
    if(!isset($accion))
        $accion = "Agregar";

    if(!isset($data_webcam))
        $data_webcam = new \App\Models\WebCam();
?>
    <p class="titulo-modal">{{$accion}} Web Cam</p>
    @include("templates.mensajes",["id_contenedor"=>"accion-webcam"])
    {!! Form::model($data_webcam,["id"=>"form-webcam"]) !!}
        <div id="" style="width: 100%" class="padding-40">
            <div class="row">
                <div class="input-field col s12">
                    {!! Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre de la web cam"]) !!}
                    <label for="nombre" class="active">Nombre</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s6">
                    {!! Form::text("alias",null,["id"=>"alias","placeholder"=>"Digite el alias de la web cam"]) !!}
                    <label for="alias" class="active">Alias</label>
                </div>
                <div class="input-field col s6">
                    {!! Form::text("url",null,["id"=>"url","placeholder"=>"Url de la Web cam"]) !!}
                    <label for="url" class="active">Url de la WEB CAM</label>
                </div>
            </div>
            <div class="row">
                 <div class="input-field col s6">
                    {!! Form::text("usuario_acceso",null,["id"=>"usuario_acceso","placeholder"=>"Digite el usuario de acceso para la web cam"]) !!}
                    <label for="usuario_acceso" class="active">Usuario de acceso</label>
                </div>
                <div class="input-field col s6">
                {{-- password --}}
                    {!! Form::text("pass_acceso",null,["id"=>"pass_acceso","placeholder"=>"Digite la contraseña de acceso para la Web cam"]) !!}
                    <label for="pass_acceso" class="active">Contraseña de acceso</label>
                </div>
            </div>
            <div class="row">
                <div class="input-field col s12">
                {!! Form::select('ubicacion_id',array('' => 'Seleccione...') + $ubicaciones,null, array('id'=>'ubicacion_id' )) !!}
                    <label for="ubicacion_id" class="active">Ubicación web cam</label>
                </div>
            </div>
            {!! Form::hidden("accion",$accion,["name"=>"accion"]) !!}
            {!! Form::hidden("webcam",$data_webcam->id,["name"=>"webcam"]) !!}
        </div>
    {!! Form::close() !!}
    <script type="text/javascript">
    $(function(){
        inicializarMaterialize();    
    });
    </script>