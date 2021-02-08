<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Abono;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Cotizacion;
use App\Models\CotizacionHistorial;
use App\Models\Factura;
use App\Models\FacturaProducto;
use App\Models\Notificacion;
use App\Models\Producto;
use App\Models\ProductoHistorial;
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

class CotizacionesController extends Controller {

	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modCotizaciones");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		return view('cotizaciones.index');
	}

	public function getListCotizaciones(Request $request)
	{
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = /*'cotizaciones.id';*/$columna[$sortColumnIndex]['data'];
		if($orderBy == "id")$orderBy = "cotizaciones.id";

		if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
		else $sortColumnDir = "ASC";

		$cotizaciones = Cotizacion::permitidos()->select("cotizaciones.*","clientes.nombre as cliente", DB::RAW("CONCAT(usuarios.nombres, ' ', usuarios.apellidos) as usuario"))
			->join("usuarios", "usuarios.id", "=", "cotizaciones.usuario_creador_id")
			->leftJoin("clientes", "clientes.id", "=", "cotizaciones.cliente_id");
		$cotizaciones = $cotizaciones->orderBy($orderBy, $sortColumnDir);
		$totalRegistros = $cotizaciones->count();
		if ($search['value'] != null) {
			$cotizaciones = $cotizaciones->whereRaw(
				" ( cotizaciones.id LIKE '%" . $search["value"] . "%' OR " .
				" numero LIKE '%" . $search["value"] . "%' OR" .
				" cotizaciones.	created_at LIKE '%" . $search["value"] . "%' OR" .
				" nombre LIKE '%" .$search["value"] . "%' OR".
				" LOWER(CONCAT(usuarios.nombres, ' ', usuarios.apellidos)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
				" estado LIKE '%" . $search["value"] ."%' )");
		}

		$parcialRegistros = $cotizaciones->count();
        $cotizaciones = $cotizaciones->skip($start)->take($length);

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($cotizaciones->get() as $value) {
				$vender = "";
				if($value->estado == "generada" && Auth::user()->permitirFuncion("Editar","cotizaciones","inicio")){
					$vender = "<a href='#' class='blue-grey-text text-darken-2'><i class='fa fa-paper-plane'></i></a>";
				}
				$count_historial = count($value->historial);

				$historial = "";
				if($count_historial)
					$historial = "<a href='#' class='historial-cotizacion' data-cotizacion='".$value->id."'><i class='fa fa-list-alt'></i></a>";

				$myArray[]=(object) array('id' => $value->id,
					'numero'=>$value->numero,
					'valor'=>"$ ".number_format($value->getValor(),2,',','.'),
					'created_at'=>$value->created_at->format('Y-m-d H:i:s'),
					'cliente'=> $value->cliente,
					'usuario'=> $value->usuario,
					'vender'=> $vender,
					'estado'=>$value->estado,
					'historial'=>$historial,
					'detalles' => "<a href='".url("/cotizacion/detalle/".$value->id)."'><i class='fa fa-chevron-right'></i></a>");

            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $cotizaciones->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$cotizaciones->get()];

		return response()->json($data);

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		if(Auth::user()->permitirFuncion("Crear","cotizaciones","inicio")) {
			return view('cotizaciones.create')->with("display_factura_abierta",false)->with("display_compra_js",false);
		}

		return redirect("/");
	}

	public function postStore(Request $request){
		if(Auth::user()->permitirFuncion("Crear","cotizaciones","inicio")) {
			$resolucion = Resolucion::getActiva();

			if($request->has("dias_vencimiento")){
				if(!is_numeric($request->input("dias_vencimiento"))){
					return response(["error"=>["El campo días para vencimiento de la cotización debe ser de tipo numérico"]],422);
				}else{
					if($request->input("dias_vencimiento") < 1){
						return response(["error"=>["El campo días para vencimiento de la cotización debe ser mayor a 0"]],422);
					}
				}
			}else{
				return response(["error"=>["El campo días para vencimiento de la cotización es obligatorio"]],422);
			}

			if ($request->has("id_cliente")) {
				$cliente = Cliente::permitidos()->where("id", $request->input("id_cliente"))->first();

				if ($cliente) {
					$ultimaCotizacion = Cotizacion::ultimaCotizacion();
					$numero = 1;
					if($ultimaCotizacion){
						$numero = intval($ultimaCotizacion->numero) + 1;
					}

					$aux = 1;
					$continuar = true;
					DB::beginTransaction();
					$cotizacion = new Cotizacion();
					$cotizacion->numero = "00".$numero;
					$cotizacion->estado = "generada";
					$cotizacion->cliente_id = $cliente->id;
					$cotizacion->usuario_creador_id = Auth::user()->id;
					$cotizacion->dias_vencimiento = $request->input("dias_vencimiento");
					$cotizacion->observaciones = $request->input("observaciones");
					if (Auth::user()->perfil->nombre == "administrador")
						$cotizacion->usuario_id = Auth::user()->id;
					else
						$cotizacion->usuario_id = Auth::user()->usuario_creador_id;
					$cotizacion->save();


					while ($continuar) {
						if ($request->has("producto_" . $aux)) {
							$pr = $request->input("producto_" . $aux);
							if ($pr["cantidad"] >= 0.1) {
								$producto = Producto::permitidos()->where("id", $pr["producto"])->first();
								if ($producto) {
									//validar medidas unitarias y fraccionales
									if($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0){
										return response(["Error" => ["El producto ".$producto->producto_nombre." no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad."]], 422);
									}

									if($producto->tipo_producto == "Terminado"){
										$historial = $producto->ultimoHistorialProveedor();
									}else{
										$historial = $producto->ultimoHistorial();
									}

									$cotizacion->productosHistorial()->save($historial,["cantidad"=>$pr["cantidad"]]);
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
					Session::flash("mensaje", "La cotización ha sido registrada con éxito");
					$dataResponse = ["success" => true,"url"=>url("/cotizacion/detalle/".$cotizacion->id)];
					return $dataResponse;
				}
			}
			return response(["Error" => ["La información enviada es incorrecta."]], 422);

		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function postBuscarCliente(Request $request){
		return view("cotizaciones.buscar_clientes");
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

	public function getListCotizacionCliente(Request $request){
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
		$cotizacion = Cotizacion::permitidos()->where("id",$id)->first();

		if($cotizacion){
			return view('cotizaciones.detalle')->with("cotizacion",$cotizacion);
		}
		return redirect("/cotizacion");
	}

	public function postStoreCliente(RequestNuevoCliente $request){
		if(Auth::user()->perfil->nombre == "administrador"){
			$administrador = Auth::user();
		}else{
			$administrador = User::find(Auth::user()->usuario_creador_id);
		}
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
				return view("cotizaciones.form_cliente")->with("cliente",$cliente);
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

	function getImprimir($id){
		$cotizacion = Cotizacion::permitidos()->where("id",$id)->first();

		if($cotizacion){
			$pdf = App::make('dompdf.wrapper');
			//$pdf->set_paper('A4', 'landscape');
			$pdf = $pdf->loadView('cotizaciones.factura_carta.index', ['cotizacion'=>$cotizacion]);

			/*if (isset($pdf)){
				$font = Font_Metrics::get_font("Arial", "bold");
				$pdf->page_text(765, 550, "Pagina {PAGE_NUM} de {PAGE_COUNT}", $font, 9, array(0, 0, 0));
			}*/

			return $pdf->stream();
		}
		return redirect("/");
	}

	public function postQuitarProductoDetalle(Request $request){
		if(Auth::user()->permitirFuncion("Editar","cotizaciones","inicio")) {
			if ($request->has("cotizacion") && $request->has("relacion")) {
				$cotizacion = Cotizacion::permitidos()->where("id",$request->input("cotizacion"))->where("estado","generada")->first();
				if($cotizacion){
					if(count($cotizacion->productosHistorial) > 1){
						$relacion = $cotizacion->productosHistorial()->where("cotizaciones_productos_historial.id",$request->input("relacion"))->first();
						if($relacion){
							$sql = "DELETE FROM cotizaciones_productos_historial WHERE cotizaciones_productos_historial.id = ".$request->input("relacion");
							DB::statement($sql);
							$datos = $cotizacion->getDatosValor();
							$datosValor = [
								"subtotal" => "$ ".number_format($datos["subtotal"],2,',','.'),
								"iva" => "$ ".number_format($datos["iva"],2,',','.'),
								"total" => "$ ".number_format($datos["subtotal"]+$datos["iva"],2,',','.'),
								"total_num" => $datos["subtotal"]+$datos["iva"]
							];
							return ["success"=>true]+$datosValor;
						}
					}else{
						return response(["error"=>["No es posible eliminar el producto, una cotización debe contener por lo menos un producto."]],422);
					}
				}
			}
		}else{
			return response(["error"=>["Unauthorized."]],401);
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function postFacturar(Request $request){
        $caja = Auth::user()->cajaAsignada();
		if(Auth::user()->permitirFuncion("Crear","facturas","inicio") && Auth::user()->permitirFuncion("Editar","cotizaciones","inicio") && $caja) {
			$valor_puntos_redimidos = 0;
			if ($request->has('valor_puntos'))
				$valor_puntos_redimidos = $request->get('valor_puntos');

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
			if(!$request->has("cotizacion"))return response(["error"=>["La información enviada es incorrecta."]],422);
			$cotizacion = Cotizacion::permitidos()->where("cotizaciones.id",$request->input("cotizacion"))->where("estado","generada")->first();
			if(!$cotizacion)return response(["error"=>["La información enviada es incorrecta."]],422);

			$resolucion_espera = Resolucion::permitidos()->where("estado","en espera")->orderBy("inicio","ASC")->first();
			if($resolucion) {
				if ($request->has("id_cliente")) {
					$cliente = Cliente::permitidos()->where("id", $request->input("id_cliente"))->first();

					if ($cliente) {
						DB::beginTransaction();
						//se le quitan los puntos redimidos en el pago de la factura
						$cliente->valor_puntos -= $valor_puntos_redimidos;
						$cliente->save();

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

						if(Factura::permitidos()->where("numero","00".$numero)->first()){
							return response(["Error" => ["A ocurrido un error generando el número de la factura, por favor recargue su página y diligencie nuevamente la factura."]], 422);
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

                        $relacion_caja_usuario = Auth::user()->cajas()->select("cajas_usuarios.id")->where("cajas_usuarios.caja_id",$caja->id)
                            ->where("cajas_usuarios.estado","activo")->first();

						$factura = new Factura();
						$factura->numero = "00".$numero;
						$factura->estado = "Pagada";
						$factura->cliente_id = $cliente->id;
						$factura->usuario_creador_id = Auth::user()->id;
						if (Auth::user()->perfil->nombre == "administrador")
							$factura->usuario_id = Auth::user()->id;
						else
							$factura->usuario_id = Auth::user()->usuario_creador_id;
						$factura->resolucion_id = $resolucion->id;
						$datos_valor_cotizacion = $cotizacion->getDatosValor();
						$factura->subtotal = $datos_valor_cotizacion["subtotal"];
						$factura->iva = $datos_valor_cotizacion["iva"];;
						$factura->observaciones = $request->input("observaciones");
                        $factura->caja_usuario_id = $relacion_caja_usuario->id;
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

						$relacion = $cotizacion->productosHistorial()->select("cotizaciones_productos_historial.cantidad","productos_historial.*")->get();
						foreach ($relacion as $obj) {
								$pr = $request->input("producto_" . $aux);
									$producto = $obj->producto;
										$maxima_cantidad = $producto->stock;
										if ($obj->cantidad <= $maxima_cantidad) {

											$factura->productosHistorial()->save($obj,["cantidad"=>$obj->cantidad]);
											$producto_aux = Producto::find($producto->id);
											$producto_aux->stock = $producto->stock - $obj->cantidad;
											$producto_aux->save();
											if($producto_aux->umbral >= $producto_aux->stock){
												$bajoUmbral .= $producto_aux->nombre.", ";
												$enviarNotificacion = true;
												$notificacion = new Notificacion();
												$notificacion->mensaje = '<strong>Producto bajo umbral</strong><p>Producto: '.$producto_aux->nombre.'</p><p>Umbral: '.$producto_aux->umbral.'</p><p>Stock: '.$producto_aux->stock.'</p>';
												$notificacion->tipo = "inventario";
												$notificacion->save();
												$notificacion->usuarios()->save(Auth::user());
												foreach (User::permitidos()->get() as $usr){
													if($usr->id != Auth::user()->id) {
														$notificacion->usuarios()->attach($usr->id);
													}
												}
											}
										} else {
											return response(["Error" => ["La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $maxima_cantidad]], 422);
										}
						}

                        $relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
						if($factura->estado == "Pagada") {

							$ultimo_token = TokenPuntos::select('*')->where('cliente_id',$cliente->id)
								->where('fecha_vigencia', date('Y-m-d'))
								->orderby('created_at','DESC')->take(1)->first();
							$valor_factura = 0;
							if($request->has('token_puntos')){
								if ($request->get('token_puntos')['fecha_vigencia'] == date('Y-m-d') && $request->get('token_puntos')['cliente_id']== $cliente->id && $request->get('token_puntos')['token'] == $ultimo_token->token){
                                    $relacion->valor_final +=  ($factura->subtotal + $factura->iva) - $valor_puntos_redimidos;

									//SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
									$valor_factura = ($factura->subtotal + $factura->iva) - $valor_puntos_redimidos;
									Cliente::savePoins($valor_factura,$cliente->id);
									//Desactiva el token
									$ultimo_token->estado = 'Inhabilitado';
									$ultimo_token->factura_id = $factura->id;
									$ultimo_token->save();
								}
							}else{
                                $relacion->valor_final +=  $factura->subtotal + $factura->iva;
								//SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
								$valor_factura = ($factura->subtotal + $factura->iva) - $valor_puntos_redimidos;
								Cliente::savePoins($valor_factura,$cliente->id);
							}

                            if($request->has("efectivo")){
                                if(intval($request->input("efectivo")) < intval(($factura->subtotal + $factura->iva) - $valor_puntos_redimidos)){
                                    if(Auth::user()->permitirFuncion("Descuentos","facturas","inicio")) {
                                        $factura->descuento = (($factura->subtotal + $factura->iva) - $valor_puntos_redimidos) - $request->input("efectivo");
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

						$cotizacion->factura_id = $factura->id;
						$cotizacion->estado = "facturada";
						$cotizacion->save();

						DB::commit();
						Session::flash("mensaje", "La cotización ha sido facturada con éxito");
						$dataResponse = ["success" => true,"url"=>url("/factura/detalle/".$factura->id)];
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

	public function postStoreHistorial(Request $request){
		if(Auth::user()->permitirFuncion("Editar","cotizaciones","inicio")) {
			if ($request->has("cotizacion")) {
				$cotizacion = Cotizacion::permitidos()->where("id",$request->input("cotizacion"))->first();
				if($cotizacion) {
					if ($request->has("observacion")) {
						if(strlen($request->input("observacion")) <= 250) {
							$historial = new CotizacionHistorial();
							$historial->observacion = $request->input("observacion");
							$historial->cotizacion_id = $cotizacion->id;
							$historial->usuario_id = Auth::user()->id;
							$historial->save();
							Session::flash("mensaje", "El historial de la cotización ha sido registrado con éxito");
							return ["success" => true];
						} else {
							return response(["error" => ["El campo observación debe tener máximo 250 caracteres"]], 422);
						}
					} else {
						return response(["error" => ["El campo observación es obligatorio"]], 422);
					}
				}
			}
			return response(["error" => ["La información enviada es incorrecta"]], 422);
		}else{
			return response(["error"=>["Unauthorized."]],401);
		}
	}

	public function postListaHistorial(Request $request){
		if($request->has("cotizacion")){
			$cotizacion = Cotizacion::permitidos()->where("id",$request->input("cotizacion"))->first();
			if($cotizacion){
				$historiales = $cotizacion->historial()->orderBy("created_at","DESC")->get();
				$view = view("cotizaciones.lista_historial")->with("historiales",$historiales)->render();
				return $view;
			}
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}
}
