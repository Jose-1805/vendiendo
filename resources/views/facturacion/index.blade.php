<?php
    if(Auth::user()->perfil->nombre == "administrador"){
        $usuario = Auth::user();
    }else{
        $usuario = \App\User::find(Auth::user()->usuario_creador_id);
    }

    $src = "";
    if($usuario->logo){

        if(file_exists(public_path("img/users/logo/".$usuario->id."/".$usuario->logo)))
            $src = url("/app/public/img/users/logo/".$usuario->id."/".$usuario->logo);
    }

?>
<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-40" style="margin-top: 85px">
        <p class="titulo">Facturación</p>

        @include('facturacion.form_logo')
        @include('facturacion.form_datos_factura')
        @include('facturacion.form_resolucion')


        @section('js')
        @parent
        <script>
            $(function(){
                CKEDITOR.replace( 'encabezado_factura' );
                CKEDITOR.replace( 'pie_factura' );
            })
        </script>
        @endsection
    </div>
@endsection


@section('js')
    @parent
    <script src="{{asset('js/facturacionAction.js')}}"></script>
    <script src="{{ asset('ckeditor_basic/ckeditor.js') }}"></script>
@stop