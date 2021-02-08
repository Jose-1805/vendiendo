<!DOCTYPE html>
<html>
<head>
    <title>
        @section("titulo")
            Vendiendo.co - Facturación en Línea
        @show
    </title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @section('css')
        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <!-- Compiled and minified CSS -->
        <link rel="stylesheet" href="{{ asset('materialize/css/materialize.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/global.css') }}">
        <link rel="stylesheet" href="{{ asset('css/mis_pedidos.css') }}">
        <link rel="stylesheet" href="{{ asset('css/propiedades.css') }}">
        <link rel="stylesheet" href="{{ asset('css/teclado_numerico.css') }}">
    @show

    @section('js')
        @if(\Illuminate\Support\Facades\Auth::check())
            <script>
                mRoomid = '{{\Illuminate\Support\Facades\Auth::user()->userAdminId()}}';
                mUsername = '{{ \Illuminate\Support\Facades\Auth::user()->nombres  }}';
                /*if(localStorage.getItem("state") === null){
                    localStorage.setItem("state","1");
                    window.location.reload();
                }else{
                    if(localStorage.state != "1"){
                        localStorage.setItem("state","1");
                        window.location.reload();
                    }
                }*/

            </script>
        @endif

        <script type="text/javascript" src="{{ asset('js/jquery-2.2.3.min.js') }}"></script>
        <!-- Compiled and minified JavaScript -->
        <script src="{{ asset('materialize/js/materialize.min.js') }}"></script>
        <script src="https://use.fontawesome.com/825447006e.js"></script>
        <script src="{{ asset('js/global.js') }}"></script>
        <script src="{{ asset('js/pedidos.js') }}"></script>
        <script src="{{ asset('js/Numericos.js') }}"></script>
        <script src="{{ asset('js/pedidos/funciones.js') }}"></script>
        <script src="{{ asset('js/jquery.blockUI.js') }}"></script>
        <script src="{{ asset('js/teclado_numerico.js') }}"></script>
        <script src="{{ asset('js/validacion_numeric.js') }}"></script>
        <script src="{{ asset('js/notificaciones.js') }}"></script>
    @show
    {!! Html::script('librerias/datatable/js/jquery.dataTables.min.js') !!}
    {!! Html::style('librerias/datatable/css/jquery.dataTables.css') !!}
</head>
<body>
    {!!Form::hidden(null,url("/"),["id"=>"base_url"])!!}
    <div class="toast-numericos hide">
        <p id="valor">$ 0</p>
    </div>
    <div class="toast-vendiendo z-depth-3">
        <a href="#!" id="btn-cerrar-toast" class="right red-text"><i class="fa fa-times-circle"></i></a>
        <p id="titulo" class="truncate">Confirmación</p>
        <p id="mensaje">Mesaje de confirmación vendiendo.co </p>
    </div>
    <script>
        @if(\Illuminate\Support\Facades\Session::has("mensaje_toast"))
            <?php
                $datos = \Illuminate\Support\Facades\Session::get("mensaje_toast");
            ?>
            lanzarToast('{{$datos["mensaje"]}}','{{$datos["titulo"]}}','{{$datos["duracion"]}}','{{$datos["color_titulo"]}}');
        @endif
    </script>
    @section('contenido')
    @show
</body>
</html>
