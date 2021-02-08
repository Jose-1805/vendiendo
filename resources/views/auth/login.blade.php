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

        <!--<meta content='width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0' />-->




        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.6.1/css/font-awesome.min.css">

    </head>

    <body>

        <div class="row">

            {!!Form::open(array('id'=>'form-inicio-sesion'))!!}

            <div class="contenedor-inicio-sesion" style="margin-left:auto; margin-right:auto;">

                <div class="contenedor-form">

                    <div class="input-field">

                        <i class="fa fa-at prefix" aria-hidden="true"></i>
                        {!!Form::text("email",null,["id"=>"email"])!!}

                        {!!Form::label("email","Correo electrónico")!!}

                    </div>



                    <div class="input-field">

                        <i class="fa fa-lock prefix" aria-hidden="true"></i>



                        {!!Form::password("password",["id"=>"password"])!!}

                        {!!Form::label("password","Contraseña")!!}

                    </div>





                    <div class="center" id="contenedor-boton-inicio">

                        <a class="btn waves-effect blue darken-4 waves-light" id="btn-inicio-sesion" style="margin-top: 30px;">Ingresar</a>

                    </div>

                    @include("templates.mensajes",["id_contenedor"=>"login"])
                    <script>
                        if(localStorage.state === null || localStorage.state != '-1'){
                            @if(\Illuminate\Support\Facades\Session::has("mensaje"))
                                <?php
                                    \Illuminate\Support\Facades\Session::flash("mensaje",\Illuminate\Support\Facades\Session::get("mensaje"));
                                ?>
                            @endif
                        }
                    </script>



                    <a href="{{url('/password/email')}}" class="col s12 blue-text text-darken-4 center" style="margin-top: 15px !important; font-size: small;">¿Olvidaste la contraseña?</a>
                    <a href="{{url('/home/preguntas-frecuentes')}}" class="col s12 blue-text text-darken-4 center" style="margin-top: 15px !important; font-size: small;">Preguntas frecuentes</a>



                    <div class="progress hide" id="progres-inicio-sesion">

                        <div class="indeterminate cyan"></div>

                    </div>

                    {!!Form::hidden(null,url("/"),["id"=>"base_url"])!!}

                </div>

            </div>


            <p class="center" style="font-size: small;color: orange !important;"><a style="color: #0d47a1;" href="{{url("/")}}">Vendiendo.co</a> recomienda su versión movil usando Google Chrome</p>
            <p class="center" style="font-size: small;color: orange !important;">Copyright © {{date("Y")}} <a style="color: #0d47a1;" href="http://fireosoft.com.co/">Fireos SAS</a></p>

        </div>

        {!!Form::close()!!}



        <script src="{{ asset('js/jquery-2.2.3.min.js') }}"></script>

        <!-- Compiled and minified JavaScript -->

        <script src="{{ asset('materialize/js/materialize.min.js') }}"></script>

        <script src="{{ asset('js/global.js') }}"></script>

        <script src="{{ asset('js/login.js') }}"></script>

    </body>

</html>

