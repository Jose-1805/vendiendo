@extends('templates.master')
@section('css')
    @parent
    <link rel="stylesheet" type="text/css" href="{{asset('fullcalendar/fullcalendar.min.css')}}">
    <link rel="stylesheet" href="http://code.jquery.com/mobile/1.2.0/jquery.mobile-1.2.0.min.css" />
    <link rel="stylesheet" type="text/css" href="{{asset('fullcalendar/fullcalendar.print.css')}}" media="print">
    <link rel="stylesheet" href="{{asset('fullcalendar/lib/cupertino/jquery-ui.min.css')}}">
    <link rel="stylesheet" href="{{asset('fullcalendar/style.css')}}">
    <style>
        #calendar {
            max-width: 900px;
            margin: 0 auto;
        }
        .color-class:after{
            border: 0px !important;
            background-color: transparent !important;

        }
        .color-class:before{
            margin-left: 24px !important;
            margin-top: 5px !important;
        }
        a.boxclose{
            float:right;
            margin-top:-20px;
            /*margin-right:-30px;*/
            cursor:pointer;
            color: #fff;
            /*border: 1px solid #AEAEAE;*/
            border-radius: 30px;
            /*background: #605F61;*/
            font-size: 25px;
            font-weight: bold;
            display: inline-block;
            line-height: 0px;
            padding: 11px 3px;
            position: relative;
            z-index: 100;

        }
        .boxclose:before {
            content: "×";
        }
        .tooltip {
            display: none;
            position: absolute;
            width: 200px;
            padding: 10px;
            margin: 0 0 12px 0;
            z-index: 100;
            bottom: 100%;
            background: #FDD017;
            color: #fff;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border-radius: 5px;
        }

        .tooltip:after {
            content: "";
            position: absolute;
            bottom: -14px;
            z-index: 100;
            border: 0 solid transparent;
            border-bottom: 14px solid transparent;
            border-left-width: 10px;
            width: 50%;
            left: 50%;
        }
        .tooltip.special_a:after {
            content: "";
            position: absolute;
            bottom: -14px;
            z-index: 100;
            border: 0 solid red;
            border-bottom: 14px solid transparent;
            border-left-width: 10px;
            width: 50%;
            left: 50%;
        }

        .tooltip:before {
            content: "";
            position: absolute;
            border: 0 solid transparent;
            bottom: -14px;
            z-index: 100;
            border-right-width: 10px;
            border-bottom: 14px solid transparent;
            width: 50%;
            right: 50%;
        }
        .tooltip.special_b:before {
            content: "";
            position: absolute;
            border: 0 solid red;
            bottom: -14px;
            z-index: 100;
            border-right-width: 10px;
            border-bottom: 14px solid transparent;
            width: 50%;
            right: 50%;
        }
    </style>
