
<?php
    if(!isset($anuncio))$anuncio = new \App\Models\Anuncio();
?>
@include("templates.mensajes",["id_contenedor"=>"accion-anuncio"])
{!! Form::model($anuncio,["id"=>"form-anuncio"]) !!}
    <div id="" style="width: 100%" class="padding-40">
        <div class="input-field col s12">
            {!! Form::text("titulo",null,["id"=>"titulo","placeholder"=>"Título del anuncio"]) !!}
            {!! Form::label("titulo","Título") !!}
        </div>
        <div class="col s12">
            {!! Form::label(null,"Descripción") !!}
            {!! Form::textarea("descripcion",null,["id"=>"descripcion","class"=>"materialize-textarea"]) !!}
        </div>
        <div class="input-field col s12 m6">
            {!! Form::text("valor",null,["id"=>"valor","placeholder"=>"Valor del producto","class"=>"num-entero"]) !!}
            {!! Form::label("valor","Valor") !!}
        </div>
        <div class="input-field col s12 m6">
            {!! Form::text("contacto",null,["id"=>"contacto","placeholder"=>"Contacto del vendedor"]) !!}
            {!! Form::label("contacto","Contacto") !!}
        </div>
        <div class="input-field col s12 m6">
            <?php $classOtras = "hide"; ?>
            @if(!$anuncio->exists)
                {!! Form::select("categoria",[""=>"Seleccione una categoría"]+App\Models\Categoria::where("negocio","si")->lists("nombre","id")+["otras"=>"Otras"],null,["id"=>"categoria_anuncio"]) !!}
            @else
                @if($anuncio->otras)
                    <?php $classOtras = ""; ?>
                    {!! Form::select("categoria",[""=>"Seleccione una categoría"]+App\Models\Categoria::where("negocio","si")->lists("nombre","id")+["otras"=>"Otras"],"otras",["id"=>"categoria_anuncio"]) !!}
                @else
                    {!! Form::select("categoria",[""=>"Seleccione una categoría"]+App\Models\Categoria::where("negocio","si")->lists("nombre","id")+["otras"=>"Otras"],$anuncio->categoria_id,["id"=>"categoria_anuncio"]) !!}
                @endif
            @endif
            {!! Form::label("categoria_anuncio","Categoría") !!}
        </div>
        <div class="input-field col s12 m6 {{$classOtras}}" id="otras_categorias">
            {!! Form::text("otras",null,["id"=>"otras","placeholder"=>"Otro tipo de categorías"]) !!}
            {!! Form::label("otras","Cuales?") !!}
        </div>
        <div class="row"></div>

        <div class="file-field input-field col s12 m4" style="padding-top: 10px;">
            <div class="col s12 center-align" style="height: 180px">
                @if(!$anuncio->exists || !$anuncio->imagen_1)
                    <img id="preview_1" src="{{asset('img/sistema/LogoVendiendo.png')}}" alt="Producto" class="" style="max-height: 100%;opacity: 0.2;max-width: 100%;">
                @endif
                @if($anuncio->imagen_1)
                    <img id="preview_1" src="{{url('/app/public/img/anuncios/'.$anuncio->id.'/'.$anuncio->imagen_1)}}" alt="Producto" class="" style="max-height: 100%; max-width: 100%;">
                @endif
            </div>
            <div class="file-path-wrapper col s12 m8">
                <input class="file-path validate" type="text">
            </div>
            <div class="btn blue-grey darken-2 col s12 m4">
                <span>Imagen</span>
                <input type="file" name="imagen_1" id="imagen_1" class="imagen_anuncio" data-preview="preview_1">
            </div>
        </div>
        <div class="file-field input-field col s12 m4" style="padding-top: 10px;">
            <div class="col s12 center-align" style="height: 180px">
                @if(!$anuncio->exists || !$anuncio->imagen_2)
                    <img id="preview_2" src="{{asset('img/sistema/LogoVendiendo.png')}}" alt="Producto" class="" style="max-height: 100%;opacity: 0.2;max-width: 100%;">
                @endif
                @if($anuncio->imagen_2)
                    <img id="preview_2" src="{{url('/app/public/img/anuncios/'.$anuncio->id.'/'.$anuncio->imagen_2)}}" alt="Producto" class="" style="max-height: 100%; max-width: 100%;">
                @endif
            </div>
            <div class="file-path-wrapper col s12 m8">
                <input class="file-path validate" type="text">
            </div>
            <div class="btn blue-grey darken-2 col s12 m4">
                <span>Imagen</span>
                <input type="file" name="imagen_2" id="imagen_2" class="imagen_anuncio" data-preview="preview_2">
            </div>
        </div>
        <div class="file-field input-field col s12 m4" style="padding-top: 10px;">
            <div class="col s12 center-align" style="height: 180px">
                @if(!$anuncio->exists || !$anuncio->imagen_3)
                    <img id="preview_3" src="{{asset('img/sistema/LogoVendiendo.png')}}" alt="Producto" class="" style="max-height: 100%;opacity: 0.2;max-width: 100%;">
                @endif
                @if($anuncio->imagen_3)
                    <img id="preview_3" src="{{url('/app/public/img/anuncios/'.$anuncio->id.'/'.$anuncio->imagen_3)}}" alt="Producto" class="" style="max-height: 100%; max-width: 100%;">
                @endif
            </div>
            <div class="file-path-wrapper col s12 m8">
                <input class="file-path validate" type="text">
            </div>
            <div class="btn blue-grey darken-2 col s12 m4">
                <span>Imagen</span>
                <input type="file" name="imagen_3" id="imagen_3" class="imagen_anuncio" data-preview="preview_3">
            </div>
        </div>

        {!! Form::hidden("anuncio",$anuncio->id,["name"=>"anuncio"]) !!}
    </div>
{!! Form::close() !!}

@section('js')
    @parent
    <script>
        $(function(){
            CKEDITOR.replace( 'descripcion' );
        })
    </script>
@endsection