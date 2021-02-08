<?php
$numColumns = 5;
?>
<table class="bordered highlight centered" id="tabla_sms" width="100%">
    <thead>
    <tr>
        <th >Titulo</th>
        <th >Mensaje</th>
        <th >Fecha programación</th>
        <th >Telefonos</th>
        <th >Estado</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","sms","inicio") || \Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","sms","inicio"))
            <th >Acción</th>
        @else
            <th class="hide"></th>
        @endif
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","sms","inicio"))
            <th >Eliminar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>

    <tbody>
    </tbody>
</table>


@if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","sms","inicio"))
    <div id="modal-eliminar-sms" class="modal modal-fixed-footer modal-small">
        <div class="modal-content">
            <p class="titulo-modal">Eliminar</p>
            <p>¿Está seguro de eliminar este sms?</p>
        </div>

        <div class="modal-footer">
            <div class="col s12" id="contenedor-botones-eliminar-sms">
                <a href="#!" class="red-text btn-flat" onclick="javascript: eliminarSms(id_select)">Aceptar</a>
                <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            </div>

            <div class="progress hide" id="progress-eliminar-sms">
                <div class="indeterminate cyan"></div>
            </div>
        </div>
    </div>
@endif


<div id="modal-duplicar-sms" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Duplicando mensaje</p>
        @include("templates.mensajes",['id_contenedor'=>'sms-duplicar'])

        <form name="duplicar-sms-form" id="form-sms" action="{{url('sms/duplicar-sms')}}" class="col s12">
            <div class="col s12 m4 input-field">
                <input type="text" name="f_h_programacion" id="f_h_programacion"
                       class='flatpickr' data-enable-time=true data-time_24hr=true placeholder="Fecha y hora (24 horas)"/>
                {!! Form::label("f_h_programacion_dup","Fecha y hora de envio",["class"=>"active"]) !!}
            </div>
            <div class="col s12" id="contenido-duplicar-sms" style="width: 100%">

            </div>
        </form>
    </div>

    <div class="modal-footer">
        <div class="progress hide" id="progress-action-form-duplicar-sms" style="top: 30px;margin-bottom: 30px;">
            <div class="indeterminate cyan"></div>
        </div>
        <div class="col s12" id="contenedor-botones-duplicar-sms">
            <a href="#!" class="modal-close cyan-text btn-flat">Cancelar</a>
            <a id="btn-action-form-duplicar-sms" class="red-text btn-flat">Duplicar</a>
        </div>
    </div>
</div>
<div id="modal-telefonos-sms" class="modal modal-fixed-footer">
    <div class="modal-content">
        <p class="titulo-modal">Listado de teléfonos</p>
        <div class="col s12" id="contenido-telefonos-sms" style="width: 100%">

        </div>
    </div>
    <div class="modal-footer">
        <a href="#!" class="modal-close cyan-text btn-flat">Cerrar</a>
    </div>
</div>
@section('js')

    @parent
    <script src="{{asset('js/sms/smsAction.js')}}"></script>
    <script>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","sms","inicio"))
                setPermisoEditarSMS(true);
            @else
                setPermisoEditarSMS(false);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Crear","sms","inicio"))
                setPermisoCrearSMS(true);
            @else
                setPermisoCrearSMS(false);
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","sms","inicio"))
            setPermisoEliminarSMS(true);
        @else
            setPermisoEliminarSMS(false);
        @endif
    </script>
    <script type="application/javascript">
        $(document).ready(function(){
            cargaTablaSms();
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
