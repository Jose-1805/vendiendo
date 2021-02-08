<h2>Recordatorio de vencimiento de plan</h2>

<p>Estimad@ {{$usuario->nombres." ".$usuario->apellidos}}, este es un recordatorio de la fecha de vencimiento de su plan en <a href="https://vendiendo.com.co">Vendiendo.co (sistema)</a>. Para obtener
información sobre más planes o renovación de los mismos ingrese a <a href="https://vendiendo.co">Vendiendo.co</a>.</p>
<br><strong>Datos de su plan</strong>
<p><strong>Nombre:</strong> {{$usuario->plan()->nombre}}</p>
<p><strong>Fecha de vencimiento:</strong> {{$usuario->hasta}}</p>