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
        <div class="row" id="prueba">
            <div class="contenedor-reestablecer-password-confirm">
                <div class="contenedor-form-confirm">
                        <form name="form-reset" id="form-reset"  method="POST" action="{{ url('/password/reset') }}">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="input-field input-confirm-reset">
                                <i class="fa fa-at prefix " aria-hidden="true"></i>
                                <label class="col-md-4 control-label">Correo electrónico</label>
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" >
                            </div>

                            <div class="input-field input-confirm-reset">
                                <i class="fa fa-lock prefix" aria-hidden="true"></i>
                                <label class="col-md-4 control-label">Nueva contraseña</label>
                                <input type="password" class="form-control" name="password" >
                            </div>

                            <div class="input-field input-confirm-reset">
                                <i class="fa fa-lock prefix" aria-hidden="true"></i>
                                <label class="col-md-4 control-label">Confirmar contraseña</label>
                                <input type="password" class="form-control" name="password_confirmation" >
                            </div>
                            <div class="center contenedor-botones-reestablecer-confirm">
                                    <button type="button" class="btn waves-effect blue darken-4 waves-light" id="btn-form-action-reset">
                                        Cambiar Password
                                    </button>
                                @if (count($errors) > 0)
                                    <div class="contenedor-errores" id="contenedor-errores-confirm">
                                        <i class='fa fa-close btn-cerrar-errores'></i>
                                        <ul>
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>

                            @include("templates.mensajes",['id_contenedor'=>'reset-password'])
                        </form>
                        {!!Form::hidden(null,url("/"),["id"=>"base_url"])!!}
                </div>
            </div>

            <p class="center" style="font-size: small;color: orange !important;">Copyright © {{date("Y")}} Fireos SAS</p>
        </div>

        <script src="{{ asset('js/jquery-2.2.3.min.js') }}"></script>
        <!-- Compiled and minified JavaScript -->
        <script src="{{ asset('materialize/js/materialize.min.js') }}"></script>
        <script src="{{ asset('js/global.js') }}"></script>
        <script src="{{ asset('js/login.js') }}"></script>
    </body>
</html>

<div class="panel-body">

</div>
<script type="application/javascript">
    $(function () {
        $("#btn-form-action-reset").click(function (event) {
            event.preventDefault();
            var url = $("#btn-form-action-reset").attr("action");
            var params = new FormData(document.getElementById('form-reset'));
            $.ajax({
                url: url,
                type: "POST",
                dataType: "html",
                data: params,
                cache: false,
                contentType: false,
                processData: false
            }).done(function (data) {
                //console.log(data);
                $("#prueba").html(data);
            }).error(function (data) {
                mostrarErrores("contenedor-errores-reset-password",JSON.parse(data.responseText));
                //console.log(data);
            })
        })
    });


</script>

