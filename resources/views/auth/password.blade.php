@include('templates.no_cache')
<!DOCTYPE html>
<html>
    <head>
        <title>Vendiendo.co - Facturación en Línea</title>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
         <!-- Compiled and minified CSS -->
        <link rel="stylesheet" href="{{ asset('materialize/css/materialize.min.css') }}">
        <link rel="stylesheet" href="{{ asset('css/login.css') }}">

        
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">
    </head>
    <body>
        <div class="row">
            {!!Form::open(array('id'=>'form-reestablecer-password'))!!}
            <div class="contenedor-reestablecer-password-email">
                <div class="contenedor-form-email">
                    <div class="input-field">
                        <i class="fa fa-at prefix" aria-hidden="true"></i>
                        {!!Form::text("email",null,["id"=>"email"])!!}
                        {!!Form::label("email","Correo electrónico")!!}
                    </div>

                    <div class="center contenedor-botones-reestablecer-password" id="contenedor-botones-reestablecer-password">
                        <a class="btn waves-effect blue darken-4 waves-light" id="btn-reestablecer-password">Restablecer</a>
                    </div>
                    @include("templates.mensajes",["id_contenedor"=>"reestablecer-password"])

                    <div class="progress hide" id="progres-reestablecer-password">
                        <div class="indeterminate cyan"></div>
                    </div>
                    {!!Form::hidden(null,url("/"),["id"=>"base_url"])!!}
                </div>
            </div>
            <p class="center copyright" style="font-size: small;color: orange !important;">Copyright © {{date("Y")}} Fireos SAS</p>
        </div>
        {!!Form::close()!!}
       
        <script src="{{ asset('js/jquery-2.2.3.min.js') }}"></script>
        <!-- Compiled and minified JavaScript -->
        <script src="{{ asset('materialize/js/materialize.min.js') }}"></script>
        <script src="{{ asset('js/global.js') }}"></script>
        <script src="{{ asset('js/login.js') }}"></script>
    </body>
</html>
