@include("templates.mensajes",["id_contenedor"=>"gastos-diarios"])
<div class="row">
    <div class="col s12 m4 l3 input-field right">
        <input type="text" value="{{date('Y-m-d G:i')}}" name="fecha_fin" id="fecha_fin"
               data-enable-time=true data-time_24hr=true placeholder="" class="fecha"/>
        {!! Form::label("fecha_fin","Fecha fin",["class"=>"active"]) !!}
    </div>
    <div class="col s12 m4 l3 input-field right">
        <input type="text" value="{{date('Y-m-d G:i',strtotime("-1 days",strtotime(date("Y-m-d G:i"))))}}" name="fecha_inicio" id="fecha_inicio"
               data-enable-time=true data-time_24hr=true placeholder="" class="fecha"/>
        {!! Form::label("fecha_inicio","Fecha inicio",["class"=>"active"]) !!}
    </div>
</div>
<table class="bordered highlight centered" id="tabla_gastos" style="width: 100%;">
    <thead>
    <tr>
        <th>Valor</th>
        <th>Descripción</th>
        <th>Fecha</th>
        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Editar","gastos diarios","inicio"))
            <th >Editar</th>
        @else
            <th class="hide"></th>
        @endif

        @if(\Illuminate\Support\Facades\Auth::user()->permitirFuncion("Eliminar","Gastos diarios","inicio"))
            <th >Eliminar</th>
        @else
            <th class="hide"></th>
        @endif
    </tr>
    </thead>
</table>

@section('js')
    @parent
    <script type="application/javascript">
        $(document).ready(function(){
            Flatpickr.l10n.firstDayOfWeek = 1;
            document.getElementById("fecha_inicio").flatpickr({});
            document.getElementById("fecha_fin").flatpickr({});
        });
    </script>
@endsection