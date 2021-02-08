<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ABAlmacenStockProducto;
use App\Models\ABFactura;
use App\Models\ABHistorialCosto;
use App\Models\ABHistorialUtilidad;
use App\Models\Abono;
use App\Models\ABProducto;
use App\Models\ABRemision;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CotizacionHistorial;
use App\Models\Factura;
use App\Models\FacturaProducto;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\ProductoHistorial;
use App\Models\Remision;
use App\Models\Resolucion;
use App\Models\TokenPuntos;
use Barryvdh\DomPDF\PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestNuevoCliente;
use App\User;
use Illuminate\Support\Facades\Validator;

class RemisionesController extends Controller {

	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modRemisiones");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		return view('remisiones.index');
	}

	public function getListRemisiones(Request $request)
	{
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = /*'remisiones.id';*/$columna[$sortColumnIndex]['data'];
		if($orderBy == "usuario")$orderBy = "usuarios.nombres";
		else $orderBy = "remisiones.".$orderBy;

		if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
		else $sortColumnDir = "ASC";

		if(Auth::user()->bodegas == 'si'){
            $remisiones = ABRemision::permitidos()->select("remisiones.*", "clientes.nombre as cliente", DB::RAW("CONCAT(usuarios.nombres, ' ', usuarios.apellidos) as usuario"))
                ->join("vendiendo.usuarios", "usuarios.id", "=", "remisiones.usuario_creador_id")
                ->leftJoin("vendiendo.clientes", "clientes.id", "=", "remisiones.cliente_id");

            if(Auth::user()->admin_bodegas == 'no'){
                $almacen = Auth::user()->almacenActual()->id;
                $remisiones = $remisiones->where('remisiones.almacen_id',$almacen);
            }
        }else {
            $remisiones = Remision::permitidos()->select("remisiones.*", "clientes.nombre as cliente", DB::RAW("CONCAT(usuarios.nombres, ' ', usuarios.apellidos) as usuario"))
                ->join("usuarios", "usuarios.id", "=", "remisiones.usuario_creador_id")
                ->leftJoin("clientes", "clientes.id", "=", "remisiones.cliente_id");
        }

		$remisiones = $remisiones->orderBy($orderBy, $sortColumnDir);
		$totalRegistros = $remisiones->count();
		if ($search['value'] != null) {
			$remisiones = $remisiones->whereRaw(
				" ( remisiones.id LIKE '%" . $search["value"] . "%' OR " .
				" numero LIKE '%" . $search["value"] . "%' OR" .
				" remisiones.	created_at LIKE '%" . $search["value"] . "%' OR" .
				" nombre LIKE '%" .$search["value"] . "%' OR".
				" LOWER(CONCAT(usuarios.nombres, ' ', usuarios.apellidos)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
				" estado LIKE '%" . $search["value"] ."%' )");
		}

		$parcialRegistros = $remisiones->count();
        $remisiones = $remisiones->skip($start)->take($length);

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($remisiones->get() as $value) {

				$myArray[]=(object) array('id' => $value->id,
					'numero'=>$value->numero,
					'valor'=>"$ ".number_format($value->getValor(),2,',','.'),
					'created_at'=>$value->created_at->format('Y-m-d H:i:s'),
					'cliente'=> $value->cliente,
					'usuario'=> $value->usuario,
					'estado'=>$value->estado,
					'detalles' => "<a href='".url("/remision/detalle/".$value->id)."'><i class='fa fa-chevron-right'></i></a>");

            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $remisiones->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$remisiones->get()];

		return response()->json($data);

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		if(Auth::user()->permitirFuncion("Crear","remisiones","inicio")) {
			return view('remisiones.create')->with("display_factura_abierta",false)->with("display_compra_js",false);
		}

		return redirect("/");
	}

	public function postStore(Request $request){
		if(Auth::user()->permitirFuncion("Crear","remisiones","inicio")) {

			if($request->has("fecha_vencimiento")){
				if(strtotime($request->input("fecha_vencimiento")) <= strtotime(date('Y-m-d'))){
					return response(["error"=>["La fecha de vencimiento de la remisión debe ser mayor a la fecha actual"]],422);
				}
			}else{
				return response(["error"=>["El campo fecha de vencimiento de la remisión es obligatorio"]],422);
			}

			if ($request->has("id_cliente")) {
				$cliente = Cliente::permitidos()->where("id", $request->input("id_cliente"))->first();

				if ($cliente) {
				    if(Auth::user()->bodegas == 'si') {
                        $ultimaRemision = ABRemision::ultimaRemision();
                    }else{
                        $ultimaRemision = Remision::ultimaRemision();
                    }
					$numero = 1;
					if($ultimaRemision){
						$numero = intval($ultimaRemision->numero) + 1;
					}

					$aux = 1;
					$continuar = true;
					DB::beginTransaction();
                    if(Auth::user()->bodegas == 'si') $remision = new ABRemision();
                    else $remision = new Remision();

					$remision->numero = "00".$numero;
					$remision->estado = "registrada";
					$remision->cliente_id = $cliente->id;
					$remision->usuario_creador_id = Auth::user()->id;
					$remision->fecha_vencimiento = $request->input("fecha_vencimiento");
                    $remision->usuario_id = Auth::user()->userAdminId();
                    if(Auth::user()->bodegas == 'si'){
                        $almacen = Auth::user()->almacenActual();
                        $remision->almacen_id = $almacen->id;
                    }
					$remision->save();


					while ($continuar) {
						if ($request->has("producto_" . $aux)) {
							$pr = $request->input("producto_" . $aux);
							if ($pr["cantidad"] >= 0.1) {
							    if(Auth::user()->bodegas == 'si')
								    $producto = ABProducto::permitidos()->where("id", $pr["producto"])->first();
                                else
                                    $producto = Producto::permitidos()->where("id", $pr["producto"])->first();

                                if ($producto) {
									//validar medidas unitarias y fraccionales
									if($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0){
										return response(["Error" => ["El producto ".$producto->producto_nombre." no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad."]], 422);
									}

									if(Auth::user()->bodegas == 'si'){

                                        $almacen_stock  = ABAlmacenStockProducto::where('producto_id',$producto->id)
                                            ->where('almacen_id',$almacen->id)->first();

                                        $ultimoHistorial = $producto->ultimoHistorial();
                                        if($almacen_stock && $ultimoHistorial){
                                            if ($almacen_stock->stock - $pr["cantidad"] < 0)
                                                return response(["Error" => ["La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $almacen_stock->stock]], 422);

                                            $historial_utilidad = ABHistorialUtilidad::where('almacen_id',$almacen->id)
                                                ->where('producto_id',$producto->id)->orderBy('created_at','DESC')->first();

                                            $historial_utilidad_aux = new ABHistorialUtilidad();
                                            $historial_utilidad_aux->utilidad = $pr['utilidad'];
                                            $historial_utilidad_aux->actualizacion_utilidad = $pr['utilidad'];
                                            $historial_utilidad_aux->producto_id = $producto->id;
                                            $historial_utilidad_aux->almacen_id = $almacen->id;
                                            $historial_utilidad_aux->save();

                                            $remision->historialCostos()->save($ultimoHistorial, ["cantidad" => $pr["cantidad"], "historial_utilidad_id" => $historial_utilidad_aux->id]);
                                            $almacen_stock->stock -= $pr['cantidad'];
                                            $almacen_stock->save();

                                            $historial_utilidad_reset = new ABHistorialUtilidad();
                                            $historial_utilidad_reset->utilidad = $historial_utilidad->utilidad;
                                            $historial_utilidad_reset->actualizacion_utilidad = $historial_utilidad->actualizacion_utilidad;
                                            $historial_utilidad_reset->producto_id = $historial_utilidad->producto_id;
                                            $historial_utilidad_reset->almacen_id = $historial_utilidad->almacen_id;
                                            $historial_utilidad_reset->save();
                                        }
                                    }else {
                                        if ($producto->stock - $pr["cantidad"] < 0)
                                            return response(["Error" => ["La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $producto->stock]], 422);

                                        $remision->productos()->save($producto, ["cantidad" => $pr["cantidad"], "utilidad" => $pr["utilidad"], "iva" => $producto->iva, "precio_costo" => $producto->precio_costo]);
                                        $producto->stock -= $pr['cantidad'];
                                        $producto->save();
                                    }
                                    $aux++;

								} else {
									return response(["Error" => ["La información enviada es incorrecta."]], 422);
								}
							} else {
								return response(["Error" => ["La cantidad de un producto debe ser mayor o igual a 1."]], 422);
							}
						} else {
							if ($aux == 1) {
								return response(["Error" => ["Seleccione por lo menos un producto."]], 422);
							} else {
								$continuar = false;
							}
						}
					}

					DB::commit();
					Session::flash("mensaje", "La remisión ha sido registrada con éxito");
					$dataResponse = ["success" => true,"url"=>url("/remision/detalle/".$remision->id)];
					return $dataResponse;
				}
			}
			return response(["Error" => ["La información enviada es incorrecta."]], 422);

		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function postBuscarCliente(Request $request){
		return view("remisiones.buscar_clientes");
	}

	public function getListRemisionCliente(Request $request){
		// Datos de DATATABLE
		$search = $request->get("search");
		$order = $request->get("order");
		$sortColumnIndex = $order[0]['column'];
		$sortColumnDir = $order[0]['dir'];
		$length = $request->get('length');
		$start = $request->get('start');
		$columna = $request->get('columns');
		$orderBy = 'id';

		$clientes = Cliente::permitidos()->select('id','nombre','identificacion','correo')->where("predeterminado","no");

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

	public function getDetalle($id)
	{
	    if(Auth::user()->bodegas == 'si')
		    $remision = ABRemision::permitidos()->where("id",$id)->first();
		else
	        $remision = Remision::permitidos()->where("id",$id)->first();

		if($remision){
			return view('remisiones.detalle')->with("remision",$remision);
		}
		return redirect("/remision");
	}

	public function postStoreCliente(RequestNuevoCliente $request){
        $administrador = User::find(Auth::user()->userAdminId());
		$clienteIdentificacion = Cliente::where("identificacion",$request->input("identificacion"))->where("usuario_id",$administrador->id)->first();
		if($clienteIdentificacion){
			return response(["error"=>["Ya existe un cliente con el número de identificaión ingresado"]],422);
		}

		$cliente = new Cliente();
		$cliente->fill($request->all());
		$cliente->usuario_id = $administrador->id;
		$cliente->usuario_creador_id = Auth::user()->id;
		$cliente->save();
		return ["success"=>true,"cliente"=>$cliente];
	}

	public function postFormEditarCliente(Request $request){
		if($request->has("cliente_id")){
			$cliente = Cliente::permitidos()->where("id",$request->input("cliente_id"))->first();
			if($cliente){
				return view("remisiones.form_cliente")->with("cliente",$cliente);
			}
			return response(["error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function postUpdateCliente(RequestNuevoCliente $request){
		if($request->has("id_cliente")){
			if(Auth::user()->perfil->nombre == "administrador"){
				$administrador = Auth::user();
			}else{
				$administrador = User::find(Auth::user()->usuario__creador_id);
			}

			$clienteIdentificacion = Cliente::where("identificacion",$request->input("identificacion"))->where("id","<>",$request->input("id_cliente"))->where("usuario_id",$administrador->id)->first();
			if(!$clienteIdentificacion){
				$cliente = Cliente::permitidos()->where("id",$request->input("id_cliente"))->first();
				if($cliente){
					$cliente->fill($request->all());
					$cliente->save();
					//Session::flash("mensaje","La información del cliente ha sido editada con éxito");
					return ["success"=>true];
				}
			}else{
				return response(["Ya existe un cliente con el número de identificaión ingresado"],422);
			}
		}
		return response(["La información enviada es incorrecta"],422);
	}

	public function postTotalCliente(Request $request){
		$filtro = "";
		if($request->has("filtro")) {
			$filtro = $request->input("filtro");
		}
		$clientes = Cliente::permitidos()->where(function($q) use ($filtro){
			$q->where("identificacion","like","%".$filtro."%")
				->orWhere("nombre","like","%".$filtro."%");
		})->get();
		if($clientes)
			return ["success"=>true,"clientes"=>$clientes];
		//return view("factura.buscar_clientes");
	}

	public function postFacturar(Request $request){
		$caja_asignada = Auth::user()->cajaAsignada();
		if(Auth::user()->permitirFuncion("Crear","facturas","inicio") && $caja_asignada) {

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
			if(!$request->has("remision"))return response(["error"=>["La información enviada es incorrecta."]],422);
			if(Auth::user()->bodegas == 'si')
			    $remision = ABRemision::permitidos()->where("remisiones.id",$request->input("remision"))->where("estado","registrada")->first();
			else
			    $remision = Remision::permitidos()->where("remisiones.id",$request->input("remision"))->where("estado","registrada")->first();

			if(!$remision)return response(["error"=>["La información enviada es incorrecta."]],422);

			$resolucion_espera = Resolucion::permitidos()->where("estado","en espera")->orderBy("inicio","ASC")->first();
			if($resolucion) {

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

                if(Auth::user()->bodegas == 'si') {
                    if (ABFactura::permitidos()->where("numero", "00" . $numero)->first()) {
                        return response(["Error" => ["A ocurrido un error generando el número de la factura, por favor recargue su página y diligencie nuevamente la factura."]], 422);
                    }
                }else {
                    if (Factura::permitidos()->where("numero", "00" . $numero)->first()) {
                        return response(["Error" => ["A ocurrido un error generando el número de la factura, por favor recargue su página y diligencie nuevamente la factura."]], 422);
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
				$aux = 1;
				$continuar = true;
                if(Auth::user()->bodegas == 'si')
                    $factura = new ABFactura();
                else
                    $factura = new Factura();

				$factura->numero = "00".$numero;
				$factura->estado = "Pagada";
				$factura->cliente_id = $remision->cliente->id;
				$factura->usuario_creador_id = Auth::user()->id;
                $factura->usuario_id = Auth::user()->userAdminId();
				$factura->resolucion_id = $resolucion->id;
				if(Auth::user()->bodegas == 'si'){
				    $almacen = Auth::user()->almacenActual();
				    $factura->almacen_id = $almacen->id;
                }
				$factura->subtotal = 0;
				$factura->iva = 0;
				$factura->caja_usuario_id = $caja_asignada->relacion_id;
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

				if(Auth::user()->bodegas == 'si'){
                    $productos = $remision->historialCostos()
                        ->join('productos','historial_costos.producto_id','=','productos.id')
                        ->join('historial_utilidad','remisiones_historial_costos.historial_utilidad_id','=','historial_utilidad.id')
                        ->select(
                            "productos.id as producto_id",
                            "remisiones_historial_costos.cantidad as cantidad",
                            "historial_costos.precio_costo_nuevo as precio_costo",
                            "historial_utilidad.utilidad as utilidad",
                            "historial_costos.iva_nuevo as iva"
                        )->get();
                }else {
                    $productos = $remision->productos()->select("productos_remisiones.*")->get();
                }
				foreach ($productos as $p) {
				    if(Auth::user()->bodegas == 'si')
                        $prod = ABProducto::find($p->producto_id);
				    else
                        $prod = Producto::find($p->producto_id);

					$aux = 1;
					$data = [];
					while(true){
						if($request->has("producto_".$aux) && is_array($request->input("producto_".$aux))) {
							if($request->input("producto_" . $aux)["producto"] == $prod->id){
								$data = $request->input("producto_" . $aux);
								if($data["cantidad"] < 0)return response(["error"=>["La cantidad regresada no puede ser inferior a 0"]],422);
								if($data["cantidad"] > $p->cantidad)return response(["error"=>["La cantidad regresada no puede ser mayor a la cantidad entregada"]],422);
							}
						}else {
							break;
						}

						$aux++;
					}

					//no se encuentra la informacion del producto
					if(!count($data))return response(["error"=>["La información enviada es incorrecta"]],422);


					if(Auth::user()->bodegas == 'si'){
					    $almacen = Auth::user()->almacenActual();
                        $ultimo_historial = $prod->ultimoHistorialProveedor();
                        $ultimo_historial_utilidad = $prod->ultimoHistorialUtilidadAlmacen($almacen->id);

                        $historial_aux = new ABHistorialCosto();
                        $historial_aux->precio_costo_anterior = $p->precio_costo;
                        $historial_aux->precio_costo_nuevo = $p->precio_costo;
                        $historial_aux->iva_anterior = $p->iva;
                        $historial_aux->iva_nuevo = $p->iva;
                        $historial_aux->producto_id = $p->producto_id;
                        $historial_aux->proveedor_id = $prod->proveedor_actual;
                        $historial_aux->usuario_id = Auth::user()->id;
                        $historial_aux->save();

                        $historial_utilidad_aux = new ABHistorialUtilidad();
                        $historial_utilidad_aux->utilidad = $p->utilidad;
                        $historial_utilidad_aux->actualizacion_utilidad = $p->utilidad;
                        $historial_utilidad_aux->producto_id = $p->producto_id;
                        $historial_utilidad_aux->almacen_id = $almacen->id;
                        $historial_utilidad_aux->save();

                        //se deja nuevamente los datos del ultimo historial
                        $historial_aux_last = new ABHistorialCosto();
                        $historial_aux_last->precio_costo_anterior = $ultimo_historial->precio_costo_anterior;
                        $historial_aux_last->precio_costo_nuevo = $ultimo_historial->precio_costo_nuevo;
                        $historial_aux_last->iva_anterior = $ultimo_historial->iva_anterior;
                        $historial_aux_last->iva_nuevo = $ultimo_historial->iva_nuevo;
                        $historial_aux_last->producto_id = $ultimo_historial->producto_id;
                        $historial_aux_last->proveedor_id = $ultimo_historial->proveedor_id;
                        $historial_aux_last->usuario_id = $ultimo_historial->usuario_id;
                        $historial_aux_last->save();


                        $historial_utilidad_aux = new ABHistorialUtilidad();
                        $historial_utilidad_aux->utilidad = $ultimo_historial_utilidad->utilidad;
                        $historial_utilidad_aux->actualizacion_utilidad = $ultimo_historial_utilidad->actualizacion_utilidad;
                        $historial_utilidad_aux->producto_id = $ultimo_historial_utilidad->producto_id;
                        $historial_utilidad_aux->almacen_id = $ultimo_historial_utilidad->almacen_id;
                        $historial_utilidad_aux->save();

                        $factura->productosHistorialUtilidad()->save($historial_utilidad_aux, ["cantidad" => $data["cantidad"]]);
                        $factura->productosHistorialCosto()->save($historial_aux);

                        //si se regresan productos
                        if ($data["cantidad"] < $p->cantidad) {
                            $cantidad_regresada = $p->cantidad - $data["cantidad"];
                            $stock_almacen = ABAlmacenStockProducto::where('almacen_id',$almacen->id)
                                                ->where('producto_id',$prod->id)->first();
                            $stock_almacen->stock += $cantidad_regresada;
                            $stock_almacen->save();
                        }
                    }else {
                        $ultimo_historial = $prod->ultimoHistorialProveedor();

                        $historial_aux = new ProductoHistorial();
                        $historial_aux->precio_costo_anterior = $p->precio_costo;
                        $historial_aux->precio_costo_nuevo = $p->precio_costo;
                        $historial_aux->utilidad_anterior = $p->utilidad;
                        $historial_aux->utilidad_nueva = $p->utilidad;
                        $historial_aux->iva_anterior = $p->iva;
                        $historial_aux->iva_nuevo = $p->iva;
                        $historial_aux->producto_id = $p->producto_id;
                        $historial_aux->proveedor_id = $prod->proveedor_actual;
                        $historial_aux->usuario_id = Auth::user()->id;
                        $historial_aux->save();
                        //se deja nuevamente los datos del ultimo historial
                        $historial_aux_last = new ProductoHistorial();
                        $historial_aux_last->precio_costo_anterior = $ultimo_historial->precio_costo_anterior;
                        $historial_aux_last->precio_costo_nuevo = $ultimo_historial->precio_costo_nuevo;
                        $historial_aux_last->utilidad_anterior = $ultimo_historial->utilidad_anterior;
                        $historial_aux_last->utilidad_nueva = $ultimo_historial->utilidad_nueva;
                        $historial_aux_last->iva_anterior = $ultimo_historial->iva_anterior;
                        $historial_aux_last->iva_nuevo = $ultimo_historial->iva_nuevo;
                        $historial_aux_last->producto_id = $ultimo_historial->producto_id;
                        $historial_aux_last->proveedor_id = $ultimo_historial->proveedor_id;
                        $historial_aux_last->usuario_id = $ultimo_historial->usuario_id;
                        $historial_aux_last->save();

                        $factura->productosHistorial()->save($historial_aux, ["cantidad" => $data["cantidad"]]);

                        //si se regresan productos
                        if ($data["cantidad"] < $p->cantidad) {
                            $cantidad_regresada = $p->cantidad - $data["cantidad"];
                            $prod->stock += $cantidad_regresada;
                            $prod->save();
                        }
                    }

					$subtotal = ($p->precio_costo + (($p->precio_costo * $p->utilidad)/100)) * $data["cantidad"];
					$iva = (($subtotal * $p->iva)/100);
					$factura->subtotal += $subtotal;
					$factura->iva += $iva;
					$factura->save();
				}

				$relacion = $caja_asignada->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
				$relacion->valor_final +=  $factura->subtotal + $factura->iva;

				$sql = "UPDATE cajas_usuarios SET valor_final = ".$relacion->valor_final." where id = ".$relacion->id;
				DB::statement($sql);
				//SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
				//$valor_factura = ($factura->subtotal + $factura->iva);
				//Cliente::savePoins($valor_factura,$remision->cliente->id);


				$factura->save();

				$remision->factura_id = $factura->id;
				$remision->estado = "facturada";
				$remision->updated_at = date('Y-m-d H:i:s');
				$remision->save();

				DB::commit();
				Session::flash("mensaje", "La remisión ha sido facturada con éxito");
				$dataResponse = ["success" => true,"url"=>url("/factura/detalle/".$factura->id)];
				return $dataResponse;

			}else{
				response(["Error" => ["No existe ninguna resolución activa relacionada con su usuario."]], 422);
			}
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}
}
