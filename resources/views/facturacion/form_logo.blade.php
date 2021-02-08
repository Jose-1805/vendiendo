<p class="col s12 font-large margin-top-40">Logo de negocio</p>
<ul class="col s12 padding-left-30 grey-text text-darken-1">
    <li style="list-style-type: disc;">Para óptimos resultados, seleccione imagenes con dimensiones de 300px de ancho por 200px de alto.</li>
</ul>
@include("templates.mensajes",["id_contenedor"=>"upload-logo"])
<div class="col s8 offset-s2 m4 offset-m4 l2 offset-l5" style="padding: 0px !important;">
    <img id="preview-logo" src="{{$src}}" class="col s12">
</div>
{!! Form::open(["id"=>"form-logo","class"=>"col s12 m8 offset-m2 l6 offset-l3","enctype"=>"multipart/form-data"]) !!}
<div class="file-field input-field">
    <div class="file-path-wrapper">
        <input class="file-path validate" type="text" style="margin-left: -2%;">
    </div>
    <div class="btn blue-grey darken-2 col s6 btn-upload-logo">
        <span class="truncate">Seleccione un logo</span>
        <input type="file" name="logo" id="logo" style="margin-bottom: -3rem !important;">
    </div>
    <div class="col s6 offset-s6 btn-upload-logo" style="position: absolute;">
        <a class="btn blue-grey darken-2 col s12 truncate" onclick="uploadLogo();">Guardar</a>
    </div>

    <div class="progress hide" id="progres-upload-logo"><div class="indeterminate"></div></div>
</div>
{!! Form::close() !!}