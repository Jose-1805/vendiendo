<?php
$todos = "";
if (isset($reporte_plano->campos)){
    $num_campos = explode('-',$reporte_plano->campos);
    $num_campos_seccion = explode('-',$campos->campos);

    if (count($num_campos) == count($num_campos_seccion))
        $todos = "checked";
}
?>
<div class="titulo">
    <input type="hidden" id="campos" name="campos" value="<?php if (isset($reporte_plano->campos)) echo $reporte_plano->campos?>">
    <div style="">Listado de campos
        <p class="" style="display: inline">
            <input type="checkbox" class="filled-in" id="seleccionar-todos" {{ $todos }} onchange="seleccionarTodos(this.checked)" />
            <label style="top: 1px;;" for="seleccionar-todos">Seleccionar todos</label>
        </p>
    </div>

</div>
<div class="row">
    @if(count($campos)>0)
        <?php
            $lista_campos = array();
            if (isset($reporte_plano))
                $lista_campos = explode('-',$reporte_plano->campos);
            $campos_array = explode('-',$campos->campos);
        ?>
        @foreach($campos_array as $campo)
                <div class="col s3">
                    <?php
                    $checked = "";
                        if (in_array($campo, $lista_campos)){
                            $checked = "checked";
                        }

                    ?>
                    <p>
                        <input type="checkbox" id="{{$campo}}" {{ $checked }} onchange="agregarListaCampos()" class="lista-campos"/>
                        <label for="{{$campo}}">{{str_replace('_',' ',ucfirst($campo))}}</label>
                    </p>
                </div>
        @endforeach
    @else
        <div>
            No hay campos relacionados con esta sección
        </div>
    @endif

</div>