<h2>Nuevo pedido</h2>

<p>Usted ha recibido una solicitud de pedido en <a href="{{url('/')}}">Vendiendo.co</a></p>
<p>Para gestionar este pedido ingrese a <a href="{{url('/')}}">Vendiendo.co</a> y seleccione la opción pedidos, posteriormente seleccione el pedido relacionado</p>
<br>
<strong>DATOS DEL PEDIDO</strong>
<br>
<p><strong>Número:</strong> 00{{$pedido->consecutivo}}</p>
<p><strong>Fecha de registro:</strong> {{date("Y-m-d",strtotime($pedido->created_at))}}</p>
<p><strong>Usuario:</strong> {{$usuario->nombres." ".$usuario->apellidos}}</p>
