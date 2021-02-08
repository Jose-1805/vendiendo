<?php if(!isset($full_screen))$full_screen = true; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')

@section('contenido')
        <div class="col s10 offset-s1 m8 offset-m2 l6 offset-l3" style="background-color: rgba(255,255,255,.85); margin-top: 100px;border-radius: 3px;">
            <div class="col s12 center"><img src="{{ asset('img/sistema/LogoCompletoMini.png') }}" class="logo-fireos" alt="Fireos SAS"></div>
            <p style="font-size: large;text-align: justify;"><strong>{{\Illuminate\Support\Facades\Auth::user()->nombres}},</strong> ha sido solicitado y registrado un cambio de versión de vendiendo.co por su administrador, este cambio
                requiere de una posterior configuración para permitir que usted y los demás usuarios continuen utilizando vendiendo.co. Su intefaz de usuario permanecerá bloqueada hasta que el administrador
                realice las configuraciones necesarias, una vez terminado el proceso de configuración se habilitarán las funcionalidades del sistema y así podrá seguir usando nuestra plataforma en su nueva versión. Para más información dirijase a <a href="#">preguntas frecuentes.</a></p>
        </div>
@stop
