<h2>Notificación de vencimiento de remisión</h2>

<p>Estimad@ {{$usuario->nombres." ".$usuario->apellidos}}, este es un recordatorio de la fecha de vencimiento de su remisión No. {{$remision->numero}}, en <a href="https://vendiendo.com.co">Vendiendo.co (sistema)</a>.</p>
<br><strong>Datos de la remisión</strong>
<p><strong>Número:</strong> {{$remision->numero}}</p>
<p><strong>Cliente:</strong> {{$remision->cliente->nombre}}</p>
<p><strong>Fecha de vencimiento:</strong> {{$remision->fecha_vencimiento}}</p>
