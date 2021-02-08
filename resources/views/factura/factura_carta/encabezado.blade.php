<div id="contenedor-encabezado"  style="width: 100%;">
    <?php
    $color_file = $_SERVER['DOCUMENT_ROOT']."/librerias/colors.inc.php";
    include_once($color_file);
    $logo = \Illuminate\Support\Facades\Auth::user()->logo;
    $usuario_id = \Illuminate\Support\Facades\Auth::user()->id;
    $encabezado = \Illuminate\Support\Facades\Auth::user()->encabezado_factura;

    $perfil = \Illuminate\Support\Facades\Auth::user()->perfil->nombre;
    if($perfil == "usuario"){
        $usuario_id = \App\User::find($usuario_id)->usuario_creador_id;
        $logo = \App\User::find($usuario_id)->logo;
    }
    $imagen = url("/app/public/img/users/logo/".$usuario_id."/".$logo);

    ?>
    <div id="datos-cabezara" style="margin: 10px 10px 10px 10px; ">
        <div id="logo" style=" width: 50%; height:140px; display: inline-block">
            {!! Html::image($imagen, $alt="", $attributes = array('style'=>'max-height: 100px;')) !!}
        </div>
        <div id="info-empresa" style="background-color: white;width: 50%; height:140px; display: inline-block;text-align: right;font-family: Arial, sans-serif; font-size: 9pt;color: #000 !important;">
            <p style="margin-top:0; margin-bottom:0;">{!! $factura->usuario->nombre_negocio !!}</p>
            <p style="margin-top:0; margin-bottom:0;">NIT: {!! $factura->usuario->nit !!}</p>
            <p style="margin-top:0; margin-bottom:0;">{!! str_replace("</p><p>","<br/>",$encabezado) !!}</p>
        </div>
    </div>
    <hr>
    <div id="datos-cliente" style="box-sizing: border-box; width: 100%;margin-right: 10px; margin: 0px 10px 10px 10px; ">
        <div style="width: 35%;font-size: x-small;display: inline-block;">
            <p><em>Se&ntilde;or(a): </em> {{$factura->cliente->nombre}}</p>
        </div>
        <div style="width: 30%; font-size: x-small;display: inline-block;">
            <p><em>CC/NIT: </em> {{$factura->cliente->identificacion}}</p>
        </div>
        <div style="width: 35%; font-size: x-small;margin-bottom: -5px; display: inline-block;">
            <p><em>Fecha facturaci&oacute;n: </em>{{$factura->created_at}}</p>
        </div>
    </div>
    <div style="box-sizing: border-box; width: 100%;margin-right: 10px; margin: 0px 10px 10px 10px; ">
        <div style="width: 35%; font-size: x-small;display: inline-block">
            <p><em>Direcci&oacute;n: </em> {{$factura->cliente->direccion}}</p>
        </div>
        <div style="width: 30%; font-size: x-small;display: inline-block;">
            <p><em>Telefono: </em>{{$factura->cliente->telefono}}</p>
        </div>
        <div style="width: 35%; font-size: x-small;display: inline-block; text-align: left; ">
            <p><em>Correo: </em>{{$factura->cliente->correo}}</p>
        </div>
    </div>
</div>