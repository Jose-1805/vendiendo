<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
<div class="col s12 {{$size_medium}} white" style="margin-top: 85px;">
     <p class="titulo">Crear  materia prima</p>
     @include('materias_primas.form',["funcion"=>"crear"])
</div>
@stop