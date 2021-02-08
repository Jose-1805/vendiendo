<input type="hidden" id="telefonos" name="telefonos" value="<?php if (isset($sms)) echo $sms->telefonos?>">
<div class="row">
    <div class="col s12 m4 input-field">
        <input type="text" value="<?php if(isset($sms))echo date_format(date_create($sms->f_h_programacion),'Y-m-d H:i')?>" name="f_h_programacion" id="f_h_programacion"
               data-enable-time=true data-time_24hr=true placeholder="<?php if(!isset($sms)) echo 'Fecha y hora (24 horas)'?>"/>
        {!! Form::label("f_h_programacion","Fecha y hora de envio",["class"=>"active"]) !!}
    </div>
</div>
<?php
    $todos = "";
        if (isset($sms->telefonos)){
            $num_telefonos = explode('-',$sms->telefonos);

            if (count($num_telefonos) == count($usuarios))
                $todos = "checked";
        }
?>
<div class="row titulo">
<div style="display:inline;margin-right: 30px;">Listado de celulares</div>
    <div style="display:inline;" class="right-align">
        <input type="checkbox" class="filled-in" id="seleccionar-todos" {{ $todos }} />
        <label for="seleccionar-todos">Seleccionar todos</label>
    </div>

    <div style="display: inline-block">
        <?php
            $plan = \Illuminate\Support\Facades\Auth::user()->plan();
            $cantidad_enviada = \App\Models\Sms::countSmsSendOk();
            $cantidad_permitida = $plan->n_promociones_sms;

            if($cantidad_permitida > 0){
                if($cantidad_enviada >= $cantidad_permitida){
                    $maximo = "Ya se ha enviado la cantidad máxima de mensajes permitidos por este mes";
                }else{
                    $maximo = $cantidad_permitida - $cantidad_enviada;
                }
            }else{
                $maximo = "Ilimitado";
            }
        ?>
        <p style="font-size: medium;color:#9e9e9e;"> - Máximo: ({{$maximo}})</p>
    </div>
</div>
    <div class="row" style="margin-bottom: 60px;">

        @if (isset($sms))
        <div class="col s12 m6 input-field">
            <?php
            $checked ="";
            if($sms->estado == "enviado")
                $checked = "checked";
            ?>
            <td>
                <div class="switch">
                    <label>
                        Pendiente
                        <input type="checkbox" id="estado" {{$checked}} name="estado">
                        <span class="lever"></span>
                        Enviado
                    </label>
                </div>
            </td>
        </div>
        @endif
    </div>

    <?php
            $telefonos=array();
            if (isset($sms))
             $telefonos = explode('-',$sms->telefonos);
    ?>
@if(count($usuarios))
   <div class="row">
       @foreach($usuarios as $usuario)
            <div class="col s2">
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
    </div>
@else
    <p class="text-center">No hay usuarios registrados</p>
@endif
@section('js')
    @parent
   <script type="application/javascript">
       $(document).ready(function(){

           Flatpickr.l10n.firstDayOfWeek = 1;
           var calendars = document.getElementsByClassName("flatpickr").flatpickr();

           document.getElementById("f_h_programacion").flatpickr({
               minDate: "today",
               defaultHour: sumaHoras('h'),
               defaultMinute: sumaHoras('s'),
           });
       });
       function sumaHoras(tipo) {
           var tiempo_programacion_sms = 60 * {{config('options.tiempo_programacion_sms')}};

           var fecha = new Date(),
                   dia = fecha.getDate(),
                   mes = fecha.getMonth() + 1,
                   anio = fecha.getFullYear(),
                   tiempo = tiempo_programacion_sms,
                   addTime = tiempo; //Tiempo en segundos

           fecha.setSeconds(addTime); //Añado el tiempo
           var minimo = '';
           if (tipo == 'h' )
             minimo = fecha.getHours();
           if (tipo == 's')
             minimo = fecha.getHours()+ ":" + fecha.getMinutes();
           return minimo;
       }
   </script>
@stop