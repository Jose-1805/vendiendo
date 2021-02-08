<?php
    $width = 700;
    if(!isset($almacen))$almacen = '';
?>

@foreach($objetivosVentas as $o)
    <?php $width += 70; ?>
@endforeach
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <div id="chart_div" style="height:300px;width: {{$width}}px; margin: 0 auto !important;"></div>
  <script>
  google.charts.load('current', {packages: ['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawGrafica);

  function drawGrafica() {
      // Some raw data (not necessarily accurate)
      var data = google.visualization.arrayToDataTable([
          ['Año/Mes', 'Valor Acumulado', 'Valor Fijado'],
          @foreach($objetivosVentas as $o)
              @if(Auth::user()->bodegas == 'si')
                    @if(Auth::user()->admin_bodegas == 'si')
                        @if($almacen && !is_null($almacen) && $almacen != 0)
                            ['{{$o->anio."/".$o->mes}}',  {{$o->valorAcumulado($o->almacen_id)}}, {{$o->valor}}],
                        @else
                            ['{{$o->anio."/".$o->mes}}',  {{$o->valorAcumulado()}}, {{$o->valor}}],
                        @endif
                    @else
                        ['{{$o->anio."/".$o->mes}}',  {{$o->valorAcumulado($o->almacen_id)}}, {{$o->valor}}],
                    @endif
                @else
                    ['{{$o->anio."/".$o->mes}}',  {{$o->valorAcumulado()}}, {{$o->valor}}],
              @endif
          @endforeach
      ]);

      var options = {
          title : 'Objetivos de ventas',
          vAxis: {title: 'Valores'},
          hAxis: {title: 'Meses'},
          seriesType: 'bars',
          series: {1: {type: 'line'}},
          chartArea: {width: '60%'},
      };

      var chart = new google.visualization.ComboChart(document.getElementById('chart_div'));
      chart.draw(data, options);
      }
  </script>