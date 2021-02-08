<div class="row">
    <input type="hidden" name="titulo" value="{{ $sms->titulo }}">
    <input type="hidden" name="mensaje" value="{{ $sms->mensaje }}">
    <input type="hidden" id="telefonos" name="telefonos" value="{{ $sms->telefonos }}">

    @if(count($usuarios))
        <?php
        $todos = "";
        if (isset($sms->telefonos)){
            $num_telefonos = explode('-',$sms->telefonos);

            if (count($num_telefonos) == count($usuarios))
                $todos = "checked";
        }
        ?>
        <p>
            <input type="checkbox" class="filled-in" id="seleccionar-todos" {{$todos}} onchange="seleccionarTodos(this.checked)"  />
            <label for="seleccionar-todos">Seleccionar todos</label>
        </p>
        <?php
        $telefonos=array();
        if (isset($sms))
            $telefonos = explode('-',$sms->telefonos);
        ?>
        @foreach($usuarios as $usuario)
            <div class="col s3">
                <?php
                $checked = "";
                if (in_array($usuario->telefono , $telefonos)){
                    $checked = "checked";
                }
                ?>
                <p>
                    <input type="checkbox" id="{{$usuario->id ."-".$usuario->telefono}}" {{ $checked }} onchange="agregarListaTelefonos()" class="lista-telefonos"/>
                    <label for="{{$usuario->id ."-".$usuario->telefono}}">{{$usuario->nombre}}</label>
                </p>
            </div>
        @endforeach
    @else
        <h3 class="text-center">No hay usuarios registrados</h3>
    @endif

</div>
