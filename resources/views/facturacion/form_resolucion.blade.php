<p class="font-large margin-top-50 col s12">Agregar resolución</p>
<div class="col s12 right-align " style="margin-top: -3.5rem;">
    <a href="{{url('/facturacion/resoluciones')}}" target="_blank"><i class="fa fa-list-alt fa-2x waves-effect waves-light black-text tooltipped" data-position="bottom" data-delay="50" data-tooltip="Historial de resoluciones" style="cursor: pointer"></i></a>
</div>
<ul class="col s12 padding-left-30 grey-text text-darken-1">
    <li style="list-style-type: disc;">Agregue las resoluciones en orden secuencial y cronológico.</li>
</ul>
@include('facturacion.form_accion_resolucion')