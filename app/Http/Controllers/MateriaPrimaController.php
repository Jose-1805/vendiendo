<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ImportacionMateriaPrima;
use App\Models\MateriaPrima;
use App\Models\MateriaPrimaHistorial;
use App\Models\Proveedor;
use App\Models\ProveedorMateriaPrima;
use App\Models\Unidad;
use App\User;
use Illuminate\Http\Request;
use App\Http\Requests\RequestNuevaMateriaPrima;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class MateriaPrimaController extends Controller
{

    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("modMateriaPrima");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(Request $request)
    {
        $filtro = "";
        if ($request->has("filtro")) {
            $materias_primas = $this->listaFiltro($request->get("filtro"));
            $filtro = $request->get("filtro");
        } else {
            $materias_primas = MateriaPrima::permitidos()->groupby('materias_primas.nombre')->orderBy("updated_at", "DESC")->paginate(10);
        }
        return view('materias_primas.index')->with("materias_primas", $materias_primas)->with("filtro", $filtro);
    }

    public function getCreate(Request $request)
    {
        $pr_ = "";
        $barCodeProducto_ = "";
        if ($request->has("pr_")) $pr_ = $request->input("pr_");
        if ($request->has("barCodeProducto_")) $barCodeProducto_ = $request->input("barCodeProducto_");
        if (Auth::user()->permitirFuncion("Crear", "materias primas", "inicio")) {
            $view = view('materias_primas.create')->with("materia_prima", new MateriaPrima())->with(["pr_" => $pr_, "barCodeProducto_" => $barCodeProducto_]);
            if ($request->has("noPrOpc")) {
                $view = $view->with("noPrOpc", true);
            }
            return $view;
        }
        return redirect("/");
    }

    public function postStore(RequestNuevaMateriaPrima $request)
    {
        $data = $request->all();
        if ($request->has('select-unidad'))
            $data['unidad_id'] = $request->get('select-unidad');

        if (!$request->has("proveedor_actual")) {
            return response(['proveedor' => ['Seleccione el proveedor actual del producto']], 422);
        }
        $data["proveedor_actual"] = null;
        DB::beginTransaction();
        $materia_prima = new MateriaPrima();

        $materia_prima->fill($data);
        //$materia_prima->proveedor_actual = $request->input("pr_");
        $materia_prima->usuario_id = Auth::user()->id;
        $materia_prima->imagen = "";
        $materia_prima->save();
        $aux = 1;
        $continuar = true;
        $total = 0;
        $stock = 0;
        while ($continuar) {
            if ($aux == 1) {
                if (!$request->has("select-proveedor-" . $aux)) {
                    return response(['proveedor' => ['Debe relacionar por lo menos un proveedor']], 422);
                }
            }

            if ($request->has("select-proveedor-" . $aux)) {
                $proveedor = Proveedor::find($request->get("select-proveedor-" . $aux));
                if ($proveedor && $proveedor->exists) {
                    if ($aux == $request->input("proveedor_actual")) {
                        $materia_prima->proveedor_actual = $proveedor->id;
                        $materia_prima->save();
                    }

                    if (MateriaPrimaHistorial::where("proveedor_id", $proveedor->id)->where("materia_prima_id", $materia_prima->id)->get()->count()) {
                        return response(['proveedor' => ['No es posible relacionar más de una vez un proveedor y una materia prima']], 422);
                    } else {
                        if (Proveedor::permitidos()->whereIn("id", [$proveedor->id])->get()->count()) {
                            if (!$request->has("valor-" . $aux)) {
                                return response(['valor' => ['Debe ingresar el valor del producto en relación con el proveedor ' . $aux]], 422);
                            } else {
                                $cant = 0;
                                if ($request->has("cantidad-" . $aux)) {
                                    $total += floatval($request->input("valor-" . $aux)) * floatval($request->input("cantidad-" . $aux));
                                    $stock += floatval($request->input("cantidad-" . $aux));
                                    $cant = floatval($request->input("cantidad-" . $aux));
                                }
                                $obj = new MateriaPrimaHistorial();
                                $obj->materia_prima_id = $materia_prima->id;
                                $obj->proveedor_id = $proveedor->id;
                                $obj->usuario_id = Auth::user()->id;
                                $obj->precio_costo_anterior = $request->get("valor-" . $aux);
                                $obj->precio_costo_nuevo = $request->get("valor-" . $aux);
                                $obj->stock = $cant;
                                $obj->save();
                            }
                            if ($aux == $request->input("proveedor_actual")) {
                                $materia_prima->precio_costo = $obj->precio_costo_nuevo;
                                $materia_prima->save();
                            }
                        } else {
                            return response(['Unauthorized' => ['No tiene permisos para relacionar con el proveedor seleccionado']], 422);
                        }
                    }
                } else {
                    return response(['error' => ['La información enviada es incorrecta']], 422);
                }
            } else {
                $continuar = false;
            }
            $aux++;
        }

        $materia_prima->stock = $stock;
        $materia_prima->promedio_ponderado = $total / $stock;
        $materia_prima->save();
        if ($request->hasFile("imagen")) {
            if ($request->file('imagen')->isValid()) {
                $img = $request->file("imagen");
                $nombre = $materia_prima->id . "." . $img->getClientOriginalExtension();
                $ruta = public_path("img/materias_primas");
                $img->move($ruta, $nombre);
                $materia_prima->imagen = $nombre;
                $materia_prima->save();
            } else {
                return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
            }
        }
        DB::commit();
        $data = ["success" => true];
        if ($request->has("noPrOpc")) {
            $data["seleccion"] = $materia_prima->id;
            $data["location"] = "compra";
        } else {
            Session::flash("mensaje", "La materia prima ha sido registrada con éxito");
        }
        return $data;

    }

    public function postRelacionarMateriaPrima(Request $request)
    {
        $materiaPrima_ID = $request->input('materiaPrima_ID');
        $materia = MateriaPrima::permitidos()->where("materias_primas.id", $materiaPrima_ID)->first();
        $proveedor = Proveedor::find($request->input("proveedor"));
        $valor = $request->input("valor");
        $proveedorPermitido = "";
        if (Proveedor::permitidos()->whereIn("id", [$proveedor->id])->get()->count() && $materia) {
            //$proveedorPermitido = $proveedor->id;

            $obj = new MateriaPrimaHistorial();
            $obj->proveedor_id = $proveedor->id;
            $obj->materia_prima_id = $materiaPrima_ID;
            $obj->precio_costo_nuevo = $valor;
            $obj->precio_costo_anterior = $valor;
            $obj->usuario_id = Auth::user()->id;
            $obj->save();

            return ["success" => true];
        } else {
            return response(["success" => false, "materiaPrima_ID" => $materiaPrima_ID, "proveedor" => $proveedor, "proveedorPermitido" => $proveedorPermitido, "valor" => $valor]);
        }
    }

    public function show($id)
    {
        //
    }

    public function getEdit($id)
    {
        if (Auth::user()->permitirFuncion("Editar", "materias primas", "inicio")) {

            $materia_prima = MateriaPrima::permitidos()->where("materias_primas.id", $id)->first();
            //dd($id);
            if ($materia_prima && $materia_prima->exists) {
                return view("materias_primas.edit")->with("materia_prima", $materia_prima);
            }
            return redirect("/");
        }

        return redirect("/");
    }

    public function postUpdate($id, Requests\RequestEditarMateriaPrima $request)
    {
        $data = $request->all();
        if ($request->has('select-unidad'))
            $data['unidad_id'] = $request->get('select-unidad');


        if (Auth::user()->permitirFuncion("Editar", "materias primas", "inicio")) {
            $materia_prima = MateriaPrima::permitidos()->where("materias_primas.id", $id)->first();
            if ($materia_prima && $materia_prima->exists) {

                if ($materia_prima->codigo == $request->get('codigo')) {
                    unset($data['codigo']);
                } else {
                    $materia_aux = MateriaPrima::where('usuario_id', Auth::user()->userAdminId())
                        ->where('codigo', $request->get('codigo'))->get();
                    if (count($materia_aux) >= 1) {
                        return response(['valor' => ['Este codigo ya se encuentra en uso ']], 422);
                    }
                }

                $proveedor_actual = null;
                if ($request->has('proveedor_actual')) {
                    $proveedor_actual = $data['proveedor_actual'];
                }
                unset($data['proveedor_actual']);

                DB::beginTransaction();
                $materia_prima->fill($data);
                $materia_prima->save();

                if (!$request->has("proveedor_actual")) {
                    return response(['proveedor' => ['Seleccione el proveedor actual del producto']], 422);
                }

                $aux = 1;
                $continuar = true;
                $proveedores_id = [];
                $total = 0;
                $cantidad_stock = 0;
                while ($continuar) {
                    if ($aux == 1) {
                        if (!$request->has("select-proveedor-" . $aux)) {
                            return response(['proveedor' => ['Debe relacionar por lo menos un proveedor']], 422);
                        }
                    }

                    if ($request->has("select-proveedor-" . $aux)) {
                        $proveedor = Proveedor::permitidos()->where("proveedores.id", $request->get("select-proveedor-" . $aux))->first();
                        if ($proveedor && $proveedor->exists) {
                            if ($aux == $request->input("proveedor_actual")) {
                                $materia_prima->proveedor_actual = $proveedor->id;
                                $materia_prima->save();
                            }

                            if (in_array($proveedor->id, $proveedores_id)) {
                                return response(['proveedor' => ['No es posible relacionar más de una vez un proveedor y una materia prima']], 422);
                            } else {
                                $proveedores_id[] = $proveedor->id;
                                if (!$request->has("valor-" . $aux)) {
                                    return response(['valor' => ['Debe ingresar el valor del producto en relación con el proveedor ' . $aux]], 422);
                                } else {
                                    $ultimo_historial_proveedor = $materia_prima->ultimoHistorial($proveedor->id);

                                    $obj = new MateriaPrimaHistorial();
                                    $obj->proveedor_id = $proveedor->id;
                                    $obj->materia_prima_id = $materia_prima->id;
                                    $obj->precio_costo_nuevo = $request->get("valor-" . $aux);
                                    if (!$materia_prima->aparicionesProductosCompras()) {
                                        $obj->stock = $request->get("cantidad-" . $aux);
                                        $total += $request->get("valor-" . $aux) * $request->get("cantidad-" . $aux);
                                        $cantidad_stock += $request->get("cantidad-" . $aux);
                                    }

                                    if ($ultimo_historial_proveedor)
                                        $obj->precio_costo_anterior = $ultimo_historial_proveedor->precio_costo_nuevo;
                                    else
                                        $obj->precio_costo_anterior = $request->get("valor-" . $aux);
                                    $obj->usuario_id = Auth::user()->id;
                                    $obj->save();

                                    if ($aux == $request->input("proveedor_actual")) {
                                        $materia_prima->precio_costo = $request->get("valor-" . $aux);
                                        $materia_prima->save();
                                    }
                                }
                            }
                        } else {
                            return response(['error' => ['La información enviada es incorrecta']], 422);
                        }
                    } else {
                        $continuar = false;
                    }
                    $aux++;
                }

                if (!$materia_prima->aparicionesProductosCompras()) {
                    $materia_prima->stock = $cantidad_stock;
                    $materia_prima->promedio_ponderado = $total / $cantidad_stock;
                    $materia_prima->save();
                }


                if ($request->hasFile("imagen")) {
                    if ($request->file('imagen')->isValid()) {
                        $img = $request->file("imagen");
                        $nombre = $materia_prima->id . "." . $img->getClientOriginalExtension();
                        $ruta = public_path("img/materias_primas");
                        if ($materia_prima->imagen) {
                            @unlink($ruta . '/' . $materia_prima->imagen);
                        }
                        $img->move($ruta, $nombre);

                        $materia_prima->imagen = $nombre;
                        $materia_prima->save();
                    } else {
                        return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
                    }
                }
                DB::commit();
                Session::flash("mensaje", "La materia prima ha sido editada con éxito");
                return ["success" => true, "href" => url('/materia-prima')];

                //sino
            }
        }

        return response("Unauthorized", 401);
    }

    public function postDestroy(Request $request)
    {
        if (Auth::user()->permitirFuncion("Eliminar", "materias primas", "inicio") && $request->has("materia_prima")) {
            $materia_prima = MateriaPrima::permitidos()->where("materias_primas.id", $request->input("materia_prima"))->first();
            if ($materia_prima && $materia_prima->permitirEliminar()) {
                $materia_prima->delete();
                return ["success" => true];
            }
        }
        return response(["error" => ["La información envida es incorrecta"]], 422);
    }

    public function postFiltro(Request $request)
    {
        if ($request->has("filtro")) {
            $materias_primas = $this->listaFiltro($request->get("filtro"));
        } else {
            $materias_primas = MateriaPrima::permitidos()->groupby('materias_primas.nombre')->orderBy("updated_at", "DESC")->paginate(10);
        }
        $materias_primas->setPath(url('/materia-prima'));
        return view("materias_primas.lista")->with("materias_primas", $materias_primas);
    }

    public function listaFiltro($filtro)
    {
        $f = "%" . $filtro . "%";
        return $materias_primas = MateriaPrima::permitidos()->where(
            function ($query) use ($f) {
                $query->where("materias_primas.nombre", "like", $f)
                    ->orWhere("codigo", "like", $f)
                    ->orWhere("descripcion", "like", $f);
            }
        )->groupby('materias_primas.nombre')->orderBy("updated_at", "DESC")->paginate(10);
    }

    public function getDetalle($id, Request $request)
    {

        $detalle_proveedores = MateriaPrima::permitidos()->where('materia_prima_id', $id)->orderBy('valor_mp', 'ASC')->get();
        $materia_prima = MateriaPrima::permitidos()->where("materias_primas.id", $id)->first();

        if ($detalle_proveedores && count($detalle_proveedores) > 0) {
            return view('materias_primas.lista_detalle')->with('detalle_proveedores', $detalle_proveedores)->with('materia_prima', $materia_prima);
        }
        return redirect('/');

    }

    public function postDatosMateriaPrima(Request $request)
    {
        $barCode = $request->input("barCodeProducto");
        $otros_proveedores = Proveedor::permitidos()->get();
        if ($request->ajax()) {
            if ($request->has("id") || $request->has("barCodeProducto")) {
                if ($request->has("proveedor")) {
                    $proveedor = Proveedor::permitidos()->where("id", $request->input("proveedor"))->first();
                    $materias_primas = $proveedor->materiasPrimas()->select("materias_primas.*", "materias_primas_historial.precio_costo_nuevo as valor_proveedor", "unidades.sigla as sigla")
                        ->join("unidades", "materias_primas.unidad_id", "=", "unidades.id")
                        ->where("materias_primas.id", $request->input("id"))->orderBy("materias_primas_historial.id","DESC")->first();
                    if ($materias_primas) {
                        return ["success" => true, "materia_prima" => $materias_primas];
                    } else if ($barCode != '') {
                        //return["success" => false, "mensaje" => "pruebas"];
                        $materias_primas = $proveedor->materiasPrimas()->select("materias_primas.*",
                            "materias_primas_historial.precio_costo_nuevo as valor_proveedor", "unidades.sigla as sigla")
                            ->join("unidades", "materias_primas.unidad_id", "=", "unidades.id")->where("materias_primas.codigo", $request->input("barCodeProducto"))->first();
                        if ($materias_primas) {
                            return ["success" => true, "materia_prima" => $materias_primas];
                        } else if (!$materias_primas) {
                            $otros_proveedores = Proveedor::permitidos()->get();
                            $cantidad = 0;
                            foreach ($otros_proveedores as $p) {
                                $materias_primas = $p->materiasPrimas()
                                    ->select("materias_primas.*", "materias_primas_historial.precio_costo_nuevo as valor_proveedor", "unidades.sigla as sigla")
                                    ->join("unidades", "materias_primas.unidad_id", "=", "unidades.id")
                                    ->where("materias_primas.codigo", $request->input("barCodeProducto"))->first();
                                if ($materias_primas) {
                                    $cantidad = 1;
                                    return ["success" => false, "sugerencia" => "relacionarMp", "mensaje" => "Debes relacionar esta materia prima con el proveedor actual", "materiasP" => $materias_primas];
                                }
                            }
                            if ($cantidad == 0)
                                return ["success" => false, "mensaje" => "Debes crear esta materia prima"];
                        }
                    }

                }
                $materia_prima = MateriaPrima::permitidos()->where("materias_primas.id", $request->input("id"))->first();
                if ($materia_prima) {
                    return ["success" => true, "materia_prima" => $materia_prima];
                }
                return ["success" => false, "mensaje" => "Ocurrio un error al seleccionar el producto, por favor intente nuevamente", "barcode" => $barCode];
            }
        } else {
            return redirect("/");
        }
        return response(["error" => ["La información enviada es incorrecta"]], 422);
    }

    public function postDatosMateriaPrimaRemision(Request $request)
    {
        $barCode = $request->input("barCodeProducto");
        if ($request->ajax()) {
            if ($request->has("id") || $request->has("barCodeProducto")) {

                $sub_sql = "(select MAX(materias_primas_historial.id) from materias_primas_historial where materias_primas_historial.materia_prima_id = materias_primas.id)";
                $materias_primas = MateriaPrima::select("materias_primas.*", "materias_primas_historial.precio_costo_nuevo as valor_proveedor", "unidades.sigla as sigla")
                    ->join("materias_primas_historial", "materias_primas.id", "=", "materias_primas_historial.materia_prima_id")
                    ->join("unidades", "materias_primas.unidad_id", "=", "unidades.id")
                    ->whereRaw("materias_primas_historial.id = ".$sub_sql)
                    ->where("materias_primas.id", $request->input("id"))->orderBy("materias_primas_historial.id", "DESC")->groupBy("materias_primas.id")->first();
                /*echo $materias_primas;
                dd($materias_primas);*/
                if ($materias_primas) {
                    return ["success" => true, "materia_prima" => $materias_primas];
                } else if ($barCode != '') {
                    //return["success" => false, "mensaje" => "pruebas"];
                    $materias_primas = MateriaPrima::select("materias_primas.*", "materias_primas_historial.precio_costo_nuevo as valor_proveedor", "unidades.sigla as sigla")
                        ->join("materias_primas_historial", "materias_primas.id", "=", "materias_primas_historial.materia_prima_id")
                        ->join("unidades", "materias_primas.unidad_id", "=", "unidades.id")
                        ->whereRaw("materias_primas_historial.id = ".$sub_sql)
                        ->where("materias_primas.codigo", $request->input("barCodeProducto"))->orderBy("materias_primas_historial.id", "DESC")->groupBy("materias_primas.id")->first();

                    if ($materias_primas) {
                        return ["success" => true, "materia_prima" => $materias_primas];
                    }

                    return ["success" => false, "mensaje" => "Ocurrio un error inesperado."];

                }
            } else {
                return redirect("/");
            }
            return response(["error" => ["La información enviada es incorrecta"]], 422);
        }
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
        $orderBy = "materias_primas." . $columna[$sortColumnIndex]['data'];


        $materias_primas = MateriaPrima::permitidos()->select("materias_primas.id", "materias_primas.nombre", "materias_primas.codigo", "materias_primas.descripcion", "unidades.nombre as unidad", "materias_primas.stock", "materias_primas.umbral")
            ->join("unidades", "materias_primas.unidad_id", "=", "unidades.id")->groupBy('materias_primas.id');

        $materias_primas = $materias_primas->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $materias_primas->get()->count();
        //BUSCAR
        if ($search['value'] != null) {
            $f = "%" . $search['value'] . "%";
            $materias_primas = $materias_primas->where(
                function ($query) use ($f) {
                    $query->where("materias_primas.nombre", "like", $f)
                        ->orWhere("codigo", "like", $f)
                        ->orWhere("descripcion", "like", $f);
                }
            );
        }

        $parcialRegistros = $materias_primas->get()->count();
        $materias_primas = $materias_primas->skip($start)->take($length);

        $myArray = [];
        $object = new \stdClass();
        if ($parcialRegistros > 0) {
            foreach ($materias_primas->get() as $value) {
                $myArray[] = (object)array(
                    'id' => $value->id,
                    'nombre' => $value->nombre,
                    'codigo' => $value->codigo,
                    'descripcion' => $value->descripcion,
                    'unidad' => $value->unidad,
                    'stock' => $value->stock,
                    'umbral' => $value->umbral,
                    'eliminar' => $value->permitirEliminar(),
                    'detalle' => '<a href="#modal-detalle-proveedor" class="modal-trigger tooltipped" data-tooltip="Ver proveedores" onclick="javascript: detalleProveedor(' . $value->id . ')"><i class="fa fa-list fa-2x" style="cursor: pointer;"></i></a>');
            }
        } else {
            $myArray = [];
        }

        $data = ['length' => $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $materias_primas->toSql(),
            'recordsTotal' => $totalRegistros,
            'recordsFiltered' => $parcialRegistros,
            'data' => $myArray,
            'info' => $materias_primas];

        return response()->json($data);

    }

    public function postEditPrecio(Request $request)
    {
        if (Auth::user()->permitirFuncion("Editar", "materias primas", "inicio")) {
            //$materia_prima = VProductoMateriaProveedor::findOrNew($id_producto);
            $materia_prima = MateriaPrima::permitidos()->where('materias_primas.id', $request->input("id_materia_prima"))->first();
            //dd($materia_prima);
            $proveedor = Proveedor::permitidos()->where("id", $request->input("id_proveedor"))->first();
            if ($materia_prima) {
                if(!$proveedor){
                    $proveedor = $materia_prima->proveedorActual()->first();
                }

                if($proveedor) {
                    $historial = $materia_prima->ultimoHistorial($proveedor->id);
                    if ($historial) {
                        //echo count($detalles_producto);
                        //dd($detalles_producto);
                        return view('materias_primas.edit_precio')->with('materia_prima', $materia_prima)->with("proveedor", $proveedor)->with("historial", $historial);
                    }
                }
            }
            return response(["Error" => ["La información envidada es incorrecta"]], 422);
        }
        return response("Unauthorized", 401);

    }

    public function postUpdatePrecio(Request $request)
    {
        if (Auth::user()->permitirFuncion("Editar", "materias primas", "inicio")) {

            if(!$request->has("precio_costo_nuevo"))return response(["Error"=>["El campo precio costo es obligatorio"]],422);

            $materia_prima = MateriaPrima::permitidos()->where("materias_primas.id", $request->input("id"))->first();
            $proveedor = Proveedor::permitidos()->where("id", $request->input("proveedor"))->first();

            if ($materia_prima) {
                if(!$proveedor){
                    $proveedor = $materia_prima->proveedorActual()->first();
                }

                if($proveedor) {
                    $cant = 0;
                    if (!$materia_prima->aparicionesProductosCompras()) {
                        $cant = $request->input("stock");
                    }

                    $lastHistorial = $materia_prima->ultimoHistorial($proveedor->id);
                    if ($lastHistorial) {
                        $dataHistorial = [
                            "precio_costo_nuevo" => $request->input("precio_costo_nuevo"),
                            "precio_costo_anterior" => $lastHistorial->precio_costo_nuevo,
                            "materia_prima_id" => $materia_prima->id,
                            "proveedor_id" => $proveedor->id,
                            "usuario_id" => Auth::user()->id,
                            "stock" => $cant
                        ];


                        $historial = new MateriaPrimaHistorial();
                        $historial->fill($dataHistorial);
                        $historial->save();

                        $materia_prima->precio_costo = $historial->precio_costo_nuevo;

                        if (!$materia_prima->aparicionesProductosCompras()) {
                            $materia_prima->stock = $cant;
                            $materia_prima->promedio_ponderado = $request->input("precio_costo_nuevo");
                        }
                        $materia_prima->save();
                        return ["success" => true, "materia_prima" => $materia_prima];
                    }
                }
            }
        }
        return response(["Error" => ["La información enviada es incorrecta"]], 422);
    }

    public function getImportacion(){
        if(Auth::user()->plan()->importacion_productos == "si") {
            //$importaciones = ImportacionMateriaPrima::permitidos()->where("estado","pendiente")->get();
            return view("importacion_materias_primas.index");//->with("importaciones",$importaciones);
        }
        return redirect("/");
    }

    public function getListImportacionMateriasPrimas(Request $request){
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
        $materias_primas = ImportacionMateriaPrima::permitidos()->where("estado","pendiente")->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $materias_primas->count();
        if($search['value'] != null){
            $materias_primas = $materias_primas->whereRaw(
                " ( codigo LIKE '%".$search["value"]."%' OR".
                " LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                " cantidad LIKE '%".$search["value"]."%' OR".
                " umbral LIKE '%".$search["value"]."%' ".
                ")");
        }

        $parcialRegistros = $materias_primas->count();
        $materias_primas = $materias_primas->skip($start)->take($length);
        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $materias_primas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' =>$materias_primas->get()];

        return response()->json($data);
    }

    public function postStoreImportacion(Request $request){
        if ($request->hasFile("archivo")) {
            if ($request->file('archivo')->isValid()) {
                $img = $request->file("archivo");
                $ext = $img->getClientOriginalExtension();
                if($ext == "xls" || $ext == "xlsx"){
                    $ruta = storage_path("app/sistema/users/temporal/".Auth::user()->id);
                    $img->move($ruta, $img->getClientOriginalName());
                    $datos = [];
                    Excel::load($ruta."/".$img->getClientOriginalName(),function($reader) use (&$datos){
                        $datos = $reader->toArray();
                    });
                    @unlink($ruta."/".$img->getClientOriginalName());
                    //dd($datos);
                    if(count($datos)){
                        $i = 1;
                        DB::beginTransaction();
                        foreach ($datos as $d){
                            $i++;
                            //todos la fila contiene el formato correcto
                            if(!(isset($d["nombre"]) &&
                                isset($d["descripcion"])&&
                                isset($d["valor"])&&
                                //isset($d["codigo"])&&
                                isset($d["umbral"])&&
                                isset($d["cantidad"]))){
                                break;
                            }

                            $validator = Validator::make($d,RequestNuevaMateriaPrima::getRulesImportacion(),RequestNuevaMateriaPrima::getMessagesImportacion());
                            if($validator->fails()){
                                //dd($validator->errors()->all());
                                return response($validator->errors()->all()+["Error"=>"Error en fila #".$i],422);
                            }else{
                                $codigo = false;

                                if($d["codigo"] != "")
                                    $codigo = MateriaPrima::permitidos()->where("codigo",$d["codigo"])->first();

                                if($codigo)
                                    return response(["Error"=>["Ya existe una materia prima con el código '".$d["codigo"]."'"],"linea"=>["Error en la linea #".$i]],422);

                                $importacion = new ImportacionMateriaPrima();
                                $importacion->fill($d);
                                $importacion->usuario_id = Auth::user()->userAdminId();
                                $importacion->usuario_creador_id = Auth::user()->id;
                                $importacion->save();
                            }

                            /*}else{
                                return response(["Error"=>["El formato enviado es incorrecto, asegurese de utilizar unicamente el formato generado por el sistema"]],422);
                            }*/
                        }
                        DB::commit();
                        Session::flash("mensaje","La importación de productos se realizó con éxito");
                        return ["success" => true];
                    }else{
                        return response(["Error" => "El archivo enviado se encuentra vacio"], 422);
                    }
                }else{
                    return response(["Error" => "Seleccione únicamente archivos con extensión xls o xlsx"], 422);
                }
            }
        } else {
            return response(["Error" => "Seleccione un archivo"], 422);
        }
        return response(["Error" => "La información enviada es incorrecta"], 422);
    }

    public function postProcesarImportacion(Request $request){
        if($request->has("id") && $request->has("unidad") && $request->has("proveedor")){
            if(Unidad::unidadesPermitidas()->where("id",$request->input("unidad"))->first() && Proveedor::permitidos()->where("id",$request->input("proveedor"))->first()) {
                $importacion = ImportacionMateriaPrima::find($request->input("id"));
                if ($importacion) {
                    $data = $importacion->toArray();
                    $codigo = false;

                    if($data["codigo"] != "")
                        $codigo = MateriaPrima::permitidos()->where("codigo",$data["codigo"])->first();

                    if($codigo){
                        return response(["Error"=>["Ya se ha registrado una materia prima con el código '".$data["codigo"]."'"]],422);
                    }

                    $data["unidad_id"] = $request->input("unidad");
                    $data["proveedor_actual"] = $request->input("proveedor");
                    $data["stock"] = $data["cantidad"];
                    $data["promedio_ponderado"] = $data["valor"];
                    $data["precio_costo"] = $data["valor"];
                    $data["usuario_id"] = Auth::user()->userAdminId();
                    $data["usuario_id_creator"] = Auth::user()->id;
                    $materia_prima = new MateriaPrima();
                    $materia_prima->fill($data);
                    $materia_prima->save();

                    $dataHistorial = [
                        "precio_costo_nuevo" => $data["precio_costo"],
                        "precio_costo_anterior" => $data["precio_costo"],
                        "stock" => $data["stock"],
                        "materia_prima_id" => $materia_prima->id,
                        "proveedor_id" => $request->input("proveedor"),
                        "usuario_id" => Auth::user()->id
                    ];

                    $historial = new MateriaPrimaHistorial();
                    $historial->fill($dataHistorial);
                    $historial->save();

                    $importacion->estado = "procesado";
                    $importacion->save();

                    return ["success" => true];
                }
            }
        }
        return response(["Error"=>["La información enviada es incorrecta"]],422);
    }

    public function postProcesarImportacionTodo(Request $request){

        if( $request->has("unidad") && $request->has("proveedor")){
            if(Unidad::unidadesPermitidas()->where("id",$request->input("unidad"))->first() && Proveedor::permitidos()->where("id",$request->input("proveedor"))->first()) {
                $importaciones = ImportacionMateriaPrima::permitidos()->where('estado','pendiente')->get();
                DB::beginTransaction();
                foreach($importaciones as $importacion) {
                    if ($importacion) {
                        $data = $importacion->toArray();
                        $codigo = false;

                        if ($data["codigo"] != "")
                            $codigo = MateriaPrima::permitidos()->where("codigo", $data["codigo"])->first();

                        if ($codigo) {
                            return response(["Error" => ["Ya se ha registrado una materia prima con el código '" . $data["codigo"] . "'"]], 422);
                        }

                        $data["unidad_id"] = $request->input("unidad");
                        $data["proveedor_actual"] = $request->input("proveedor");
                        $data["usuario_id"] = Auth::user()->userAdminId();
                        $data["usuario_id_creator"] = Auth::user()->id;
                        $data["stock"] = $data["cantidad"];
                        $data["promedio_ponderado"] = $data["valor"];
                        $data["precio_costo"] = $data["valor"];
                        $materia_prima = new MateriaPrima();
                        $materia_prima->fill($data);
                        $materia_prima->save();

                        $dataHistorial = [
                            "precio_costo_nuevo" => $data["precio_costo"],
                            "precio_costo_anterior" => $data["precio_costo"],
                            "stock" => $data["stock"],
                            "materia_prima_id" => $materia_prima->id,
                            "proveedor_id" => $request->input("proveedor"),
                            "usuario_id" => Auth::user()->id
                        ];

                        $historial = new MateriaPrimaHistorial();
                        $historial->fill($dataHistorial);
                        $historial->save();

                        $importacion->estado = "procesado";
                        $importacion->save();

                    }

                }

                DB::commit();
                Session::flash("mensaje","Se han procesado todas las importaciones con éxito");
                return ["success" => true];
            }
        }else{
            return response(['error'=>['Para procesar todas las importaciones debe seleccionar un valor de categoria, unidad y proveedor']],422);
        }
        return response(["Error"=>["La información enviada es incorrecta"]],422);
    }

    public function postRechazarImportacion(Request $request){

        if($request->has("id")){
            $importacion = ImportacionMateriaPrima::permitidos()->where("id",$request->input("id"))->first();
            if ($importacion) {
                $importacion->delete();/*
				$importacion->estado = "rechazado";
				$importacion->save();*/
                return ["success" => true];
            }
        }
        return response(["Error"=>["La información enviada es incorrecta"]],422);
    }

    public function getFormatoImportacion(){
        $path = storage_path() . '/app/sistema/formatos/PlantillaMateriasPrimas.xlsx';
        if(!File::exists($path)) abort(404);

        $file = File::get($path);
        $type = File::mimeType($path);

        $response = response($file, 200);
        $response->header("Content-Type", $type);

        return $response;
    }

}