@endsection
@section('contenido')
    <div class="col s12 m10 offset-m1 white padding-10" style="margin-top: 120px;">
        <div class="col s12">
            <div id="calendar"></div>

            <div class="tooltip">Texto del tooltip</div>
        </div>

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Gestionar","Eventos","inicio"))
            <div id="modal-event" class="modal modal-fixed-footer">
                {!!Form::open(['url'=>'evento/store/','id'=>'evento-form', 'method'=>'POST'])!!}
                <div class="modal-content">
                    <p class="titulo-modal">Gestionar evento <label id="hora_label" style="margin-left: 10em; font-size: 15px; color: #00b0ff"></label></p>
                    <div id='mensaje-confirmacion-evento'></div>
                    <div class="input-field col s12 m6 center-block hide">
                        <label for="f_inicio" id="label_inicio">Inicio evento</label>
                        <input name="inicio" id="f_inicio" type="text"readonly>
                    </div>
                    <div class="input-field col s12 m6 center-block hide">
                        <label for="f_fin" id="label_fin">Fin evento</label>
                        <input name="fin" id="f_fin" type="text" readonly>
                    </div>
                    <div class="input-field col s12 m12 center-block ">
                        <label for="titulo" id="label_titulo">Titulo del evento</label>
                        <input name="titulo" id="titulo" type="text">
                    </div>
                    <div class="input-field col s12 m12">
                        <textarea id="descripcion" name="descripcion" class="materialize-textarea"></textarea>
                        <label for="descripcion" id="label_descripcion">Descripción del evento</label>
                    </div>
                    <p id="contenedor_eventos_diarios" class="hide">
                        <input type="checkbox" id="eventos_diarios" name="eventos_diarios"/>
                        <label for="eventos_diarios">Crear un evento por cada día seleccionado.</label>
                    </p>
                    <div class="input-field col s12 m12">
                        <p style="margin-bottom: -17px;">Seleccione un color que represente el evento</p>
                        <p>
                            @foreach(config('options.colores_events') as $key => $color)
                                <input name="color" type="checkbox" class="filled-in color-chek" id="filled-in-box-{{$key}}" value="{{$color}}"/>
                                <label class="color-class" for="filled-in-box-{{$key}}" style="color: {{$color}};background: {{$color}};margin-top: 5px;" onclick="seleccionarColor('filled-in-box-{{$key}}')">Color</label>
                            @endforeach
                        </p>
                    </div>
                </div>

                <div class="modal-footer">
                    <div class="progress hide" id="progress-action-form-evento" style="top: 30px;margin-bottom: 30px;">
                        <div class="indeterminate cyan"></div>
                    </div>
                    <div class="col s12" id="contenedor-botones-evento">
                        <a href="#!" class="modal-close cyan-text btn-flat" onclick="window.location.reload()">Cancelar</a>
                        <a href="#!" id="btn-action-form-evento" class="modal-action waves-effect waves-green btn-flat cyan-text" onclick="crearEvento()">Crear evento</a>
                    </div>
                </div>
                {!! Form::close() !!}
            </div>

            <div id="modal-event-edit" class="modal modal-fixed-footer">
                {!!Form::open(['url'=>'evento/update/','id'=>'evento-form-edit', 'method'=>'POST'])!!}
                <div class="modal-content">
                    <p class="titulo-modal">Gestionar evento <label id="hora_label" style="margin-left: 10em; font-size: 15px; color: #00b0ff"></label></p>
                    <div id='mensaje-confirmacion-evento-edit'></div>
                    {!! Form::hidden('id',null,['id'=>'id']) !!}

                    <div class="input-field col s12 m12 center-block ">
                        <label for="titulo" id="label_titulo">Titulo del evento</label>
                        <input name="titulo" id="titulo" type="text" class="active">
                    </div>
                    <div class="input-field col s12 m12">
                        <textarea id="descripcion" name="descripcion" class="materialize-textarea active"></textarea>
                        <label for="descripcion" id="label_descripcion">Descripción del evento</label>
                    </div>

                    <div class="input-field col s12 m12">
                        <p style="margin-bottom: 5px;">Seleccione un color que represente el evento</p>
                    </div>

                    <p>
                        @foreach(config('options.colores_events') as $key => $color)
                            <input name="color" type="checkbox" class="filled-in color-chek-edit" id="edit-filled-in-box-{{$key}}" value="{{$color}}"/>
                            <label class="color-class" for="edit-filled-in-box-{{$key}}" style="color: {{$color}};background: {{$color}};margin-top: 5px;" onclick="seleccionarColorEdit('edit-filled-in-box-{{$key}}')">Color</label>
                        @endforeach
                    </p>
                </div>
                {!! Form::close() !!}

                <div class="modal-footer">
                    <div class="progress hide" id="progress-action-form-evento-edit" style="top: 30px;margin-bottom: 30px;">
                        <div class="indeterminate cyan"></div>
                    </div>
                    <div class="col s12" id="contenedor-botones-evento">
                        <a href="#!" class="modal-close cyan-text btn-flat" onclick="window.location.reload()">Cancelar</a>
                        <a href="#!" id="btn-action-form-evento-edit" class="modal-action waves-effect waves-green btn-flat cyan-text" onclick="editarEvento()">Editar evento</a>
                    </div>
                </div>
            </div>

            <div id="modal-event-hour" class="modal modal-fixed-footer" style="width: 50%">
            <div class="modal-content">
                <p class="titulo-modal">Asignar horario</p>
                <div class="col s12 m12 input-field" style="width: 100%">
                    <input type="text" name="horaI" id="horaI"
                           class='flatpickr' data-enabletime = 'true'  data-nocalendar = 'true' value="07:00"/>
                    {!! Form::label("horaI","Hora inicio",["class"=>"active"]) !!}
                </div>
                <div class="col s12 m12 input-field" style="width: 100%">
                    <input type="text" name="horaF" id="horaF"
                           class='flatpickr' data-enabletime = 'true'  data-nocalendar = 'true' value="07:00"/>
                    {!! Form::label("horaF","Hora fin",["class"=>"active"]) !!}
                </div>
            </div>
            <div class="modal-footer">
                <a href="#!" class="modal-action modal-close waves-effect waves-green btn-flat cyan-text" onclick="asignarHora()">Asignar hora</a>
            </div>
        </div>
        @endif

        <div id="modal-event-description" class="modal" style="width: 40%; background: #546e7a; color: white;">
            <div class="modal-content">
                @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Gestionar","Eventos","inicio"))
                    <i class="fa fa-pencil-square-o right modal-trigger" id="btn-editar-evento" href="#modal-event-edit" style="margin-top: -15px;cursor: pointer;"></i>
                @endif
                <span id="title-event" style="font-size: 25px;"></span>
                <p id="description-event"></p>
            </div>
        </div>

        <div id="modal-alert" class="modal" style="width: 40%; background: #546e7a; color: white;">
            <div class="modal-content">
                <span id="title-alert" style="font-size: 25px;"></span>
                <p id="description-alert"></p>
            </div>
        </div>

    </div>
    @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Pagar","costos fijos","inicio"))
        <div id="modal-pagar-costo-fijo" class="modal modal-fixed-footer modal-small" style="max-height: 55% !important;">
            <div class="modal-content">
                <p class="titulo-modal" id="titulo-modal-pagar">Pagar</p>
                @include("templates.mensajes",["id_contenedor"=>"modal-pagar-costos-fijos"])
                <div class="row center-align" id="load-contenido-pagar-costo-fijo">
                    <i class="fa fa-spinner fa-spin" style="font-size: x-large;"></i>
                    <p>Cargando</p>
                </div>
                <div class="row hide" id="contenido-pagar-costo-fijo">
                    <div class="col s12 input-field">
                        {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero"]) !!}
                        {!! Form::label("valor","Valor",["id"=>"label-valor"]) !!}
                    </div>
                    <div class="col s12 input-field">
                        {!! Form::input("date","fecha",date("Y-m-d"),["id"=>"fecha"]) !!}
                        {!! Form::label("fecha","Fecha",["id"=>"label-fecha","class"=>"active"]) !!}
                    </div>
                    <div class="col s12">
                        <p class="font-small">Asegurese de que la información ingressada es correcta, ninguno de los datos registrados podrá ser editado posteriormente.</p>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="col s12" id="contenedor-botones-pagar-costo-fijo">
                    <a href="#!" class="cyan-text btn-flat" onclick="guardarPago();">Aceptar</a>
                    <a href="#!" class="red-text modal-close btn-flat" onclick="window.location.reload();">Cancelar</a>
                </div>

                <div class="progress hide" id="progress-pagar-costo-fijo">
                    <div class="indeterminate cyan"></div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('js')
    @parent
    <script src="{{asset('fullcalendar/lib/jquery-ui.min.js')}}"></script>
    <script src="{{asset('fullcalendar/lib/moment.min.js')}}"></script>
    <script src="{{asset('fullcalendar/fullcalendar.min.js')}}"></script>
    <script src="{{asset('fullcalendar/gcal.js')}}"></script>
    <script src="{{asset('fullcalendar/locale/es.js')}}"></script>
    <script src="{{asset('js/eventos/eventosAction.js')}}"></script>
    <script>
        $(function () {
            @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Gestionar","Eventos","inicio"))
                setPermiso(true);
                inicializarCalendario();
            @endif
        })
    </script>
@endsection