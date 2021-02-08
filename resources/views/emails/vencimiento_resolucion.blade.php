<h2>Notificación de vencimiento de resolución</h2>

<p>Estimad@ {{$usuario->nombres." ".$usuario->apellidos}}, este es un recordatorio de la fecha de vencimiento de su actual resolución en <a href="https://vendiendo.com.co">Vendiendo.co (sistema)</a>.</p>
<br><strong>Datos de la resolución</strong>
<p><strong>Número:</strong> {{$resolucion->numero}}</p>
<p><strong>Fecha de emisión:</strong> {{$resolucion->fecha}}</p>
<p><strong>Fecha de vencimiento:</strong> {{$resolucion->fecha_vencimiento}}</p>
<p><strong>Número inicial de factura:</strong> {{$resolucion->inicio}}</p>
<p><strong>Número final de factura:</strong> {{$resolucion->fin}}</p>