<?php $width = 700; ?>

@foreach($empleados as $e)
    <?php $width += 70; ?>
@endforeach
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <div id="chart_div" style="height:300px;width: {{$width}}px; margin: 0 auto !important;"></div>
  <script>
  google.charts.load('current', {packages: ['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawGrafica);

  function drawGrafica() {
         var data = new google.visualization.DataTable();
              data.addColumn('string', 'Empleado');
              data.addColumn('number', 'Valor vendido');
            data.addRows([
                @foreach($empleados as $e)
                    ['{!! $e->alias !!}', {{$e->total}}],
                @endforeach
            ]);

            var options = {
              title: 'Ventas por empleado',
              hAxis: {
                  title: 'Empleados',
              },
              vAxis: {
                  title: 'Valores vendidos'
              },
                chartArea: {width: '50%',height:'70%'},
            };


              var chart = new google.visualization.ColumnChart(document.getElementById('chart_div'));

              chart.draw(data, options);
      }
  </script>