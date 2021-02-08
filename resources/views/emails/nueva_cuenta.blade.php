<h2>Nueva cuenta de usuario</h2>

<p>Se ha creado una cuenta de usuario relacionada con este correo en <a href="{{url('/')}}">Vendiendo.co</a></p>
<p><strong>Perfil:</strong> {{$usuario->perfil->nombre}}</p>
@if($usuario->perfil->nombre == "usuario")
    <?php
        $creador = \App\User::find($usuario->usuario_creador_id);
    ?>
    <p><strong>Administrador responsable:</strong> {{$creador->nombres." ".$creador->apellidos}}</p>
@endif
<p><strong>Usuario:</strong> {{$usuario->email}}</p>
<p><strong>Contraseña:</strong> {{$clave}}</p>