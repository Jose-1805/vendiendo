<?php
    if(!isset($accion))
        $accion = "Agregar";

    if(!isset($categoria))
        $categoria = new \App\Models\Categoria();
?>
    <p class="titulo-modal">{{$accion}} categoría</p>
    @include("templates.mensajes",["id_contenedor"=>"accion-categoria"])
    {!! Form::model($categoria,["id"=>"form-categoria"]) !!}
        <div id="" style="width: 100%" class="padding-40">
            <div class="input-field col s12">
                {!! Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre de la categoría"]) !!}
                <label for="nombre" class="active">Nombre</label>
            </div>
            <div class="input-field col s12">
                {!! Form::textarea("descripcion",null,["id"=>"descripcion","class"=>"materialize-textarea","placeholder"=>"Describa brevemente la categoría"]) !!}
                <label for="descripcion" class="active">Descripción</label>
            </div>

            {!! Form::hidden("accion",$accion,["name"=>"accion"]) !!}
            {!! Form::hidden("categoria",$categoria->id,["name"=>"categoria"]) !!}
            @if(isset($negocio))
                {!! Form::hidden("negocio",$negocio,["name"=>"negocio"]) !!}
            @endif
        </div>
    {!! Form::close() !!}