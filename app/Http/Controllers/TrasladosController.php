<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ABAlmacenStockProducto;
use App\Models\ABBodegaStockProducto;
use App\Models\ABHistorialCosto;
use App\Models\ABHistorialUtilidad;
use App\Models\Abono;
use App\Models\ABProducto;
use App\Models\ABTrasladoPrecio;
use App\Models\Almacen;
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
use App\Models\Traslado;
use App\Models\TrasladoPrecio;
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

class TrasladosController extends Controller {

	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modTraslados");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		return view('traslados.index');
	}

	public function getListTraslados(Request $request)
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
		else $orderBy = "traslados.".$orderBy;

		if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
		else $sortColumnDir = "ASC";

		$traslados = Traslado::permitidos()->select("traslados.*","almacenes.nombre as almacen", DB::RAW("CONCAT(vendiendo.usuarios.nombres, ' ', vendiendo.usuarios.apellidos) as usuario"))
			->join("vendiendo.usuarios", "vendiendo.usuarios.id", "=", "traslados.usuario_creador_id")
			->leftJoin("almacenes", "almacenes.id", "=", "traslados.almacen_id");

		if(Auth::user()->admin_bodegas == 'no'){
		    if(Auth::user()->perfil->nombre == 'administrador'){
		        $almacen = Almacen::where('administrador',Auth::user()->id)->first();
            }else{
                $almacen = Almacen::where('id',Auth::user()->almacen_id)->first();
            }

            if(!$almacen) return false;
		    $traslados = $traslados->where('traslados.almacen_id',$almacen->id);
        }

		$traslados = $traslados->orderBy($orderBy, $sortColumnDir);
		$totalRegistros = $traslados->count();
		if ($search['value'] != null) {
			$traslados = $traslados->whereRaw(
				" ( traslados.id LIKE '%" . $search["value"] . "%' OR " .
				" numero LIKE '%" . $search["value"] . "%' OR" .
				" traslados.created_at LIKE '%" . $search["value"] . "%' OR" .
				" nombre LIKE '%" .$search["value"] . "%' OR".
				" LOWER(CONCAT(vendiendo.usuarios.nombres, ' ', vendiendo.usuarios.apellidos)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
				" estado LIKE '%" . $search["value"] ."%' )");
		}

		$parcialRegistros = $traslados->count();
        $traslados = $traslados->skip($start)->take($length);

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($traslados->get() as $value) {

				$myArray[]=(object) array('id' => $value->id,
					'numero'=>$value->numero,
					'valor'=>"$ ".number_format($value->getValor(),2,',','.'),
					'created_at'=>$value->created_at->format('Y-m-d H:i:s'),
					'almacen'=> $value->almacen,
					'usuario'=> $value->usuario,
					'estado'=>$value->estado,
					'detalles' => "<a href='".url("/traslado/detalle/".$value->id)."'><i class='fa fa-chevron-right'></i></a>");

            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $traslados->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$traslados->get()];

		return response()->json($data);

	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		if(Auth::user()->permitirFuncion("Crear","Traslados","inicio")) {
			return view('traslados.create')->with("display_factura_abierta",false)->with("display_compra_js",false);
		}

		return redirect("/");
	}

	public function postStore(Request $request){
		if(Auth::user()->permitirFuncion("Crear","Traslados","inicio")) {

			if ($request->has("almacen")) {

			    $almacen = null;
			    $desde_almacen = false;
			    if(Auth::user()->admin_bodegas == 'no')$desde_almacen = true;

			    if($request->input('almacen') != 'bodega')
				    $almacen = Almacen::permitidos()->where("id", $request->input("almacen"))->first();


				if ($almacen || $desde_almacen) {

					$ultimoTraslado = Traslado::ultimoTraslado();
					$numero = 1;
					if($ultimoTraslado){
						$numero = intval($ultimoTraslado->numero) + 1;
					}

					$aux = 1;
					$continuar = true;
					DB::beginTransaction();
					$traslado = new Traslado();
					$traslado->numero = "00".$numero;
					$traslado->estado = "enviado";
					if($almacen)
    					$traslado->almacen_id = $almacen->id;
					if($desde_almacen)
                        $traslado->almacen_remitente_id = Auth::user()->almacenActual()->id;

					$traslado->usuario_creador_id = Auth::user()->id;
                    $traslado->usuario_id = Auth::user()->userAdminId();

					$traslado->save();

					while ($continuar) {
						if ($request->has("producto_" . $aux)) {
							$pr = $request->input("producto_" . $aux);
							if ($pr["cantidad"] >= 0.1) {
								$producto = ABProducto::permitidos()->where("id", $pr["producto"])->first();
								if($desde_almacen){
                                    $producto = ABProducto::permitidos()->select("productos.*", "almacenes_stock_productos.stock as stock")
                                        ->join("vendiendo_alm.almacenes_stock_productos", "productos.id", "=", "vendiendo_alm.almacenes_stock_productos.producto_id")
                                        ->where("almacenes_stock_productos.almacen_id", Auth::user()->almacenActual()->id)
                                        ->where("productos.id", $pr["producto"])->first();
                                }
								if ($producto) {
									//validar medidas unitarias y fraccionales
									if($producto->medida_venta == "Unitaria" && $pr["cantidad"] % 1 != 0){
										return response(["Error" => ["El producto ".$producto->producto_nombre." no acepta cantidades de ventas fraccionales, por favor ingrese un numero entero en el campo cantidad."]], 422);
									}

                                    if($producto->stock - $pr["cantidad"] < 0)
                                        return response(["Error" => ["La cantidad máxima permitida para el producto ".$producto->producto_nombre." es ".$producto->stock]], 422);

									$historialCosto = ABHistorialCosto::where('producto_id',$producto->id)->where('proveedor_id',$producto->proveedor_actual)->orderBy('created_at','DESC')->first();

                                    $trasladoPrecio = new ABTrasladoPrecio();
                                    $trasladoPrecio->cantidad = $pr['cantidad'];
                                    $trasladoPrecio->traslado_id = $traslado->id;
                                    $trasladoPrecio->historial_costo_id = $historialCosto->id;

									if(!$desde_almacen || $almacen) {
                                        $ultimoHistorialUtilidad = ABHistorialUtilidad::where('producto_id', $producto->id)->where('almacen_id', $almacen->id)->orderBy('created_at', 'DESC')->first();

                                        if ($ultimoHistorialUtilidad) {
                                            $historialUtilidad = $ultimoHistorialUtilidad;
                                        } else {
                                            $historialUtilidad = new ABHistorialUtilidad();
                                            $historialUtilidad->utilidad = $pr['utilidad'];
                                            $historialUtilidad->producto_id = $producto->id;
                                            $historialUtilidad->almacen_id = $almacen->id;
                                        }
                                        $historialUtilidad->actualizacion_utilidad = $pr['utilidad'];

                                        $historialUtilidad->save();
                                        $trasladoPrecio->historial_utilidad_id = $historialUtilidad->id;
                                    }

                                    $trasladoPrecio->save();

									if($desde_almacen)
                                        $stockObj = ABAlmacenStockProducto::where('producto_id',$producto->id)->where('almacen_id',Auth::user()->almacenActual()->id)->first();
									else
									    $stockObj = ABBodegaStockProducto::where('producto_id',$producto->id)->first();

									if(!$stockObj)return response(["Error" => ["El producto (".$producto->nombre.") no ha sido encontrado."]], 422);

									if(($stockObj->stock - $pr['cantidad'])<0)
                                        return response(["Error" => ["La cantidad máxima permitida para el producto (".$producto->nombre.") es ".$stockObj->stock."."]], 422);

									$stockObj->stock -= $pr['cantidad'];
									$stockObj->save();

									/*$stockAlmacen = ABAlmacenStockProducto::where('producto_id',$producto->id)->where('almacen_id',$almacen->id)->first();
									if($stockAlmacen){
									    $stockAlmacen->stock += $pr['cantidad'];
                                    }else{
									    $stockAlmacen = new ABAlmacenStockProducto();
									    $stockAlmacen->stock = $pr['cantidad'];
									    $stockAlmacen->almacen_id = $almacen->id;
									    $stockAlmacen->producto_id = $producto->id;
                                    }

                                    $stockAlmacen->save();*/
                                    if(!$desde_almacen) {
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
					Session::flash("mensaje", "El traslado ha sido registrado con éxito");
					$dataResponse = ["success" => true,"url"=>url("/remision/detalle/".$traslado->id)];
					return $dataResponse;
				}
			}
			return response(["Error" => ["La información enviada es incorrecta."]], 422);

		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function getDetalle($id)
	{
		$traslado = Traslado::permitidos()->where("id",$id)->first();

		if($traslado){
			return view('traslados.detalle')->with("traslado",$traslado);
		}
		return redirect("/traslado");
	}

    public function postProcesar(Request $request){
        if(Auth::user()->permitirFuncion("Crear","Traslados","inicio")) {
            if($request->has('traslado')) {
                $traslado = Traslado::permitidos()->where('traslados.id',$request->input('traslado'))->where('estado','enviado')->first();
                if($traslado && (Auth::user()->admin_bodegas == 'no' || (Auth::user()->admin_bodegas == 'si' && !$traslado->almacen_id))) {
                    $traslado_precios = TrasladoPrecio::where('traslado_id',$traslado->id)->get();
                    if(Auth::user()->perfil->nombre == 'administrador' && Auth::user()->admin_bodegas == 'no')$almacen = Almacen::where('administrador',Auth::user()->id)->first();
                    else if(Auth::user()->perfil->nombre == 'usuario') $almacen = Almacen::where('almacenes.id',Auth::user()->almacen_id)->first();
                    else $almacen = false;
                    DB::beginTransaction();
                    foreach ($traslado_precios as $tp){
                        $pr = $tp->historialCosto->producto;
                        if($pr && $request->has('recibido_'.$pr->id)
                            && is_numeric($request->input('recibido_'.$pr->id))){

                            if($request->input('recibido_'.$pr->id) > $tp->cantidad){
                                return response(['error'=>['La cantidad máxima permitida para el producto '.$pr->nombre.' es '.$tp->cantidad]],422);
                            }

                            $tp->cantidad_recibida = $request->input('recibido_'.$pr->id);
                            if($request->has('observaciones_'.$pr->id)){
                                $tp->observaciones = $request->input('observaciones_'.$pr->id);
                            }
                            $tp->save();

                            if($traslado->almacen_id) {
                                $stockAlmacen = ABAlmacenStockProducto::where('producto_id', $pr->id)->where('almacen_id', $almacen->id)->first();
                                if ($stockAlmacen) {
                                    $stockAlmacen->stock += $request->input('recibido_' . $pr->id);
                                } else {
                                    $stockAlmacen = new ABAlmacenStockProducto();
                                    $stockAlmacen->stock = $request->input('recibido_' . $pr->id);
                                    $stockAlmacen->almacen_id = $almacen->id;
                                    $stockAlmacen->producto_id = $pr->id;
                                }
                                $stockAlmacen->save();
                            }else{
                                $stockBodegaObj = ABBodegaStockProducto::where('producto_id',$pr->id)->first();
                                $stockBodegaObj->stock += $request->input('recibido_' . $pr->id);
                                $stockBodegaObj->save();
                                $pr->stock += $stockBodegaObj->stock;
                                $pr->save();
                            }


                            if($request->input('recibido_'.$pr->id) < $tp->cantidad) {
                                if($traslado->almacen_remitente_id){
                                    $almacen_remitente = Almacen::permitidos()->where('id',$traslado->almacen_remitente_id)->first();
                                    $stockAlmacenObj = ABAlmacenStockProducto::where('producto_id', $pr->id)->where('almacen_id', $almacen_remitente->id)->first();

                                    if (!$stockAlmacenObj) return response(["Error" => ["El producto (" . $pr->nombre . ") no ha sido encontrado en el almacén que remite el traslado."]], 422);

                                    $stockAlmacenObj->stock += $tp->cantidad - $request->input('recibido_' . $pr->id);
                                    $stockAlmacenObj->save();
                                }else {
                                    $stockBodega = ABBodegaStockProducto::where('producto_id', $pr->id)->first();
                                    if (!$stockBodega) return response(["Error" => ["El producto (" . $pr->nombre . ") no ha sido encontrado en la bodega."]], 422);

                                    $stockBodega->stock += $tp->cantidad - $request->input('recibido_' . $pr->id);
                                    $stockBodega->save();
                                    $pr->stock = $stockBodega->stock;
                                    $pr->save();
                                }
                            }

                            if($request->input('recibido_'.$pr->id)>0 && $traslado->almacen_id){
                                $ultimoHistorialUtilidad = ABHistorialUtilidad::where('producto_id',$pr->id)->where('almacen_id',$almacen->id)->whereNotNull('actualizacion_utilidad')->orderBy('created_at','DESC')->first();
                                if($ultimoHistorialUtilidad->utilidad != $ultimoHistorialUtilidad->actualizacion_utilidad){
                                    $historialUtilidad = new ABHistorialUtilidad();
                                    $historialUtilidad->utilidad = $ultimoHistorialUtilidad->actualizacion_utilidad;
                                    $historialUtilidad->producto_id = $ultimoHistorialUtilidad->producto_id;
                                    $historialUtilidad->almacen_id = $ultimoHistorialUtilidad->almacen_id;
                                    $historialUtilidad->save();
                                }
                            }

                        }else{
                            return response(['error'=>['La información enviada es incorrecta']],422);
                        }
                    }

                    $traslado->update(['estado'=>'recibido']);
                    DB::commit();
                    return ['success' => true];
                }
            }
        }else{
            return response(['Error'=>['Unauthorized']],401);
        }
        return response(['error'=>['La información enviada es incorrecta']],422);
    }

}
