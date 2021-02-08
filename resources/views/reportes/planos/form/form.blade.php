<?php
    $url = url("/reporte-plano/store/");
    $disabled ="";
    if($reporte_plano->exists){
        $url = url("/reporte-plano/update/".$reporte_plano->id);
        $disabled = "disabled";
    }
?>
@include("templates.mensajes",['id_contenedor'=>'reporte-plano'])
{!!Form::model($reporte_plano,["id"=>"form-reporte-plano","url"=>$url])!!}
<div class="col s12" style="padding: 20px;">
    <div class="input-field col s12 m6">
        {!!Form::label("nombre","Nombre")!!}
        {!!Form::text("nombre",null,["id"=>"nombre","placeholder"=>"Ingrese el nombre del reporte","maxlength"=>"30"])!!}
    </div>
    <?php
        $secciones_list = array(''=>'Seleccione una sección');
        if (isset($secciones)){
           foreach($secciones as $key => $seccion){
               $secciones_list[$seccion->seccion] = $seccion->seccion;
            }
        }
        if ($reporte_plano->exists){
            $secciones_list[$reporte_plano->seccion] = $reporte_plano->seccion;
        }
       ?>
    <div class="input-field col s12 m6">
        {!!Form::select('seccion', $secciones_list ,null,['class' => 'form-control','id'=>'seccion','onchange'=>'verCamposSeccion(this.value)',$disabled])!!}
        {!!Form::label("seccion","Seccion")!!}
        @if($reporte_plano->exists)
            <input type="hidden" name="seccion" value="{{$reporte_plano->seccion}}">
        @endif
    </div>

    <div id="lista-campos-seccion" class="input-field col s12">
        @if($reporte_plano->exists)
            @include('reportes.planos.form.lista_campos')
        @endif

    </div>

    <div class="col s12 center" id="contenedor-action-form-reporte-plano" style="margin-top: 30px;">
        <a class="btn blue-grey darken-2 waves-effect waves-light" id="btn-action-form-reporte-plano">Guardar</a>
    </div>

    <div class="progress hide" id="progress-action-form-reporte-plano" style="top: 30px;margin-bottom: 30px;">
        <div class="indeterminate cyan"></div>
    </div>
</div>
{!!Form::close()!!}
@section('js')
    @parent
    <script src="{{asset('js/reportes/reportes_planos.js')}}"></script>
@stop