<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\MateriaPrima;
use App\Models\Producto;
use App\Models\RemisionSalida;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Log;


class RemisionSalidaController extends Controller
{
	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modRemisionS");
		//$this->middleware("modCaja");
		$this->middleware("terminosCondiciones");
	}

	public function getIndex(Request $request)
	{
		return view('remisiones_salida.index')->with("display_factura_abierta",false);
	}

    public function getListRemisionesSalida(Request $request)
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


		if($orderBy == "numero")$orderBy = "remisiones_salida.id";

		if(strtoupper($sortColumnDir) == "ASC")$sortColumnDir = "DESC";
		else $sortColumnDir = "ASC";

		if($orderBy == "created_at")$orderBy = "remisiones_salida.created_at";
		if($orderBy == "usuario_creador")$orderBy = "vendiendo.usuarios.nombres";

        $remisiones_salida = RemisionSalida::permitidos()->select("remisiones_salida.*");

        $remisiones_salida = $remisiones_salida->join("vendiendo.usuarios","remisiones_salida.usuario_creador_id","=","vendiendo.usuarios.id");

        $remisiones_salida = $remisiones_salida->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $remisiones_salida->count();
        //BUSCAR
        if ($search['value'] != null) {
            $remisiones_salida = $remisiones_salida->whereRaw(
                " ( remisiones_salida.id LIKE '%" . $search["value"] . "%' OR " .
                " remisiones_salida.numero LIKE '%" . $search["value"] . "%' OR" .
                " remisiones_salida.created_at LIKE '%" . $search["value"] . "%' OR" .
                " LOWER(CONCAT(vendiendo.usuarios.nombres, ' ', vendiendo.usuarios.apellidos)) LIKE '%" . \strtolower($search["value"]) . "%')");
        }

        $parcialRegistros = $remisiones_salida->count();
        $remisiones_salida = $remisiones_salida->skip($start)->take($length);

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($remisiones_salida->get() as $value) {
                $myArray[]=(object) array('id' => $value->id,
                    'numero'=>$value->numero,
                    'created_at'=>$value->created_at->format('Y-m-d H:i:s'),
                    'usuario_creador'=> $value->usuarioCreador->nombres." ".$value->usuarioCreador->apellidos);
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            'last_query' => $remisiones_salida->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$remisiones_salida->get()];

        return response()->json($data);

    }

	public function postStore(Request $request){
		if(Auth::user()->permitirFuncion("Crear","Remision salida","inicio")) {
            if(Auth::user()->bodegas == 'si')
                return response(['error'=>['Unauthorized.']],401);

                    $ultimaRemision = RemisionSalida::ultimaRemisionSalida();
                    $numero = 1;
                    $productos = false;
                    $materias_primas = false;
                    if ($ultimaRemision) {
                        $numero = intval($ultimaRemision->numero) + 1;
                    }

                    $aux = 1;
                    $continuar = true;
                    DB::beginTransaction();
                    $remision = new RemisionSalida();
                    $remision->numero = "00" . $numero;
                    $remision->usuario_creador_id = Auth::user()->id;
                    if (Auth::user()->perfil->nombre == "administrador")
                        $remision->usuario_id = Auth::user()->id;
                    else
                        $remision->usuario_id = Auth::user()->usuario_creador_id;
                    $remision->save();

                    while ($continuar) {
                        if ($request->has("producto_" . $aux)) {
                            $pr = $request->input("producto_" . $aux);
                            if ($pr["cantidad"] >= 1) {
                                $producto = Producto::permitidos()->where("productos.id",$pr["id"])->first();
                                if ($producto) {
                                    if($producto->stock < $pr["cantidad"]){
                                        return response(['error'=>['La cantidad máxima para el producto '.$producto->nombre.' es '.$producto->stock]],422);
                                    }
                                    $remision->productos()->save($producto,["cantidad"=>$pr["cantidad"]]);
                                    $producto->stock -= $pr["cantidad"];
                                    $producto->save();
                                    $productos = true;
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
                                $materia_prima = MateriaPrima::materiasPrimasPermitidas()->where("materias_primas.id", $mp["id"])->first();
                                if ($materia_prima) {
                                    if($materia_prima->stock < $mp["cantidad"]){
                                        return response(['error'=>['La cantidad máxima para la materia prima '.$materia_prima->nombre.' es '.$materia_prima->stock]],422);
                                    }
                                    $remision->materiasPrimas()->save($materia_prima, ["cantidad" => $mp["cantidad"]]);
                                    $materia_prima->stock -= $mp["cantidad"];
                                    $materia_prima->save();
                                    $materias_primas = true;
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
                        DB::commit();
                        Session::flash("mensaje", "La remisión de salida ha sido registrada con éxito");
                        return ["success" => true];

                    }else{
                        return response(["Error" => ["Agregue por lo menos un elemento al detalle de la compra."]], 422);
                    }

				return response(["Error" => ["La información enviada es incorrecta."]], 422);
		}
		return response(["Error"=>["Usted no tiene permisos para realizar esta tarea."]],401);
	}

	public function getCreate()
	{
		$fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
		if (Auth::user()->permitirFuncion("Crear", "Remision salida", "inicio"))
            if(Auth::user()->bodegas == 'no')
                return view('remisiones_salida.create')->with("display_factura_abierta", false);

		return redirect("/");
	}

	public function getDetalle($id)
	{
	    $remision = RemisionSalida::permitidos()->where("id", $id)->first();
		if ($remision) {
			$user = Auth::user()->userAdminId();
			return view('remisiones_salida.detalle')->with("remision", $remision);
		}
		return redirect("/remision-salida");
	}

    public function getListProductos(Request $request){
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

        $productos = Producto::permitidos()->select("productos.id as id_producto", "productos.*", "productos_historial.*", "productos.stock as stock")
            ->join("vendiendo.productos_historial", "productos.id", "=", "productos_historial.producto_id")
            ->join("vendiendo.categorias", "categorias.id", "=", "productos.categoria_id")
            ->join("vendiendo.unidades", "unidades.id", "=", "productos.unidad_id")
            ->where("productos.tipo_producto","Terminado")
            ->where("productos.stock",">","0")
            ->whereRaw("productos_historial.id in (select max(productos_historial.id) as ph_id from productos_historial group by productos_historial.producto_id )");

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
                //$historial = $value->ultimoHistorialProveedorId($proveedor->id);
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

    public function getListMateriasPrimas(Request $request)
    {
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

        $materiasPrimas = MateriaPrima::materiasPrimasPermitidas()->select("materias_primas.id","materias_primas.nombre"
                ,"materias_primas.codigo","materias_primas.descripcion","unidades.sigla as sigla"
                ,"unidades.nombre as unidad","materias_primas.stock","materias_primas.umbral"
                ,"materias_primas_historial.precio_costo_nuevo as valor_proveedor")
                ->join("unidades","materias_primas.unidad_id","=","unidades.id")
                ->join("materias_primas_historial", "materias_primas.id", "=", "materias_primas_historial.materia_prima_id")
                //->whereNotIn("materias_primas_historial.materia_prima_id", $idsMp)
                //->whereIn("materias_primas_historial.proveedor_id", $ids)
                ->where("materias_primas.stock",">","0")
                ->whereRaw("materias_primas_historial.id in (select max(materias_primas_historial.id) as mp_id from materias_primas_historial group by materias_primas_historial.proveedor_id,materias_primas_historial.materia_prima_id )")
                ->groupBy("materias_primas.id");



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
            //'$proveedor_ID' => $proveedor_ID,
            //'$ids' => $ids,
            'info' =>$materiasPrimas->get()];

        return response()->json($data);

    }

    public function postListaElementosRemisionSalida(Request $request){
		if($request->has("tipo_elemento")){
            $filtro = "";
            if($request->has("filtro"))$filtro = $request->input("filtro");
                if ($request->input("tipo_elemento") == "producto") {
                    if($request->has("tipo"))$tipo = $request->input("tipo");
                    else $tipo = "productos";
                    return view("remisiones_salida.lista_productos")->with("tipo",$tipo);
                } else if ($request->input("tipo_elemento") == "materia prima") {
                    return view("remisiones_salida.lista_materias_primas");
                }else if($request->input("tipo_elemento") == "materia_prima_otros_proveedores"){
                    return ["data" => "otros proveedores mp"];
                }
		}else{
			return response("Seleccione el tipo de elemento a agregar",422);
		}
	}
}
