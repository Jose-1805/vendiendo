<?php
    $listaEstado = ["activo"=>"Activo","desactivo"=>"Desactivo"];
    if(!isset($accion))
        $accion = "Agregar";

    if(!isset($data_empleado))
        $data_empleado = new \App\Models\ControlEmpleados();
?>
    <p class="titulo-modal">{{$accion}} Empleado</p>
    @include("templates.mensajes",["id_contenedor"=>"accion-control-empleados"])
    {!! Form::model($data_empleado,["id"=>"form-control-empleados"]) !!}
        <div id="" style="width: 100%" class="padding-40">
            <div class="row">
                <div class="input-field col s6">
                    {!! Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre del empleado"]) !!}
                    <label for="nombre" class="active">Nombre</label>
                </div>
                <div class="input-field col s6">
                    {!! Form::text("cedula",null,["id"=>"cedula","placeholder"=>"Digite la cedula del empleado"]) !!}
                    <label for="cedula" class="active">Cédula</label>
                </div>

            </div>
            <div class="row">
                 <div class="input-field col s6">
                        {!! Form::select("estado_empleado",$listaEstado,null,["id"=>"estado_empleado"]) !!}
                        {!! Form::label("estado_empleado","Estado del empleado",["class"=>""]) !!}
                    </div>

                <div class="input-field col s6">
                    {!! Form::text("codigo_barras",null,["id"=>"codigo_barras","placeholder"=>"Ingrese codigo de barras del empleado"]) !!}
                    <label for="codigo_barras" class="active">Código de barras</label>
                </div>
            </div>
            {!! Form::hidden("accion",$accion,["name"=>"accion"]) !!}
            {!! Form::hidden("control-empleados",$data_empleado->id,["name"=>"control-empleados"]) !!}
        </div>
    {!! Form::close() !!}
<script type="text/javascript">
    $(function(){
        inicializarMaterialize();    
    });
</script>