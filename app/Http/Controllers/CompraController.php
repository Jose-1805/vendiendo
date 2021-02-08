<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ABAbono;
use App\Models\ABBodegaStockProducto;
use App\Models\ABCompra;
use App\Models\ABHistorialCosto;
use App\Models\Abono;
use App\Models\ABProducto;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Factura;
use App\Models\FacturaProducto;
use App\Models\HistorialDevolucion;
use App\Models\MateriaPrimaHistorial;
use App\Models\ProductoHistorial;
use App\Models\MateriaPrima;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Resolucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Http\Requests\RequestNuevoCliente;
use App\Http\Requests\DevolucionRequest;
use Symfony\Component\Security\Core\User\User;
use Log;
use App\Models\ProveedorMateriaPrima;


class CompraController extends Controller
{
	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modCompras");
		$this->middleware("modCaja");
		$this->middleware("terminosCondiciones");
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex(Request $request)
	{
		$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
		$efectico_caja = Caja::cajasPermitidas()->where('estado','abierta')->take(1)->first();
		if(!$efectico_caja)$efectico_caja = new Caja();

		$filtro = "";
		if ($request->has("filtro")) {
			$compras = $this->listaFiltro($request->get("filtro"));
			$filtro = $request->get("filtro");
		} else {
		    if(Auth::user()->bodegas == 'si')
			    $compras = Compra::permitidos()->orderBy('compras.id', 'DESC')->paginate(env('PAGINATE'));
		    else
			    $compras = ABCompra::permitidos()->orderBy('compras.id', 'DESC')->paginate(env('PAGINATE'));
            //dd($compras);
		}
		return view('compras.index')->with("compras", $compras)->with("filtro", $filtro)->with('efectivo_caja',$efectico_caja)->with("display_factura_abierta",false);
	}

    public function getListCompras(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];


		if($orderBy == "numero")$orderBy = "compras.id";

		if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
		else $sortColumnDir = "ASC";

		if($orderBy == "nombre_proveedor")$orderBy = "vendiendo.proveedores.nombre";
		if($orderBy == "created_at")$orderBy = "compras.created_at";
		if($orderBy == "usuario_creador")$orderBy = "vendiendo.usuarios.nombres";

		if(Auth::user()->bodegas == 'si')
		    $compras = ABCompra::permitidos()->select("compras.*");
		else
		    $compras = Compra::permitidos()->select("compras.*");

        $compras = $compras->join("vendiendo.proveedores","compras.proveedor_id","=","vendiendo.proveedores.id")
			->join("vendiendo.usuarios","compras.usuario_creador_id","=","vendiendo.usuarios.id")/*orderBy("updated_at", "DESC")->orderBy("id", "DESC")*/;

        $compras = $compras->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $compras->count();
        //BUSCAR
        if ($search['value'] != null) {
            $compras = $compras->whereRaw(
                " ( compras.id LIKE '%" . $search["value"] . "%' OR " .
                " compras.numero LIKE '%" . $search["value"] . "%' OR" .
                " compras.created_at LIKE '%" . $search["value"] . "%' OR" .
                " vendiendo.proveedores.nombre LIKE '%" .$search["value"] . "%' OR".
                " LOWER(CONCAT(vendiendo.usuarios.nombres, ' ', vendiendo.usuarios.apellidos)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                " compras.estado LIKE '%" . $search["value"] ."%' )");
        }

        $parcialRegistros = $compras->count();
        $compras = $compras->skip($start)->take($length);

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($compras->get() as $value) {
                $myArray[]=(object) array('id' => $value->id,
                    'numero'=>$value->numero,
                    'valor'=> "$ ".number_format($value->valor,2,',','.'),
                    'created_at'=>$value->created_at->format('Y-m-d H:i:s'),
                    'nombre_proveedor'=> $value->proveedor->nombre,
                    'usuario_creador'=> $value->usuarioCreador->nombres." ".$value->usuarioCreador->apellidos,
                    'estado'=>$value->estado,
                    'dias_credito'=>$value->dias_credito,
                    'estado_pago' => $value->estado_pago);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $compras->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$compras->get()];

