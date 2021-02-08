@include("templates.mensajes",["id_contenedor"=>"lista-sedes"])
<?php
$numColumns = 2;
?>
<table class="bordered highlight centered">
    <thead>
    <tr>
        <th >Nombre sede</th>
        <th >Nombre negocio</th>
        <th >Dirección</th>
        <th >Descripción</th>
        <th >Municipio</th>
        <th >Departamento</th>
        <th >Latitud</th>
        <th >Longitud</th>
        <th >Estado</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Sedes","inicio"))
            <th >Editar</th>
            <?php $numColumns++; ?>
        @endif
    </tr>
    </thead>
    <tbody>
    @if(count($sedes))
        @foreach($sedes as $sede)
            <tr>
                <td>{{$sede->nombre}}</td>
                <td>{{$sede->usuario->nombre_negocio}}</td>
                <td>{{$sede->direccion}}</td>
                <td>{{$sede->descripcion}}</td>
                <td>{{$sede->municipio->nombre}}</td>
                <td>{{$sede->municipio->departamento->nombre}}</td>
                <td>{{$sede->latitud}}</td>
                <td>{{$sede->longitud}}</td>
                <?php
                $checked ="";
                if($sede->estado == "Activo")
                    $checked = "checked";
                ?>
                <td>
                    <div class="switch">
                        <label>
                            Off
                            <input type="checkbox" id="{{$sede->id}}" {{$checked}} onclick="estadoSede(this.id,'{{$sede->estado}}')">
                            <span class="lever"></span>
                            On
                        </label>
                    </div>
                </td>

                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","Sedes","inicio"))
                    <td><a href="{{url('/sede/action/'.$sede->id)}}"><i class="fa fa-pencil-square-o fa-2x" style="cursor: pointer;"></i></a></td>
                @endif

            </tr>
        @endforeach
    @else
        <tr>
            <td colspan="{{$numColumns}}" class="center"><p>Sin resultados</p></td>
        </tr>
    @endif
    </tbody>
</table>
{!! $sedes->render() !!}
