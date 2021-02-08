<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
     <p class="titulo">Editar usuario</p>
     @include('usuario.form',["funcion"=>"Editar"])

     <div id="modal-version-categorias" class="modal modal-fixed-footer modal-small" style="min-height: 280px;">
          <div class="modal-content">
               <p class="titulo-modal">Versión almacenes Y bodegas</p>
               ¿Está seguro de migrar la informción del usuario a la versión de almacenes y bodegas?
               <br><br>Este paso no tiene vuelta atrás.
          </div>

          <div class="modal-footer">
               <div class="col s12">
                    <a href="#!" class="btn-flat waves-effect waves-block" id="btn-guardar-version-bodegas">Guardar</a>
                    <a href="#!" class="btn-flat waves-effect waves-block modal-close">Cerrar</a>
               </div>
          </div>
     </div>
</div>
@stop