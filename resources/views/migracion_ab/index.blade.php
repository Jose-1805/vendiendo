@extends("templates.master")

@section('titulo')
    Vendiendo.co - Inicio
@stop

@section('css')
    @parent
    <link rel="stylesheet" href="{{ asset('css/inicio.css') }}">
@stop

@section('contenido')
    <div class="" style="">
        @include('migracion_ab.vista_opciones')
    </div>
@stop

@section('js')
    @parent
    <script>
    </script>
@endsection
