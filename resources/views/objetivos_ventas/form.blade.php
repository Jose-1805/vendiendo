<?php
    if(!isset($o)){
        $o = new \App\Models\ObjetivoVenta();
        $titulo = "Crear objetivo de venta";
        $url = url("/objetivos-ventas/store");
    }else{
        $titulo = "Editar objetivo de venta";
        $url = url("/objetivos-ventas/update");
    }
    $meses = [
        1 => "Enero",
        2 => "Febrero",
        3 => "Marzo",
        4 => "Abril",
        5 => "Mayo",
        6 => "Junio",
        7 => "Julio",
        8 => "Agosto",
        9 => "Septiembre",
        10 => "Octubre",
        11 => "Noviembre",
        12 => "Diciembre",
    ];

    for($i = date('Y');$i <= date('Y')+3;$i++)
        $anios[$i] = $i;
?>
<p class="titulo-modal">{{$titulo}}</p>
@include("templates.mensajes",["id_contenedor"=>"action-objetivo-venta"])
{!! Form::model($o,["id"=>"form-action-objetivo-venta","url"=>$url]) !!}
    <div class="col s12 input-field">
        {!! Form::text("valor",null,["id"=>"valor","class"=>"num-entero","autofocus"=>true]) !!}
        {!! Form::label("valor","Valor",["class"=>"active"]) !!}
    </div>
    <div class="col s12 m6 input-field">
        {!! Form::select("mes",$meses,null,["id"=>"mes"]) !!}
        {!! Form::label("mes","Mes") !!}
    </div>
    <div class="col s12 m6 input-field">
        {!! Form::select("anio",$anios,null,["id"=>"anio"]) !!}
        {!! Form::label("anio","Año") !!}
    </div>
    @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
        <div class="col s12 input-field">
            {!! Form::label("almacen","Almacén",["class"=>"active"]) !!}
            {!! Form::select("almacen",\App\Models\Almacen::permitidos()->lists('nombre','id'),null,['id'=>'almacen']) !!}
        </div>
    @endif
    {!! Form::hidden("id",$o->id) !!}
{!! Form::close() !!}