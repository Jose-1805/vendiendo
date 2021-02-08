<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ABAlmacenStockProducto;
use App\Models\ABFactura;
use App\Models\ABHistorialUtilidad;
use App\Models\ABProducto;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Factura;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\Resolucion;
use App\Models\TokenPuntos;
use App\TildeHtml;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class MisPedidosController extends Controller {

    public function __construct(){
        $this->middleware("auth");
        //$this->middleware("modConfiguracion");
        $this->middleware("modMisPedidos");
        //$this->middleware("modCaja");*/
    }

	public function getIndex(){
        if(!Auth::user()->cajaAsignada())return redirect('/');

        $categorias = Categoria::permitidos()->get();

        return view('mis_pedidos.index')->with('categorias',$categorias);
    }

    public function getShow($id_categoria){
        $categoria = Categoria::permitidos()->where('id',$id_categoria)->first();
        if(Auth::user()->bodegas == 'si') {
            $almacen = Auth::user()->almacenActual();
            $productosByCategoria = ABProducto::permitidos()
                ->join('almacenes_stock_productos', 'productos.id', '=', 'almacenes_stock_productos.producto_id')
                ->where('almacenes_stock_productos.almacen_id', $almacen->id)
                ->where("estado", "Activo")->where('categoria_id', $id_categoria)->get();
        }else {
            $productosByCategoria = Producto::permitidos()->where("estado", "Activo")->where('categoria_id', $id_categoria)->get();
        }

        //dd($productosByCategoria);
        return view('pedido.lista_detalle')->with('productosByCategoria',$productosByCategoria)->with('categoria',$categoria);
    }

    public function postBuscarProductos(Request $request){
        if($request->has("buscar") && $request->has("class_color") && Auth::user()->cajaAsignada()){
            $buscar = "%".$request->input("buscar")."%";
            if(Auth::user()->bodegas == 'si') {
                $almacen = Auth::user()->almacenActual();
                $productos = ABProducto::permitidos()->select('productos.*')
                    ->join('almacenes_stock_productos', 'productos.id', '=', 'almacenes_stock_productos.producto_id')
                    ->where('almacenes_stock_productos.almacen_id', $almacen->id);
            }else {
                $productos = Producto::permitidos();
            }

            $productos = $productos->where(function($q)use ($buscar){
                    $q->where("nombre","like",$buscar)
                        ->orWhere("barcode","like",$buscar);
                });

            $admin = \App\User::find(Auth::user()->userAdminId());
            $plan = $admin->plan();
            if($plan->validacion_stock == "si") {
                if(Auth::user()->bodegas == 'si'){
                    $almacen = Auth::user()->almacenActual();
                    $productos = $productos->join('almacenes_stock_productos','productos.id','=','almacenes_stock_productos.producto_id')
                        ->where('almacenes_stock_productos.almacen_id',$almacen->id)
                        ->where('almacenes_stock_productos.stcck','>','0');
                }else {
                    //$productos = $productos->where("stock", ">", "0");
                }
            }

            $productos = $productos->get();
            $html = "";
            if(count($productos)){
                $html .= "<ul class=''>";
                foreach ($productos as $p){
                    $enviar = true;
                    if(($p->tipo_producto != 'Compuesto' || $p->omitir_stock_mp != 'si') && $p->stock <= 0 && $plan->validacion_stock == 'si')
                        $enviar = false;
                    if($p->tipo_producto == 'Compuesto' && $p->omitir_stock_mp == 'si'){
                        $p->stock = $p->DisponibleOmitirStockMp();
                    }
                    if($enviar)
                        $html .= view('mis_pedidos.secciones.vista_elemento',["pr"=>$p,"vista_buscar"=>true,"class_color"=>$request->input('class_color')])->render();
                }
                $html .= "</ul>";
            }else{
                $html .= "<p class='font-small center-align'>No se encontraron productos con (".$request->input('buscar').")</p>";
            }
            return $html;
        }
        return response(["erro"=>["La información enviada en incorrecta"]],422);
    }

    public function postMasProductos(Request $request){
        if($request->has("categoria") && $request->has("cantidad") && $request->has("class_color") && $request->has("class_color_text") && Auth::user()->cajaAsignada()){
            $categoria = Categoria::permitidos()->find($request->input("categoria"));
            if($categoria) {
                $productos_en_vista = [];
                if ($request->has("productos_en_vista") && is_array($request->input("productos_en_vista"))) {
                    $productos_en_vista = $request->input("productos_en_vista");
                }

                if(Auth::user()->bodegas == 'si') {
                    $almacen = Auth::user()->almacenActual();
                    $productos = ABProducto::permitidos()
                        ->join('almacenes_stock_productos','productos.id','=','almacenes_stock_productos.producto_id')
                        ->where('almacenes_stock_productos.almacen_id',$almacen->id);
                }else {
                    $productos = Producto::permitidos();
                }

                $productos = $productos->where("productos.categoria_id",$categoria->id)
                    ->whereNotIn("productos.id",$productos_en_vista)->take($request->input("cantidad"))->get();

                $html = "";
                $mensaje = false;
                foreach ($productos as $p){
                    $html .= view('mis_pedidos.secciones.vista_elemento',['pr'=>$p,'class_color'=>$request->input('class_color'),'class_color_text'=>$request->input('class_color_text')])->render();
                    $productos_en_vista[] = $p->id;
                }
                if(!count($productos))$mensaje = "No se han encontrado más productos para mostrar";
                return ["view"=>$html,"productos_en_vista"=>$productos_en_vista,"mensaje"=>$mensaje];
            }

        }
    }

    public function postVerClientes(Request $request){
        return view("mis_pedidos.secciones.vista_clientes");
    }

    public function getListaClientes(Request $request){
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'id';

        $clientes = Cliente::permitidos()->select('id','nombre','identificacion','correo');

        $clientes = $clientes->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $clientes->count();
        //BUSCAR
        if($search['value'] != null){
            $clientes  = $clientes->whereRaw(
                "( LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR ".
                "identificacion LIKE '%".$search["value"]."%' OR ".
                "LOWER(correo) LIKE '%".\strtolower($search["value"])."%')");
        }

        $parcialRegistros = $clientes->count();
        $clientes = $clientes->skip($start)->take($length);
        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $clientes->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'fecha_inicio' => $request->get("fecha_inicio"),
            'count' => $parcialRegistros,
            'data' =>$clientes->get()];

        return response()->json($data);
    }

    public function postInfoCliente(Request $request){
        if($request->has("cliente")){
            $cliente = Cliente::permitidos()->find($request->input("cliente"));
            if($cliente){
                return $cliente;
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postStore(Request $request){
        $caja = Auth::user()->cajaAsignada();
        if(Auth::user()->permitirFuncion("Crear","facturas","inicio") && Auth::user()->permitirFuncion("Crear","Factura tactil","inicio") && $caja) {

            $admin = \App\User::find(Auth::user()->userAdminId());
            $plan = $admin->plan();
            if($plan->validacion_stock == "si")$validacion_stock = true;
            else $validacion_stock = false;

            $resolucion = Resolucion::getActiva();
            //si no hay resolución activa se busca la resolución en espera con menor numero de inicio y se activa (si existe)
            if(!$resolucion){
                $r = Resolucion::permitidos()->where("estado","en espera")->orderBy("inicio","ASC")->first();
                if($r) {
                    $r->estado = "activa";
                    $r->save();
                    $resolucion = $r;
                }
            }
            $resolucion_espera = Resolucion::permitidos()->where("estado","en espera")->orderBy("inicio","ASC")->first();
            if($resolucion) {
                if ($request->has("cliente")) {
                    $cliente = Cliente::permitidos()->where("id", $request->input("cliente")["id"])->first();

                    if ($cliente) {
                        DB::beginTransaction();

                        if(Auth::user()->bodegas == 'si')
                            $ultimaFact = ABFactura::ultimaFactura();
                        else
                            $ultimaFact = Factura::ultimaFactura();
                        $numero = 1;
                        if($ultimaFact){
                            $numero = intval($ultimaFact->numero) + 1;
                        }else{
                            $numero = intval($resolucion->inicio);
                        }
                        if(!($resolucion->inicio <= $numero && $resolucion->fin >= $numero)){
                            return response(["Error" => ["A ocurrido un error generando el número de la factura, por favor verifique que su resolución actual no haya expirado."]], 422);
                        }

                        if(Auth::user()->bodegas == 'si'){
                            if(ABFactura::permitidos()->where("numero","00".$numero)->first()){
                                return response(["Error" => ["A ocurrido un error generando el número de la factura para el pedido, por favor recargue su página y diligencie nuevamente el pedido."]], 422);
                            }
                        }else{
                            if(Factura::permitidos()->where("numero","00".$numero)->first()){
                                return response(["Error" => ["A ocurrido un error generando el número de la factura para el pedido, por favor recargue su página y diligencie nuevamente el pedido."]], 422);
                            }
                        }

                        //se desactiva la anterior resolución y se activa la que esta en espera si existe y tiene el numero de inicio igual al consucutivo que se genera para la actual factura
                        if($resolucion_espera && $resolucion_espera->inicio == $numero){
                            $resolucion->estado = "terminada";
                            $resolucion->save();
                            $resolucion_espera->estado = "activa";
                            $resolucion_espera->save();
                            $resolucion = $resolucion_espera;
                        }

                        $relacion_caja_usuario = Auth::user()->cajas()->select("cajas_usuarios.id")->where("cajas_usuarios.caja_id",$caja->id)
                            ->where("cajas_usuarios.estado","activo")->first();
                        $continuar = true;
                        if(Auth::user()->bodegas == 'si')
                            $factura = new ABFactura();
                        else
                            $factura = new Factura();
                        $factura->numero = "00".$numero;
                        $factura->estado = "Pagada";
                        $factura->cliente_id = $cliente->id;
                        $factura->usuario_creador_id = Auth::user()->id;
                        $factura->caja_usuario_id = $relacion_caja_usuario->id;
                        $factura->usuario_id = Auth::user()->userAdminId();

                        if(Auth::user()->bodegas == 'si')
                            $factura->almacen_id = Auth::user()->almacenActual()->id;

                        $factura->resolucion_id = $resolucion->id;
                        $factura->subtotal = 0;
                        $factura->iva = 0;

                        $factura->save();

                        //si el numero de la factura creada es igual al numero de notificacion de la resolución se envia notificación por correo
                        if(intval($factura->numero) == $resolucion->numero_notificacion){
                            $usuario = User::find(Auth::user()->userAdminid());
                            $datos = [
                                "usuario" => $usuario,
                                "resolucion" => $resolucion
                            ];

                            $resolucion_espera = Resolucion::where("usuario_id",$usuario->id)->where("estado","en espera")->orderBy("inicio","ASC")->first();
                            if(!$resolucion_espera) {
                                Mail::send('emails.vencimiento_resolucion', $datos, function ($m) use ($usuario) {
                                    $m->from('notificaciones@vendiendo.co', 'Vendiendo.co');

                                    $m->to($usuario->email, $usuario->nombres . " " . $usuario->apellidos)->subject('Vendiendo.co - vencimiento resolución');
                                });
                            }
                        }

                        //si es la ultima factura se termina la resolucion
                        if($numero == intval($resolucion->fin)){
                            $resolucion->estado = "terminada";
                            $resolucion->save();
                            //se busca si existe una resolucion en espera que tenga continue el consecutivo
                            $resEnEspera = Resolucion::resolucionEnEspera($numero+1);
                            if($resEnEspera){
                                $resEnEspera->estado = "activa";
                                $resEnEspera->save();
                            }
                        }
                        $bajoUmbral = "Los siguientes productos se estan agotando ";
                        $enviarNotificacion = false;

                        if ($request->has("pedido") && is_array($request->input("pedido"))) {
                            foreach ($request->input("pedido") as $pr) {
                                if ($pr["cantidad"] >= 1) {
                                    if(Auth::user()->bodegas == 'si') {
                                        $producto = ABProducto::permitidos()->where("id", $pr["id"])->first();
                                    }else {
                                        $producto = Producto::permitidos()->where("id", $pr["id"])->first();
                                    }
                                    if ($producto) {

                                        if(Auth::user()->bodegas == 'si') {
                                            $almacen = Auth::user()->almacenActual();
                                            if (!$almacen) return response(["Error" => ["No se ha encontrado ningún almacén relacionado con el usuario"]], 422);
                                            $almacen_stock = ABAlmacenStockProducto::where('producto_id',$producto->id)
                                                ->where('almacen_id',$almacen->id)->first();
                                            if(!$almacen_stock)return response(["Error" => ["No se ha encontrado el producto en el almacén"]], 422);

                                            $producto->stock = $almacen_stock->stock;
                                        }

                                        if($producto->tipo_producto == 'Compuesto' && $producto->omitir_stock_mp == 'si'){
                                            $producto->stock = $producto->DisponibleOmitirStockMp();
                                        }

                                        $maxima_cantidad = $producto->stock;
                                        //validar medidas unitarias y fraccionales
                                        if ($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0) {
                                            return response(["Error" => ["El producto " . $producto->producto_nombre . " no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad."]], 422);
                                        }
                                        if ($pr["cantidad"] <= $maxima_cantidad || !$validacion_stock) {
                                            if ($producto->tipo_producto == "Terminado") {
                                                $historial = $producto->ultimoHistorialProveedor();
                                            } else {
                                                $historial = $producto->ultimoHistorial();
                                            }

                                            if(Auth::user()->bodegas == 'si'){
                                                $historial_utilidad = ABHistorialUtilidad::where('producto_id',$producto->id)
                                                    ->where('almacen_id',$almacen->id)->orderBy('created_at','DESC')->first();
                                                $utilidad = $historial_utilidad->utilidad;
                                            }else{
                                                $utilidad = $producto->utilidad;
                                            }

                                            $factura->subtotal += ($producto->precio_costo + (($producto->precio_costo * $utilidad) / 100)) * $pr["cantidad"];
                                            $factura->iva += ((($producto->precio_costo + (($producto->precio_costo * $utilidad) / 100)) * $producto->iva) / 100) * $pr["cantidad"];

                                            if(Auth::user()->bodegas == 'si'){
                                                $factura->productosHistorialUtilidad()->save($historial_utilidad,["cantidad"=>$pr["cantidad"]]);
                                                $factura->productosHistorialCosto()->save($historial);
                                            }else{
                                                $factura->productosHistorial()->save($historial,["cantidad"=>$pr["cantidad"]]);
                                            }
                                            if($validacion_stock) {
                                                if (Auth::user()->bodegas == 'si')
                                                    $producto_aux = ABProducto::find($pr["id"]);
                                                else
                                                    $producto_aux = Producto::find($pr["id"]);

                                                if(Auth::user()->bodegas == 'si') {
                                                    $almacen_stock->stock -= $pr["cantidad"];
                                                    $almacen_stock->save();
                                                }else {
                                                    if($producto->tipo_producto == 'Compuesto' && $producto->omitir_stock_mp == 'si'){
                                                        $producto_aux->DisminuirStockMp($pr["cantidad"]);
                                                    }else{
                                                        $producto_aux->stock = $producto->stock - $pr["cantidad"];
                                                        $producto_aux->save();
                                                    }
                                                }

                                                if(
                                                    (Auth::user()->bodegas == 'no' && $producto_aux->umbral >= $producto_aux->stock)
                                                    ||    (Auth::user()->bodegas == 'si' && $producto_aux->umbral >= $almacen_stock->stock)
                                                ){
                                                    $bajoUmbral .= $producto_aux->nombre . ", ";
                                                    $enviarNotificacion = true;
                                                    $notificacion = new Notificacion();
                                                    if(Auth::user()->bodegas == 'si')
                                                        $stock = $almacen_stock->stock;
                                                    else
                                                        $stock = $producto_aux->stock;
                                                    $notificacion->mensaje = '<strong>Producto bajo umbral</strong><p>Producto: ' . $producto_aux->nombre . '</p><p>Umbral: ' . $producto_aux->umbral . '</p><p>Stock: ' . $stock . '</p>';
                                                    $notificacion->tipo = "inventario";
                                                    $notificacion->save();
                                                    $notificacion->usuarios()->save(Auth::user());
                                                    foreach (User::permitidos()->get() as $usr) {
                                                        if ($usr->id != Auth::user()->id) {
                                                            $notificacion->usuarios()->attach($usr->id);
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            return response(["Error" => ["La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $maxima_cantidad]], 422);
                                        }
                                    } else {
                                        return response(["Error" => ["La información enviada es incorrecta."]], 422);
                                    }
                                } else {
                                    return response(["Error" => ["La cantidad de un producto debe ser mayor o igual a 1."]], 422);
                                }
                            }
                        } else {
                            return response(["Error" => ["Seleccione por lo menos un producto."]], 422);
                        }

                        $relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
                        if($factura->estado == "Pagada") {

                            $valor_puntos_redimidos = 0;
                            if ($request->has('valor_puntos'))
                                $valor_puntos_redimidos = $request->get('valor_puntos');

                            $admin = User::find(Auth::user()->userAdminId());

                            $tipos_pago = $admin->tiposPago;

                            $valor_medios_pago = 0;
                            foreach ($tipos_pago as $tp){
                                if($request->has('valor_tp_'.$tp->id)){
                                    $valor = $request->input('valor_tp_'.$tp->id);
                                    $valor_medios_pago += $valor;
                                    $codigo = "";
                                    if($request->has('codigo_tp_'.$tp->id))$codigo = $request->input('codigo_tp_'.$tp->id);
                                    $factura->tiposPago()->save($tp,['valor'=>$valor,'codigo_verificacion'=>$codigo]);
                                }
                            }

                            $ultimo_token = TokenPuntos::select('*')->where('cliente_id',$cliente->id)
                                ->where('fecha_vigencia', date('Y-m-d'))
                                ->orderby('created_at','DESC')->take(1)->first();
                            if($request->has('token_puntos')){
                                if ($request->get('token_puntos')['fecha_vigencia'] == date('Y-m-d') && $request->get('token_puntos')['cliente_id']== $cliente->id && $request->get('token_puntos')['token'] == $ultimo_token->token){
                                    $relacion->valor_final +=  ($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago);

                                    //SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
                                    $valor_factura = ($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago);
                                    Cliente::savePoins($valor_factura,$cliente->id);
                                    //Desactiva el token
                                    $ultimo_token->estado = 'Inhabilitado';
                                    $ultimo_token->factura_id = $factura->id;
                                    $ultimo_token->save();
                                }
                            }else{
                                $relacion->valor_final +=  ($factura->subtotal + $factura->iva)-$valor_medios_pago;
                                //SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
                                $valor_factura = ($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago);
                                Cliente::savePoins($valor_factura,$cliente->id);
                            }

                            if($request->has("efectivo")){
                                if(intval($request->input("efectivo")) < intval(($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago))){
                                    if(Auth::user()->permitirFuncion("Descuentos","facturas","inicio")) {
                                        $factura->descuento = (($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago)) - $request->input("efectivo");
                                        $relacion->valor_final -= $factura->descuento;
                                    }else{
                                        return response(["Error" => ["El valor ingresado en el campo efectivo debe ser mayor o igual al valor total de la factura."]], 422);
                                    }
                                }
                            }
                            $sql = "UPDATE cajas_usuarios SET valor_final = ".$relacion->valor_final." where id = ".$relacion->id;
                            DB::statement($sql);
                        }

                        $factura->save();

                        DB::commit();
                        $mensaje = [
                            "mensaje"=>TildeHtml::TildesToHtml("El pedido ha sido registrado con éxito"),
                            "titulo"=>TildeHtml::TildesToHtml("Confirmación"),
                            "duracion"=>8000,
                            "color_titulo"=>"green-text"
                        ];
                        Session::flash("mensaje_toast", $mensaje);
                        $token_puntos = TokenPuntos::where("factura_id",$factura->id)->first();
                        $valor_puntos = 0;
                        if($token_puntos){
                            $valor_puntos = $token_puntos->valor;
                        }
                        $dataResponse = ["success" => true,"factura_pos"=>view("factura.factura_pos",["factura"=>$factura,'medios_pago'=>$factura->tiposPago()->select('facturas_tipos_pago.*','tipos_pago.nombre')->get(),'valor_puntos'=>$valor_puntos])->render()];
                        if($enviarNotificacion){
                            $dataResponse["mensajeNotificacion"]  = substr($bajoUmbral,0,(strlen($bajoUmbral)-2));
                            $dataResponse["notificacion"]  = true;
                        }
                        return $dataResponse;
                    }
                }
                return response(["Error" => ["La información enviada es incorrecta."]], 422);
            }else{
                response(["Error" => ["No existe ninguna resolución activa relacionada con su usuario."]], 422);
            }
        }
        return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
    }
}
