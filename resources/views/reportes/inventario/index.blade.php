<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
    if(!isset($filtro)){
        $filtro = 1;
    }
    $ckTodos = "";
    $ckBajoUmbral = "";
    $ckSobreUmbral = "";

    switch ($filtro){
        case 1: $ckTodos = "checked='cheked'";
            break;
        case 2: $ckBajoUmbral = "checked='cheked'";
            break;
        case 3: $ckSobreUmbral = "checked='cheked'";
            break;
        default:
            $ckTodos = "checked='cheked'";
            $filtro = 1;
            break;
    }
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte de inventario</p>

        <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>

        @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            <div class="col s12 m4 l3 input-field">
                {!! Form::label("almacen","Almacén",["class"=>"active"]) !!}
                {!! Form::select("almacen",['Todo','bodega'=>'Bodega']+\App\Models\Almacen::permitidos()->lists('nombre','id'),null,['id'=>'almacen','onchange="cambiarFiltro()"']) !!}
            </div>
        @endif
        @if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            <div class="col s12 m8 l9 right-align">
        @else
            <div class="col s12 right-align">
        @endif
                <p style="display:  inline-block;">
                    <input type="radio" name="filtro" value="1" id="todos" {{$ckTodos}} onchange="cambiarFiltro(1);">
                    <label for="todos">Todos</label>
                </p>
                <p style="display:  inline-block;">
                    <input type="radio" name="filtro" value="2" id="bajo-umbral" {{$ckBajoUmbral}} onchange="cambiarFiltro(2);">
                    <label for="bajo-umbral">Bajo umbral</label>
                </p>
                <p style="display:  inline-block;">
                    <input type="radio" name="filtro" value="3" id="sobre-umbral" {{$ckSobreUmbral}} onchange="cambiarFiltro(3);">
                    <label for="sobre-umbral">Sobre umbral</label>
                </p>
            </div>

        {{-- @if(count($productos)) --}}
            <div class="col s12 content-table-slide" id="contenedor-inventario">
               {{--  @include('reportes.inventario.lista') --}}
                <table id="tabla_reporte_inventario" class="table centered highlight">
                    <thead>
                        <th>Código de barras</th>
                        <th>Nombre producto</th>
                        <th>Umbral</th>
                        <th>Stock</th>
                        @if(
                            (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                            ||(Auth::user()->bodegas == 'no')
                        )
                            <th>Costo unidad</th>
                            <th>Costo total</th>
                        @else
                            <th class="hide"></th>
                            <th class="hide"></th>
                        @endif
                        <th>Unidad</th>
                        <th>Categor&iacute;a</th>
                    </thead>
                </table>
            </div>
     {{--    @else
            <p class="center-align">No se han encontrado productos para generar el reporte.</p>
        @endif --}}
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/inventario.js')}}"></script>
@endsection