<?php
    $height = 40;
?>
@foreach($productos as $p)
    <?php $height += 40; ?>
@endforeach
<?php $height += 40; ?>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
  <div id="chart_div" style="height: {{$height}}px !important;"></div>
  <script>
  google.charts.load('current', {packages: ['corechart', 'bar']});
  google.charts.setOnLoadCallback(drawGrafica);

  function drawGrafica() {
         var data = google.visualization.arrayToDataTable([
                ['',''],
                @foreach($productos as $p)
                    ['{!!  $p->nombre !!}', {{$p->cantidad_vendida}}],
                @endforeach
              ]);

              var options = {
                title: 'Reporte top ventas',
                chartArea: {width: '50%'},
                hAxis: {
                  title: 'Cantidades vendidas',
                  minValue: 0
                },
                vAxis: {
                  title: ''
                }
              };

              var chart = new google.visualization.BarChart(document.getElementById('chart_div'));

              chart.draw(data, options);
      }
  </script>