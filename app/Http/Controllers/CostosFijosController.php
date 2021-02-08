<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Caja;
use App\Models\Categoria;
use App\Models\CostoFijo;
use App\Models\PagoCostoFijo;
use Illuminate\Http\Request;
use App\Http\Requests\RequestCategoria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;

class CostosFijosController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modCostoFijo");
		$this->middleware("modCaja");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Lista todas las categorias relacionadas con el usuario
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{

		if($request->has("mes") && $request->has("anio")){
			$diasMes = cal_days_in_month(CAL_GREGORIAN, $request->input("mes"), $request->input("anio"));
			$fecha_fin = $request->input("anio")."-".$request->input("mes")."-". $diasMes;
			$anio = $request->input("anio");
			$mes = $request->input("mes");
		}else {
			$diasMes = cal_days_in_month(CAL_GREGORIAN, date("n"), date("Y"));
			$fecha_fin = date("Y-m-") . $diasMes;
			$anio = date("Y");
			$mes = date("m");
		}


		//$costos_fijos = CostoFijo::permitidos()->where("created_at","<=",$fecha_fin)->get();
		return view("costos_fijos.index")->/*with("costos_fijos",$costos_fijos)->*/with("anio",$anio)->with("mes",$mes);
	}

	public function getListCostosFijos(Request $request){
			$search = $request->get("search");
	        $order = $request->get("order");
	        $sortColumnIndex = $order[0]['column'];
	        $sortColumnDir = $order[0]['dir'];
	        $length = $request->get('length');
	        $start = $request->get('start');
	        $columna = $request->get('columns');
	        $orderBy = $columna[$sortColumnIndex]['data'];   
	        // if($orderBy == null){$orderBy= 'nombre';}
	    	$myArray=[];
			if($request->has("mes") && $request->has("anio")){
				$diasMes = cal_days_in_month(CAL_GREGORIAN, $request->input("mes"), $request->input("anio"));
				$fecha_fin = $request->input("anio")."-".$request->input("mes")."-". $diasMes;
				$anio = $request->input("anio");
				$mes = $request->input("mes");
			}else {
				$diasMes = cal_days_in_month(CAL_GREGORIAN, date("n"), date("Y"));
				$fecha_fin = date("Y-m-") . $diasMes;
				$anio = date("Y");
				$mes = date("m");
			}

			$costos_fijos = CostoFijo::permitidos()->where("created_at","<=",$fecha_fin);//->orderBy($orderBy, $sortColumnDir);

            if(Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
                $costos_fijos = $costos_fijos->whereNull('costos_fijos.almacen_id');

			$totalRegistros = $costos_fijos->count();  
		 	if($search['value'] != null){
	            $costos_fijos = $costos_fijos->whereRaw(
	                " ( LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " LOWER(estado) LIKE '%".\strtolower($search["value"])."%')");
	        }

	        $parcialRegistros = $costos_fijos->count();
	        $costos_fijos = $costos_fijos->skip($start)->take($length);
		    $object = new \stdClass();
		        if($parcialRegistros > 0){
		            foreach ($costos_fijos->get() as $costo_fijo){
						$pago = $costo_fijo->pagoMes($mes,$anio);
						$alert = "red-text";
                        $intervalo =0;
                        $diasMes = cal_days_in_month(CAL_GREGORIAN, date("n"), date("Y"));
                        $mostrar = true;
                        if($pago && $pago->exists){
                            $fecha_pago = date($pago->fecha);
                            $hoy = date("Y").'-'.date("m").'-'.date("d");
                            $datetime1 = new \DateTime($fecha_pago);
                            $datetime2 = new \DateTime($hoy);
                            $interval = $datetime1->diff($datetime2);
                            $intervalo = $interval->format('%R%a');
                            if($intervalo <=$diasMes){
                                $alert = "green-text";
                                $mostrar = false;
                            }

                        }


		            	$myArray[]=(object) array(
		            		'id' => $costo_fijo->id,
		            		'nombre' => \App\TildeHtml::TildesToHtml($costo_fijo->nombre),
		            		'estado' => \App\TildeHtml::TildesToHtml($costo_fijo->estado),
		            		'fecha_pago' => ($pago && $pago->exists)?date("Y-m-d",strtotime($pago->fecha)):'',
		            		'valor' => ($pago && $pago->exists)?'$ '.number_format($pago->valor,0,null,'.'):'',		            		
		            		'if_pagar'=>$mostrar,
		            		'style_pagar'=>$alert
	            		);	
		            }
		        }

	        $data = ['length'=> $length,
	            'start' => $start,
	            'buscar' => $search['value'],
	            'draw' => $request->get('draw'),
	            //'last_query' => $costos_fijos->toSql(),              
                'recordsTotal' =>$totalRegistros,
                'recordsFiltered' =>$parcialRegistros,
	            'data' =>$myArray,
	            'info' =>$costos_fijos->get()];
		    
	        return response()->json($data);
	}

	public function postDataCostoFijo($id){
		$costo_fijo = CostoFijo::permitidos()->where("id",$id)->first();
		if($costo_fijo && $costo_fijo->exists){
			return ["success"=>true,"costo_fijo"=>$costo_fijo];
		}else{
			return response(["error"=>"Usted no tiene permisos para realizar esta acción"],401);
		}
	}


	public function postUpdate(Request $request){
		if(Auth::user()->permitirFuncion("Editar","costos fijos","inicio")) {
			if ($request->has("id")) {
				if ($request->has("nombre") && $request->has("estado") && ($request->input("estado") == "habilitado" || $request->input("estado") == "inhabilitado")) {
					$costo_fijo = CostoFijo::permitidos()->where("id", $request->input("id"))->first();
					if ($costo_fijo && $costo_fijo->exists) {
						$costo_fijo->nombre = $request->input("nombre");
						$costo_fijo->estado = $request->input("estado");
						$costo_fijo->save();
						Session::flash("mensaje", "La información del costo fijo ha sido editada con éxito.");
						return ["success" => true];
					} else {
						return response(["error" => "Usted no tiene permisos para realizar esta acción"], 401);
					}
				} else {
					return response(["error" => "Todos los campos son obligatorios."], 422);
				}
			} else {
				return response(["error" => "La información enviada es incorrecta"], 422);
			}
		}else{
			return response(["error" => "Usted no tiene permisos para realizar esta acción"], 401);
		}
	}


	public function postStore(Requests\createCostoFijoRequest $request){
		if(Auth::user()->permitirFuncion("Crear","costos fijos","inicio")) {
				if ($request->has("nombre") && $request->has("estado") && ($request->input("estado") == "habilitado" || $request->input("estado") == "inhabilitado")) {
					$costo_fijo = new CostoFijo();
					$costo_fijo->nombre = $request->input("nombre");
					$costo_fijo->estado = $request->input("estado");
					$costo_fijo->usuario_creador_id = Auth::user()->id;
                    $costo_fijo->usuario_id = Auth::user()->useradminId();
                    if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no'){
                        $almacen = Auth::user()->almacenActual();
                        if($almacen){
                            $costo_fijo->almacen_id = $almacen->id;
                        }
                    }
					$costo_fijo->save();
					Session::flash("mensaje", "El costo fijo ha sido registrado con éxito.");
					return ["success" => true];
				} else {
					return response(["error" => "Todos los campos son obligatorios."], 422);
				}
		}else{
			return response(["error" => "Usted no tiene permisos para realizar esta acción"], 401);
		}
	}

	public function postPagar(Requests\RequestPagarCostoFijo $request){
		$caja = Caja::abierta();
		if(Auth::user()->permitirFuncion("Pagar","costos fijos","inicio") && $caja) {
			if ($request->has("id")) {
					$costo_fijo = CostoFijo::permitidos()->where("id", $request->input("id"))->first();
					$fecha = $request->input("fecha");
					$valor = $request->input("valor");

					if(strtotime($fecha) <= strtotime(date("Y-m-d")) ) {
						if ($costo_fijo && $costo_fijo->exists) {

							if(strtotime($fecha) >= strtotime($costo_fijo->created_at)) {
								$mes = date("m",strtotime($fecha));
								$anio = date("Y",strtotime($fecha));
								if(!$costo_fijo->pagoMes($mes,$anio)) {
									if($costo_fijo->estado == "habilitado") {
                                        $caja = Caja::abierta();

                                        if($caja->efectivo_final - $valor < 0)
                                            return response(['error'=>['El valor ingresado es mayor al valor que existe actualmente en la caja']],422);
										//DB::beginTransaction();
										$pago = new PagoCostoFijo();
										$pago->valor = $valor;
										$pago->fecha = $fecha;
										$pago->caja_maestra_id = $caja->id;
										$pago->costo_fijo_id = $costo_fijo->id;
										$pago->usuario_id = Auth::user()->id;
										$pago->save();
										if ($pago)
											PagoCostoFijo::PayFixedCost($valor);
										Session::flash("mensaje", "El pago ha sido registrado con éxito.");
										return ["success" => true];
									}else{
										return response(["error" => "No se pudo registrar el pago, el costo fijo se encuentra deshabilitado."], 422);
									}
								}else{
									return response(["error" => "Ya se ha realizado un pago, para el costo fijo ".$costo_fijo->nombre.", en el mes y el año seleccionados."], 422);
								}
							}else{
								return response(["error" => "La fecha de pago debe ser mayor o igual a la fecha de creación de costo fijo (".date('Y-m-d',strtotime($costo_fijo->created_at)).")."], 422);
							}
						} else {
							return response(["error" => "Usted no tiene permisos para realizar esta acción"], 401);
						}
					}else{
						return response(["error" => "La fecha de pago debe ser menor o igual a la fecha actual"], 422);
					}
			} else {
				return response(["error" => "La información enviada es incorrecta"], 422);
			}
		}else{
			return response(["error" => "Usted no tiene permisos para realizar esta acción"], 401);
		}
	}
}
