<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
        <p class="titulo">Crear producto</p>
        @if(\App\Models\Categoria::permitidos()->get()->count() != 0 && \App\Models\Unidad::unidadesPermitidas()->get()->count() != 0 && (\App\Models\Proveedor::permitidos()->get()->count() != 0 || \App\Models\MateriaPrima::materiasPrimasPermitidas()->get()->count() != 0))
            <input type="hidden" id="proveedores-count" value="{{\App\Models\Proveedor::permitidos()->get()->count()}}">
            <input type="hidden" id="materias-primas-count" value="{{\App\Models\MateriaPrima::materiasPrimasPermitidas()->get()->count()}}">

            <div id="mensaje-advertencia">
               @if(\App\Models\Proveedor::permitidos()->get()->count() == 0 )
                  <p style="text-align: center; background-color: #80d8ff; color: #0D47A1"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Para la creación de un producto terminado se requiere que haya regitrado al menos un proveedor</p>
               @endif
            </div>

            @include('productos.forms.form',["funcion"=>"crear"])
        @else
            <p style="text-align: center; background-color: #80d8ff; color: #0D47A1"><i class="fa fa-exclamation-triangle" aria-hidden="true"></i> Para la creación de un producto se requiere que haya regitrado unidades y categorias, además de proveedores y/o materias primas según sea el caso</p>
        @endif
    </div>
@endsection