        return response()->json($data);

    }

	public function postNull(){
		return ["success"=>true];
	}

	public function postStore(Request $request){
		$caja_abierta = Caja::abierta();
		if(Auth::user()->permitirFuncion("Crear","compras","inicio") && $caja_abierta && (Auth::user()->plan()->n_compras == 0 || Auth::user()->plan()->n_compras > Auth::user()->countComprasAdministrador())) {
            if(!((Auth::user()->bodegas == 'si' && \App\Models\Bodega::permitidos()->get()->count()) || Auth::user()->bodegas == 'no'))
                return response(['error'=>['Unauthorized.']],401);
			//dd($request->all());
				if ($request->has("id_proveedor") && $request->has("estado")) {
					$estados = ['Recibida','Pendiente por recibir'];
					$estados_pago = ['Pendiente por pagar','Pagada'];
					if(in_array($request->input("estado"),$estados) && in_array($request->input("estado_pago"),$estados_pago)) {
						$proveedor = Proveedor::permitidos()->where("id", $request->input("id_proveedor"))->first();
						if ($proveedor) {
						    if(Auth::user()->bodegas == 'si')
							    $ultimaCompra = ABCompra::ultimaCompra();
							else
						        $ultimaCompra = Compra::ultimaCompra();
							$numero = 1;
							$productos = false;
							$materias_primas = false;
							if ($ultimaCompra) {
								$numero = intval($ultimaCompra->numero) + 1;
							}

							$aux = 1;
							$continuar = true;
							DB::beginTransaction();
							if(Auth::user()->bodegas == 'si')
							    $compra = new ABCompra();
							else
							    $compra = new Compra();
							$compra->numero = "00" . $numero;
							$compra->estado = $request->input("estado");
							$compra->estado_pago = $request->input("estado_pago");
							$compra->proveedor_id = $proveedor->id;
							$compra->usuario_creador_id = Auth::user()->id;
							$compra->caja_maestra_id = $caja_abierta->id;
							if (Auth::user()->perfil->nombre == "administrador")
								$compra->usuario_id = Auth::user()->id;
							else
								$compra->usuario_id = Auth::user()->usuario_creador_id;

							$compra->valor = 0;
							$compra->numero_cuotas = $request->get('numero_cuotas');
							$compra->fecha_primera_notificacion = $request->get('fecha_primera_notificacion');
							$compra->tipo_periodicidad_notificacion = $request->get('tipo_periodicidad_notificacion');
							$compra->save();

							while ($continuar) {
								if ($request->has("producto_" . $aux)) {
									$pr = $request->input("producto_" . $aux);
									if ($pr["cantidad"] >= 1) {
										$producto = $proveedor->productos()->where("productos.id",$pr["id"])->first();
										if ($producto) {
											$historial = $producto->ultimoHistorialProveedorId($proveedor->id);

											if($request->has("predeterminado") && $request->input("predeterminado") == "1"){
												$producto->precio_costo = $historial->precio_costo_nuevo;
												$producto->iva = $historial->iva_nuevo;
												if(Auth::user()->bodegas == 'no')
												    $producto->utilidad = $historial->utilidad_nueva;
												$producto->proveedor_actual = $proveedor->id;
												$producto->save();
											}

											if(Auth::user()->bodegas == 'si')
											    $compra->historialCostos()->save($historial,["cantidad"=>$pr["cantidad"]]);
											else
											    $compra->productosHistorial()->save($historial,["cantidad"=>$pr["cantidad"]]);

											if($request->input("estado") == "Recibida") {
												$data_precios = [[$historial->precio_costo_nuevo,$pr["cantidad"]]];
												$producto->updatePromedioPonderado($data_precios);

												if(Auth::user()->bodegas == 'si'){
												    $stock_bodega = ABBodegaStockProducto::where('producto_id',$producto->id)->first();
												    if(!$stock_bodega){
												        $stock_bodega = new ABBodegaStockProducto();
												        $bodega = Bodega::permitidos()->first();
												        if(!$bodega)return response(["Error" => ["La información enviada es incorrecta."]], 422);

												        $stock_bodega->bodega_id = $bodega->id;
												        $stock_bodega->producto_id = $producto->id;
												        $stock_bodega->stock = 0;
                                                    }
                                                    $stock_bodega->stock += $pr["cantidad"];
												    $stock_bodega->save();
                                                }
											}
											$productos = true;
											$compra->valor += ($historial->precio_costo_nuevo+(($historial->precio_costo_nuevo * $historial->iva_nuevo)/100)) * $pr["cantidad"];
											$aux++;

										} else {
											return response(["Error" => ["La información enviada es incorrecta."]], 422);
										}
									} else {
										return response(["Error" => ["La cantidad de un producto debe ser mayor o igual a 1."]], 422);
									}
								}else{
									$continuar = false;
								}
							}
							$continuar = true;
							$aux = 1;
							while ($continuar) {
								if ($request->has("materia_prima_" . $aux)) {
									$mp = $request->input("materia_prima_" . $aux);
									if ($mp["cantidad"] >= 1) {
										$materia_prima = $proveedor->materiasPrimas()->where("materias_primas.id",$mp["id"])->first();
										if ($materia_prima) {
											$historial = $materia_prima->ultimoHistorial($proveedor->id);
											$compra->materias_primas_historial()->save($historial,["cantidad"=>$mp["cantidad"]]);
											if($request->input("estado") == "Recibida") {
												$total = $materia_prima->promedio_ponderado * $materia_prima->stock;
												$total += $historial->precio_costo_nuevo * $mp["cantidad"];
												$materia_prima->stock += $mp["cantidad"];
												$materia_prima->promedio_ponderado = $total/$materia_prima->stock;
												$materia_prima->save();
											}//SI NO, PREGUNTAR COMO ENTRA A INVENTARIO LOS PRODUCTOS QUE ESTAN CON ESTADO NO RECIBIDO
											$materias_primas = true;
											$compra->valor += $historial->precio_costo_nuevo * $mp["cantidad"];
											$aux++;
										} else {
											return response(["Error" => ["La información enviada es incorrecta."]], 422);
										}
									} else {
										return response(["Error" => ["La cantidad de una materia prima debe ser mayor o igual a 1."]], 422);
									}
								}else{
									$continuar = false;
								}
							}
							if($materias_primas || $productos) {
								if (($request->get('dias_credito') > 0 && $request->get('dias_credito') <= 120) || $request->get('estado_pago') == 'Pagada'){
									$compra->dias_credito = $request->get('dias_credito');
									$compra->save();
									//Decrementa el valor de la caja en el valor de la compra de productos
									if ($request->get('estado_pago') == 'Pagada'){
										$compra_update = Compra::payPurchase($compra->valor);
										if(!$compra_update)
											return response(["Error" => ["No existe suficiente efectivo en la caja maestra"]], 422);
									}
									DB::commit();
									Session::flash("mensaje", "La compra ha sido registrada con éxito");
									return ["success" => true];
								}else{
									return response(["Error" => ["Los dias de credito son minimo 1 dia y maximo 120 dias"]], 422);
								}

							}else{
								return response(["Error" => ["Agregue por lo menos un elemento al detalle de la compra."]], 422);
							}
						}
					}
				}
				return response(["Error" => ["La información enviada es incorrecta."]], 422);
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function getCreate()
	{
		$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
		$efectico_caja = Caja::cajasPermitidas()->where('fecha',$fecha_actual)->take(1)->first();
		if(!$efectico_caja)$efectico_caja = new Caja();
		if (Auth::user()->permitirFuncion("Crear", "compras", "inicio") && Caja::abierta())
            if(Auth::user()->plan()->n_compras == 0 || Auth::user()->plan()->n_compras > Auth::user()->countComprasAdministrador()) {
                if((Auth::user()->bodegas == 'si' && \App\Models\Bodega::permitidos()->get()->count()) || Auth::user()->bodegas == 'no')
                return view('compras.create')->with('efectivo_caja', $efectico_caja)->with("display_factura_abierta", false);
            }

		return redirect("/");
	}

	public function getDetalle($id)
	{
	    if(Auth::user()->bodegas == 'si')
		    $compra = ABCompra::permitidos()->where("id", $id)->first();
		else
	        $compra = Compra::permitidos()->where("id", $id)->first();
		if ($compra) {
			$user = Auth::user()->userAdminId();
			$lista_devoluciones = CuentaPorCobrar::where('usuario_id',$user)
				->where('compra_id',$id)
				//->where('estado','POR PAGAR')
				->orderby('estado','desc')
				->get();
			return view('compras.detalle')->with("compra", $compra)->with("lista_devoluciones", $lista_devoluciones);
		}
		return redirect("/compra");
	}

	public function postFiltro(Request $request)
	{
		if ($request->has("filtro")) {
			$compras = $this->listaFiltro($request->get("filtro"));
		} else {
			$compras = Compra::permitidos()->orderBy("updated_at", "DESC")->orderBy("id", "DESC")->paginate(10);
		}
		$compras->setPath(url('/factura'));
		$efectico_caja = Caja::cajasPermitidas()->where('fecha',date("Y-m-d"))->take(1)->first();
		return view("compras.lista")->with("compras", $compras)->with("efectivo_caja",$efectico_caja);
	}

	public function listaFiltro($filtro)
	{
		$f = "%" . $filtro . "%";
		$compras = Compra::permitidos();
		if(Auth::user()->bodegas == 'si')$compras = ABCompra::permitidos();
		return $compras->where(
			function ($query) use ($f) {
				$query->where("numero", "like", $f)
					->orWhere("estado", "like", $f)
					->orWhere("valor", "like", $f)
					->orWhere("created_at", "like", $f);
			}
		)->orderBy("compras.id", "DESC")->paginate(env('PAGINATE'));
	}

	public function postBuscarProveedor(Request $request){
		$filtro = "";
		if($request->has("filtro")) {
			$filtro = $request->input("filtro");
		}
		$proveedores = Proveedor::permitidos()->where(
			function($query) use ($filtro){
				$query->where("nit","like","%".$filtro."%")->
						orWhere("nombre","like","%".$filtro."%");
			})->get();
		if($proveedores)
			return ["success"=>true,"proveedores"=>$proveedores];
		return ["success"=>false];
	}

    public function getListProveedores(Request $request)
    {

        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'proveedores.id';//$columna[$sortColumnIndex]['data'];

        if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
        else $sortColumnDir = "ASC";

        $proveedores = Proveedor::permitidos();

        $proveedores = $proveedores->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $proveedores->count();
        if ($search['value'] != null) {
            $proveedores = $proveedores->whereRaw(
                " ( nit LIKE '%" . $search["value"] . "%' OR " .
                " nombre LIKE '%" . $search["value"] . "%' OR" .
                " correo LIKE '%" . $search["value"] ."%' )");
        }

        $proveedores = $proveedores->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $proveedores->count();
        //BUSCAR
        $parcialRegistros = $proveedores->count();
        $proveedores = $proveedores->skip($start)->take($length);

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $proveedores->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $proveedores->get(),
            'info' =>$proveedores->get()];

        return response()->json($data);

    }

    public function getListProductosProveedorActual(Request $request){
        $proveedor_ID = $request->get('proveedor_ID');
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'productos.id';//$columna[$sortColumnIndex]['data'];

        if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
        else $sortColumnDir = "ASC";
        $proveedor = Proveedor::permitidos()->where("id",$request->get("proveedor"))->first();

        if(Auth::user()->bodegas == 'si') {
            $productos = $proveedor->productos()->select("productos.id as id_producto", "productos.*", "historial_costos.*", "productos.stock as stock")
                ->join("vendiendo.categorias", "categorias.id", "=", "productos.categoria_id")
                ->join("vendiendo.unidades", "unidades.id", "=", "productos.unidad_id")
                ->where("productos.tipo_producto","Terminado")
                ->whereRaw("historial_costos.id in (select max(historial_costos.id) as ph_id from historial_costos where proveedor_id='" . $proveedor->id . "' group by historial_costos.producto_id )");
        }else{
            $productos = $proveedor->productos()->select("productos.id as id_producto", "productos.*", "productos_historial.*", "productos.stock as stock")
                ->join("vendiendo.categorias", "categorias.id", "=", "productos.categoria_id")
                ->join("vendiendo.unidades", "unidades.id", "=", "productos.unidad_id")
                ->where("productos.tipo_producto","Terminado")
                ->whereRaw("productos_historial.id in (select max(productos_historial.id) as ph_id from productos_historial where proveedor_id='" . $proveedor->id . "' group by productos_historial.producto_id )");
        }
        $productos = $productos->orderBy($orderBy, $sortColumnDir);

        $totalRegistros = $productos->get()->count();
        if ($search['value'] != null) {
            $productos = $productos->whereRaw(
                " ( productos.nombre LIKE '%" . $search["value"] . "%' OR " .
                " productos_historial.precio_costo_nuevo LIKE '%" . $search["value"] . "%' OR" .
                " productos_historial.iva_nuevo LIKE '%" . $search["value"] . "%' OR" .
                " stock LIKE '%" . $search["value"] . "%' OR" .
                " umbral LIKE '%" . $search["value"] . "%' OR" .
                " unidades.sigla LIKE '%" . $search["value"] . "%' OR" .
                " categorias.nombre LIKE '%" . $search["value"] ."%' )");
        }
        $productos = $productos->orderBy($orderBy, $sortColumnDir);
        //BUSCAR
        $parcialRegistros = $productos->get()->count();
        $productos = $productos->skip($start)->take($length);
        if($parcialRegistros > 0){
            foreach ($productos->get() as $value) {
                $historial = $value->ultimoHistorialProveedorId($proveedor->id);
                $ArrayProductos[]=(object) array(
                    'id'=>$value->id_producto,
                    'nombre'=>$value->nombre,
                    'precio_costo_nuevo'=>"$". number_format($value->precio_costo_nuevo, 0, "," , "." ),
                    'iva_nuevo'=>$value->iva_nuevo."%",
                    'stock'=> $value->stock,
                    'umbral'=>$value->umbral,
                    'sigla'=>$value->unidad->sigla,
                    'nombre_categoria'=> $value->categoria->nombre);
            }
        }else{
            $ArrayProductos=[];
        }
        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $productos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $ArrayProductos,
            '$proveedor_ID' => $proveedor_ID,
            'info' =>$productos->get()];

        return response()->json($data);

    }

    public function getListProductosOtrosProveedores(Request $request){
        $proveedor_ID = $request->get('proveedor_ID');
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'productos.id';//$columna[$sortColumnIndex]['data'];

        if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
        else $sortColumnDir = "ASC";
        $proveedor = Proveedor::permitidos()->where("id",$request->get("proveedor"))->first();
        $productos = $proveedor->productosOtrosProveedores()->select("productos.*")
            ->join("vendiendo.categorias", "categorias.id", "=", "productos.categoria_id")
            ->join("vendiendo.unidades", "unidades.id", "=", "productos.unidad_id")
            ->leftJoin("vendiendo.proveedores", "proveedores.id", "=", "productos.proveedor_actual")
            ->where("productos.tipo_producto","Terminado")
            ->groupBy("productos.id");

        $productos = $productos->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $productos->get()->count();
        //BUSCAR
        if ($search['value'] != null) {
            $productos = $productos->whereRaw(
                " ( productos.nombre LIKE '%" . $search["value"] . "%' OR " .
                " precio_costo LIKE '%" . $search["value"] . "%' OR" .
                " iva LIKE '%" . $search["value"] . "%' OR" .
                " stock LIKE '%" . $search["value"] . "%' OR" .
                " unidades.sigla LIKE '%" . $search["value"] . "%' OR" .
                " categorias.nombre LIKE '%" . $search["value"] . "%' OR" .
                " proveedores.nombre LIKE '%" . $search["value"] . "%' OR" .
                " umbral LIKE '%" . $search["value"] ."%' )");
        }
        $parcialRegistros = $productos->get()->count();
        $productos = $productos->skip($start)->take($length);
        if($parcialRegistros > 0){
            foreach ($productos->get() as $value) {
                $historial = $value->ultimoHistorialProveedorId($proveedor->id);
                $ArrayProductos[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'precio_costo'=>"$". number_format($value->precio_costo, 0, "," , "." ),
                    'iva'=>$value->iva."%",
                    'stock'=> $value->stock,
                    'umbral'=>$value->umbral,
                    'sigla'=>$value->unidad->sigla,
                    'tipo_producto'=>$value->tipo_producto,
                    'nom_proveedor'=> $value->tipo_producto == "Terminado" ? $value->proveedorActual->nombre : "",
                    'nombre_categoria'=> $value->categoria->nombre);
            }
        }else{
            $ArrayProductos=[];
        }
        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $productos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $ArrayProductos,
            'info' =>$productos->get()];

        return response()->json($data);
    }

    public function getListMpOtrosProveedores(Request $request)
    {
        $tipos_de_Mp = $request->get('tipos_de_Mp');
        $proveedor_ID = $request->get('proveedor_ID');
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'materias_primas.id';//$columna[$sortColumnIndex]['data'];

        if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
        else $sortColumnDir = "ASC";

        $otros_proveedores = Proveedor::permitidos()->where('id', '<>', $proveedor_ID)->get();
        $materiasP = MateriaPrimaHistorial::where('proveedor_id', $proveedor_ID)->groupBy("materia_prima_id")->get();

        $ids = [];
        foreach($otros_proveedores as $op){
            $ids[] = $op->id;
        }

        $idsMp = [];
        foreach($materiasP as $m){
            $idsMp[] = $m->materia_prima_id;
        }


        if($tipos_de_Mp == "todos"){
            $materiasPrimas = MateriaPrima::select("materias_primas.id","materias_primas.nombre"
                ,"materias_primas.codigo","materias_primas.descripcion","unidades.sigla as sigla"
                ,"unidades.nombre as unidad","materias_primas.stock","materias_primas.umbral"
                ,"materias_primas_historial.precio_costo_nuevo as valor_proveedor")
                ->join("unidades","materias_primas.unidad_id","=","unidades.id")
                ->join("materias_primas_historial", "materias_primas.id", "=", "materias_primas_historial.materia_prima_id")
                ->whereNotIn("materias_primas_historial.materia_prima_id", $idsMp)
                ->whereIn("materias_primas_historial.proveedor_id", $ids)
                ->whereRaw("materias_primas_historial.id in (select max(materias_primas_historial.id) as mp_id from materias_primas_historial group by materias_primas_historial.proveedor_id,materias_primas_historial.materia_prima_id )")
                ->groupBy("materias_primas.id")
            ;
        }else if("proveedor_actual"){
            $materiasPrimas = MateriaPrima::select("materias_primas.id","materias_primas.nombre"
                ,"materias_primas.codigo","materias_primas.descripcion","unidades.sigla as sigla"
                ,"unidades.nombre as unidad","materias_primas.stock","materias_primas.umbral"
                ,"materias_primas_historial.precio_costo_nuevo as valor_proveedor")
                ->join("unidades","materias_primas.unidad_id","=","unidades.id")
                ->join("materias_primas_historial", "materias_primas.id", "=", "materias_primas_historial.materia_prima_id")
                ->where("materias_primas_historial.proveedor_id", $proveedor_ID)
                ->whereRaw("materias_primas_historial.id in (select max(materias_primas_historial.id) as mp_id from materias_primas_historial group by materias_primas_historial.proveedor_id,materias_primas_historial.materia_prima_id )");

        }



        $materiasPrimas = $materiasPrimas->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $materiasPrimas->count();
        if ($search['value'] != null) {
            $materiasPrimas = $materiasPrimas->whereRaw(
                " ( materias_primas.nombre LIKE '%" . $search["value"] . "%' OR " .
                " materias_primas_historial.precio_costo_nuevo LIKE '%" . $search["value"] . "%' OR" .
                " stock LIKE '%" . $search["value"] . "%' OR" .
                " umbral LIKE '%" . $search["value"] . "%' OR" .
                " sigla LIKE '%" . $search["value"] ."%' )");
        }

        $totalRegistrosF = $materiasPrimas->count();
        //BUSCAR
        $materiasPrimas = $materiasPrimas->skip($start)->take($length);

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $materiasPrimas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$totalRegistrosF,
            'data' => $materiasPrimas->get(),
            '$proveedor_ID' => $proveedor_ID,
            '$ids' => $ids,
            'info' =>$materiasPrimas->get()];

        return response()->json($data);

    }

    public function postListaElementosCompra(Request $request){
		if($request->has("tipo_elemento")){
			if($request->has("proveedor")){
				$filtro = "";
				if($request->has("filtro"))$filtro = $request->input("filtro");
				$proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();
				if($proveedor) {
					if ($request->input("tipo_elemento") == "producto") {
						if($request->has("tipo"))$tipo = $request->input("tipo");
						else $tipo = "productos";

						return view("compras.lista_productos")->with("tipo",$tipo)->with("proveedor",$proveedor);
					} else if ($request->input("tipo_elemento") == "materia prima") {
						return view("compras.lista_materias_primas");
					}else if($request->input("tipo_elemento") == "materia_prima_otros_proveedores"){
                        return ["data" => "otros proveedores mp"];
                    }
				}else{
					return response("La información enviada es incorrecta.",422);
				}
			}else{
				return response("Seleccione un proveedor.",422);
			}
		}else{
			return response("Seleccione el tipo de elemento a agregar",422);
		}
	}

	public function postDevolucion(DevolucionRequest $request,$id_compra){

        if(Auth::user()->bodegas == 'si')
		    $compra = ABCompra::find($id_compra);
        else
            $compra = Compra::find($id_compra);

        if ($compra->estado == "Recibida"){
			if($request->get("cantidad")!="" && $request->get('motivo')!=""){
				DB::beginTransaction();
				//Se resta al valor de la compra el valor de la devolucion
				$valor_devolucion = $request->get('cantidad') * $request->get('valor_proveedor');
				if($compra->updateCompra($id_compra,$valor_devolucion)){
					//Actualizar cantidad tablas
					if ($request->get('tipo_compra')=='MateriaPrima'){
						$historial = MateriaPrimaHistorial::find($request->get("producto_id"));
						if(Auth::user()->bodegas == 'si')
	    					$cantidad = ABCompra::calculateQuantity($request->get('tipo_compra'),$id_compra,$historial->materia_prima_id,$request->get('cantidad'),$request->get('producto_historial_id'));
						else
    						$cantidad = Compra::calculateQuantity($request->get('tipo_compra'),$id_compra,$historial->materia_prima_id,$request->get('cantidad'),$request->get('producto_historial_id'));

						if ($compra->materias_primas_historial()->updateExistingPivot($request->get('producto_id'), ['cantidad'=>$cantidad])){

							if($compra->updateStock($historial->materia_prima_id,$request->get('cantidad'),$request->get('tipo_compra'))){
								//almaceno historial devolucion
								Compra::saveDevolutionHistory($id_compra,$historial->materia_prima_id,$request->get('cantidad'),$request->get('tipo_compra'),$request->get('motivo'),$request->get('proveedor_id'));

								//Se elimina el elemento de la tabla pivot cuando es cero
								if ($cantidad == 0) {
								    if(Auth::user()->bodegas == 'si')
                                        ABCompra::deleteElemento($request->get('tipo_compra'), $id_compra, $request->get('producto_id'));
                                    else
								        Compra::deleteElemento($request->get('tipo_compra'), $id_compra, $request->get('producto_id'));
                                }


								//Se genera una cuenta por cobrar al proveedor
								$valor_devolucion = $request->get('cantidad') * $request->get('valor_proveedor');
								$estado='POR PAGAR'; //falta por definir
								$cuentas_por_cobrar = CuentaPorCobrar::GenerateReceivable($id_compra,$historial->materia_prima_id,$request->get('producto_historial_id'),$request->get('cantidad'),
									$valor_devolucion,$request->get('motivo'),$request->get('tipo_compra'),$estado,$request->get('proveedor_id'));

								DB::commit();
								//retorna listado detalle actualizado
                                if(Auth::user()->bodegas == 'si')
	    							$compra = ABCompra::permitidos()->where("id", $id_compra)->first();
								else
                                    $compra = Compra::permitidos()->where("id", $id_compra)->first();
								if ($compra->valor >= 0.1) {
									$user = Auth::user()->userAdminId();
									$lista_devoluciones = CuentaPorCobrar::where('usuario_id',$user)
										->where('compra_id',$id_compra)
										//->where('estado','POR PAGAR')
										->orderby('estado','desc')
										->get();
									return view('compras.detalles.index')->with("compra", $compra)->with("lista_devoluciones", $lista_devoluciones);
								}else{
									//ELIMINAR LAS RELACIONES DE LA COMPRA CON LA CUENTAS POR COBRAR A PROVEEDORES
									CuentaPorCobrar::deleteCuentasXCobrar($id_compra);
									$compra->delete();
									Session::flash("mensaje","La compra fue eliminada debido a la ausencia de elementos");								
									return "Compra-eliminada";
								}
							}else{
								return response(["error"=>"1 Oopps! Ocurrio un error con la información"],401);
							}
						}else{
							return response(["error"=>"2 Oopps! Ocurrio un error con la información"],401);
						}
					}else if ($request->get('tipo_compra')=='Producto'){
						//reduce la cantidad a devolver de la compra
                        if(Auth::user()->bodegas == 'si')
						    $cantidad = AbCompra::calculateQuantity($request->get('tipo_compra'),$id_compra,$request->get('producto_id'),$request->get('cantidad'),$request->get('producto_historial_id'));
						else
                            $cantidad = Compra::calculateQuantity($request->get('tipo_compra'),$id_compra,$request->get('producto_id'),$request->get('cantidad'),$request->get('producto_historial_id'));

						$update_existing_pivot = false;

						if(Auth::user()->bodegas == 'si') {
                            if ($compra->historialCostos()->updateExistingPivot($request->get('producto_historial_id'), ['cantidad' => $cantidad]))
                                $update_existing_pivot = true;
                        }else{
                            if ($compra->productosHistorial()->updateExistingPivot($request->get('producto_historial_id'), ['cantidad' => $cantidad]))
                                $update_existing_pivot = true;
                        }

						if ($update_existing_pivot){
							if(Auth::user()->bodegas == 'si')
						        $pr_historial = ABHistorialCosto::find($request->input('producto_historial_id'));
						    else
							    $pr_historial = ProductoHistorial::find($request->input('producto_historial_id'));

							if(!$pr_historial)return response(["error"=>"La información enviada es incorrecta"],422);
							$data_precios = [[$pr_historial->precio_costo_nuevo,-1*$request->get('cantidad')]];
							if(Auth::user()->bodegas == 'si')
							    $pr = ABProducto::permitidos()->where("id",$request->get('producto_id'))->first();
							else
							    $pr = Producto::permitidos()->where("id",$request->get('producto_id'))->first();

							$pr->updatePromedioPonderado($data_precios);
							if($compra->updateStock($request->get('producto_id'),$request->get('cantidad'),$request->get('tipo_compra'))){
								//almaceno historial devolucion
								if(Auth::user()->bodegas == 'si')
                                    ABCompra::saveDevolutionHistory($id_compra,$request->get('producto_id'),$request->get('cantidad'),$request->get('tipo_compra'),$request->get('motivo'),$request->get('proveedor_id'));
								else
								    Compra::saveDevolutionHistory($id_compra,$request->get('producto_id'),$request->get('cantidad'),$request->get('tipo_compra'),$request->get('motivo'),$request->get('proveedor_id'));

								//Se elimina el elemento de la tabla pivot cuando es cero
								//echo "cantidad ".$cantidad;
								if ($cantidad == 0) {
								    if(Auth::user()->bodegas == 'si')
                                        ABCompra::deleteElemento($request->get('tipo_compra'), $id_compra, $request->get('producto_historial_id'));
                                    else
								        Compra::deleteElemento($request->get('tipo_compra'), $id_compra, $request->get('producto_historial_id'));
                                }

								//Se genera una cuenta por cobrar al proveedor
								$valor_devolucion = $request->get('cantidad') * $request->get('valor_proveedor');
								$estado='POR PAGAR'; //falta por definir
								$cuentas_por_cobrar = CuentaPorCobrar::GenerateReceivable($id_compra,$request->get('producto_id'),$request->get('producto_historial_id'),$request->get('cantidad'),
									$valor_devolucion,$request->get('motivo'),$request->get('tipo_compra'),$estado,$request->get('proveedor_id'));
								DB::commit();

								//retorna listado detalle actualizado
                                if(Auth::user()->bodegas == 'si')
								    $compra = ABCompra::permitidos()->where("id", $id_compra)->first();
								else
                                    $compra = Compra::permitidos()->where("id", $id_compra)->first();

								//if ($compra->valor >= 0.1) {
									$user = Auth::user()->userAdminId();
									$lista_devoluciones = CuentaPorCobrar::where('usuario_id',$user)
										->where('compra_id',$id_compra)
										//->where('estado','POR PAGAR')
										->orderby('estado','desc')
										->get();
									return view('compras.detalles.index')->with("compra", $compra)->with("lista_devoluciones", $lista_devoluciones);
								/*}else{
									//CuentaPorCobrar::deleteCuentasXCobrar($id_compra);
									//$compra->delete();
									Session::flash("mensaje","La compra fue eliminada debido a la ausencia de elementos");
									return "Compra-eliminada";
								}*/
							}else{
								return response(["error"=>"3 Oopps! Ocurrio un error con la información"],401);
							}
						}else{
							return response(["error"=>"4 Oopps! Ocurrio un error con la información"],401);
						}
					}
				}else{
					return response(["error"=>"5 Oopps! Ocurrio un error con la información"],401);
				}
				//Session::flash("mensaje","La devolucion se ejecutó manera exitosa");

			}else{
				return response(['mensaje'=>['Existen campos vacios']], 422);
			}
		}else{

			return response(["error"=>"El estado de la compra no permite hacer esta acción"],401);
		}

	}

	public function getEdit($id_compra){
		if(Auth::user()->permitirFuncion("Editar","compras","inicio") && Caja::abierta()){
			$compra = Compra::permitidos()->where('id',$id_compra)->first();
			if ($compra) {
				$suma_abonos = $compra->abonos()->sum('valor');
				$saldo = $compra->valor - $suma_abonos;
			}
			return view("compras.edit")->with("compra",$compra)->with('id',$id_compra)->with('saldo',$saldo);
		}
		return response("Unauthorized",401);
	}

	public function postUpdate(Request $request){

		if(Auth::user()->permitirFuncion("Editar","compras","inicio") && Caja::abierta()){

			DB::beginTransaction();
			//verificamos estado actual de la compra
			$continuar = false;
			$compra = Compra::permitidos()->where('id',$request->get('id_compra'))->first();
			if ($compra->estado == 'Pendiente por recibir' || $compra->estado_pago == 'Pendiente por pagar'){
				if ($request->get('estado') == 'Recibida'){
					$compra_update = $compra->update(["estado" => $request->get('estado')]);
					//actualiza el inventario
					if ($compra_update){
						$continuar = true;
						$compra_update = Compra::updateStockReceived($request->get('id_compra'));
					}
				}
				if ($request->get('estado_pago') == 'Pagada'){
					$compra_update = $compra->update(["estado_pago" => $request->get('estado_pago')]);
					//actualiza la caja
					if ($compra_update){
						$compra_update = Compra::payPurchase($request->get('valor_pagar'));
						if ($compra_update){
							Compra::payCompraAbonos($request->get('id_compra'));
						}
						$continuar = true;
					}
				}
			}
			DB::commit();
			if ($continuar){
				if ($request->get('estado') && $request->get('estado_pago')){
					if(Compra::saveStatesHistory($request->get("id_compra"),$request->get('estado'),$request->get('estado_pago'))){
						$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
						$efectico_caja = Caja::cajasPermitidas()->where('fecha',$fecha_actual)->take(1)->first();
						$compras = Compra::permitidos()->orderBy("updated_at", "DESC")->orderBy("id", "DESC")->paginate(10);
						return view('compras.lista')->with("compras", $compras)->with("efectivo_caja",$efectico_caja);
					}else{
						return response()->json(['response' => 'Oopss! Ocurrio un error']);
					}
				}
				return response()->json(['response' => 'Oopss! Ocurrio un error']);
			}else{
				return response()->json(['response' => 'Oopss! Ocurrio un error']);
			}

		}
		return response("Unauthorized",401);
	}

	public function getPrueba(){
		$compra = Compra::find(15);
		$producto = Producto::find(17);

		$var = DB::table('compras_materias_primas')->select('cantidad','id')
			->where('compra_id',17)
			->where('materia_prima_id',33)->first();

		dd($var->id);

		//dd($compra->productos()->updateExistingPivot(17, ['cantidad'=>50]));

	}

	public function getCobrar($id_cuenta,$estado,$forma_pago){

		if($estado != 'PAGADA'){
			//cambia estado de la CC y actualiza la caja
			//DB::beginTransaction();
			$estado = 'PAGADA';
			$cuentaXcobrar = CuentaPorCobrar::CollectReceivable($id_cuenta,$estado,$forma_pago);

			return response()->json([
				'response' => 'La operación se realizo satisfactoriamente'

			]);
		}else{
			return response()->json(['response' => 'Oops!, Esta cuanta por cobrar ya fue cobrada']);
		}
		//return response("Unauthorized",401);
	}
	public function getAbono($id)
	{
		$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
		$efectico_caja = Caja::abierta();

		if(Auth::user()->bodegas == 'si')
		    $compra = ABCompra::permitidos()->where("id", $id)->first();
		else
		    $compra = Compra::permitidos()->where("id", $id)->first();

		if ($compra /*&& Caja::abierta()*/) {
			$suma_abonos = $compra->abonos()->sum('valor');
			$saldo = $compra->valor - $suma_abonos;
			//dd($compra->valor);
			$abonos = $compra->abonos()->orderby('id','DESC')->orderby('fecha','DESC')->get();
			return view('compras.abonos.lista')->with("compra", $compra)->with('abonos',$abonos)->with('saldo',$saldo)->with('efectivo_caja',$efectico_caja);
		}
		return redirect("/compra");
	}
	public function postAbonar(Request $request){

		$caja = Caja::abierta();
		if($caja) {
			DB::beginTransaction();

			if(Auth::user()->bodegas == 'si')
			    $abono = new ABAbono();
			else
			    $abono = new Abono();

			$abono->valor = $request->get('valor');
			$abono->nota = $request->get('nota');
			$abono->fecha = $request->get('fecha');
			$abono->usuario_id = Auth::user()->id;
			$abono->caja_maestra_id = $caja->id;
			$abono->tipo_abono = 'compra';
			$abono->tipo_abono_id = $request->get('tipo_abono_id');
			$abono->save();


			if ($abono) {
				//Actualizo la caja con el valor del abono
				if(Auth::user()->bodegas == 'si')
                    $pago_caja = ABCompra::payBox($request->get('valor'));
                else
				    $pago_caja = Compra::payBox($request->get('valor'));

				if ($pago_caja) {
                    if(Auth::user()->bodegas == 'si')
					    $compra = ABCompra::permitidos()->where("id", $request->get('tipo_abono_id'))->first();
					else
                        $compra = Compra::permitidos()->where("id", $request->get('tipo_abono_id'))->first();

					if ($compra) {
						$suma_abonos = $compra->abonos()->sum('valor');
						$saldo = $compra->valor - $suma_abonos;
						if ($saldo == 0) {
							//actualizamos estado de pago de la compra
                            if(Auth::user()->bodegas == 'si')
							    ABCompra::updatePaymentStatus($request->get('tipo_abono_id'));
							else
                                Compra::updatePaymentStatus($request->get('tipo_abono_id'));
						}
						DB::commit();
						$efectico_caja = Caja::abierta();
						$abonos = $compra->abonos()->orderby('id', 'DESC')->orderby('fecha', 'DESC')->get();
						return view('compras.abonos.lista')->with("compra", $compra)->with('abonos', $abonos)->with('saldo', $saldo)->with('efectivo_caja', $efectico_caja);
					} else {
						abort('503');
					}
				} else {
					abort('503');
				}

			} else {
				abort('503');
			}
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);

	}
	public function getDetalleDevolucion($id_compra){
		$user = Auth::user()->userAdminId();
		$lista_devoluciones = CuentaPorCobrar::where('usuario_id',$user)
			->where('compra_id',$id_compra)
			//->where('estado','POR PAGAR')
			->orderby('estado','desc')
			->get();
		if (!empty($lista_devoluciones)){
			return view('compras.devoluciones.index',compact('lista_devoluciones'));
		}
		return redirect("/");
	}

}
