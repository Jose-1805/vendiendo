<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ABAbono;
use App\Models\ABAlmacenStockProducto;
use App\Models\ABConsignacion;
use App\Models\ABFactura;
use App\Models\ABHistorialUtilidad;
use App\Models\Abono;
use App\Models\ABProducto;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Consignacion;
use App\Models\CuentaBancaria;
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

class FacturaController extends Controller {

	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modFacturas");
		//$this->middleware("modCaja");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		if(Auth::user()->cajaAsignada() || Auth::user()->perfil->nombre == "administrador") {
			$filtro = "";
			if ($request->has("filtro")) {
				$facturas = $this->listaFiltro($request->get("filtro"));
				$filtro = $request->get("filtro");
			} else {
				$facturas = Factura::permitidos()->orderBy("created_at", "DESC")->orderBy("estado", "DESC")->orderBy("estado", "DESC")->where("estado", "<>", "abierta")->paginate(env('PAGINATE'));
			}
			return view('factura.index')->with("facturas", $facturas)->with("filtro", $filtro);
		}
		return redirect("/");
	}

	public function getListFacturas(Request $request)
	{
		if(Auth::user()->cajaAsignada() || Auth::user()->perfil->nombre == "administrador") {
			// Datos de DATATABLE
			$search = $request->get("search");
			$order = $request->get("order");
			$sortColumnIndex = $order[0]['column'];
			$sortColumnDir = $order[0]['dir'];
			$length = $request->get('length');
			$start = $request->get('start');
			$columna = $request->get('columns');
			$orderBy = 'facturas.id';//$columna[$sortColumnIndex]['data'];

			if (strtoupper($sortColumnDir) == "ASC") $sortColumnDir = "DESC";
			else $sortColumnDir = "ASC";
			//$facturas = Factura::permitidos()->orderBy("estado", "DESC")->where("estado","<>","abierta")->orderBy("id","DESC");

            if(Auth::user()->bodegas == 'si')
                $facturas = ABFactura::permitidos();
            else
                $facturas = Factura::permitidos();

			$facturas = $facturas->select("facturas.id as id_factura", "facturas.numero as numero", "facturas.created_at as created_at","facturas.dias_credito", "facturas.subtotal as subtotal", "facturas.iva as iva","facturas.descuento",
				"facturas.estado as estado", "vendiendo.clientes.nombre as cliente", DB::RAW("CONCAT(vendiendo.usuarios.nombres, ' ', vendiendo.usuarios.apellidos) as usuario"),DB::RAW("CONCAT(vendiendo.cajas.nombre, ' - ', vendiendo.cajas.prefijo) as caja"))
				->join("vendiendo.usuarios", "vendiendo.usuarios.id", "=", "facturas.usuario_creador_id")
				->leftJoin("vendiendo.clientes", "vendiendo.clientes.id", "=", "facturas.cliente_id")/*->orderBy("facturas.numero", "DESC")/*->orderBy("facturas.id","DESC")->orderBy("facturas.estado", "DESC")/*->where("estado","<>","abierta")*/
				->join("vendiendo.cajas_usuarios", "vendiendo.cajas_usuarios.id", "=", "facturas.caja_usuario_id")
				->join("vendiendo.cajas", "vendiendo.cajas_usuarios.caja_id", "=", "cajas.id");

            if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas =='no'){
                $almacen = Auth::user()->almacenActual();
                if(!$almacen)return response(['error'=>['No se ha encontrado ningún almacén relacionado con el usuario']],422);

                $facturas = $facturas->where("facturas.almacen_id",$almacen->id);
            }
			$facturas = $facturas->orderBy($orderBy, $sortColumnDir);
			$totalRegistros = $facturas->count();
			if ($search['value'] != null) {
				$facturas = $facturas->whereRaw(
					" ( facturas.id LIKE '%" . $search["value"] . "%' OR " .
					" numero LIKE '%" . $search["value"] . "%' OR" .
					" facturas.created_at LIKE '%" . $search["value"] . "%' OR" .
					" clientes.nombre LIKE '%" . $search["value"] . "%' OR" .
					" LOWER(CONCAT(usuarios.nombres, ' ', usuarios.apellidos)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
					" LOWER(CONCAT(cajas.nombre, ' - ', cajas.prefijo)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
					" facturas.estado LIKE '%" . $search["value"] . "%' )");
			}

			$parcialRegistros = $facturas->count();
			$facturas = $facturas->skip($start)->take($length);

			$object = new \stdClass();
			if ($parcialRegistros > 0) {
				foreach ($facturas->get() as $value) {
					$myArray[] = (object)array('id' => $value->id_factura,
						'numero' => $value->numero,
						'valor' => "$ " . number_format($value->subtotal + $value->iva - $value->descuento, 2, ',', '.'),
						'created_at' => $value->created_at->format('Y-m-d H:i:s'),
						'cliente' => $value->estado != "cerrada" ? $value->cliente : "",
						'usuario' => $value->usuario,
						'caja' => $value->caja,
						'estado' => $value->estado,
						'dias_credito' => $value->dias_credito,
						'abonos_count' => \App\Models\Factura::abonosByFactura($value->id_factura));
				}
			} else {
				$myArray = [];
			}

			$data = ['length' => $length,
				'start' => $start,
				'buscar' => $search['value'],
				'draw' => $request->get('draw'),
				//'last_query' => $facturas->toSql(),
				'recordsTotal' => $totalRegistros,
				'recordsFiltered' => $parcialRegistros,
				'data' => $myArray,
				'info' => $facturas->get()];

			return response()->json($data);
		}

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		Resolucion::activarEnEspera();
		if(Auth::user()->permitirFuncion("Crear","facturas","inicio") && Auth::user()->cajaAsignada() && (Auth::user()->plan()->n_facturas == 0 || Auth::user()->plan()->n_facturas > Auth::user()->countFacturasAdministrador())) {
			$cliente = null;
			if(Auth::user()->plan()->cliente_predeterminado == "si"){
				$cliente = Cliente::permitidos()->where("predeterminado","si")->first();
			}
			return view('factura.create')->with("cliente_",$cliente)->with("display_factura_abierta",false)->with("full_screen",true);
		}

		return redirect("/");
	}

	public function postFacturar(Request $request){
		$caja = Auth::user()->cajaAsignada();
		if(Auth::user()->permitirFuncion("Crear","facturas","inicio") && $caja && (Auth::user()->plan()->n_facturas == 0 || Auth::user()->plan()->n_facturas > Auth::user()->countFacturasAdministrador())) {
			$valor_puntos_redimidos = 0;
			if ($request->has('valor_puntos'))
				$valor_puntos_redimidos = $request->get('valor_puntos');

			if(!$request->has("estado") || !($request->has("estado") && ($request->input("estado") == "Pendiente por pagar" || $request->input("estado") == "Pagada" || $request->input("estado") == "Pedida")))
				return response(["Error"=>["La informacion enviada es incorrecta"]],422);
			if($request->input("estado") == "Pendiente por pagar"){

				$messages = [
					"numero_cuotas.required"=>"El campo número de cuotas es requerido",
					"numero_cuotas.numeric"=>"El campo número de cuotas debe ser numérico",
					"numero_cuotas.max"=>"El número máximo de cuotas puede ser 12",

					"tipo_periodicidad_notificacion.required"=>"El campo tipo periodicidad de la notificación es requerido",
					"tipo_periodicidad_notificacion.in"=>"La información enviada es incorrecta",

					"fecha_primera_notificacion.required_if"=>"El campo fecha primera notificación es requerido",
					"fecha_primera_notificacion.date"=>"El campo fecha primera notificación debe ser de tipo fecha",

					"dias_credito.required" => "El campo número de dias de credito es requerido",
					"dias_credito.numeric"  => "El campo número de dias de credito debe ser númerico",
					"dias_credito.between"  => "El campo número de dias de credito debe estar entre :min - :max dias",
				];

				$rules = [
					"numero_cuotas"=>"required|numeric|max:12",
					"tipo_periodicidad_notificacion"=>"required|in:quincenal,mensual,nunca",
					"fecha_primera_notificacion"=>"required_if:tipo_periodicidad_notificacion,quincenal,mensual|date",
					"dias_credito"=>"required|numeric|between:1,120",
				];

				$validator = Validator::make($request->all(), $rules, $messages);

				if($validator->fails())
					return response($validator->errors(),422);
			}
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
				if ($request->has("id_cliente")) {
					$cliente = Cliente::permitidos()->where("id", $request->input("id_cliente"))->first();

					if ($cliente) {

						DB::beginTransaction();
						//se le quitan los puntos redimidos en el pago de la factura
						$cliente->valor_puntos -= $valor_puntos_redimidos;
						$cliente->save();

						if(Auth::user()->bodegas == 'si')
						    $ultimaFact = ABFactura::ultimaFactura(true);
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

						if((Auth::user()->bodegas == 'no' && Factura::permitidos()->where("numero","00".$numero)->first())
                        ||(Auth::user()->bodegas == 'si' && ABFactura::permitidos()->where("numero","00".$numero)->first())){
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
						$relacion_caja_usuario = Auth::user()->cajas()->select("cajas_usuarios.id")->where("cajas_usuarios.caja_id",$caja->id)
							->where("cajas_usuarios.estado","activo")->first();
						$continuar = true;
						if(Auth::user()->bodegas == 'si')
						    $factura = new ABFactura();
						else
						    $factura = new Factura();

						$factura->numero = "00".$numero;
						$factura->estado = "facturada";
						$factura->cliente_id = $cliente->id;
						$factura->usuario_creador_id = Auth::user()->id;
						$factura->caja_usuario_id = $relacion_caja_usuario->id;
						$factura->plan_usuario_id = Auth::user()->ultimoPlan()->id_relacion;
                        if(Auth::user()->bodegas == 'si')
                            $factura->almacen_id = Auth::user()->almacenActual()->id;

                        if (Auth::user()->perfil->nombre == "administrador"){
                            if(Auth::user()->bodegas == 'si')
                                $factura->usuario_id = Auth::user()->userAdminId();
                            else
							    $factura->usuario_id = Auth::user()->id;
						}else {
                            $factura->usuario_id = Auth::user()->usuario_creador_id;
                        }
						$factura->resolucion_id = $resolucion->id;
						$factura->subtotal = 0;
						$factura->iva = 0;
						$factura->estado = $request->input("estado");

						if($request->input("estado") == "Pendiente por pagar"){
							$factura->numero_cuotas = $request->input("numero_cuotas");
							$factura->tipo_periodicidad_notificacion = $request->input("tipo_periodicidad_notificacion");
							if($request->input("tipo_periodicidad_notificacion") == "quincenal" || $request->input("tipo_periodicidad_notificacion") == "mensual"){
								$factura->fecha_primera_notificacion = $request->input("fecha_primera_notificacion");
							}
						}
						if($request->get('estado') == 'Pedida'){
							$factura->numero_cuotas = '0';
							$factura->tipo_periodicidad_notificacion = "";
							$factura->fecha_primera_notificacion = "";
							$factura->dias_credito =  "0";
							//$factura->estado = "Pagada";
							$factura->save();
						}
						$factura->dias_credito = $request->get('dias_credito');
						$factura->observaciones = $request->input("observaciones");
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
						while ($continuar) {
							if ($request->has("producto_" . $aux)) {
								$pr = $request->input("producto_" . $aux);
								if ($pr["cantidad"] >= 0.1) {
									if(Auth::user()->bodegas == 'si') {
                                        $producto = ABProducto::permitidos()->where("id", $pr["producto"])->first();
                                    }else {
                                        $producto = Producto::permitidos()->where("id", $pr["producto"])->first();
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
										if($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0){
											return response(["Error" => ["El producto ".$producto->producto_nombre." no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad."]], 422);
										}
										if ($pr["cantidad"] <= $maxima_cantidad) {
											if($producto->tipo_producto == "Terminado"){
												$historial = $producto->ultimoHistorialProveedor();
											}else{
												$historial = $producto->ultimoHistorial();
											}

											if(Auth::user()->bodegas == 'si'){
											    $historial_utilidad = ABHistorialUtilidad::where('producto_id',$producto->id)
                                                    ->where('almacen_id',$almacen->id)->orderBy('created_at','DESC')->first();
											    $utilidad = $historial_utilidad->utilidad;
                                            }else{
											    $utilidad = $producto->utilidad;
                                            }

											$factura->subtotal += ($producto->precio_costo+(($producto->precio_costo*$utilidad)/100)) * $pr["cantidad"];
											$factura->iva += ((($producto->precio_costo+(($producto->precio_costo*$utilidad)/100)) * $producto->iva) / 100) * $pr["cantidad"];
											if(Auth::user()->bodegas == 'si'){
                                                $factura->productosHistorialUtilidad()->save($historial_utilidad,["cantidad"=>$pr["cantidad"]]);
                                                $factura->productosHistorialCosto()->save($historial);
                                            }else{
                                                $factura->productosHistorial()->save($historial,["cantidad"=>$pr["cantidad"]]);
                                            }
                                            if (Auth::user()->bodegas == 'si')
											    $producto_aux = ABProducto::find($pr["producto"]);
											else
											    $producto_aux = Producto::find($pr["producto"]);

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
												$bajoUmbral .= $producto_aux->nombre.", ";
												$enviarNotificacion = true;
												$notificacion = new Notificacion();
												if(Auth::user()->bodegas == 'si')
												    $stock = $almacen_stock->stock;
												else
												    $stock = $producto_aux->stock;
												$notificacion->mensaje = '<strong>Producto bajo umbral</strong><p>Producto: '.$producto_aux->nombre.'</p><p>Umbral: '.$producto_aux->umbral.'</p><p>Stock: '.$stock.'</p>';
												$notificacion->tipo = "inventario";
												$notificacion->save();
												$notificacion->usuarios()->save(Auth::user());
												foreach (User::permitidos()->get() as $usr){
													if($usr->id != Auth::user()->id) {
														$notificacion->usuarios()->attach($usr->id);
													}
												}
											}
											$aux++;
										} else {
											return response(["Error" => ["La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $maxima_cantidad]], 422);
										}
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
						$relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
						if($factura->estado == "Pagada") {

						    $admin = User::find(Auth::user()->userAdminId());

						    $tipos_pago = $admin->tiposPago;

						    $valor_medios_pago = 0;
						    $valor_medios_pago_a_caja = 0;
						    foreach ($tipos_pago as $tp){
						        if($request->has('valor_tp_'.$tp->id)){
                                    $valor = $request->input('valor_tp_'.$tp->id);
                                    //si el valor del medio de pago no se debe enviar a la caja

                                    $valor_medios_pago += $valor;
                                    $codigo = "";
                                    if($request->has('codigo_tp_'.$tp->id))$codigo = $request->input('codigo_tp_'.$tp->id);

                                    $factura->tiposPago()->save($tp,['valor'=>$valor,'codigo_verificacion'=>$codigo]);
                                    if($tp->valor_a_caja == 'si') {
                                        $valor_medios_pago_a_caja += $valor;
                                    }else{
                                        $relacion_tipo_pago = $factura->tiposPago()->select('facturas_tipos_pago.*')->where('tipos_pago.id',$tp->id)->first();
                                        $cuenta = CuentaBancaria::permitidos()->where("id",$admin->cuenta_bancaria_forma_pago)->first();
                                        if($cuenta){
                                            $cuenta->saldo += $valor;
                                            $cuenta->save();
                                            if(Auth::user()->bodegas == 'si')
                                                $consignacion = new ABConsignacion();
                                            else
                                                $consignacion = new Consignacion();
                                            $consignacion->valor = $valor;
                                            $consignacion->saldo = $cuenta->saldo;
                                            $consignacion->cuenta_id = $cuenta->id;
                                            $consignacion->factura_tipo_pago_id = $relacion_tipo_pago->id;
                                            $consignacion->usuario_creador_id = Auth::user()->id;
                                            $consignacion->save();
                                        }else{
                                            return response(["Error" => ["Debe configurar las formas de pago y seleccionar una de las cuentas bancarias."]], 422);
                                        }
                                    }
                                }
                            }

							$ultimo_token = TokenPuntos::select('*')->where('cliente_id',$cliente->id)
								->where('fecha_vigencia', date('Y-m-d'))
								->orderby('created_at','DESC')->first();
							if($request->has('token_puntos')){
								if ($request->get('token_puntos')['fecha_vigencia'] == date('Y-m-d') && $request->get('token_puntos')['cliente_id']== $cliente->id && $request->get('token_puntos')['token'] == $ultimo_token->token){
									$relacion->valor_final +=  ($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago);

									//SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
									$valor_factura = ($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago);
									Cliente::savePoins($valor_factura+$valor_medios_pago,$cliente->id);
									//Desactiva el token
									$ultimo_token->estado = 'Inhabilitado';
									$ultimo_token->factura_id = $factura->id;
									$ultimo_token->save();
								}
							}else{
								$relacion->valor_final +=  ($factura->subtotal + $factura->iva)-$valor_medios_pago;
								//SE INGRESA EL NUMERO DE PUNTOS A LA CUENTA DEL CLIENTE
								$valor_factura = ($factura->subtotal + $factura->iva) - ($valor_puntos_redimidos+$valor_medios_pago);
								Cliente::savePoins($valor_factura+$valor_medios_pago,$cliente->id);
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
                            $relacion->valor_final += $valor_medios_pago_a_caja;
							$sql = "UPDATE cajas_usuarios SET valor_final = ".$relacion->valor_final." where id = ".$relacion->id;
							DB::statement($sql);

						}

						$factura->save();

						DB::commit();
						Session::flash("mensaje", "La factura ha sido registrada con éxito");
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

	public function postBuscarCliente(Request $request){
		// $filtro = "";
		// if($request->has("filtro")) {
		// 	$filtro = $request->input("filtro");
		// }
		// 	$clientes = Cliente::permitidos()->where(function($q) use ($filtro){
		// 		$q->where("identificacion","like","%".$filtro."%")
		// 			->orWhere("nombre","like","%".$filtro."%");
		// 	})->get();
		// 	if($clientes)
		//return ["success"=>true,"clientes"=>$clientes];
		return view("factura.buscar_clientes");
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



	public function getListFacturaCliente(Request $request){
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

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function postStore(RequestNuevoProveedor $request)
	{
		if(Auth::user()->permitirFuncion("Crear","proveedores","configuracion")) {
			$proveedor = new Proveedor();
			$proveedor->fill($request->all());
			$proveedor->usuario_creador_id = Auth::user()->id;
			$proveedor->usuario_id = Auth::user()->userAdminId();
			$proveedor->save();
			Session::flash("mensaje", "El proveedor a sido registrado con éxito");
			return ["success" => true];
		}

		return response("Unauthorized",401);
	}

	public function postAnular(Request $request){
		if(Auth::user()->permitirFuncion("Anular","facturas","inicio") && Auth::user()->cajaAsignada()){
			if($request->has("id")){
			    if(Auth::user()->bodegas == 'si')
				    $factura = ABFactura::permitidos()->where("estado","<>","abierta")->where("id",$request->input("id"))->first();
			    else
				    $factura = Factura::permitidos()->where("estado","<>","abierta")->where("id",$request->input("id"))->first();

				if($factura){
					DB::beginTransaction();
					if($factura->estado == "Pendiente por pagar") $editCaja = false;
					else $editCaja = true;

					if($editCaja){
						$caja = Auth::user()->cajaAsignada();
						if(!$caja->exists)
							return response(["Error"=>["No se ha registrado la caja"]],422);

						$relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();

						if(!$relacion)
							return response(["Error"=>["No se ha asignado ninguna caja"]],422);

						$relacion->valor_final -=  $factura->subtotal + $factura->iva - $factura->descuento;
						$sql = "UPDATE cajas_usuarios SET valor_final = ".$relacion->valor_final." where id = ".$relacion->id;
						DB::statement($sql);

						$cliente = $factura->cliente;
						if($cliente && $cliente->predeterminado == 'no') {
                            $valor_factura = $factura->subtotal + $factura->iva;
                            $token = $factura->token;

                            if ($token) $valor_factura -= $token->valor;

                            $cliente = Cliente::unSavePoins($valor_factura,$cliente->id);
                        }

					}

					//siempre se debe editar el stock de los productos
                    if(Auth::user()->bodegas == 'si') {
                        $productosHistorial = $factura->productosHistorialUtilidad()->select("historial_utilidad.*", "facturas_historial_utilidad.cantidad")->get();
                    }else{
                        $productosHistorial = $factura->productosHistorial()->select("productos_historial.*", "facturas_productos_historial.cantidad")->get();
                    }
					foreach ($productosHistorial as $historial){
						$producto = $historial->producto;
						if(Auth::user()->bodegas =='si') {
						    $almacen = Auth::user()->almacenActual();
						    $almacen_stock = ABAlmacenStockProducto::where('almacen_id',$almacen->id)
                                ->where('producto_id',$producto->id)->first();
                            $almacen_stock->stock += $historial->cantidad;
                            $almacen_stock->save();

                        }else{
                            $producto->stock += $historial->cantidad;
                            $producto->save();
                        }
					}

					$factura->estado = "anulada";
					$factura->subtotal = 0;
					$factura->iva = 0;
					$factura->descuento = 0;
					$factura->save();
					DB::commit();
					Session::flash("mensaje","La factura ha sido anulada con éxito.");
					return ["success"=>true];
				}
			}
		}
		return response(["error"=>"Usted no tiene permisos para realizar esta acción"],401);
	}

	public function postPagar(Request $request){
		if(Auth::user()->permitirFuncion("Editar","facturas","inicio")){
			if($request->has("id")){
				$factura = Factura::permitidos()->where("id",$request->input("id"))->where("estado","<>","abierta")->first();
				if($factura){
					if(!$factura->estado == "Pendiente por pagar")
						return response(["Error"=>["La factura no se encuentra en estado Pendiente por pagar"]],422);

					$caja = Caja::where("fecha",date("Y-m-d"))
						->where("usuario_id",Auth::user()->userAdminId())->first();
					if(!$caja->exists)
						return response(["Error"=>["No se ha registrado la caja"]],422);

					$caja->efectivo_final +=  $factura->subtotal + $factura->iva;
					$caja->save();

					$factura->estado = "Pagada";
					$factura->save();
					Session::flash("mensaje","La factura ha sido pagada con éxito.");
					return ["success"=>true];
				}
			}
		}
		return response(["error"=>"Usted no tiene permisos para realizar esta acción"],401);
	}

	public function getDetalle($id)
	{
		if(Auth::user()->bodegas == 'si')
	        $factura = ABFactura::permitidos()->where("id",$id)->where("estado","<>","abierta")->first();
        else
	        $factura = Factura::permitidos()->where("id",$id)->where("estado","<>","abierta")->first();

		if($factura){
            if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no'){
                $almacen = Auth::user()->almacenActual();

                if(!$almacen)return redirect('/');

                if($factura->almacen_id != $almacen->id)return redirect('/');
            }
            $token_puntos = TokenPuntos::where("factura_id",$factura->id)->first();
            $valor_puntos = 0;
            if($token_puntos){
                $valor_puntos = $token_puntos->valor;
            }
			return view('factura.detalle')->with("factura",$factura)->with("valor_puntos",$valor_puntos)->with("token_puntos",$token_puntos)->with("full_screen",true);
		}
		return redirect("/factura");
	}

	public function postFiltro(Request $request){
		if($request->has("filtro")){
			$facturas = $this->listaFiltro($request->get("filtro"));
		}else{
			$facturas = Factura::permitidos()->where("estado","<>","abierta")->orderBy("estado","DESC")->orderBy("id","DESC")->paginate(env('PAGINATE'));
		}
		$facturas->setPath(url('/factura'));
		return view("factura.lista")->with("facturas",$facturas);
	}

	public function listaFiltro($filtro){
		$f = "%".$filtro."%";
		return $proveedores = Factura::permitidos()->where("estado","<>","abierta")->where(
			function($query) use ($f){
				$query->where("numero","like",$f)
					->orWhere("estado","like",$f)
					->orWhere("created_at","like",$f);
			}
		)->orderBy("created_at","DESC")->orderBy("id","DESC")->orderBy("estado","DESC")->paginate(env('PAGINATE'));
	}

	public function postStoreCliente(RequestNuevoCliente $request){
		if(Auth::user()->permitirFuncion("Crear","clientes","inicio")) {
            $administrador = User::find(Auth::user()->userAdminId());
			$clienteIdentificacion = Cliente::where("identificacion", $request->input("identificacion"))->where("usuario_id", $administrador->id)->first();
			if ($clienteIdentificacion) {
				return response(["error" => ["Ya existe un cliente con el número de identificaión ingresado"]], 422);
			}

			$cliente = new Cliente();
			$cliente->fill($request->all());
			$cliente->usuario_id = $administrador->id;
			$cliente->usuario_creador_id = Auth::user()->id;
			$cliente->save();
			return ["success" => true, "cliente" => $cliente];
		}else{
			return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],422);
		}
	}

	public function postFormEditarCliente(Request $request){
		if($request->has("cliente_id")){
			$cliente = Cliente::permitidos()->where("id",$request->input("cliente_id"))->first();
			if($cliente){
				return view("factura.form_cliente")->with("cliente",$cliente);
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
	    if(Auth::user()->bodegas == 'si')
		    $factura = ABFactura::permitidos()->where("estado","<>","abierta")->where("id",$id)->first();
	    else
            $factura = Factura::permitidos()->where("estado","<>","abierta")->where("id",$id)->first();

		if($factura){
			//$vista = view('factura.factura_carta.index')->with('factura',$factura);
			//$pdf = \PDFS::loadView('factura.factura_carta.index', ['factura'=>$factura]);
			//return \PDFS::loadFile('http://www.github.com')->stream('github.pdf');
			//$pdf->setOption('footer-right','Pag.[page]/[toPage]');

            $token_puntos = TokenPuntos::where("factura_id",$factura->id)->first();
            $valor_puntos = 0;
            if($token_puntos){
                $valor_puntos = $token_puntos->valor;
            }

			$pdf = App::make('dompdf.wrapper');
			//$pdf->set_paper('A4', 'landscape');
			$pdf = $pdf->loadView('factura.factura_carta.index', ['factura'=>$factura,'token_puntos'=>$token_puntos,'valor_puntos'=>$valor_puntos]);

			/*if (isset($pdf)){
				$font = Font_Metrics::get_font("Arial", "bold");
				$pdf->page_text(765, 550, "Pagina {PAGE_NUM} de {PAGE_COUNT}", $font, 9, array(0, 0, 0));
			}*/

			return $pdf->stream();
		}
		return redirect("/");
	}

	public function postAbonos(Request $request){

		if(Auth::user()->permitirFuncion("Editar","facturas","inicio")) {
			if ($request->has("id")) {
				if(Auth::user()->bodegas == 'si')
			        $factura = ABFactura::permitidos()->where("estado","<>","abierta")->where("id",$request->input("id"))->first();
                else
                    $factura = Factura::permitidos()->where("estado","<>","abierta")->where("id",$request->input("id"))->first();
				$abonos = $factura->abonos()->orderBy("id","DESC")->orderBy("fecha","DESC")->get();

				if ($factura){
					return view("factura.abonos")->with("factura", $factura)->with("abonos",$abonos)->render();
				}
			}
			return response(["Error" => ["La información enviada es incorrecta"]], 422);
		}
		return response(["Error" => ["Usted no tiene permisos para realizar esta tarea"]], 422);
	}

	public function postStoreAbono(Request $request)
	{
		//dd($request->all());
		$caja = Auth::user()->cajaAsignada();
		if (Auth::user()->permitirFuncion("Editar", "facturas", "inicio") && $caja) {
			//estado pedida
			if(Auth::user()->bodegas == 'si')
                $factura = ABFactura::permitidos()->where("estado","<>","abierta")->where("id",$request->input("factura"))->first();
            else
			    $factura = Factura::permitidos()->where("estado","<>","abierta")->where("id",$request->input("factura"))->first();

			if ($request->has('estado') && $request->get('estado') == 'Pedida'){
				$rules_p = [
					"numero_cuotas"=>"required|numeric",
					"dias_credito"=>"required|numeric|between:1,120",
					"fecha_primera_notificacion"=>"required|date"
				];

				$mensajes = [
					"fecha.required"=>"El campo fecha primera notificacion es requerido",
					"fecha.date"=>"El campo fecha primera notoficacion debe ser de tipo fecha",
					"numero_cuotas.required"=>"El número de cuotas es requerido",
					"numero_cuotas.numeric"=>"El número de cuotas debe ser numerico",
					"dias_credito.required"=>"El número de dias de credito es requerido",
					"dias_credito.numeric"=>"El número de dias de credito debe ser numerico",
					"dias_credito.between"=>"El número de dias de credito debe estar entre 1 y 120 dias",
				];

				$validator = Validator::make($request->all(),$rules_p,$mensajes);
				if($validator->fails()){
					return response($validator->errors(),422);
				}
				$factura->estado = "Pendiente por pagar";
				$factura->numero_cuotas = $request->get('numero_cuotas');
				$factura->dias_credito = $request->get('dias_credito');
				$factura->fecha_primera_notificacion = $request->get('fecha_primera_notificacion');
				$factura->tipo_periodicidad_notificacion = $request->get('tipo_periodicidad_notificacion');
				$factura->save();
			}

			$rules = [
				"fecha"=>"required|date",
				"valor"=>"required|numeric",
				"nota"=>"max:500",
				"factura"=>"required"
			];

			$mensajes = [
				"fecha.required"=>"El campo fecha es requerido",
				"fecha.date"=>"El campo fecha debe ser de tipo fecha",

				"valor.required"=>"El campo valor es requerido",
				"valor.numeric"=>"El campo valor debe ser numerico",
				"nota.max"=>"El campo nota debe contener máximo 500 caracteres",
				"factura.required"=>"La información envida es incorrecta fac"
			];

			$validator = Validator::make($request->all(),$rules,$mensajes);
			if($validator->fails()){
				return response($validator->errors(),422);
			}

			//$factura = Factura::permitidos()->where("id",$request->input("factura"))->first();
			if ($factura) {
				$relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
				$saldo = $factura->getSaldo();
				/*if($saldo > $request->input("valor")){
					dd("Saldo mayor");
				}else if($saldo < $request->input("valor")){
					dd("Saldo menor - ".$saldo ." - ". $request->input("valor")." - ".abs(round($saldo - $request->input("valor"),2)));
				}else{
					dd("IGUALES");
				}*/
				if((round($saldo,2) - round($request->input("valor"),2)) < 0){
					return response(["Error" => ["El valor ingresado no puede ser mayor al saldo actual ($ ".number_format($saldo,2,',','.').")"]], 422);
				}
				$cuotas_pagas = count($factura->abonos);
				if(($factura->numero_cuotas - $cuotas_pagas) == 1){
					if(!(round($saldo,2) - round($request->input("valor"),2)) == 0){
						return response(["Error" => ["Recuerde que este es el último abono, por lo tanto el valor ingresado debe ser $ ".number_format($saldo,2,',','.')]], 422);
					}
				}
				//	DB::beginTransaction();
                if(Auth::user()->bodegas == 'si')
    				$abono = new ABAbono();
				else
				    $abono = new Abono();

				$data = $request->all();
				$data["usuario_id"] = Auth::user()->id;
				$data["tipo_abono"] = "factura";
				$data["tipo_abono_id"] = $factura->id;
				$data["caja_usuario_id"] = $relacion->id;
				$abono->fill($data);
				$abono->save();

				$relacion->valor_final += $abono->valor;

				$sql = "UPDATE cajas_usuarios SET valor_final = ".$relacion->valor_final." where id = ".$relacion->id;
				DB::statement($sql);

				$mensaje = "El abono ha sido registrado con éxito";
				if(abs(round($saldo,2) - round($request->input("valor"),2)) == 0){
					$factura->estado = "Pagada";
					$factura->save();
					$mensaje .= ", el saldo de la factura ha sido cancelado completamente";
				}

				Session::flash("mensaje", $mensaje);
				return ["success" => true];
			}

			return response(["Error" => ["La información enviada es incorrecta"]], 422);
		}
		return response(["Error" => ["Usted no tiene permisos para realizar esta tarea"]], 422);
	}

	public static function postEstado(Request $request)
	{
		if (Auth::user()->permitirFuncion("Editar", "facturas", "inicio")) {

			DB::beginTransaction();
			if ($request->get('estado') == 'Pedida'){
				if(Auth::user()->bodegas == 'si')
			        $factura = ABFactura::permitidos()->where("estado","<>","abierta")->where('id',$request->get('id_factura'))->first();
				else
    			    $factura = Factura::permitidos()->where("estado","<>","abierta")->where('id',$request->get('id_factura'))->first();

				if ($factura){
					$factura->estado = "Pagada";

					//Incrementa el efectivo de la caja
					$caja = Caja::abierta();
					if(!$caja->exists)
						return response(["Error"=>["No se ha registrado la caja"]],422);

					$caja->efectivo_final +=  $factura->subtotal + $factura->iva;
					$caja->save();
				}
				$factura->save();
				DB::commit();
				if ($factura)
					return response()->json(['response' => 'Se realizó satisfactoriamente el pago de la factura']);
				else
					return response()->json(['response' => 'Ocurrió un error']);
			}else{
				return response()->json(['response' => 'Ocurrió un error']);
			}
		}
		return response(["Error" => ["Usted no tiene permisos para realizar esta tarea"]], 422);
	}

	public function postFacturarFacturaAbierta(Request $request){
		if(Auth::user()->plan()->factura_abierta == "si") {

			$resolucion = Resolucion::getActiva();
			if($resolucion) {
				$aux = 1;
				$continuar = true;
				DB::beginTransaction();
				$inicio = date("Y-m-d")." 00:00:00";
				$fin = date("Y-m-d")." 23:59:59";
				$nueva = false;//identifica si la factura se a creado antes
				if(Auth::user()->bodegas == 'si')
                    $facturaAbierta = ABFactura::permitidos()->whereBetween("created_at",[$inicio,$fin])->where("estado","abierta")->first();
                else
                    $facturaAbierta = Factura::permitidos()->whereBetween("created_at",[$inicio,$fin])->where("estado","abierta")->first();
                if(!$facturaAbierta) {
					$nueva = true;
                    if(Auth::user()->bodegas == 'si')
					    $ultimaFact = ABFactura::ultimaFactura();
                    else
					    $ultimaFact = Factura::ultimaFactura();
					$numero = 1;
					if ($ultimaFact) {
						$numero = intval($ultimaFact->numero) + 1;
					} else {
						$numero = intval($resolucion->inicio);
					}
					if (!($resolucion->inicio <= $numero && $resolucion->fin >= $numero)) {
						return response(["Error" => ["A ocurrido un error generando el número de la factura, por favor verifique que su resolución actual no haya expirado."]], 422);
					}

					if (
                        (Factura::permitidos()->where("numero", "00" . $numero)->first() && Auth::user()->bodegas == 'no')
                        || (ABFactura::permitidos()->where("numero", "00" . $numero)->first() && Auth::user()->bodegas == 'si')
                    ) {
						return response(["Error" => ["A ocurrido un error generando el número de la factura, por favor recargue su página y diligencie nuevamente la factura."]], 422);
					}

                    if(Auth::user()->bodegas == 'si')
					    $facturaAbierta =  new ABFactura();
					else
					    $facturaAbierta =  new Factura();

					$facturaAbierta->numero = "00".$numero;
					$facturaAbierta->estado = "abierta";
					$facturaAbierta->usuario_creador_id = Auth::user()->id;
					$facturaAbierta->usuario_id = Auth::user()->userAdminId();
					$facturaAbierta->resolucion_id = $resolucion->id;
					$facturaAbierta->subtotal = 0;
					$facturaAbierta->iva = 0;
				}


				$facturaAbierta->save();

				if($nueva) {
					//si es la ultima factura se termina la resolucion
					if ($numero == intval($resolucion->fin)) {
						$resolucion->estado = "terminada";
						$resolucion->save();
						//se busca si existe una resolucion en espera que tenga continue el consecutivo
						$resEnEspera = Resolucion::resolucionEnEspera($numero + 1);
						if ($resEnEspera) {
							$resEnEspera->estado = "activa";
							$resEnEspera->save();
						}
					}
				}
				$bajoUmbral = "Los siguientes productos se estan agotando ";
				$enviarNotificacion = false;

				while ($continuar) {
					if ($request->has("producto_" . $aux)) {
						$pr = $request->input("producto_" . $aux);
						if ($pr["cantidad"] >= 0.1) {
							$producto = Producto::permitidos()->where("id", $pr["producto"])->first();
							if ($producto) {
								$maxima_cantidad = $producto->stock;
								//validar medidas unitarias y fraccionales
								if($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0){
									return response(["Error" => ["El producto ".$producto->producto_nombre." no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad."]], 422);
								}
								if ($pr["cantidad"] <= $maxima_cantidad) {
									if($producto->tipo_producto == "Terminado"){
										$historial = $producto->ultimoHistorialProveedor();
									}else{
										$historial = $producto->ultimoHistorial();
									}
									$facturaAbierta->subtotal += ($producto->precio_costo+(($producto->precio_costo*$producto->utilidad)/100)) * $pr["cantidad"];
									$facturaAbierta->iva += ((($producto->precio_costo+(($producto->precio_costo*$producto->utilidad)/100)) * $producto->iva) / 100) * $pr["cantidad"];

									$facturaAbierta->productosHistorial()->save($historial,["cantidad"=>$pr["cantidad"],"usuario_id"=>Auth::user()->id]);
									$producto_aux = Producto::find($pr["producto"]);
									$producto_aux->stock = $producto->stock - $pr["cantidad"];
									$producto_aux->save();
									/*if($producto_aux->umbral >= $producto_aux->stock){
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
                                    }*/
									$aux++;
								} else {
									return response(["Error" => ["La cantidad máxima permitida para el producto " . $producto->producto_nombre . " es " . $maxima_cantidad]], 422);
								}
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

				$caja = Caja::where("fecha",date("Y-m-d"))
					->where("usuario_id",Auth::user()->userAdminId())->first();
				if(!$caja->exists)
					return response(["Error"=>["No se ha registrado la caja"]],422);

				$caja->efectivo_final +=  $facturaAbierta->subtotal + $facturaAbierta->iva;
				$caja->save();
				$facturaAbierta->save();
				DB::commit();
				Session::flash("mensaje-factura-abierta", "La venta ha sido registrada con éxito");
				$dataResponse = ["success" => true];
				/*if($enviarNotificacion){
                    $dataResponse["mensajeNotificacion"]  = $bajoUmbral;
                    $dataResponse["notificacion"]  = true;
                }*/
				return $dataResponse;
			}else{
				response(["Error" => ["No existe ninguna resolución activa relacionada con su usuario."]], 422);
			}
			return response(["Error" => ["La información enviada es incorrecta."]], 422);
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function getDetalleFacturaAbierta($id = null){
		$facturaAbierta = Factura::permitidos()->where(function($q){
			$q->where("estado","cerrada")
				->orWhere("estado","abierta");
		})->orderBy("created_at","DESC");
		if($id != null) {
			$facturaAbierta = $facturaAbierta->where("id",$id);
		}
		$facturaAbierta = $facturaAbierta->first();

		$detalle = [];
		if($facturaAbierta){
			$detalle = $facturaAbierta->productosHistorial()->select("facturas_productos_historial.cantidad","productos_historial.*")->get();
		}else{
			return redirect()->back();
		}

		return view("factura.detalle_factura_abierta")->with("detalles",$detalle)->with("factura",$facturaAbierta);
	}

	public function postGenerarTokenPuntos(Request $request){
		if($request->has("cliente") && $request->has("valor")){
			$cliente = Cliente::permitidos()->where("predeterminado","<>","si")->where("id",$request->input("cliente"))->first();

			if($cliente){
				if($cliente->valor_puntos >= $request->input("valor")){
					DB::beginTransaction();
					$token = new TokenPuntos();
					$token->generarToken();
					$token->valor = $request->input("valor");
					$token->fecha_vigencia = date("Y-m-d");
					$token->cliente_id = $cliente->id;
					$token->save();
					$token->nombre_negocio = User::find(Auth::user()->userAdminId())->nombre_negocio;
					DB::commit();
					return ["success"=>true,"token"=>$token];
				}
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}
	public function postGenerarFacturaAbierta(Request $request){
		$dataResponse = ["success" => true];
		return $dataResponse;
	}
}
