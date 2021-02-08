<?php 
$f_inicio='N/A';
$f_fin='N/A';
if($registros != null){
	if($registros->fecha_llegada != null)
		$f_inicio = $registros->fecha_llegada;
	if($registros->fecha_salida != null)
		$f_fin = $registros->fecha_salida;
}

?>
<p class="titulo-modal">Información del empleado</p>
<div id="" style="width: 100%" class="padding-18">
    <div class="row">
        <div class="col s6">
           <p><label for="nombre" class="active">Nombre:  </label> {{$data_empleado->nombre}}</p>
        </div>
        <div class="col s6">
            <p><label for="cedula" class="active">Cedula:  </label> {{$data_empleado->cedula}}</p>
        </div>
         <div class="col s6">
            <p><label for="estado_empleado" class="active">Estado del empleado:  </label> {{$data_empleado->estado_empleado}}</p>
            </div>

        <div class="col s6">
            <p><label for="codigo_barras" class="active">Código de barras:  </label> {{$data_empleado->codigo_barras}}</p>
        </div>
    </div>

    @if($registros != null)
     
     <div class="card-panel">
     	<div class="row">
     		<b><center>Ultimo registro</center></b>     		
     	</div>
     	<div class="row">
     		<div class="input-field col s6">
        		<label for="estado_empleado" class="active">Fecha de inicio de sesión:  </label><p> {{$f_inicio}}</p>
    		</div>
	        <div class="input-field col s6">
	            <label for="codigo_barras" class="active">Fecha de cierre de sesión:  </label><p> {{$f_fin}}</p>
	        </div>
        </div>
    </div>
    @endif
</div>
<script type="text/javascript">
    $(function(){
        inicializarMaterialize();    
    });
</script>