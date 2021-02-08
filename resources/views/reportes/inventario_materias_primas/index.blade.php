<?php if(!isset($full_screen))$full_screen = false; if($full_screen)$size_medium = ""; else $size_medium = "m10 offset-m1"; ?>@extends('templates.master')
<?php
if(!isset($filtro)){
    $filtro = 'todos';
}
$ckTodos = "";
$ckBajoUmbral = "";
$ckSobreUmbral = "";

switch ($filtro){
    case 'todos': $ckTodos = "checked='cheked'";
        break;
    case 'bajoUmbral': $ckBajoUmbral = "checked='cheked'";
        break;
    case 'altoUmbral': $ckSobreUmbral = "checked='cheked'";
        break;
    default:
        $ckTodos = "checked='cheked'";
        $filtro = 'todos';
        break;
}
?>
@section('contenido')
    <div class="col s12 {{$size_medium}} white padding-bottom-30" style="margin-top: 85px">
        <p class="titulo">Reporte de inventario materias primas</p>

        <div class="col s12 right-align" style="margin-top: -60px;"><i class="fa fa-file-excel-o fa-2x margin-left-20 cyan-text tooltipped waves-effect waves-light" data-position="bottom" data-delay="50" data-tooltip="Reporte en Excel" style="cursor: pointer;" onclick="reporteExcel()"></i></div>
        <div class="col s12 right-align">
            <p style="display:  inline-block;">
                <input type="radio" name="filtro" value="todos" id="todos" {{$ckTodos}} onchange="cambiarFiltro('todos');">
                <label for="todos">Todos</label>
            </p>
            <p style="display:  inline-block;">
                <input type="radio" name="filtro" value="bajoUmbral" id="bajo-umbral" {{$ckBajoUmbral}} onchange="cambiarFiltro('bajoUmbral');">
                <label for="bajo-umbral">Bajo umbral</label>
            </p>
            <p style="display:  inline-block;">
                <input type="radio" name="filtro" value="altoUmbral" id="sobre-umbral" {{$ckSobreUmbral}} onchange="cambiarFiltro('altoUmbral');">
                <label for="sobre-umbral">Sobre umbral</label>
            </p>
        </div>

        {{-- @if(count($materias)) --}}
            <div class="col s12 content-table-slide" id="contenedor-inventario-materias-primas">

                <table class="table centered highlight" id="tabla_reporte_inventario_mp" width="100%">
                    <thead>
                    <th>C&oacute;digo</th>
                    <th>Nombre materia prima</th>
                    <th>Descripci&oacute;n</th>
                    <th>Umbral</th>
                    <th>Stock</th>
                    <th>Costo actual</th>
                    <th>Unidad</th>
                    <th>Proveedor actual</th>
                    </thead>
                    <tbody>
                    </tbody>
                </table>

               {{--  @include('reportes.inventario_materias_primas.lista') --}}
            </div>
       {{--  @else
            <p class="center-align">No se han encontrado materias primas para generar el reporte.</p>
        @endif --}}
    </div>
@endsection

@section('js')
    @parent
    <script src="{{asset('js/reportes/inventario_materias_primas.js')}}"></script>
@endsection
