<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use \App\General;
use App\Models\ABCompra;
use App\Models\ABFactura;
use App\Models\Abono;
use App\Models\ABProducto;
use App\Models\ABRemision;
use App\Models\Almacen;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Consignacion;
use App\Models\CostoFijo;
use App\Models\CuentaBancaria;
use App\Models\Factura;
use App\Models\GastoDiario;
use App\Models\MateriaPrima;
use App\Models\MovimientoCajaBanco;
use App\Models\ObjetivoVenta;
use App\Models\Producto;
use App\Models\CuentaPorCobrar;
use App\Models\Proveedor;
use App\Models\Reporte;
use App\Models\Resolucion;
use App\Models\TokenPuntos;
use App\Models\unidad;
use App\Models\ControlEmpleados;
use App\Models\ControlEmpleadosRegistros;
use Illuminate\Http\Request;
use App\Http\Requests\RequestCategoria;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PlanUsuario;
class ReporteController extends Controller {

    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("modReportes");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(){
        return redirect()->back();
    }

    public function getInventario(Request $request){
        $r = Reporte::where('nombre','Inventarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
        //	$productos = $productos->paginate(env("PAGINATE"));
        return view('reportes.inventario.index');//->with('productos',$productos)->with("filtro",$filtro);

        return redirect('/');
    }

    public function getListReporteInventario(Request $request){
        $r = Reporte::where('nombre','Inventarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            if ($orderBy == 'costo_total')
                $orderBy = 'barcode';
            if ($orderBy == 'unidad')
                $orderBy = 'unidad_id';

            if ($orderBy == 'categoria')
                $orderBy = 'categoria_id';

            if(Auth::user()->bodegas == 'si')
                $productos = ABProducto::Permitidos();
            else
                $productos = Producto::Permitidos();


            if (!$request->has("filtro"))
                $filtro = 1;
            else
                $filtro = $request->get("filtro");

            if(Auth::user()->bodegas == 'si'){
                $almacen = false;
                if(Auth::user()->admin_bodegas == 'si'){
                    if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0 && $request->input('almacen') != 'bodega'){
                        $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                        if(!$alm_obj)return false;
                        $almacen = $alm_obj->id;
                    }else if($request->has('almacen') && $request->input('almacen') == 'bodega'){
                        $bodega = Bodega::permitidos()->first();
                        $productos = $productos->select('productos.*','bodegas_stock_productos.stock as stock')
                            ->join('bodegas_stock_productos','productos.id','=','bodegas_stock_productos.producto_id')
                            ->where('bodegas_stock_productos.bodega_id',$bodega->id);

                        switch ($filtro) {
                            case 2:
                                $productos = $productos->whereRaw("bodegas_stock_productos.stock <= productos.umbral");
                                break;
                            case 3:
                                $productos = $productos->whereRaw("bodegas_stock_productos.stock > productos.umbral");
                                break;
                        }
                    }else{
                        $bodega = Bodega::permitidos()->first();
                        $sub_consulta_stock = "(bodegas_stock_productos.stock + (IF(ISNULL((select sum(asp.stock) as stock from almacenes_stock_productos asp where asp.producto_id = productos.id group by asp.producto_id)),0,(select sum(asp.stock) as stock from almacenes_stock_productos asp where asp.producto_id = productos.id group by asp.producto_id))))";

                        $productos = $productos->select('productos.*',DB::raw($sub_consulta_stock.' as stock'))
                            ->join('bodegas_stock_productos','productos.id','=','bodegas_stock_productos.producto_id')
                            ->where('bodegas_stock_productos.bodega_id',$bodega->id);

                    }
                }else{
                    $almacen = Auth::user()->almacenActual()->id;
                }

                if($almacen){ 
                    $productos = $productos->select('productos.*','almacenes_stock_productos.stock as stock')
                        ->join('almacenes_stock_productos','productos.id','=','almacenes_stock_productos.producto_id')
                        ->where('almacenes_stock_productos.almacen_id',$almacen);

                    switch ($filtro) {
                        case 2:
                            $productos = $productos->whereRaw("almacenes_stock_productos.stock <= productos.umbral");
                            break;
                        case 3:
                            $productos = $productos->whereRaw("almacenes_stock_productos.stock > productos.umbral");
                            break;
                    }
                }
            }else {
                switch ($filtro) {
                    case 2:
                        $productos = $productos->whereRaw("productos.stock <= productos.umbral");
                        break;
                    case 3:
                        $productos = $productos->whereRaw("productos.stock > productos.umbral");
                        break;
                }
            }

            $productos = $productos->orderBy($orderBy, $sortColumnDir);
            $totalRegistros = $productos->count();
            //BUSCAR
            if ($search['value'] != null) {

                $productos = $productos->whereRaw(
                    " ( barcode LIKE '%" . $search["value"] . "%' OR " .
                    " LOWER(nombre) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                    " umbral LIKE '%" . $search["value"] . "%' OR" .
                    " stock LIKE '%" . $search["value"] . "%' OR" .
                    " precio_costo LIKE '%" . $search["value"] . "%' OR" .
                    " precio_costo * stock LIKE '%" . $search["value"] . "%')");
            }

            $parcialRegistros = $productos->count();
            $productos = $productos->skip($start)->take($length);


            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($productos->get() as $producto) {
                    $show_costos = 1;
                    if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')$show_costos = 0;
                    $myArray[] = (object)array(
                        'show_costos'=>$show_costos,
                        'barcode' => $producto->barcode,
                        'nombre' => \App\TildeHtml::TildesToHtml($producto->nombre),
                        'umbral' => $producto->umbral,
                        'stock' => $producto->stock,
                        'precio_costo' => '$' . number_format($producto->promedio_ponderado, 0, ",", "."),
                        'costo_total' => '$' . number_format($producto->promedio_ponderado * $producto->stock, 0, ",", "."),
                        'unidad' => $producto->unidad->sigla,
                        'categoria' => $producto->categoria->nombre);
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $productos->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'info' => $productos->get()];

            return response()->json($data);
        }
    }

    public function getExcelInventario(Request $request){
        $r = Reporte::where('nombre','Inventarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if (!$request->has("filtro")) {
                $filtro = 1;
            } else {
                $filtro = $request->input("filtro");
            }
            if(Auth::user()->bodegas == 'si')
                $productos = ABProducto::permitidos();
            else
                $productos = Producto::permitidos();
            $nombre = "Reporte de inventario";

            if(Auth::user()->bodegas == 'si'){
                $almacen = false;
                if(Auth::user()->admin_bodegas == 'si'){
                    if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0 && $request->input('almacen') != 'bodega'){
                        $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                        if(!$alm_obj)return false;
                        $almacen = $alm_obj->id;
                    }else if($request->has('almacen') && $request->input('almacen') == 'bodega'){
                        $bodega = Bodega::permitidos()->first();
                        $productos = $productos->select('productos.*','bodegas_stock_productos.stock as stock')
                            ->join('bodegas_stock_productos','productos.id','=','bodegas_stock_productos.producto_id')
                            ->where('bodegas_stock_productos.bodega_id',$bodega->id);

                        switch ($filtro) {
                            case 2:
                                $productos = $productos->whereRaw("bodegas_stock_productos.stock <= productos.umbral");
                                break;
                            case 3:
                                $productos = $productos->whereRaw("bodegas_stock_productos.stock > productos.umbral");
                                break;
                        }
                    }else{
                        //cuando se selecciona la opcion de todos
                        $bodega = Bodega::permitidos()->first();
                        $sub_consulta_stock = "(bodegas_stock_productos.stock + (IF(ISNULL((select sum(asp.stock) as stock from almacenes_stock_productos asp where asp.producto_id = productos.id group by asp.producto_id)),0,(select sum(asp.stock) as stock from almacenes_stock_productos asp where asp.producto_id = productos.id group by asp.producto_id))))";

                        $productos = $productos->select('productos.*',DB::raw($sub_consulta_stock.' as stock'))
                            ->join('bodegas_stock_productos','productos.id','=','bodegas_stock_productos.producto_id')
                            ->where('bodegas_stock_productos.bodega_id',$bodega->id);
                    }
                }else{
                    $almacen = Auth::user()->almacenActual()->id;
                }

                if($almacen){
                    $productos = $productos->select('productos.*','almacenes_stock_productos.stock as stock')
                        ->join('almacenes_stock_productos','productos.id','=','almacenes_stock_productos.producto_id')
                        ->where('almacenes_stock_productos.almacen_id',$almacen);

                    switch ($filtro) {
                        case 2:
                            $productos = $productos->whereRaw("almacenes_stock_productos.stock <= productos.umbral");
                            break;
                        case 3:
                            $productos = $productos->whereRaw("almacenes_stock_productos.stock > productos.umbral");
                            break;
                    }
                }
            }else {

                switch ($filtro) {
                    case 2:
                        $productos = $productos->whereRaw("productos.stock <= productos.umbral");
                        $nombre .= " - bajo umbral";
                        break;
                    case 3:
                        $productos = $productos->whereRaw("productos.stock > productos.umbral");
                        $nombre .= " - sobre umbral";
                        break;
                }
            }

            $productos = $productos->get();
            return Excel::create($nombre, function ($excel) use ($productos) {
                $excel->sheet('Lista de productos', function ($sheet) use ($productos) {
                    $sheet->loadView('reportes.inventario.lista', ["productos" => $productos, "reporte" => true]);
                });
            })->export("xls");
        }
    }

    public function getTopVentas(){
        $r = Reporte::where('nombre','Top de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.top_ventas.index');
        return redirect('/');
    }

    public function postListTopVentas(Request $request){
        $r = Reporte::where('nombre','Top de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("top")) {
                $categoria = null;
                if ($request->has("rep_categoria")) {
                    $categoriaObj = Categoria::permitidos()->where("id", $request->input("rep_categoria"))->first();
                    if ($categoriaObj) {
                        $categoria = $categoriaObj->id;
                    } else {
                        return response(["La información enviada es incorrecta"], 422);
                    }
                }
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                //$productos = Producto::topVentas($request->input("fecha_inicio"),$fecha_fin,$request->input("top"),$categoria,10);
                return view("reportes.top_ventas.lista");//->with("productos",$productos);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }
    /*** REVISAR LEO ***/
    public function getListReporteTopVentas(Request $request){
        $r = Reporte::where('nombre','Top de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $myArray = [];
            $fechaInicio = $request->input("fecha_inicio");
            $fechaFin = $request->has("fecha_fin");
            $top = $request->input("top");
            $categoria = null;
            $paginate = null;
            $data = ['length' => $length, 'start' => $start, 'buscar' => $search['value'], 'draw' => '', 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'info' => 0];
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("top")) {
                $categoria = null;
                if ($request->has("rep_categoria")) {
                    $categoriaObj = Categoria::permitidos()->where("id", $request->input("rep_categoria"))->first();
                    if ($categoriaObj) {
                        $categoria = $categoriaObj->id;
                    } else {
                        return response()->json($data);
                    }
                }
                $fechaFin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

                if(Auth::user()->bodegas == 'si') {
                    $result = ABProducto::select("productos.*", DB::raw("sum(facturas_historial_utilidad.cantidad) as cantidad_vendida"))
                        ->join("historial_utilidad", "productos.id", "=", "historial_utilidad.producto_id")
                        ->join("facturas_historial_utilidad", "historial_utilidad.id", "=", "facturas_historial_utilidad.historial_utilidad_id")
                        ->join("facturas", "facturas_historial_utilidad.factura_id", "=", "facturas.id")
                        ->where("productos.usuario_id", Auth::user()->userAdminId())
                        ->where(function ($q) {
                            $q->where("facturas.estado", "Pagada")
                                ->orWhere("facturas.estado", "Pendiente por pagar")
                                ->orWhere("facturas.estado", "cerrada")
                                ->orWhere("facturas.estado", "abierta");
                        })
                        ->whereBetween("facturas.created_at", [$fechaInicio, $fechaFin]);

                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $almacen = $alm_obj->id;
                                $result = $result->where('facturas.almacen_id',$almacen);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $result = $result->where('facturas.almacen_id',$almacen);
                    }
                }else{
                    $result = Producto::select("productos.*", DB::raw("sum(facturas_productos_historial.cantidad) as cantidad_vendida"))
                        ->join("productos_historial", "productos.id", "=", "productos_historial.producto_id")
                        ->join("facturas_productos_historial", "productos_historial.id", "=", "facturas_productos_historial.producto_historial_id")
                        ->join("facturas", "facturas_productos_historial.factura_id", "=", "facturas.id")
                        ->where("productos.usuario_id", Auth::user()->userAdminId())
                        ->where(function ($q) {
                            $q->where("facturas.estado", "Pagada")
                                ->orWhere("facturas.estado", "Pendiente por pagar")
                                ->orWhere("facturas.estado", "cerrada")
                                ->orWhere("facturas.estado", "abierta");
                        })
                        ->whereBetween("facturas.created_at", [$fechaInicio, $fechaFin]);
                }
                if ($categoria != null) {
                    $result = $result->where("productos.categoria_id", $categoria);
                }

                if(Auth::user()->bodegas == 'si')
                    $result = $result->groupBy("productos.id")->orderBy(DB::raw("sum(facturas_historial_utilidad.cantidad)"), "DESC")->take($top);
                else
                    $result = $result->groupBy("productos.id")->orderBy(DB::raw("sum(facturas_productos_historial.cantidad)"), "DESC")->take($top);
                $totalRegistros = $result->get()->count();
                //BUSCAR
                // if($search['value'] != null){

                //     $result = $result->whereRaw(
                //         " ( LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                //         " LOWER(descripcion) LIKE '%".\strtolower($search["value"])."%' OR".
                //         //" LOWER(categoria) LIKE '%".\strtolower($search["value"])."%' OR".
                //         " cantidad_vendida LIKE '%".$search["value"]."%' )");
                // }

                $parcialRegistros = $result->get()->count();
                $result = $result->skip($start)->take($length);
                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    foreach ($result->get() as $producto) {
                        $myArray[] = (object)array(
                            'nombre' => \App\TildeHtml::TildesToHtml($producto->nombre),
                            'descripcion' => \App\TildeHtml::TildesToHtml($producto->descripcion),
                            'categoria' => \App\TildeHtml::TildesToHtml($producto->categoria->nombre),
                            'cantidad_vendida' => $producto->cantidad_vendida,
                            'stock' => $producto->stock);
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $result->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'data' => $myArray,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    'info' => $result->get()];
            }
            return response()->json($data);
        }
    }

    public function postGraficaTopVentas(Request $request){
        $r = Reporte::where('nombre','Top de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("top")) {
                $categoria = null;
                if ($request->has("rep_categoria")) {
                    $categoriaObj = Categoria::permitidos()->where("id", $request->input("rep_categoria"))->first();
                    if ($categoriaObj) {
                        $categoria = $categoriaObj->id;
                    } else {
                        return response(["La información enviada es incorrecta"], 422);
                    }
                }
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si') {
                    $almacen = null;

                    if (Auth::user()->admin_bodegas == 'si') {
                        if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                            $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                            if ($alm_obj) {
                                $almacen = $alm_obj->id;
                            } else return response(['error' => ['la información enviada es incorrecta']], 422);
                        }
                    } else {
                        $almacen = Auth::user()->almacenActual()->id;
                    }
                    $productos = ABProducto::topVentas($request->input("fecha_inicio"), $fecha_fin, $request->input("top"), $categoria, 10,$almacen);
                }else {
                    $productos = Producto::topVentas($request->input("fecha_inicio"), $fecha_fin, $request->input("top"), $categoria, 10);
                }
                return view("reportes.top_ventas.grafica")->with("productos", $productos);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getExcelTopVentas(Request $request){
        $r = Reporte::where('nombre','Top de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte top ventas";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("top")) {
                $categoria = null;
                if ($request->has("rep_categoria")) {
                    $categoriaObj = Categoria::permitidos()->where("id", $request->input("rep_categoria"))->first();
                    if ($categoriaObj) {
                        $categoria = $categoriaObj->id;
                    } else {
                        return response(["La información enviada es incorrecta"], 422);
                    }
                }
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si') {
                    $almacen = null;

                    if (Auth::user()->admin_bodegas == 'si') {
                        if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                            $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                            if ($alm_obj) {
                                $almacen = $alm_obj->id;
                            } else return response(['error' => ['la información enviada es incorrecta']], 422);
                        }
                    } else {
                        $almacen = Auth::user()->almacenActual()->id;
                    }

                    $productos = ABProducto::topVentas($request->input("fecha_inicio"), $fecha_fin, $request->input("top"), $categoria, null,$almacen);
                }else {
                    $productos = Producto::topVentas($request->input("fecha_inicio"), $fecha_fin, $request->input("top"), $categoria, null);
                }
                return Excel::create($nombre, function ($excel) use ($productos) {
                    $excel->sheet('Lista de productos', function ($sheet) use ($productos) {
                        $sheet->loadView('reportes.top_ventas.listaExcel', ["productos" => $productos, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getPerdidasGanancias(){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.utilidades.index');
        return redirect('/');
    }

    public function postListPerdidasGanancias(Request $request){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $almacen = false;
                if(Auth::user()->bodegas == 'no') {
                    $totalFacturas = Factura::totalVentasSinIva($request->input("fecha_inicio"), $fecha_fin);
                    $totalCompras = Factura::totalPrecioCosto($request->input("fecha_inicio"), $fecha_fin);
                    $totalCostosFijos = CostoFijo::totalPagos($request->input("fecha_inicio"), $fecha_fin);
                    $totalGastosDiarios = GastoDiario::totalPagos($request->input("fecha_inicio"), $fecha_fin);
                }else{

                    if(Auth::user()->admin_bodegas == 'no'){
                        $almacen = Auth::user()->almacenActual()->id;
                    }else{
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$almacen = $alm_obj->id;
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }
                    $totalFacturas = ABFactura::totalVentasSinIva($request->input("fecha_inicio"), $fecha_fin, $almacen);
                    $totalCompras = ABFactura::totalPrecioCosto($request->input("fecha_inicio"), $fecha_fin, $almacen);
                    $totalCostosFijos = CostoFijo::totalPagos($request->input("fecha_inicio"), $fecha_fin, $almacen);
                    $totalGastosDiarios = GastoDiario::totalPagos($request->input("fecha_inicio"), $fecha_fin, $almacen);
                }
                return view("reportes.utilidades.lista")
                    ->with('totalGastosDiarios',$totalGastosDiarios)->with("totalFacturas", $totalFacturas)->with("totalCompras", $totalCompras)->with("totalCostosFijos", $totalCostosFijos)->with("fecha_inicio", $request->input("fecha_inicio"))->with("fecha_fin", $request->input("fecha_fin"))->with('almacen',$almacen);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getPerdidasGananciasDetalle(Request $request){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

                $almacen = false;
                if(Auth::user()->bodegas == 'no') {
                    /*$facturas = Factura::permitidos()
                        ->leftJoin('token_puntos', 'facturas.id', '=', 'token_puntos.factura_id')
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("facturas.estado", "<>", "anulada")->get();
                    $compras = Compra::permitidos()->whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("estado", "<>", "Cancelada")->get();
                    $costosFijos = CostoFijo::permitidos()->select("costos_fijos.*", "pagos_costos_fijos.valor", "pagos_costos_fijos.fecha")
                        ->join("pagos_costos_fijos", "costos_fijos.id", "=", "pagos_costos_fijos.costo_fijo_id")
                        ->whereBetween("pagos_costos_fijos.fecha", [$request->input("fecha_inicio"), $fecha_fin])->get();*/
                }else{
                    if(Auth::user()->admin_bodegas == 'no'){
                        $almacen = Auth::user()->almacenActual()->id;
                    }else{
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$almacen = $alm_obj->id;
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }/*
                    $facturas = ABFactura::permitidos()
                        ->leftJoin('vendiendo.token_puntos', 'facturas.id', '=', 'vendiendo.token_puntos.factura_id')
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("facturas.estado", "<>", "anulada");
                    if($almacen)$facturas = $facturas->where('almacen_id',$almacen);
                        $facturas = $facturas->get();
                    $compras = ABCompra::permitidos()->whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("estado", "<>", "Cancelada");

                    //if($almacen)$compras = $compras->where('almacen_id',$almacen);
                    $compras = $compras->get();

                    $costosFijos = CostoFijo::permitidos()->select("costos_fijos.*", "pagos_costos_fijos.valor", "pagos_costos_fijos.fecha")
                        ->join("pagos_costos_fijos", "costos_fijos.id", "=", "pagos_costos_fijos.costo_fijo_id")
                        ->whereBetween("pagos_costos_fijos.fecha", [$request->input("fecha_inicio"), $fecha_fin]);

                    if($almacen)$costosFijos = $costosFijos->where('almacen_id',$almacen);
                    $costosFijos = $costosFijos->get();*/
                }
                return view("reportes.utilidades.lista_detalle")/*->with("facturas", $facturas)->with("compras", $compras)->with("costosFijos", $costosFijos)*/->with("fecha_inicio", $request->input("fecha_inicio"))->with("fecha_fin", $request->input("fecha_fin"))->with('almacen',$almacen);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    /*Pobla Datatable*/
    public function getListReporteDetalleUtilidad(Request $request){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $total_compras = 0;
            $total_facturas = 0;
            $total_descuentos = 0;
            if ($orderBy == 'created_at') {
                $orderBy = 'facturas.created_at';
            }

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

            if(Auth::user()->bodegas == 'no') {
                $facturas = Factura::select("facturas.id as id", "facturas.created_at as created_at", "facturas.created_at as created_at", "productos.nombre as nombre", "productos_historial.precio_costo_nuevo as precio_costo_nuevo", "productos_historial.utilidad_nueva as utilidad_nueva", "facturas_productos_historial.cantidad as cantidad", "facturas.descuento as descuento", "token_puntos.valor as valor_puntos")
                    ->leftJoin('token_puntos', 'facturas.id', '=', 'token_puntos.factura_id')
                    ->join("facturas_productos_historial", "facturas_productos_historial.factura_id", "=", "facturas.id")
                    ->join("productos_historial", "productos_historial.id", "=", "facturas_productos_historial.producto_historial_id")
                    ->join("productos", "productos.id", "=", "productos_historial.producto_id")
                    ->where("facturas.usuario_id", Auth::user()->userAdminId())
                    ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                    ->where("facturas.estado", "<>", "anulada");
                //$fact_aux = $facturas->groupBy("facturas.id")->get();//Se agrupa
            }else{
                $almacen = false;
                if(Auth::user()->admin_bodegas == 'no'){
                    $almacen = Auth::user()->almacenActual()->id;
                }else{
                    if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                        $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                        if($alm_obj)$almacen = $alm_obj->id;
                        else return response(['error'=>['la información enviada es incorrecta']],422);
                    }
                }
                $facturas = ABFactura::select("facturas.id as id", "facturas.created_at as created_at", "facturas.created_at as created_at", "productos.nombre as nombre", "historial_costos.precio_costo_nuevo as precio_costo_nuevo", "historial_utilidad.utilidad as utilidad_nueva", "facturas_historial_utilidad.cantidad as cantidad", "facturas.descuento as descuento", "token_puntos.valor as valor_puntos")
                    ->leftJoin('vendiendo.token_puntos', 'facturas.id', '=', 'vendiendo.token_puntos.factura_id')
                    ->join("facturas_historial_costos", "facturas.id", "=", "facturas_historial_costos.factura_id")
                    ->join("historial_costos", "facturas_historial_costos.historial_costo_id", "=", "historial_costos.id")
                    ->join("productos", "historial_costos.producto_id", "=", "productos.id")
                    ->join("facturas_historial_utilidad", "facturas.id", "=", "facturas_historial_utilidad.factura_id")
                    ->join("historial_utilidad", "facturas_historial_utilidad.historial_utilidad_id", "=", "historial_utilidad.id")
                    ->where("facturas.usuario_id", Auth::user()->userAdminId())
                    ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                    ->where("facturas.estado", "<>", "anulada");

                if($almacen)$facturas = $facturas->where('facturas.almacen_id',$almacen);
                //$facturas = $facturas->get();
            }


                $facturas = $facturas->orderBy($orderBy, $sortColumnDir);
                $totalRegistros = $facturas->count();
                $parcialRegistros = $facturas->count();

                $fact_aux = $facturas->get();//Se agrupa
                $fact_aux2 = $fact_aux;//$facturas->groupBy("facturas.id")->get();
                $facturas = $facturas->skip($start)->take($length);
                $object = new \stdClass();
                $myArray = [];
                if ($parcialRegistros > 0) {
                    $total_facturas = 0;
                    $total_compras = 0;
                    //$total_descuentos = 0;
                    $total_valor_puntos = 0;
                    foreach ($fact_aux as $v) {
                        $valorVenta = ($v->precio_costo_nuevo + (($v->precio_costo_nuevo * $v->utilidad_nueva) / 100)) * $v->cantidad;
                        $valorCompra = $v->precio_costo_nuevo * $v->cantidad;
                        $total_facturas += $valorVenta /*- ($v->descuento + $v->valor_puntos)*/
                        ;
                        $total_compras += $valorCompra;
                        //$total_descuentos += $v->descuento;
                        $total_valor_puntos += $v->valor_puntos;
                    }

                    $facturas_sumadas = [];
                    $total_descuentos = 0;
                    foreach ($fact_aux2 as $value) {
                        if(!array_key_exists($value->id,$facturas_sumadas)) {
                            $total_facturas -= ($value->descuento + $value->valor_puntos);
                            $total_descuentos += $value->descuento;
                            $facturas_sumadas[$value->id] = true;
                        }
                    }
                    foreach ($facturas->get() as $value) {
                        $valorVenta = ($value->precio_costo_nuevo + (($value->precio_costo_nuevo * $value->utilidad_nueva) / 100)) * $value->cantidad;
                        $valorCompra = $value->precio_costo_nuevo * $value->cantidad;

                        $myArray[] = (object)array('id' => $value->id,
                            'created_at' => (String)$value->created_at,
                            'producto' => $value->nombre,
                            'venta' => '$' . number_format($valorVenta, 2, ",", "."),
                            'costo_compra' => '$' . number_format($valorCompra, 2, ",", "."),
                            'utilidad' => '$' . number_format($valorVenta - $valorCompra, 2, ",", "."),
                            'utilidad_porciento' => number_format((($valorVenta - $valorCompra) / $valorCompra) * 100, 2, ",", ".") . "%",
                            'total_factura' => number_format($total_facturas, 2, ",", "."),
                            'total_compras' => number_format($total_compras, 2, ",", "."),
                            'total_factura_compras' => number_format($total_facturas - $total_compras, 2, ",", "."),
                            'total_descuentos' => number_format($total_descuentos, 2, ",", "."),
                            'total_porciento' => number_format(((($total_facturas - $total_compras)) / $total_compras) * 100, 2, ",", "."),
                            'total_valor_puntos' => number_format($total_valor_puntos, 2, ",", ".")
                        );
                    }

                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'count' => $parcialRegistros,
                    'data' => $myArray];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }
    /*Pobla Datatable*/
    public function getListReporteDetalleUtilidadCF(Request $request){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $total_compras = 0;
            $total_facturas = 0;
            $total_gastos = 0;
            $orderBy == 'costos_fijos.id';

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $costosFijos = CostoFijo::permitidos()->select("costos_fijos.*", "pagos_costos_fijos.valor", "pagos_costos_fijos.fecha")
                    ->join("pagos_costos_fijos", "costos_fijos.id", "=", "pagos_costos_fijos.costo_fijo_id")
                    ->whereBetween("pagos_costos_fijos.fecha", [$request->input("fecha_inicio"), $fecha_fin]);

                $costosFijos = $costosFijos->orderBy($orderBy, $sortColumnDir);
                $totalRegistros = $costosFijos->count();
                $parcialRegistros = $costosFijos->count();
                $costosFijos = $costosFijos->skip($start)->take($length);
                $object = new \stdClass();
                $myArray = [];
                if ($parcialRegistros > 0) {
                    foreach ($costosFijos->get() as $c) {
                        $total_gastos += $c->valor;
                        $myArray[] = (object)array('id' => $c->fecha,
                            'fecha' => $c->fecha,
                            'item' => $c->nombre,
                            'valor' => '$ ' . \number_format($c->valor, 2, ",", "."),
                            'total_gastos' => number_format($total_gastos, 2, ",", "."));

                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'count' => $parcialRegistros,
                    'sql' => $parcialRegistros,
                    'data' => $myArray];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListReporteDetalleUtilidadGD(Request $request){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = "gastos_diarios.".$columna[$sortColumnIndex]['data'];
            if($orderBy == "gastos_diarios.usuario")$orderBy = "usuarios.nombres";
            $total_compras = 0;
            $total_facturas = 0;
            $total_gastos = 0;
            $orderBy == 'costos_fijos.id';

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $gastos_diarios = GastoDiario::permitidos()->select("gastos_diarios.*", DB::raw('CONCAT(usuarios.nombres," ",usuarios.apellidos) as usuario'))
                    ->join("usuarios", "gastos_diarios.usuario_creador_id", "=", "usuarios.id")
                    ->whereBetween("gastos_diarios.created_at", [$request->input("fecha_inicio"), $fecha_fin]);

                if(Auth::user()->bodegas == 'si') {
                    if (Auth::user()->admin_bodegas == 'no') {
                        $almacen = Auth::user()->almacenActual()->id;
                        $gastos_diarios = $gastos_diarios->where('gastos_diarios.almacen_id',$almacen);
                    }
                }
                $gastos_diarios = $gastos_diarios->orderBy($orderBy, $sortColumnDir);
                $totalRegistros = $gastos_diarios->count();
                $parcialRegistros = $gastos_diarios->count();
                $gastos_diarios = $gastos_diarios->skip($start)->take($length);
                $object = new \stdClass();
                $myArray = [];
                if ($parcialRegistros > 0) {
                    foreach ($gastos_diarios->get() as $gd) {
                        $total_gastos += $gd->valor;
                        $myArray[] = (object)array('id' => $gd->id,
                            'created_at' => (string)$gd->created_at,
                            'descripcion' => $gd->descripcion,
                            'usuario' => $gd->usuario,
                            'valor' => '$ ' . \number_format($gd->valor, 2, ",", "."),
                            'total_gastos_diarios' => number_format($total_gastos, 2, ",", "."));

                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'count' => $parcialRegistros,
                    'sql' => $parcialRegistros,
                    'data' => $myArray];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }


    public function getExcelPerdidasGanancias(Request $request){
        $r = Reporte::where('nombre','Perdidas y ganancias')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte perdidas y ganancias";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

                $gastos_diarios = GastoDiario::permitidos()->select("gastos_diarios.*", DB::raw('CONCAT(usuarios.nombres," ",usuarios.apellidos) as usuario'))
                    ->join("usuarios", "gastos_diarios.usuario_creador_id", "=", "usuarios.id")
                    ->whereBetween("gastos_diarios.created_at", [$request->input("fecha_inicio"), $fecha_fin]);

                if(Auth::user()->bodegas == 'no') {
                    $facturas = Factura::permitidos()->select('facturas.*', 'token_puntos.valor')
                            ->leftJoin('token_puntos', 'facturas.id', '=', 'token_puntos.factura_id')
                            ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin]) ->where("facturas.estado", "<>", "anulada")->get();
                    $compras = Compra::permitidos()->whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("estado", "<>", "Cancelada")->get();
                    $costosFijos = CostoFijo::permitidos()->select("costos_fijos.*", "pagos_costos_fijos.valor", "pagos_costos_fijos.fecha")
                        ->join("pagos_costos_fijos", "costos_fijos.id", "=", "pagos_costos_fijos.costo_fijo_id")
                        ->whereBetween("pagos_costos_fijos.fecha", [$request->input("fecha_inicio"), $fecha_fin])->get();
                }else{
                    if(Auth::user()->admin_bodegas == 'no'){
                        $almacen = Auth::user()->almacenActual()->id;
                        $gastos_diarios = $gastos_diarios->where('gastos_diarios.almacen_id',$almacen);
                    }else{
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$almacen = $alm_obj->id;
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }
                    $facturas = ABFactura::permitidos()->select('facturas.*')
                        ->leftJoin('vendiendo.token_puntos', 'facturas.id', '=', 'vendiendo.token_puntos.factura_id')
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("facturas.estado", "<>", "anulada");
                    if($almacen)$facturas = $facturas->where('almacen_id',$almacen);
                    $facturas = $facturas->get();
                    $compras = ABCompra::permitidos()->whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("estado", "<>", "Cancelada");

                    //if($almacen)$compras = $compras->where('almacen_id',$almacen);
                    $compras = $compras->get();

                    $costosFijos = CostoFijo::permitidos()->select("costos_fijos.*", "pagos_costos_fijos.valor", "pagos_costos_fijos.fecha")
                        ->join("pagos_costos_fijos", "costos_fijos.id", "=", "pagos_costos_fijos.costo_fijo_id")
                        ->whereBetween("pagos_costos_fijos.fecha", [$request->input("fecha_inicio"), $fecha_fin]);

                    if($almacen)$costosFijos = $costosFijos->where('almacen_id',$almacen);
                    $costosFijos = $costosFijos->get();
                }

                $gastos_diarios = $gastos_diarios->get();

                return Excel::create($nombre, function ($excel) use ($facturas, $compras, $costosFijos,$gastos_diarios, $fecha_fin, $request) {
                    $excel->sheet('Lista de facturas', function ($sheet) use ($facturas, $compras, $costosFijos, $gastos_diarios, $fecha_fin, $request) {
                        if(Auth::user()->bodegas == 'no') {
                            $totalFacturas = Factura::totalVentasSinIva($request->input("fecha_inicio"), $fecha_fin);
                            $totalCompras = Factura::totalPrecioCosto($request->input("fecha_inicio"), $fecha_fin);
                            $totalCostosFijos = CostoFijo::totalPagos($request->input("fecha_inicio"), $fecha_fin);
                            $totalGastosDiarios = GastoDiario::totalPagos($request->input("fecha_inicio"), $fecha_fin);
                        }else{
                            $almacen_ = false;
                            if(Auth::user()->admin_bodegas == 'no'){
                                $almacen_ = Auth::user()->almacenActual()->id;
                            }else{
                                if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                                    $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                                    if($alm_obj)$almacen_ = $alm_obj->id;
                                    else return response(['error'=>['la información enviada es incorrecta']],422);
                                }
                            }
                            $totalFacturas = ABFactura::totalVentasSinIva($request->input("fecha_inicio"), $fecha_fin,$almacen_);
                            $totalCompras = ABFactura::totalPrecioCosto($request->input("fecha_inicio"), $fecha_fin,$almacen_);
                            $totalCostosFijos = CostoFijo::totalPagos($request->input("fecha_inicio"), $fecha_fin,$almacen_);
                            $totalGastosDiarios = GastoDiario::totalPagos($request->input("fecha_inicio"), $fecha_fin, $almacen_);
                        }
                        $sheet->loadView('reportes.utilidades.lista_detalle_excel', ["facturas" => $facturas, "compras" => $compras, "costosFijos" => $costosFijos,"gastosDiarios"=>$gastos_diarios, "reporte" => true, "totalFacturas" => $totalFacturas, "totalCompras" => $totalCompras, "totalCostosFijos" => $totalCostosFijos,"totalGastosDiarios"=>$totalGastosDiarios, "fecha_inicio" => $request->input("fecha_inicio"), "fecha_fin" => $fecha_fin]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getVentas(){
        $r = Reporte::where('nombre','Ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.ventas.index');
        return redirect('/');
    }
    //public function postListVentas(Request $request){
    //	if($request->has("fecha_inicio") && $request->has("fecha_fin")){
    //		$fecha_fin = date("Y-m-d",strtotime("+1days",strtotime($request->input("fecha_fin"))));
    //		$ventas = Factura::ventas($request->input("fecha_inicio"),$fecha_fin,10);
    //		//dd($utilidades);
    //		return view("reportes.ventas.lista")->with("ventas",$ventas);
    //	}else{
    //		return response(["La información enviada es incorrecta"],422);
    //	}
    //}

    public function getListReporteVentas(Request $request){
        $r = Reporte::where('nombre','Ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            // Datos de DATATABLE
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            if ($orderBy == "created_at")
                $orderBy = "facturas.created_at";
            if ($orderBy == "almacen")
                $orderBy = "facturas.created_at";

            $totalIva = 0;
            $totalSubtotal = 0;
            $totalDescuento = 0;

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si')
                    $ventas = ABFactura::select("facturas.*", "clientes.nombre", DB::RAW("facturas.subtotal+facturas.iva AS total"));
                else
                    $ventas = Factura::select("facturas.*", "clientes.nombre", DB::RAW("facturas.subtotal+facturas.iva AS total"));


                $ventas = $ventas->leftJoin("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id")
                    ->where("facturas.usuario_id", Auth::user()->userAdminId())
                    ->where(function ($q) {
                        $q->where("facturas.estado", "Pagada")
                            ->orWhere("facturas.estado", "Pendiente por pagar")
                            ->orWhere("facturas.estado", "cerrada")
                            ->orWhere("facturas.estado", "abierta");
                    })
                    ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin]);

                if(Auth::user()->bodegas == 'si')
                    $totales = ABFactura::select(DB::RAW("SUM(facturas.subtotal) as totalSubtotal"), DB::RAW("SUM(facturas.iva) as totalIva"), DB::RAW("SUM(facturas.descuento) as totalDescuento"));
                else
                    $totales = Factura::select(DB::RAW("SUM(facturas.subtotal) as totalSubtotal"), DB::RAW("SUM(facturas.iva) as totalIva"), DB::RAW("SUM(facturas.descuento) as totalDescuento"));

                $totales = $totales->leftJoin("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id")
                    ->where("facturas.usuario_id", Auth::user()->userAdminId())
                    ->where(function ($q) {
                        $q->where("facturas.estado", "Pagada")->orWhere("facturas.estado", "Pendiente por pagar")->orWhere("facturas.estado", "cerrada")->orWhere("facturas.estado", "abierta");
                    })
                    ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin]);

                if(Auth::user()->bodegas == 'si') {
                    $almacen = '';
                    if (Auth::user()->admin_bodegas == 'si') {
                        if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                            $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                            if ($alm_obj) {
                                $almacen = $alm_obj->id;
                                $totales = $totales->where('almacen_id', $almacen);
                                $ventas = $ventas->where('almacen_id', $almacen);
                            } else return response(['error' => ['la información enviada es incorrecta']], 422);
                        }
                    } else {
                        $almacen = Auth::user()->almacenActual()->id;
                        $totales = $totales->where('almacen_id', $almacen);
                        $ventas = $ventas->where('almacen_id', $almacen);
                    }
                }

                $totales = $totales->first();

                if ($totales != null) {
                    $totalIva = $totales->totalIva;
                    $totalSubtotal = $totales->totalSubtotal;
                    $totalDescuento = $totales->totalDescuento;
                }

                $ventas = $ventas->orderBy($orderBy, $sortColumnDir);
                $totalRegistros = $ventas->count();
                //BUSCAR
                if ($search['value'] != null) {
                    $ventas = $ventas->whereRaw(
                        " ( numero LIKE '%" . $search["value"] . "%' OR " .
                        " facturas.created_at LIKE '%" . $search["value"] . "%' OR" .
                        " LOWER(nombre) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " subtotal LIKE '%" . $search["value"] . "%' OR" .
                        " iva LIKE '%" . $search["value"] . "%' OR" .
                        " facturas.subtotal+facturas.iva LIKE '%" . $search["value"] . "%' )");
                }

                $parcialRegistros = $ventas->count();
                $ventas = $ventas->skip($start)->take($length);

                $object = new \stdClass();
                $myArray = [];
                if ($parcialRegistros > 0) {
                    foreach ($ventas->get() as $value) {
                        $almacen = '';
                        if(Auth::user()->bodegas == 'si')
                            $almacen = Almacen::permitidos()->where('id',$value->almacen_id)->first()->nombre;

                        $myArray[] = (object)array(
                            'almacen'=>$almacen,
                            'numero' => $value->numero,
                            'created_at' => (String)$value->created_at,
                            'nombre' => \App\TildeHtml::TildesToHtml($value->nombre),
                            'subtotal' => '$' . number_format($value->subtotal, 2, ",", "."),
                            'iva' => '$' . number_format($value->iva, 2, ",", "."),
                            'descuento' => '$' . number_format($value->descuento, 2, ",", "."),
                            'total' => '$' . number_format($value->subtotal + $value->iva - $value->descuento, 2, ",", "."),
                            'totalSubtotal' => '$' . number_format($totalSubtotal, 2, ",", "."),
                            'totalIva' => '$' . number_format($totalIva, 2, ",", "."),
                            'totalDescuento' => '$' . number_format($totalDescuento, 2, ",", "."),
                            'totalFinal' => '$' . number_format(($totalSubtotal + $totalIva - $totalDescuento), 2, ",", "."),
                        );
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $ventas->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'fecha_inicio' => $request->get("fecha_inicio"),
                    'count' => $parcialRegistros,
                    'data' => $myArray];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }


    public function getExcelVentas(Request $request){
        $r = Reporte::where('nombre','Ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte ventas";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si')
                    $ventas = ABFactura::select("facturas.*", "clientes.nombre", DB::RAW("facturas.subtotal+facturas.iva AS total"));
                else
                    $ventas = Factura::select("facturas.*", "clientes.nombre", DB::RAW("facturas.subtotal+facturas.iva AS total"));

                $ventas = $ventas->leftJoin("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id")
                    ->where("facturas.usuario_id", Auth::user()->userAdminId())
                    ->where(function ($q) {
                        $q->where("facturas.estado", "Pagada")
                            ->orWhere("facturas.estado", "Pendiente por pagar")
                            ->orWhere("facturas.estado", "cerrada")
                            ->orWhere("facturas.estado", "abierta");
                    })
                    ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin]);

                $almacen = '';
                if(Auth::user()->admin_bodegas == 'si'){
                    if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                        $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                        if($alm_obj){
                            $almacen = $alm_obj->id;
                            $ventas = $ventas->where('almacen_id',$almacen);
                        }
                        else return response(['error'=>['la información enviada es incorrecta']],422);
                    }
                }else{
                    $almacen = Auth::user()->almacenActual()->id;
                    $ventas = $ventas->where('almacen_id',$almacen);
                }

                $ventas = $ventas->get();

                return Excel::create($nombre, function ($excel) use ($ventas) {
                    $excel->sheet('Lista de ventas', function ($sheet) use ($ventas) {
                        $sheet->loadView('reportes.ventas.lista', ["ventas" => $ventas, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getCierreCaja(){
        $r = Reporte::where('nombre','Cierre de caja')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.cierre_caja.index');
        return redirect('/');
    }

    public function postListCierreCaja(Request $request){
        $r = Reporte::where('nombre','Cierre de caja')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $cajas = Caja::whereBetween("fecha", [$request->input("fecha_inicio"), $fecha_fin])
                    ->where("usuario_id", Auth::user()->userAdminId())
                    ->orderby('fecha', 'DESC')
                    ->paginate(env('PAGINATE'));
                return view("reportes.cierre_caja.lista")->with("cajas", $cajas);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListCierreCaja(Request $request){
        $r = Reporte::where('nombre','Cierre de caja')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $cajas = Caja::whereBetween("fecha", [$request->input("fecha_inicio"), $fecha_fin])
                    ->where("usuario_id", Auth::user()->userAdminId())
                    ->orderby('fecha', 'DESC')
                    //->skip(10)->take(5);
                    ->paginate(env('PAGINATE'));
                return view("reportes.cierre_caja.lista")->with("cajas", $cajas);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListCierreCajas(Request $request){
        $r = Reporte::where('nombre','Cierre de caja')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            // Datos de DATATABLE
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $cajas = Caja::where("caja.estado", "cerrada ")->whereBetween("fecha", [$request->get("fecha_inicio"), $fecha_fin])->where("usuario_id", Auth::user()->userAdminId())/*->orderby('fecha', 'DESC')*/
                ;

                $cajas = $cajas->orderBy($orderBy, $sortColumnDir);
                $totalRegistros = $cajas->count();
                //BUSCAR
                if ($search['value'] != null) {
                    $cajas = $cajas->whereRaw(
                        " ( fecha LIKE '%" . $search["value"] . "%' OR " .
                        " id LIKE '%" . $search["value"] . "%' OR" .
                        " efectivo_inicial LIKE '%" . $search["value"] . "%' OR" .
                        " efectivo_final LIKE '%" . $search["value"] . "%' )");
                }

                $parcialRegistros = $cajas->count();
                $cajas = $cajas->skip($start)->take($length);


                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    foreach ($cajas->get() as $value) {
                        $efectivo_facturas = \App\Models\Factura::getEfectivoFacturasByCajaMaestra($value->id);
                        $descuentos_facturas = \App\Models\Factura::getDescuentoFacturasByCajaMaestra($value->id);
                        $ventas_credito = \App\Models\Factura::getValorFacturasCreditoByCajaMaestra($value->id);
                        $valor_puntos_redimidos = \App\Models\Factura::getPuntosFacturasByCajaMaestra($value->id);
                        $valor_medios_pago = \App\Models\Factura::getValorMediosPagoFacturasByCajaMaestra($value->id);


                        $compras_efectivo = \App\Models\Compra::getEfectivoByCajaMaestra($value->id);
                        $compras_credito = \App\Models\Compra::getCreditoByCajaMaestra($value->id);
                        $compras_devoluciones_efectivo = \App\Models\Compra::getEfectivoCuentasPorCobrarByCajaMaestra($value->id);

                        $abonos_clientes =  Abono::getEfectivoFacturasByCajaMaestra($value->id);
                        $abonos_proveedores =  Abono::getEfectivoComprasByCajaMaestra($value->id);

                        $gastos_diarios = \App\Models\GastoDiario::getValorByCajaMaestra($value->id);
                        $costos_fijos = \App\Models\CostoFijo::getValorByCajaMaestra($value->id);

                        $retiros = $value->valorRetiros();
                        $consignaciones = $value->valorConsignaciones();

                        $efectivo_total = ($value->efectivo_inicial+$efectivo_facturas+$abonos_clientes+$retiros+$compras_devoluciones_efectivo)
                            -(/*$descuentos_facturas+$valor_puntos_redimidos+*/$compras_efectivo+$abonos_proveedores+$gastos_diarios+$costos_fijos+$consignaciones);

                        $myArray[] = (object)array('id' => $value->id,
                            /*'fecha' => $value->fecha,
                            'efectivo_inicial' => "$ " . number_format($value->efectivo_inicial, 2, ',', '.'),
                            'efectivo_final' => "$ " . number_format($value->efectivo_final, 2, ',', '.'),
                            'efectivo_facturas' => '$ ' . number_format($efectivo_facturas, 2, ',', '.'),
                            'valor_puntos_redimidos' => '$ ' . number_format($valor_puntos_redimidos, 2, ',', '.'),
                            'retiros' => '$ ' . number_format($retiros, 2, ',', '.'),
                            'valor_medios_pago' => '$ ' . number_format($valor_medios_pago, 2, ',', '.'),
                            'total_ingresos' => '$ ' . number_format($total_ingresos, 2, ',', '.'),

                            'compras_realizadas' => '$ ' . number_format($compras_realizadas, 2, ',', '.'),
                            'gastos_diarios' => '$ ' . number_format($gastos_diarios, 2, ',', '.'),
                            'costos_fijos' => '$ ' . number_format($costos_fijos, 2, ',', '.'),
                            'valor_medios_pago_no_caja' => '$ ' . number_format($valor_medios_pago_no_caja, 2, ',', '.'),
                            'consignaciones' => '$ ' . number_format($consignaciones, 2, ',', '.'),
                            'total_egresos' => '$ ' . number_format($total_egresos, 2, ',', '.'));*/
                            'fecha'=>$value->fecha,
                            'saldo_efectivo'=>'$ ' . number_format($value->efectivo_inicial, 2, ',', '.'),
                            'space_1'=>'',
                            //VENTAS
                            'space_2'=>'',
                            'ventas_efectivo'=>'$ ' . number_format($efectivo_facturas, 2, ',', '.'),
                            'ventas_descuento'=>'$ ' . number_format($descuentos_facturas, 2, ',', '.'),
                            'ventas_medios_pago'=>'$ ' . number_format($valor_medios_pago, 2, ',', '.'),
                            'ventas_credito'=>'$ ' . number_format($ventas_credito, 2, ',', '.'),
                            'puntos'=>'$ ' . number_format($valor_puntos_redimidos, 2, ',', '.'),
                            'total_ventas'=>'$ ' . number_format(($efectivo_facturas + $valor_medios_pago + $ventas_credito), 2, ',', '.'),
                            'space_3'=>'',
                            //COMPRAS
                            'space_4'=>'',
                            'compras_efectivo'=>'$ ' . number_format($compras_efectivo, 2, ',', '.'),
                            'compras_credito'=>'$ ' . number_format($compras_credito, 2, ',', '.'),
                            'total_compras'=>'$ ' . number_format($compras_credito+$compras_efectivo, 2, ',', '.'),
                            'compras_devolucion_efectivo'=>'$ ' . number_format($compras_devoluciones_efectivo, 2, ',', '.'),
                            'space_5'=>'',
                            //ABONOS
                            'space_6'=>'',
                            'abonos_clientes'=>'$ ' . number_format($abonos_clientes, 2, ',', '.'),
                            'abonos_proveedores'=>'$ ' . number_format($abonos_proveedores, 2, ',', '.'),
                            'total_abonos'=>'$ ' . number_format($abonos_clientes+$abonos_proveedores, 2, ',', '.'),
                            'space_7'=>'',
                            //COSTOS
                            'space_8'=>'',
                            'gastos_diarios'=>'$ ' . number_format($gastos_diarios, 2, ',', '.'),
                            'costos_fijos'=>'$ ' . number_format($costos_fijos, 2, ',', '.'),
                            'total_costos'=>'$ ' . number_format($gastos_diarios+$costos_fijos, 2, ',', '.'),
                            'space_9'=>'',
                            //CONSIGNACIONES
                            'space_10'=>'',
                            'consignaciones_banco'=>'$ ' . number_format($consignaciones, 2, ',', '.'),
                            'consignaciones_caja'=>'$ ' . number_format($retiros, 2, ',', '.'),
                            'space_11'=>'',
                            //CIERRE TOTAL CAJA GENERAL
                            'space_12'=>'',
                            'efectivo'=>'$ ' . number_format($efectivo_total, 2, ',', '.'),
                            'otros_medios_pago'=>'$ ' . number_format($valor_medios_pago, 2, ',', '.'),
                            'totales'=>'$ ' . number_format($efectivo_total+$valor_medios_pago, 2, ',', '.'));
                    }
                } else {
                    $myArray = [];
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $cajas->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'fecha_inicio' => $request->get("fecha_inicio"),
                    'count' => $parcialRegistros,
                    'data' => $myArray,//->where('id','"',22)->get(),
                    'info' => $cajas->get()];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }


    public function getExcelCierreCaja(Request $request){
        $r = Reporte::where('nombre','Cierre de caja')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte cierre caja";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $cajas = Caja::where("caja.estado", "cerrada")->whereBetween("fecha", [$request->input("fecha_inicio"), $fecha_fin])
                    ->orderby('fecha', 'DESC')
                    ->where("usuario_id", Auth::user()->userAdminId())
                    ->get();

                $myArray = [];
                foreach ($cajas as $value) {
                    $efectivo_facturas = \App\Models\Factura::getEfectivoFacturasByCajaMaestra($value->id);
                    $descuentos_facturas = \App\Models\Factura::getDescuentoFacturasByCajaMaestra($value->id);
                    $ventas_credito = \App\Models\Factura::getValorFacturasCreditoByCajaMaestra($value->id);
                    $valor_puntos_redimidos = \App\Models\Factura::getPuntosFacturasByCajaMaestra($value->id);
                    $valor_medios_pago = \App\Models\Factura::getValorMediosPagoFacturasByCajaMaestra($value->id);


                    $compras_efectivo = \App\Models\Compra::getEfectivoByCajaMaestra($value->id);
                    $compras_credito = \App\Models\Compra::getCreditoByCajaMaestra($value->id);
                    $compras_devoluciones_efectivo = \App\Models\Compra::getEfectivoCuentasPorCobrarByCajaMaestra($value->id);

                    $abonos_clientes =  Abono::getEfectivoFacturasByCajaMaestra($value->id);
                    $abonos_proveedores =  Abono::getEfectivoComprasByCajaMaestra($value->id);

                    $gastos_diarios = \App\Models\GastoDiario::getValorByCajaMaestra($value->id);
                    $costos_fijos = \App\Models\CostoFijo::getValorByCajaMaestra($value->id);

                    $retiros = $value->valorRetiros();
                    $consignaciones = $value->valorConsignaciones();

                    $efectivo_total = ($value->efectivo_inicial+$efectivo_facturas+$abonos_clientes+$retiros+$compras_devoluciones_efectivo)
                        -(/*$descuentos_facturas+$valor_puntos_redimidos+*/$compras_efectivo+$abonos_proveedores+$gastos_diarios+$costos_fijos+$consignaciones);

                    $myArray[] = (object)array('id' => $value->id,
                        /*'fecha' => $value->fecha,
                        'efectivo_inicial' => "$ " . number_format($value->efectivo_inicial, 2, ',', '.'),
                        'efectivo_final' => "$ " . number_format($value->efectivo_final, 2, ',', '.'),
                        'efectivo_facturas' => '$ ' . number_format($efectivo_facturas, 2, ',', '.'),
                        'valor_puntos_redimidos' => '$ ' . number_format($valor_puntos_redimidos, 2, ',', '.'),
                        'retiros' => '$ ' . number_format($retiros, 2, ',', '.'),
                        'valor_medios_pago' => '$ ' . number_format($valor_medios_pago, 2, ',', '.'),
                        'total_ingresos' => '$ ' . number_format($total_ingresos, 2, ',', '.'),

                        'compras_realizadas' => '$ ' . number_format($compras_realizadas, 2, ',', '.'),
                        'gastos_diarios' => '$ ' . number_format($gastos_diarios, 2, ',', '.'),
                        'costos_fijos' => '$ ' . number_format($costos_fijos, 2, ',', '.'),
                        'valor_medios_pago_no_caja' => '$ ' . number_format($valor_medios_pago_no_caja, 2, ',', '.'),
                        'consignaciones' => '$ ' . number_format($consignaciones, 2, ',', '.'),
                        'total_egresos' => '$ ' . number_format($total_egresos, 2, ',', '.'));*/
                        'fecha'=>$value->fecha,
                        'saldo_efectivo'=>'$ ' . number_format($value->efectivo_inicial, 2, ',', '.'),
                        'space_1'=>'',
                        //VENTAS
                        'space_2'=>'',
                        'ventas_efectivo'=>'$ ' . number_format($efectivo_facturas, 2, ',', '.'),
                        'ventas_descuento'=>'$ ' . number_format($descuentos_facturas, 2, ',', '.'),
                        'ventas_medios_pago'=>'$ ' . number_format($valor_medios_pago, 2, ',', '.'),
                        'ventas_credito'=>'$ ' . number_format($ventas_credito, 2, ',', '.'),
                        'puntos'=>'$ ' . number_format($valor_puntos_redimidos, 2, ',', '.'),
                        'total_ventas'=>'$ ' . number_format(($efectivo_facturas + $valor_medios_pago + $ventas_credito), 2, ',', '.'),
                        'space_3'=>'',
                        //COMPRAS
                        'space_4'=>'',
                        'compras_efectivo'=>'$ ' . number_format($compras_efectivo, 2, ',', '.'),
                        'compras_credito'=>'$ ' . number_format($compras_credito, 2, ',', '.'),
                        'total_compras'=>'$ ' . number_format($compras_credito+$compras_efectivo, 2, ',', '.'),
                        'compras_devolucion_efectivo'=>'$ ' . number_format($compras_devoluciones_efectivo, 2, ',', '.'),
                        'space_5'=>'',
                        //ABONOS
                        'space_6'=>'',
                        'abonos_clientes'=>'$ ' . number_format($abonos_clientes, 2, ',', '.'),
                        'abonos_proveedores'=>'$ ' . number_format($abonos_proveedores, 2, ',', '.'),
                        'total_abonos'=>'$ ' . number_format($abonos_clientes+$abonos_proveedores, 2, ',', '.'),
                        'space_7'=>'',
                        //COSTOS
                        'space_8'=>'',
                        'gastos_diarios'=>'$ ' . number_format($gastos_diarios, 2, ',', '.'),
                        'costos_fijos'=>'$ ' . number_format($costos_fijos, 2, ',', '.'),
                        'total_costos'=>'$ ' . number_format($gastos_diarios+$costos_fijos, 2, ',', '.'),
                        'space_9'=>'',
                        //CONSIGNACIONES
                        'space_10'=>'',
                        'consignaciones_banco'=>'$ ' . number_format($consignaciones, 2, ',', '.'),
                        'consignaciones_caja'=>'$ ' . number_format($retiros, 2, ',', '.'),
                        'space_11'=>'',
                        //CIERRE TOTAL CAJA GENERAL
                        'space_12'=>'',
                        'efectivo'=>'$ ' . number_format($efectivo_total, 2, ',', '.'),
                        'otros_medios_pago'=>'$ ' . number_format($valor_medios_pago, 2, ',', '.'),
                        'totales'=>'$ ' . number_format($efectivo_total+$valor_medios_pago, 2, ',', '.'));
                }
                return Excel::create($nombre, function ($excel) use ($myArray) {
                    $excel->sheet('Lista de cierres de caja', function ($sheet) use ($myArray) {
                        $sheet->loadView('reportes.cierre_caja.lista', ["cajas" => $myArray, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getFactura(){
        $r = Reporte::where('nombre','Facturas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.facturas.index');
        return redirect('/');
    }

    public function postListFactura(Request $request){
        $r = Reporte::where('nombre','Facturas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("estado")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

                if(Auth::user()->bodegas == 'si') {
                    $facturas = ABFactura::whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("usuario_id", Auth::user()->userAdminId());
                }else{
                    $facturas = Factura::whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("usuario_id", Auth::user()->userAdminId());
                }

                if(Auth::user()->bodegas == 'si') {
                    if (Auth::user()->admin_bodegas == 'si') {
                        if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                            $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                            if ($alm_obj) {
                                $facturas = $facturas->where('almacen_id', $alm_obj->id);
                            }
                        }
                    } else {
                        $almacen = Auth::user()->almacenActual()->id;
                        $facturas = $facturas->where('almacen_id', $almacen);
                    }
                }


                if ($request->input("estado") != "Todas") {
                    $facturas = $facturas->where("estado", $request->input("estado"));
                }
                $facturas = $facturas->paginate(env('PAGINATE'));
                return view("reportes.facturas.lista");//->with("facturas",$facturas);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListReporteFacturas(Request $request){
        $r = Reporte::where('nombre','Facturas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $full_subtotal = 0;
            $full_iva = 0;
            $full_facturas = 0;
            $full_descuentos = 0;
            $full_valor_puntos = 0;


            $myArray = [];
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("estado")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si')
                    $facturas = ABFactura::whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("usuario_id", Auth::user()->userAdminId());
                else
                    $facturas = Factura::whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("usuario_id", Auth::user()->userAdminId());

                if(Auth::user()->bodegas == 'si') {
                    if (Auth::user()->admin_bodegas == 'si') {
                        if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                            $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                            if ($alm_obj) {
                                $facturas = $facturas->where('almacen_id', $alm_obj->id);
                            }
                        }
                    } else {
                        $almacen = Auth::user()->almacenActual()->id;
                        $facturas = $facturas->where('almacen_id', $almacen);
                    }
                }

                if ($request->input("estado") != "Todas") {
                    $facturas = $facturas->where("estado", $request->input("estado"));
                }
                $totalRegistros = $facturas->get()->count();
                //BUSCAR
                if ($search['value'] != null) {

                    $facturas = $facturas->whereRaw(
                        " ( LOWER(numero) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(estado) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " subtotal LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " iva LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " subtotal+iva LIKE '%" . $search["value"] . "%' )");
                }

                $parcialRegistros = $facturas->get()->count();
                $facturas = $facturas->skip($start)->take($length);
                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    if(Auth::user()->bodegas == 'si') {
                        $totales = ABFactura::leftJoin("vendiendo.token_puntos", "facturas.id", "=", "vendiendo.token_puntos.factura_id")
                            ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("facturas.usuario_id", Auth::user()->userAdminId())
                            ->select(DB::RAW('SUM(subtotal) AS total_sub_total'),
                                DB::RAW('ROUND(SUM(iva), 2) AS total_iva'),
                                DB::RAW('ROUND(SUM(descuento), 2) AS total_descuento'),
                                DB::RAW('ROUND(SUM(vendiendo.token_puntos.valor), 2) AS total_valor_puntos'),
                                DB::RAW('SUM(subtotal+iva) AS total'));

                        if(Auth::user()->bodegas == 'si') {
                            if (Auth::user()->admin_bodegas == 'si') {
                                if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                                    $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                                    if ($alm_obj) {
                                        $totales = $totales->where('almacen_id', $alm_obj->id);
                                    }
                                }
                            } else {
                                $almacen = Auth::user()->almacenActual()->id;
                                $totales = $totales->where('almacen_id', $almacen);
                            }
                        }
                        $totales = $totales->first();
                    }else{
                        $totales = Factura::leftJoin("token_puntos", "facturas.id", "=", "token_puntos.factura_id")
                            ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("facturas.usuario_id", Auth::user()->userAdminId())
                            ->select(DB::RAW('SUM(subtotal) AS total_sub_total'),
                                DB::RAW('ROUND(SUM(iva), 2) AS total_iva'),
                                DB::RAW('ROUND(SUM(descuento), 2) AS total_descuento'),
                                DB::RAW('ROUND(SUM(token_puntos.valor), 2) AS total_valor_puntos'),
                                DB::RAW('SUM(subtotal+iva) AS total'))->first();
                    }
                    if ($totales != null) {
                        $full_subtotal = $totales->total_sub_total;
                        $full_iva = $totales->total_iva;
                        $full_facturas = $totales->total;
                        $full_descuentos = $totales->total_descuento;
                        $full_valor_puntos = $totales->total_valor_puntos;
                        $full_valor_medios_pago = 0;
                        $full_efectivo = 0;
                    }
                    foreach ($facturas->get() as $factura) {
                        $valor_puntos = 0;
                        $valor_medios_pago = $factura->getValorMediosPago();

                        $token_puntos = TokenPuntos::where("factura_id", $factura->id)->first();
                        if ($token_puntos) $valor_puntos = $token_puntos->valor;

                        $efectivo = ($factura->subtotal + $factura->iva) - ($valor_puntos + $valor_medios_pago + $factura->descuento);

                        $full_valor_medios_pago += $valor_medios_pago;
                        $full_efectivo += $efectivo;

                        $myArray[] = (object)array(
                            'numero' => $factura->numero,
                            'estado' => $factura->estado,
                            'subtotal' => "$" . number_format($factura->subtotal, 2, ',', '.'),
                            'iva' => "$" . number_format($factura->iva, 2, ',', '.'),
                            'descuento' => "$" . number_format($factura->descuento, 2, ',', '.'),
                            'puntos' => "$" . number_format($valor_puntos, 2, ',', '.'),
                            'total' => "$" . number_format(($factura->iva + $factura->subtotal), 2, ',', '.'),
                            'valor_medios_pago' => "$" . number_format(($valor_medios_pago), 2, ',', '.'),
                            'efectivo' => "$" . number_format(($efectivo), 2, ',', '.'),
                            'data_t_subtotal' => "$" . number_format($full_subtotal, 2, ',', '.'),
                            'data_t_iva' => "$" . number_format($full_iva, 2, ',', '.'),
                            'data_t_descuento' => "$" . number_format($full_descuentos, 2, ',', '.'),
                            'data_t_valor_puntos' => "$" . number_format($full_valor_puntos, 2, ',', '.'),
                            'data_t_efectivo' => "$" . number_format($full_valor_puntos, 2, ',', '.'),
                            'data_t_efectivo' => "$" . number_format($full_efectivo, 2, ',', '.'),
                            'data_t_valor_medios_pago' => "$" . number_format($full_valor_medios_pago, 2, ',', '.'),
                            'data_t_facturas' => "$" . number_format($full_facturas, 2, ',', '.')
                        );
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    'last_query' => $facturas->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'data' => $myArray,
                    'info' => $facturas->get()];
            }
            return response()->json($data);
        }
    }

    public function getExcelFactura(Request $request){
        $r = Reporte::where('nombre','Facturas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte facturas";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("estado")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si') {
                    $facturas = ABFactura::whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("usuario_id", Auth::user()->userAdminId());
                }else{
                    $facturas = Factura::whereBetween("created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("usuario_id", Auth::user()->userAdminId());
                }

                if(Auth::user()->bodegas == 'si') {
                    if (Auth::user()->admin_bodegas == 'si') {
                        if ($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0) {
                            $alm_obj = Almacen::permitidos()->where('id', $request->input('almacen'))->first();
                            if ($alm_obj) {
                                $facturas = $facturas->where('almacen_id', $alm_obj->id);
                            }
                        }
                    } else {
                        $almacen = Auth::user()->almacenActual()->id;
                        $facturas = $facturas->where('almacen_id', $almacen);
                    }
                }

                if ($request->input("estado") != "Todas") {
                    $facturas = $facturas->where("estado", $request->input("estado"));
                }
                $facturas = $facturas->get();
                return Excel::create($nombre, function ($excel) use ($facturas) {
                    $excel->sheet('Lista facturas', function ($sheet) use ($facturas) {
                        $sheet->loadView('reportes.facturas.listaExcel', ["facturas" => $facturas, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getAbonos(){
        $r = Reporte::where('nombre','Abonos')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.abonos.index');
        return redirect('/');
    }

    public function postListAbonos(Request $request){
        $r = Reporte::where('nombre','Abonos')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("tipo")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $elementos = null;

                if ($request->input("tipo") == "factura") {
                    $elementos = Factura::permitidos()->select("facturas.*")->join("clientes", "facturas.cliente_id", "=", "clientes.id");
                    if ($request->has("nombre_cliente")) {
                        $elementos = $elementos->where("clientes.nombre", "like", "%" . $request->input("nombre_cliente") . "%");
                    }

                    if ($request->has("identificacion_cliente")) {
                        $elementos = $elementos->where("clientes.identificaion", "like", "%" . $request->input("identificacion_cliente") . "%");
                    }
                    $elementos = $elementos->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->whereNotNull("facturas.numero_cuotas");
                } else if ($request->input("tipo") == "compra") {
                    $elementos = Compra::permitidos()->select("compras.*")->join("proveedores", "compras.proveedor_id", "=", "proveedores.id");

                    if ($request->has("nombre_proveedor")) {
                        $elementos = $elementos->where("proveedores.nombre", "like", "%" . $request->input("nombre_proveedor") . "%");
                    }

                    if ($request->has("identificacion_proveedor")) {
                        $elementos = $elementos->where("proveedores.nit", "like", "%" . $request->input("identificacion_proveedor") . "%");
                    }
                    $elementos = $elementos->whereBetween("compras.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->whereNotNull("numero_cuotas");
                }
                $elementos = $elementos->paginate(env('PAGINATE'));
                return view("reportes.abonos.lista")->with("elementos", $elementos);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListReporteAbono(Request $request){
        $r = Reporte::where('nombre','Abonos')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $myArray = [];
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("tipo")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $elementos = null;

                if ($request->input("tipo") == "factura") {
                    if(Auth::user()->bodegas == 'si')
                        $elementos = ABFactura::permitidos()->select("facturas.*")->join("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id");
                    else
                        $elementos = Factura::permitidos()->select("facturas.*")->join("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id");

                    if(Auth::user()->bodegas == 'si'){
                        if(Auth::user()->admin_bodegas == 'si'){
                            if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                                $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                                if($alm_obj)
                                    $elementos = $elementos->where('almacen_id',$alm_obj->id);
                                else return response(['error'=>['la información enviada es incorrecta']],422);
                            }
                        }else{
                            $almacen = Auth::user()->almacenActual()->id;
                            $elementos = $elementos->where('almacen_id',$almacen);
                        }
                    }

                    if ($request->has("nombre_cliente")) {
                        $elementos = $elementos->where("clientes.nombre", "like", "%" . $request->input("nombre_cliente") . "%");
                    }

                    if ($request->has("identificacion_cliente")) {
                        $elementos = $elementos->where("clientes.identificaion", "like", "%" . $request->input("identificacion_cliente") . "%");
                    }
                    $elementos = $elementos->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("facturas.numero_cuotas",">","0");
                } else if ($request->input("tipo") == "compra") {
                    if(Auth::user()->bodegas == 'si')
                        $elementos = ABCompra::permitidos()->select("compras.*")->join("vendiendo.proveedores", "compras.proveedor_id", "=", "proveedores.id");
                    else
                        $elementos = Compra::permitidos()->select("compras.*")->join("proveedores", "compras.proveedor_id", "=", "proveedores.id");

                    if ($request->has("nombre_proveedor")) {
                        $elementos = $elementos->where("vendiendo.proveedores.nombre", "like", "%" . $request->input("nombre_proveedor") . "%");
                    }

                    if ($request->has("identificacion_proveedor")) {
                        $elementos = $elementos->where("vendiendo.proveedores.nit", "like", "%" . $request->input("identificacion_proveedor") . "%");
                    }
                    $elementos = $elementos->whereBetween("compras.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("numero_cuotas",">","0");
                }
            }

            $totalRegistros = $elementos->count();

            $parcialRegistros = $elementos->count();


            $full_saldos = 0;
            $full_valores = 0;
            if ($request->input("tipo") == "factura") {
                $full_valores = $this->totalesReporteAbonos($request, 'full_valores');
                $full_saldos = $this->totalesReporteAbonos($request, 'full_saldos');

            } else if ($request->input("tipo") == "compra") {
                $full_valores = $this->totalesReporteAbonos($request, 'full_valores');
                $full_saldos = $this->totalesReporteAbonos($request, 'full_saldos');
            }

            $elementos = $elementos->skip($start)->take($length);


            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($elementos->get() as $elemento) {
                    if ($request->input("tipo") == "factura") {
                        if ($elemento->numero_cuotas == "") $elemento->numero_cuotas = 0;
                        $ultimoAbono = $elemento->ultimoAbono();
                        $myArray[] = (object)array(
                            'info_tabla' => 'factura',
                            'data_1' => $elemento->numero,
                            'data_2' => $elemento->cliente->nombre . ' - ' . $elemento->cliente->identificacion,
                            'data_3' => (String)$elemento->created_at,
                            'data_4' => '$' . number_format(($elemento->subtotal + $elemento->iva), 2, ',', '.'),
                            'data_5' => '$' . number_format($elemento->getSaldo(), 2, ',', '.'),
                            'data_6' => $elemento->numero_cuotas,
                            'data_7' => count($elemento->abonos),
                            'data_8' => ($ultimoAbono) ? (String)$ultimoAbono->created_at : '',
                            'total_valores' => '$' . number_format($full_valores, 2, ',', '.'),
                            'total_saldos' => '$' . number_format($full_saldos, 2, ',', '.')
                        );

                    } else if ($request->input("tipo") == "compra") {
                        if ($elemento->numero_cuotas == "") $elemento->numero_cuotas = 0;
                        $ultimoAbono = $elemento->ultimoAbono();
                        $myArray[] = (object)array(
                            'info_tabla' => 'compra',
                            'data_1' => $elemento->numero,
                            'data_2' => $elemento->proveedor->nombre . ' - ' . $elemento->proveedor->nit,
                            'data_3' => (String)$elemento->created_at,
                            'data_4' => '$' . number_format(($elemento->valor), 2, ',', '.'),
                            'data_5' => '$' . number_format($elemento->getSaldo(), 2, ',', '.'),
                            'data_6' => $elemento->numero_cuotas,
                            'data_7' => count($elemento->abonos),
                            'data_8' => ($ultimoAbono) ? (String)$ultimoAbono->created_at : '',
                            'total_valores' => '$' . number_format($full_valores, 2, ',', '.'),
                            'total_saldos' => '$' . number_format($full_saldos, 2, ',', '.')
                        );
                    }
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $elementos->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'info' => $elementos->get()];

            return response()->json($data);
        }
    }

    public function totalesReporteAbonos(Request $request,$total){
        $r = Reporte::where('nombre','Abonos')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $data = 0;
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("tipo")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if ($request->input("tipo") == "factura") {
                    if(Auth::user()->bodegas == 'si')
                        $elementos = ABFactura::permitidos()->select("facturas.*")->join("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id");
                    else
                        $elementos = Factura::permitidos()->select("facturas.*")->join("clientes", "facturas.cliente_id", "=", "clientes.id");

                    if ($request->has("nombre_cliente")) {
                        $elementos = $elementos->where("clientes.nombre", "like", "%" . $request->input("nombre_cliente") . "%");
                    }

                    if ($request->has("identificacion_cliente")) {
                        $elementos = $elementos->where("clientes.identificaion", "like", "%" . $request->input("identificacion_cliente") . "%");
                    }
                    $elementos = $elementos->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("facturas.numero_cuotas",">","0");

                    if ($total == 'full_valores')
                        $data = $elementos->select(DB::RAW('SUM(facturas.subtotal+facturas.iva) AS TOTAL_VALORES'))->first()->TOTAL_VALORES;
                    else if ($total == 'full_saldos')
                        $data = $elementos->whereRaw('(facturas.estado ="Pendiente por pagar" OR facturas.estado ="Pedida")')->select(DB::RAW('SUM(facturas.subtotal+facturas.iva) AS TOTAL_SALDOS'))->first()->TOTAL_SALDOS;

                } else if ($request->input("tipo") == "compra") {
                    if(Auth::user()->bodegas == 'si')
                    $elementos = ABCompra::permitidos()->select("compras.*")->join("vendiendo.proveedores", "compras.proveedor_id", "=", "proveedores.id");
                    else
                    $elementos = Compra::permitidos()->select("compras.*")->join("proveedores", "compras.proveedor_id", "=", "proveedores.id");

                    if ($request->has("nombre_proveedor")) {
                        $elementos = $elementos->where("proveedores.nombre", "like", "%" . $request->input("nombre_proveedor") . "%");
                    }

                    if ($request->has("identificacion_proveedor")) {
                        $elementos = $elementos->where("proveedores.nit", "like", "%" . $request->input("identificacion_proveedor") . "%");
                    }
                    $elementos = $elementos->whereBetween("compras.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("numero_cuotas",">","0");

                    if ($total == 'full_valores')
                        $data = $elementos->select(DB::RAW('SUM(valor) AS TOTAL_VALORES'))->first()->TOTAL_VALORES;
                    else if ($total == 'full_saldos')
                        $data = $elementos->whereRaw('(compras.estado_pago ="Pendiente por pagar")')->select(DB::RAW('SUM(compras.valor) AS TOTAL_SALDOS'))->first()->TOTAL_SALDOS;
                }
            }
            return $data;
        }
    }

    public function getExcelAbonos(Request $request){
        $r = Reporte::where('nombre','Abonos')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte de abonos";

            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("tipo")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $elementos = null;

                if ($request->input("tipo") == "factura") {
                    if(Auth::user()->bodegas == 'si')
                        $elementos = ABFactura::permitidos()->select("facturas.*")->join("vendiendo.clientes", "facturas.cliente_id", "=", "clientes.id");
                    else
                        $elementos = Factura::permitidos()->select("facturas.*")->join("clientes", "facturas.cliente_id", "=", "clientes.id");
                    if ($request->has("nombre_cliente")) {
                        $elementos = $elementos->where("clientes.nombre", "like", "%" . $request->input("nombre_cliente") . "%");
                    }

                    if ($request->has("identificacion_cliente")) {
                        $elementos = $elementos->where("clientes.identificaion", "like", "%" . $request->input("identificacion_cliente") . "%");
                    }
                    $elementos = $elementos->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])->where("facturas.numero_cuotas",">","0");

                    if(Auth::user()->bodegas == 'si'){
                        if(Auth::user()->admin_bodegas == 'si'){
                            if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                                $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                                if($alm_obj)
                                    $elementos = $elementos->where('almacen_id',$alm_obj->id);
                                else return response(['error'=>['la información enviada es incorrecta']],422);
                            }
                        }else{
                            $almacen = Auth::user()->almacenActual()->id;
                            $elementos = $elementos->where('almacen_id',$almacen);
                        }
                    }
                } else if ($request->input("tipo") == "compra") {
                    if(Auth::user()->bodegas == 'si')
                        $elementos = ABCompra::permitidos()->select("compras.*")->join("vendiendo.proveedores", "compras.proveedor_id", "=", "proveedores.id");
                    else
                        $elementos = Compra::permitidos()->select("compras.*")->join("proveedores", "compras.proveedor_id", "=", "proveedores.id");

                    if ($request->has("nombre_proveedor")) {
                        $elementos = $elementos->where("proveedores.nombre", "like", "%" . $request->input("nombre_proveedor") . "%");
                    }

                    if ($request->has("identificacion_proveedor")) {
                        $elementos = $elementos->where("proveedores.nit", "like", "%" . $request->input("identificacion_proveedor") . "%");
                    }
                    $elementos = $elementos->whereBetween("compras.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->where("numero_cuotas",">","0");
                }
                $elementos = $elementos->get();
                $data = array('elementos' => $elementos, 'tipo' => $request->input("tipo"));
                return Excel::create($nombre, function ($excel) use ($data) {
                    $excel->sheet('Lista', function ($sheet) use ($data) {
                        $sheet->loadView('reportes.abonos.lista', ["elementos" => $data['elementos'], "reporte" => true, 'tipo' => $data['tipo']]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getCuentasCobrarFacturas($almacen = null){
        $r = Reporte::where('nombre','Cuentas por cobrar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $pos = '';
            if (isset($_REQUEST['page'])) {
                $pos = $_REQUEST['page'];
            }
            if(Auth::user()->bodegas == 'si'){
                $clientes_facturas_all = ABFactura::permitidos()->select('*')->where('dias_credito', '>', '0');
                $clientes_facturas = ABFactura::permitidos()
                    ->select('*', DB::raw('count(*) as num_facturas'), DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))
                    ->where('dias_credito', '>', '0')
                    ->groupBy('cliente_id');

                if(Auth::user()->admin_bodegas == 'si'){
                    if($almacen && !is_null($almacen) && $almacen != 0){
                        $alm_obj = Almacen::permitidos()->where('id',$almacen)->first();
                        if($alm_obj){
                            $clientes_facturas_all = $clientes_facturas_all->where('almacen_id',$alm_obj->id);
                            $clientes_facturas = $clientes_facturas->where('almacen_id',$alm_obj->id);
                        }
                        else return response(['error'=>['la información enviada es incorrecta']],422);
                    }
                }else{
                    $almacen = Auth::user()->almacenActual()->id;
                    $clientes_facturas_all = $clientes_facturas_all->where('almacen_id',$almacen);
                    $clientes_facturas = $clientes_facturas->where('almacen_id',$almacen);
                }
                $clientes_facturas_all = $clientes_facturas_all->get();
                $clientes_facturas = $clientes_facturas->get();
            }else {
                $clientes_facturas_all = Factura::permitidos()->select('*')->where('dias_credito', '>', '0')->get();
                $clientes_facturas = Factura::permitidos()
                    ->select('*', DB::raw('count(*) as num_facturas'), DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))
                    ->where('dias_credito', '>', '0')
                    ->groupBy('cliente_id')->paginate(env('PAGINATE'));
            }

            return view('reportes.cuentas_por_cobrar_facturas.index', compact('clientes_facturas', 'pos', 'clientes_facturas_all','almacen'));
        }
        return redirect('/');
    }

    public function getListReporteCuentasXCobrarFactura(Request $request){
        $r = Reporte::where('nombre','Cuentas por cobrar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = 'facturas.id';//$columna[$sortColumnIndex]['data'];
            $myArray = [];
            $almacen = null;
            if(Auth::user()->bodegas == 'si'){
                $clientes_facturas = ABFactura::permitidos()
                    ->where('dias_credito', '>', '0')
                    ->groupBy('cliente_id');

                if(Auth::user()->admin_bodegas == 'si'){
                    if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                        $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                        if($alm_obj){
                            $almacen = $alm_obj->id;
                            $clientes_facturas = $clientes_facturas->where('almacen_id',$almacen);
                        }
                        else return response(['error'=>['la información enviada es incorrecta']],422);
                    }
                }else{
                    $almacen = Auth::user()->almacenActual()->id;
                    $clientes_facturas = $clientes_facturas->where('almacen_id',$almacen);
                }
            }else {
                $clientes_facturas = Factura::permitidos()
                    ->where('dias_credito', '>', '0')
                    ->groupBy('cliente_id');
            }

            $totalRegistros = $clientes_facturas->get()->count();

            // if($search['value'] != null){
            //        $clientes_facturas = $clientes_facturas->whereRaw(
            //            " ( LOWER(cliente.nombre) LIKE '%".\strtolower($search["value"])."%' )");
            //    }

            $parcialRegistros = $clientes_facturas->get()->count();
            $clientes_facturas = $clientes_facturas->select('*', DB::raw('count(*) as num_facturas'), DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))->skip($start)->take($length);


            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($clientes_facturas->get() as $cf) {
                    if(Auth::user()->bodegas == 'si')
                        $abonos_cliente = ABFactura::getAbonosByCliente($cf->cliente_id,$almacen);
                    else
                        $abonos_cliente = Factura::getAbonosByCliente($cf->cliente_id);

                    $url = url('/reporte/cuentas-cobrar-cliente/' . $cf->cliente_id);
                    if($almacen)$url .= '/'.$almacen;
                    $myArray[] = (object)array(
                        'data_1' => $cf->cliente->nombre,
                        'data_2' => $cf->num_facturas,
                        'data_3' => "$ " . number_format($cf->valor_facturas, 2, ',', '.'),
                        'data_4' => "$ " . number_format($abonos_cliente, 2, ',', '.'),
                        'data_5' => "$ " . number_format($cf->valor_facturas - $abonos_cliente, 2, ',', '.'),
                        'data_6' => $url
                    );
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $clientes_facturas->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'info' => $clientes_facturas->get()];

            return response()->json($data);
        }
    }

    public function getCuentasCobrarCliente($cliente_id,$almacen = null){
        $r = Reporte::where('nombre','Cuentas por cobrar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $pos = '';
            if (isset($_REQUEST['page'])) {
                $pos = $_REQUEST['page'];
            }

            $cliente = Cliente::find($cliente_id);
            $total_abonos = 0;
            if ($cliente) {
                if(Auth::user()->bodegas == 'si') {
                    $total_abonos = ABFactura::getAbonosByCliente($cliente_id);
                    $facturas_cliente_all = ABFactura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id);
                    $facturas_cliente = ABFactura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id);

                    if(Auth::user()->admin_bodegas == 'si'){
                        if($almacen && !is_null($almacen) && $almacen != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$almacen)->first();
                            if($alm_obj){
                                $almacen = $alm_obj->id;
                                $facturas_cliente_all = $facturas_cliente_all->where('almacen_id',$almacen);
                                $facturas_cliente = $facturas_cliente->where('almacen_id',$almacen);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $facturas_cliente_all = $facturas_cliente_all->where('almacen_id',$almacen);
                        $facturas_cliente = $facturas_cliente->where('almacen_id',$almacen);
                    }

                    $facturas_cliente_all = $facturas_cliente_all->get();
                    $facturas_cliente = $facturas_cliente->get();
                }else{
                    $total_abonos = Factura::getAbonosByCliente($cliente_id);
                    $facturas_cliente_all = Factura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id)->get();
                    $facturas_cliente = Factura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id)
                        ->paginate(env('PAGINATE'));
                }
            }
            return view('reportes.cuentas_por_cobrar_facturas.detalle', compact('facturas_cliente', 'cliente', 'total_abonos', 'facturas_cliente_all', 'pos', 'almacen'));
        }
        return redirect('/');
    }

    public function getListReporteCuentasXCobrarFacturaDetalle(Request $request){
        $r = Reporte::where('nombre','Cuentas por cobrar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $cliente_id = $request->get("cliente_id");
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = 'facturas.id';//$columna[$sortColumnIndex]['data'];
            $myArray = [];

            if(Auth::user()->bodegas == 'si') {
                $facturas_cliente = ABFactura::permitidos()
                    ->where('dias_credito', '>', '0')
                    ->where('cliente_id', $cliente_id);
            }else{
                $facturas_cliente = Factura::permitidos()
                    ->where('dias_credito', '>', '0')
                    ->where('cliente_id', $cliente_id);
            }

            $totalRegistros = $facturas_cliente->count();

            // if($search['value'] != null){
            //        $facturas_cliente = $facturas_cliente->whereRaw(
            //            " ( LOWER(cliente.nombre) LIKE '%".\strtolower($search["value"])."%' )");
            //    }

            $parcialRegistros = $facturas_cliente->count();
            $facturas_cliente = $facturas_cliente->skip($start)->take($length);

            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($facturas_cliente->get() as $fc) {
                    $fecha_actual = date("Y") . "-" . date("m") . "-" . date("d");
                    $dias_trascurridos = General::dias_transcurridos(date_format($fc->created_at, 'Y-m-d'), $fecha_actual);
                    $saldo = ($fc->subtotal + $fc->iva) - $fc->abonos()->sum('valor');
                    $rango_30 = $rango_60 = $rango_120 = $vencida = '';
                    if ($fc->dias_credito > 1 && $fc->dias_credito <= 30) $rango_30 = "$" . number_format($saldo, 2, ',', '.');
                    if ($fc->dias_credito > 31 && $fc->dias_credito <= 60) $rango_60 = "$" . number_format($saldo, 2, ',', '.');
                    if ($fc->dias_credito > 61 && $fc->dias_credito <= 120) $rango_120 = "$" . number_format($saldo, 2, ',', '.');
                    if ($fc->dias_credito - $dias_trascurridos <= 0) $vencida = "$" . number_format($saldo, 2, ',', '.');

                    $myArray[] = (object)array(
                        'data_1' => $fc->numero,
                        'data_2' => (String)$fc->created_at,
                        'data_3' => "$ " . number_format($fc->subtotal + $fc->iva, 2, ',', '.'),
                        'data_4' => "$ " . number_format($fc->abonos()->sum('valor'), 2, ',', '.'),
                        'data_5' => $rango_30,
                        'data_6' => $rango_60,
                        'data_7' => $rango_120,
                        'data_8' => $vencida,
                        'data_9' => "$ " . number_format($saldo, 2, ',', '.')
                    );
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $facturas_cliente->toSql(),
                'recordsTotal' => $facturas_cliente->count(),
                'recordsFiltered' => $facturas_cliente->count(),
                'data' => $myArray,
                'info' => $facturas_cliente->get()];

            return response()->json($data);
        }
    }

    public function getExcelCuentasCobrarFacturasGeneral($almacen = null){
        $r = Reporte::where('nombre','Cuentas por cobrar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre_reporte = "Reporte cuentas por cobrar generales";

            if(Auth::user()->bodegas == 'si'){
                $clientes_facturas_all = ABFactura::permitidos()->select('*')->where('dias_credito', '>', '0');
                $clientes_facturas = ABFactura::permitidos()
                    ->select('*', DB::raw('count(*) as num_facturas'), DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))
                    ->where('dias_credito', '>', '0')
                    ->groupBy('cliente_id');

                if(Auth::user()->admin_bodegas == 'si'){
                    if($almacen && !is_null($almacen) && $almacen != 0){
                        $alm_obj = Almacen::permitidos()->where('id',$almacen)->first();
                        if($alm_obj){
                            $clientes_facturas_all = $clientes_facturas_all->where('almacen_id',$alm_obj->id);
                            $clientes_facturas = $clientes_facturas->where('almacen_id',$alm_obj->id);
                        }
                        else return response(['error'=>['la información enviada es incorrecta']],422);
                    }
                }else{
                    $almacen = Auth::user()->almacenActual()->id;
                    $clientes_facturas_all = $clientes_facturas_all->where('almacen_id',$almacen);
                    $clientes_facturas = $clientes_facturas->where('almacen_id',$almacen);
                }
                $clientes_facturas_all = $clientes_facturas_all->get();
                $clientes_facturas = $clientes_facturas->get();
            }else {
                $clientes_facturas_all = Factura::permitidos()->select('*')->where('dias_credito', '>', '0')->get();
                $clientes_facturas = Factura::permitidos()
                    ->select('*', DB::raw('count(*) as num_facturas'), DB::raw('(sum(subtotal)+sum(iva)) as valor_facturas'))
                    ->where('dias_credito', '>', '0')
                    ->groupBy('cliente_id')->get();
            }

            return Excel::create($nombre_reporte, function ($excel) use ($clientes_facturas, $clientes_facturas_all,$almacen) {
                $excel->sheet("Lista CC general", function ($sheet) use ($clientes_facturas, $clientes_facturas_all,$almacen) {
                    $sheet->loadView("reportes.cuentas_por_cobrar_facturas.excel.lista", ["clientes_facturas" => $clientes_facturas, "clientes_facturas_all" => $clientes_facturas_all, "almacen"=>$almacen, "reporte" => true]);
                    $sheet->setAutoSize(range('A', 'E'));
                    $sheet->cells('B5:D6', function ($cells) {
                        $cells->setBackground('#EFFBFB');
                    });
                    $sheet->cells('B6:D6', function ($cells) {
                        $cells->setBorder('', '', 'thin', '');
                    });
                });
            })->export("xls");
        }
    }
    public function getExcelCuentasCobrarFacturasCliente($cliente_id,$almacen = null){
        $r = Reporte::where('nombre','Cuentas por cobrar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $cliente = Cliente::find($cliente_id);
            if ($cliente) {
                $nombre_reporte = "Reporte cuentas por cobrar al cliente " . $cliente->nombre;
                if(Auth::user()->bodegas == 'si') {
                    $total_abonos = ABFactura::getAbonosByCliente($cliente_id,$almacen);
                    $facturas_cliente_all = ABFactura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id);
                    $facturas_cliente = ABFactura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id);

                    if(Auth::user()->admin_bodegas == 'si'){
                        if($almacen && !is_null($almacen) && $almacen != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$almacen)->first();
                            if($alm_obj){
                                $facturas_cliente_all = $facturas_cliente_all->where('almacen_id',$alm_obj->id);
                                $facturas_cliente = $facturas_cliente->where('almacen_id',$alm_obj->id);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $facturas_cliente_all = $facturas_cliente_all->where('almacen_id',$almacen);
                        $facturas_cliente = $facturas_cliente->where('almacen_id',$almacen);
                    }
                    $facturas_cliente_all = $facturas_cliente_all-> get();
                    $facturas_cliente = $facturas_cliente->get();
                }else{
                    $total_abonos = Factura::getAbonosByCliente($cliente_id);
                    $facturas_cliente_all = Factura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id)->get();
                    $facturas_cliente = Factura::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('cliente_id', $cliente_id)
                        ->paginate(env('PAGINATE'));
                }

                return Excel::create($nombre_reporte, function ($excel) use ($facturas_cliente, $facturas_cliente_all, $cliente, $total_abonos) {
                    $excel->sheet("Lista CC general", function ($sheet) use ($facturas_cliente, $facturas_cliente_all, $cliente, $total_abonos) {
                        $sheet->loadView("reportes.cuentas_por_cobrar_facturas.excel.lista_detalle", ["facturas_cliente" => $facturas_cliente, "facturas_cliente_all" => $facturas_cliente_all, "cliente" => $cliente, "total_abonos" => $total_abonos, "reporte" => true]);
                        $sheet->setAutoSize(range('A', 'I'));
                        $sheet->cells('A3:B5', function ($cells) {
                            $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $sheet->cells('C8:G9', function ($cells) {
                            $cells->setBackground('#EFFBFB');
                        });
                        $sheet->cells('C9:G9', function ($cells) {
                            $cells->setBorder('', '', 'thin', '');
                        });
                    });
                })->export("xls");
            }
            //return view('reportes.cuentas_por_cobrar_facturas.detalle', compact('facturas_cliente','cliente','total_abonos','facturas_cliente_all','pos'));
        }
    }
    public function getCuentaCobrarProveedores(){
        $r = Reporte::where('nombre','Devoluciones a proveedores')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $user = Auth::user();
            $pos = '';
            if (isset($_REQUEST['page'])) {
                $pos = $_REQUEST['page'];
            }
            $valorTotalCuentasCobradas = CuentaPorCobrar::valorCuentasXCobrar('PAGADA');
            $ValorTotalCuentasXCobrar = CuentaPorCobrar::valorCuentasXCobrar('POR PAGAR');

            $cuentasXpagar = CuentaPorCobrar::where('usuario_id', $user->id)
                ->where('estado', 'POR PAGAR')
                ->orderby('estado', 'desc')
                ->paginate(env('PAGINATE'));
            return view('reportes.cuentas_por_cobrar_proveedores.index', compact('cuentasXpagar', 'valorTotalCuentasCobradas', 'ValorTotalCuentasXCobrar', 'pos'));
        }
        return redirect('/');
    }

    public function getListReporteCuentasXCobrarProveedores(Request $request){
        $r = Reporte::where('nombre','Devoluciones a proveedores')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $user = Auth::user();
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = 'estado';//$columna[$sortColumnIndex]['data'];
            $myArray = [];


            if(Auth::user()->bodegas == 'si') {
                $cuentasXpagar = CuentaPorCobrar::select('vendiendo_alm.compras.numero', 'materias_primas.nombre AS nombre_materia', 'vendiendo_alm.productos.nombre AS nombre_producto', 'cuentas_por_cobrar_proveedores.cantidad_devolucion', 'cuentas_por_cobrar_proveedores.valor_devolucion', 'cuentas_por_cobrar_proveedores.motivo', 'cuentas_por_cobrar_proveedores.fecha_devolucion', 'proveedores.nombre AS nombre_proveedor', 'cuentas_por_cobrar_proveedores.tipo_compra', 'cuentas_por_cobrar_proveedores.estado', 'cuentas_por_cobrar_proveedores.id')
                    ->leftJoin("materias_primas", "cuentas_por_cobrar_proveedores.elemento_id", "=", "materias_primas.id")
                    ->leftJoin("vendiendo_alm.productos", "cuentas_por_cobrar_proveedores.elemento_id", "=", "productos.id")
                    ->join("vendiendo_alm.compras", "cuentas_por_cobrar_proveedores.compra_id", "=", "compras.id")
                    ->join("proveedores", "cuentas_por_cobrar_proveedores.proveedor_id", "=", "proveedores.id")
                    //->where('cuentas_por_cobrar_proveedores.estado','POR PAGAR')
                    ->where('.cuentas_por_cobrar_proveedores.usuario_id', $user->id)
                    ->orderby('cuentas_por_cobrar_proveedores.estado', 'desc');
            }else{
                $cuentasXpagar = CuentaPorCobrar::select('compras.numero', 'materias_primas.nombre AS nombre_materia', 'productos.nombre AS nombre_producto', 'cuentas_por_cobrar_proveedores.cantidad_devolucion', 'cuentas_por_cobrar_proveedores.valor_devolucion', 'cuentas_por_cobrar_proveedores.motivo', 'cuentas_por_cobrar_proveedores.fecha_devolucion', 'proveedores.nombre AS nombre_proveedor', 'cuentas_por_cobrar_proveedores.tipo_compra', 'cuentas_por_cobrar_proveedores.estado', 'cuentas_por_cobrar_proveedores.id')
                    ->leftJoin("materias_primas", "cuentas_por_cobrar_proveedores.elemento_id", "=", "materias_primas.id")
                    ->leftJoin("productos", "cuentas_por_cobrar_proveedores.elemento_id", "=", "productos.id")
                    ->join("compras", "cuentas_por_cobrar_proveedores.compra_id", "=", "compras.id")
                    ->join("proveedores", "cuentas_por_cobrar_proveedores.proveedor_id", "=", "proveedores.id")
                    //->where('cuentas_por_cobrar_proveedores.estado','POR PAGAR')
                    ->where('.cuentas_por_cobrar_proveedores.usuario_id', $user->id)
                    ->orderby('cuentas_por_cobrar_proveedores.estado', 'desc');
            }


            // $cuentasXpagar = CuentaPorCobrar::where('usuario_id',$user->id)
            // 	->where('estado','POR PAGAR')
            // 	->orderby('estado','desc');

            $totalRegistros = $cuentasXpagar->count();

            if ($search['value'] != null) {
                $cuentasXpagar = $cuentasXpagar->whereRaw(
                    " ( LOWER(productos.nombre) LIKE '%" . \strtolower($search["value"]) . "%' OR " .
                    " LOWER(materias_primas.nombre) LIKE '%" . \strtolower($search["value"]) . "%' OR " .
                    " compras.numero LIKE '%" . $search["value"] . "%' OR " .
                    " valor_devolucion LIKE '%" . $search["value"] . "%' OR " .
                    " motivo LIKE '%" . $search["value"] . "%' OR " .
                    " fecha_devolucion LIKE '%" . $search["value"] . "%' OR " .
                    " LOWER(proveedores.nombre) LIKE '%" . \strtolower($search["value"]) . "%' )");
            }

            $parcialRegistros = $cuentasXpagar->count();
            $cuentasXpagar = $cuentasXpagar->skip($start)->take($length);

            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($cuentasXpagar->get() as $cxp) {
                    $myArray[] = (object)array(
                        'data_1' => $cxp->numero,
                        'data_2' => ($cxp->tipo_compra == 'Producto') ? $cxp->nombre_producto : $cxp->nombre_materia,
                        'data_3' => $cxp->cantidad_devolucion,
                        'data_4' => "$" . number_format($cxp->valor_devolucion, 2, ',', '.'),
                        'data_5' => $cxp->motivo,
                        'data_6' => $cxp->fecha_devolucion,
                        'data_7' => $cxp->nombre_proveedor,
                        'data_8' => $cxp->estado,
                        'data_id' => $cxp->id
                    );
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $cuentasXpagar->toSql(),
                'recordsTotal' => $cuentasXpagar->count(),
                'recordsFiltered' => $cuentasXpagar->count(),
                'data' => $myArray,
                'info' => $cuentasXpagar->get()];

            return response()->json($data);
        }
    }

    public function getExcelCuentaCobrarProveedores(){
        $r = Reporte::where('nombre','Devoluciones a proveedores')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre_reporte = "Reporte cuentas por cobrar proveedores";
            $user = Auth::user();

            $valorTotalCuentasCobradas = CuentaPorCobrar::valorCuentasXCobrar('PAGADA');
            $ValorTotalCuentasXCobrar = CuentaPorCobrar::valorCuentasXCobrar('POR PAGAR');

            $cuentasXpagar = CuentaPorCobrar::where('usuario_id', $user->id)
                //->where('estado','POR PAGAR')
                ->orderby('estado', 'desc')->get();

            return Excel::create($nombre_reporte, function ($excel) use ($valorTotalCuentasCobradas, $ValorTotalCuentasXCobrar, $cuentasXpagar) {
                $excel->sheet("Lista CC general", function ($sheet) use ($valorTotalCuentasCobradas, $ValorTotalCuentasXCobrar, $cuentasXpagar) {
                    $sheet->loadView("reportes.cuentas_por_cobrar_proveedores.excel.lista", ["valorTotalCuentasCobradas" => $valorTotalCuentasCobradas, "ValorTotalCuentasXCobrar" => $ValorTotalCuentasXCobrar, "cuentasXpagar" => $cuentasXpagar, "reporte" => true]);
                    $sheet->setAutoSize(range('A', 'I'));

                    $sheet->cells('D4:D5', function ($cells) {
                        $cells->setBackground('#EFFBFB');
                    });
                    $sheet->cells('D5', function ($cells) {
                        $cells->setBorder('', '', 'thin', '');
                    });
                });
            })->export("xls");
        }
    }

    public function getCuentasPagarCompras(){
        $r = Reporte::where('nombre','Cuentas por pagar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $pos = '';
            if (isset($_REQUEST['page'])) {
                $pos = $_REQUEST['page'];
            }

            if(Auth::user()->bodegas == 'si')
                $compras_proveedor_all = ABCompra::permitidos()->select('*')->where('dias_credito', '>', '0')->get();
            else
                $compras_proveedor_all = Compra::permitidos()->select('*')->where('dias_credito', '>', '0')->get();

            return view('reportes.cuentas_por_pagar_proveedores.index', compact('pos', 'compras_proveedor_all'));
        }
        return redirect('/');
    }

    public function getListReporteCuentasXPagarProveedores(Request $request){
        $r = Reporte::where('nombre','Cuentas por pagar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = 'proveedor_id';//$columna[$sortColumnIndex]['data'];
            $myArray = [];

            if(Auth::user()->bodegas == 'si') {
                $compras_proveedor = ABCompra::permitidos()
                    ->select('*', DB::raw('s1.num_compras'), DB::raw('vlr_compras as valor_compras'), DB::raw('sum(abonos.valor) as abonos'))
                    ->leftJoin('abonos', 'compras.id', '=', 'abonos.tipo_abono_id')
                    ->join(DB::raw('(SELECT sum(c.valor) as vlr_compras, count(*) as num_compras, c.proveedor_id as p_id FROM compras as c WHERE c.dias_credito > 0 GROUP BY c.proveedor_id ) as s1'), 'compras.proveedor_id', '=', 's1.p_id')
                    /*->where(function ($q){
                        $q->where('abonos.tipo_abono','compra')
                            ->orWhere('abonos.tipo_abono',null);
                    })*/
                    ->where('compras.dias_credito', '>', '0')
                    //->where('estado_pago',"Pendiente por pagar")
                    ->groupBy('compras.proveedor_id');
            }else{
                $compras_proveedor = Compra::permitidos()
                    ->select('*', DB::raw('s1.num_compras'), DB::raw('vlr_compras as valor_compras'), DB::raw('sum(abonos.valor) as abonos'))
                    ->leftJoin('abonos', 'compras.id', '=', 'abonos.tipo_abono_id')
                    ->join(DB::raw('(SELECT sum(c.valor) as vlr_compras, count(*) as num_compras, c.proveedor_id as p_id FROM compras as c WHERE c.dias_credito > 0 GROUP BY c.proveedor_id ) as s1'), 'compras.proveedor_id', '=', 's1.p_id')
                    /*->where(function ($q){
                        $q->where('abonos.tipo_abono','compra')
                            ->orWhere('abonos.tipo_abono',null);
                    })*/
                    ->where('compras.dias_credito', '>', '0')
                    //->where('estado_pago',"Pendiente por pagar")
                    ->groupBy('compras.proveedor_id');
            }

            //dd($compras_proveedor->toSql());

            $totalRegistros = $compras_proveedor->get()->count();

            // if($search['value'] != null){
            //        $compras_proveedor = $compras_proveedor->whereRaw(
            //            " ( LOWER(cliente.nombre) LIKE '%".\strtolower($search["value"])."%' )");
            //    }

            $parcialRegistros = $compras_proveedor->get()->count();
            $compras_proveedor = $compras_proveedor->skip($start)->take($length);
            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($compras_proveedor->get() as $cp) {
                    if(Auth::user()->bodegas == 'si')
                    $abonos_proveedor = ABCompra::getAbonosByProveedor($cp->proveedor_id);
                    else
                    $abonos_proveedor = Compra::getAbonosByProveedor($cp->proveedor_id);
                    $myArray[] = (object)array(
                        'data_1' => $cp->proveedor->nombre,
                        'data_2' => $cp->num_compras,
                        'data_3' => "$ " . number_format($cp->valor_compras, 2, ',', '.'),
                        'data_4' => "$ " . number_format($abonos_proveedor, 2, ',', '.'),
                        'data_5' => "$ " . number_format($cp->valor_compras - $abonos_proveedor, 2, ',', '.'),
                        'data_6' => url('/reporte/cuentas-pagar-proveedor/' . $cp->proveedor_id)
                    );
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $compras_proveedor->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'info' => $compras_proveedor->get()];

            return response()->json($data);
        }
    }

    public function getCuentasPagarProveedor($proveedor_id){
        $r = Reporte::where('nombre','Cuentas por pagar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $pos = '';
            if (isset($_REQUEST['page'])) {
                $pos = $_REQUEST['page'];
            }
            $proveedor = Proveedor::find($proveedor_id);
            $total_abonos = 0;
            if ($proveedor) {
                if(Auth::user()->bodegas == 'si') {
                    $compras_proveedor_all = ABCompra::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('proveedor_id', $proveedor_id)
                        ->get();
                }else{

                    $compras_proveedor_all = Compra::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('proveedor_id', $proveedor_id)
                        ->get();
                }
                if(Auth::user()->bodegas == 'si')
                    $total_abonos = ABCompra::getAbonosByProveedor($proveedor_id);
                else
                    $total_abonos = Compra::getAbonosByProveedor($proveedor_id);

                return view('reportes.cuentas_por_pagar_proveedores.detalle', compact('compras_proveedor_all', 'total_abonos', 'pos', 'proveedor', 'proveedor_id'));
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListReporteCuentasXPagarProveedoresDetalles(Request $request){
        $r = Reporte::where('nombre','Cuentas por pagar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = 'proveedor_id';//$columna[$sortColumnIndex]['data'];
            $myArray = [];
            $proveedor_id = $request->get("proveedor_id");

            if(Auth::user()->bodegas == 'si') {
                $compras_proveedor = ABCompra::permitidos()
                    ->where('dias_credito', '>', '0')
                    ->where('proveedor_id', $proveedor_id);
            }else{
                $compras_proveedor = Compra::permitidos()
                    ->where('dias_credito', '>', '0')
                    ->where('proveedor_id', $proveedor_id);
            }

            $totalRegistros = $compras_proveedor->count();

            // if($search['value'] != null){
            //        $compras_proveedor = $compras_proveedor->whereRaw(
            //            " ( LOWER(cliente.nombre) LIKE '%".\strtolower($search["value"])."%' )");
            //    }

            $parcialRegistros = $compras_proveedor->count();
            $compras_proveedor = $compras_proveedor->skip($start)->take($length);
            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($compras_proveedor->get() as $fc) {
                    $fecha_actual = date("Y") . "-" . date("m") . "-" . date("d");
                    $dias_trascurridos = \App\General::dias_transcurridos(date_format($fc->created_at, 'Y-m-d'), $fecha_actual);
                    $saldo = $fc->valor - $fc->abonos()->sum('valor');
                    $rango_30 = $rango_60 = $rango_120 = $vencida = '';
                    if ($fc->dias_credito > 1 && $fc->dias_credito <= 30) $rango_30 = "$" . number_format($saldo, 2, ',', '.');
                    if ($fc->dias_credito > 31 && $fc->dias_credito <= 60) $rango_60 = "$" . number_format($saldo, 2, ',', '.');
                    if ($fc->dias_credito > 61 && $fc->dias_credito <= 120) $rango_120 = "$" . number_format($saldo, 2, ',', '.');
                    if ($fc->dias_credito - $dias_trascurridos <= 0) $vencida = "$" . number_format($saldo, 2, ',', '.');

                    $myArray[] = (object)array(
                        'data_1' => $fc->numero,
                        'data_2' => (String)$fc->created_at,
                        'data_3' => "$ " . number_format($fc->valor, 2, ',', '.'),
                        'data_4' => "$ " . number_format($fc->abonos()->sum('valor'), 2, ',', '.'),
                        'data_5' => $rango_30,
                        'data_6' => $rango_60,
                        'data_7' => $rango_120,
                        'data_8' => $vencida,
                        'data_9' => "$ " . number_format($saldo, 2, ',', '.')
                    );
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $compras_proveedor->toSql(),
                'recordsTotal' => $compras_proveedor->count(),
                'recordsFiltered' => $compras_proveedor->count(),
                'data' => $myArray,
                'info' => $proveedor_id];

            return response()->json($data);
        }
    }

    public function getExcelCuentasPagarCompras(){
        $r = Reporte::where('nombre','Cuentas por pagar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre_reporte = "Reporte cuentas por pagar general";
            if(Auth::user()->bodegas == 'si')
                $compras_proveedor_all = ABCompra::permitidos()->select('*')->where('dias_credito', '>', '0')->get();
            else
                $compras_proveedor_all = Compra::permitidos()->select('*')->where('dias_credito', '>', '0')->get();

            if(Auth::user()->bodegas == 'si') {
                $compras_proveedor = ABCompra::permitidos()
                    ->select('*', DB::raw('s1.num_compras'), DB::raw('vlr_compras as valor_compras'), DB::raw('sum(abonos.valor) as abonos'))
                    ->leftJoin('vendiendo_alm.abonos', 'compras.id', '=', 'abonos.tipo_abono_id')
                    ->join(DB::raw('(SELECT sum(c.valor) as vlr_compras, count(*) as num_compras, c.proveedor_id as p_id FROM compras as c WHERE c.dias_credito > 0 GROUP BY c.proveedor_id ) as s1'), 'compras.proveedor_id', '=', 's1.p_id')
                    /*->where(function ($q){
                        $q->where('abonos.tipo_abono','compra')
                            ->orWhere('abonos.tipo_abono',null);
                    })*/
                    ->where('compras.dias_credito', '>', '0')
                    //->where('estado_pago',"Pendiente por pagar")
                    ->groupBy('compras.proveedor_id')
                    ->paginate(env('PAGINATE'));
            }else{
                $compras_proveedor = Compra::permitidos()
                    ->select('*', DB::raw('s1.num_compras'), DB::raw('vlr_compras as valor_compras'), DB::raw('sum(abonos.valor) as abonos'))
                    ->leftJoin('abonos', 'compras.id', '=', 'abonos.tipo_abono_id')
                    ->join(DB::raw('(SELECT sum(c.valor) as vlr_compras, count(*) as num_compras, c.proveedor_id as p_id FROM compras as c WHERE c.dias_credito > 0 GROUP BY c.proveedor_id ) as s1'), 'compras.proveedor_id', '=', 's1.p_id')
                    /*->where(function ($q){
                        $q->where('abonos.tipo_abono','compra')
                            ->orWhere('abonos.tipo_abono',null);
                    })*/
                    ->where('compras.dias_credito', '>', '0')
                    //->where('estado_pago',"Pendiente por pagar")
                    ->groupBy('compras.proveedor_id')
                    ->paginate(env('PAGINATE'));
            }

            return Excel::create($nombre_reporte, function ($excel) use ($compras_proveedor, $compras_proveedor_all) {
                $excel->sheet("Lista CC general", function ($sheet) use ($compras_proveedor, $compras_proveedor_all) {
                    $sheet->loadView("reportes.cuentas_por_pagar_proveedores.excel.lista", ["compras_proveedor" => $compras_proveedor, "compras_proveedor_all" => $compras_proveedor_all, "reporte" => true]);
                    $sheet->setAutoSize(range('A', 'E'));
                    $sheet->cells('B5:D6', function ($cells) {
                        $cells->setBackground('#EFFBFB');
                    });
                    $sheet->cells('B6:D6', function ($cells) {
                        $cells->setBorder('', '', 'thin', '');
                    });
                });
            })->export("xls");
        }
    }
    public function getExcelCuentasPagarProveedor($proveedor_id){
        $r = Reporte::where('nombre','Cuentas por pagar')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $proveedor = Proveedor::find($proveedor_id);
            $total_abonos = 0;
            if ($proveedor) {
                $nombre_reporte = "Reporte cuentas por cobrar al proveedor " . $proveedor->nombre;
                if(Auth::user()->bodegas == 'si') {
                    $compras_proveedor_all = ABCompra::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('proveedor_id', $proveedor_id)
                        ->get();
                    $compras_proveedor = ABCompra::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('proveedor_id', $proveedor_id)
                        ->paginate(env('PAGINATE'));
                    $total_abonos = ABCompra::getAbonosByProveedor($proveedor_id);
                }else{
                    $compras_proveedor_all = Compra::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('proveedor_id', $proveedor_id)
                        ->get();
                    $compras_proveedor = Compra::permitidos()
                        ->where('dias_credito', '>', '0')
                        ->where('proveedor_id', $proveedor_id)
                        ->paginate(env('PAGINATE'));
                    $total_abonos = Compra::getAbonosByProveedor($proveedor_id);
                }

                return Excel::create($nombre_reporte, function ($excel) use ($compras_proveedor, $compras_proveedor_all, $proveedor, $total_abonos) {
                    $excel->sheet("Lista CC general", function ($sheet) use ($compras_proveedor, $compras_proveedor_all, $proveedor, $total_abonos) {
                        $sheet->loadView("reportes.cuentas_por_pagar_proveedores.excel.lista_detalle", ["compras_proveedor" => $compras_proveedor, "compras_proveedor_all" => $compras_proveedor_all, "proveedor" => $proveedor, "total_abonos" => $total_abonos, "reporte" => true]);
                        $sheet->setAutoSize(range('A', 'I'));
                        $sheet->cells('A3:B5', function ($cells) {
                            $cells->setBorder('thin', 'thin', 'thin', 'thin');
                        });
                        $sheet->cells('C8:G9', function ($cells) {
                            $cells->setBackground('#EFFBFB');
                        });
                        $sheet->cells('C9:G9', function ($cells) {
                            $cells->setBorder('', '', 'thin', '');
                        });
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getVentasPorEmpleado(){
        $r = Reporte::where('nombre','Ventas por empleado')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.ventas_por_empleado.index');
        return redirect('/');
    }

    public function postListVentasPorEmpleado(Request $request){
        $r = Reporte::where('nombre','Ventas por empleado')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view("reportes.ventas_por_empleado.lista");
        return redirect('/');
    }

    public function getListReporteVentasPorEmpleado(Request $request){
        $r = Reporte::where('nombre','Ventas por empleado')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = 'usuarios.id';//$columna[$sortColumnIndex]['data'];
            $myArray = [];
            $data = ['length' => $length, 'start' => $start, 'buscar' => $search['value'], 'draw' => '', 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'info' => 0];

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

                if(Auth::user()->bodegas == 'si'){
                    $empleados = ABFactura::permitidos()->select(DB::raw("sum(subtotal + iva) as total"), "usuarios.nombres", "usuarios.apellidos", "usuarios.alias", "perfiles.nombre as perfil")
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->join("vendiendo.usuarios", "facturas.usuario_creador_id", "=", "usuarios.id")
                        ->join("vendiendo.perfiles", "usuarios.perfil_id", "=", "perfiles.id")
                        ->where("facturas.estado", "<>", "anulada")
                        ->orderBy(DB::raw("sum(subtotal)"), "DESC")
                        ->groupBy("usuarios.id");

                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $empleados = $empleados->where('facturas.almacen_id',$alm_obj->id);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $empleados = $empleados->where('facturas.almacen_id',$almacen);
                    }
                }else {
                    $empleados = Factura::permitidos()->select(DB::raw("sum(subtotal + iva) as total"), "usuarios.nombres", "usuarios.apellidos", "usuarios.alias", "perfiles.nombre as perfil")
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->join("usuarios", "facturas.usuario_creador_id", "=", "usuarios.id")
                        ->join("perfiles", "usuarios.perfil_id", "=", "perfiles.id")
                        ->where("facturas.estado", "<>", "anulada")
                        ->orderBy(DB::raw("sum(subtotal)"), "DESC")
                        ->groupBy("usuarios.id");
                }


                $totalRegistros = $empleados->get()->count();
                if ($search['value'] != null) {
                    $empleados = $empleados->whereRaw(
                        " ( LOWER(CONCAT(nombres,' ',apellidos)) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(alias) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(perfiles.nombre) LIKE '%" . \strtolower($search["value"]) . "%')");
                }

                $parcialRegistros = $empleados->get()->count();
                $empleados = $empleados->skip($start)->take($length);
                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    foreach ($empleados->get() as $e) {
                        $myArray[] = (object)array(
                            'data_1' => \App\TildeHtml::TildesToHtml($e->nombres . " " . $e->apellidos),
                            'data_2' => \App\TildeHtml::TildesToHtml($e->alias),
                            'data_3' => $e->perfil,
                            'data_4' => "$" . number_format($e->total, 2, ',', '.')
                        );
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $empleados->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'data' => $myArray,
                    'info' => $empleados->get()];
            }
            return response()->json($data);
        }
    }


    public function postGraficaVentasPorEmpleado(Request $request){
        $r = Reporte::where('nombre','Ventas por empleado')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {

                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si') {
                    $empleados = ABFactura::permitidos()->select(DB::raw("sum(subtotal + iva) as total"), "usuarios.nombres", "usuarios.apellidos", "usuarios.alias", "perfiles.nombre as perfil")
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->join("vendiendo.usuarios", "facturas.usuario_creador_id", "=", "usuarios.id")
                        ->join("vendiendo.perfiles", "usuarios.perfil_id", "=", "perfiles.id")
                        ->where("facturas.estado", "<>", "anulada")
                        ->orderBy("total", "DESC")
                        ->groupBy("usuarios.id")->get();

                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $empleados = $empleados->where('facturas.almacen_id',$alm_obj->id);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $empleados = $empleados->where('facturas.almacen_id',$almacen);
                    }
                }else{
                    $empleados = Factura::permitidos()->select(DB::raw("sum(subtotal + iva) as total"), "usuarios.nombres", "usuarios.apellidos", "usuarios.alias", "perfiles.nombre as perfil")
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->join("usuarios", "facturas.usuario_creador_id", "=", "usuarios.id")
                        ->join("perfiles", "usuarios.perfil_id", "=", "perfiles.id")
                        ->where("facturas.estado", "<>", "anulada")
                        ->orderBy("total", "DESC")
                        ->groupBy("usuarios.id")->get();
                }
                return view("reportes.ventas_por_empleado.grafica")->with("empleados", $empleados);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getExcelVentasPorEmpleado(Request $request){
        $r = Reporte::where('nombre','Ventas por empleado')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte ventas por empleado";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si') {
                    $empleados = ABFactura::permitidos()->select(DB::raw("sum(subtotal + iva) as total"), "usuarios.nombres", "usuarios.apellidos", "usuarios.alias", "perfiles.nombre as perfil")
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->join("vendiendo.usuarios", "facturas.usuario_creador_id", "=", "usuarios.id")
                        ->join("vendiendo.perfiles", "usuarios.perfil_id", "=", "perfiles.id")
                        ->where("facturas.estado", "<>", "anulada")
                        ->orderBy("total", "DESC")
                        ->groupBy("usuarios.id")->get();

                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $empleados = $empleados->where('facturas.almacen_id',$alm_obj->id);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $empleados = $empleados->where('facturas.almacen_id',$almacen);
                    }
                }else{
                    $empleados = Factura::permitidos()->select(DB::raw("sum(subtotal + iva) as total"), "usuarios.nombres", "usuarios.apellidos", "usuarios.alias", "perfiles.nombre as perfil")
                        ->whereBetween("facturas.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                        ->join("usuarios", "facturas.usuario_creador_id", "=", "usuarios.id")
                        ->join("perfiles", "usuarios.perfil_id", "=", "perfiles.id")
                        ->where("facturas.estado", "<>", "anulada")
                        ->orderBy("total", "DESC")
                        ->groupBy("usuarios.id")->get();
                }
                return Excel::create($nombre, function ($excel) use ($empleados) {
                    $excel->sheet('Lista empleados', function ($sheet) use ($empleados) {
                        $sheet->loadView('reportes.ventas_por_empleado.listaExcel', ["empleados" => $empleados, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getObjetivosVentas(){
        $r = Reporte::where('nombre','Objetivos de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.objetivos_ventas.index');
        return redirect('/');
    }

    public function postListObjetivosVentas(Request $request){
        $r = Reporte::where('nombre','Objetivos de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $meses = [
                1 => "Enero",
                2 => "Febrero",
                3 => "Marzo",
                4 => "Abril",
                5 => "Mayo",
                6 => "Junio",
                7 => "Julio",
                8 => "Agosto",
                9 => "Septiembre",
                10 => "Octubre",
                11 => "Noviembre",
                12 => "Diciembre",
            ];
            if ($request->has("anio_inicio") && $request->has("mes_inicio") && $request->has("anio_fin") && $request->has("mes_fin")) {
                $fecha_fin_str = $request->input("anio_fin") . "-" . $request->input("mes_fin");
                $fecha_inicio_str = $request->input("anio_inicio") . "-" . $request->input("mes_inicio");
                $fecha_fin = strtotime($fecha_fin_str);
                $fecha_inicio = strtotime($fecha_inicio_str);
                if ($fecha_inicio > $fecha_fin) {
                    return response(["error" => ["La fecha de inicio compuesta por año de inicio y mes de inicio no debe ser mayor a la fecha de fin compuesta por año fin y mes fin"]], 422);
                }
                if (!ObjetivoVenta::permitidos()->get()->count()) {
                    return response(["error" => ["Aún no han registrado objetivos de ventas."]], 422);
                }

                $primer_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio", "ASC")->orderBy("mes", "ASC");

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$primer_objetivo = $primer_objetivo->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $primer_objetivo = $primer_objetivo->where('almacen_id',$almacen);
                    }
                }
                $primer_objetivo = $primer_objetivo->first();

                if ($request->input("anio_inicio") < $primer_objetivo->anio) {
                    return response(["error" => ["El valor mínimo aceptado en el campo año inicio es " . $primer_objetivo->anio]], 422);
                } else if (($request->input("anio_inicio") == $primer_objetivo->anio)) {
                    if ($request->input("mes_inicio") < $primer_objetivo->mes) {
                        return response(["error" => ["La fecha de inicio mínima aceptada es " . $meses[$primer_objetivo->mes] . "/" . $primer_objetivo->anio]], 422);
                    }
                }

                $ultimo_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio", "DESC")->orderBy("mes", "DESC");
                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$ultimo_objetivo = $ultimo_objetivo->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $ultimo_objetivo = $ultimo_objetivo->where('almacen_id',$almacen);
                    }
                }
                $ultimo_objetivo = $primer_objetivo->first();
                if ($request->input("anio_fin") > $ultimo_objetivo->anio) {
                    return response(["error" => ["El valor máximo aceptado en el campo año fin es " . $ultimo_objetivo->anio]], 422);
                } else if ($request->input("anio_fin") == $ultimo_objetivo->anio) {
                    if ($request->input("mes_fin") > $ultimo_objetivo->mes) {
                        return response(["error" => ["La fecha de fin máxima aceptada es " . $meses[$ultimo_objetivo->mes] . "/" . $ultimo_objetivo->anio]], 422);
                    }
                }
                return view("reportes.objetivos_ventas.lista");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListReporteObjetivosVentas(Request $request){
        $r = Reporte::where('nombre','Objetivos de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $meses = [1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre",];
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $myArray = [];
            $data = ['length' => $length, 'start' => $start, 'buscar' => $search['value'], 'draw' => '', 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'info' => 0];

            if ($request->has("anio_inicio") && $request->has("mes_inicio") && $request->has("anio_fin") && $request->has("mes_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->admin_bodegas == 'si')
                    $objetivosVentas = ObjetivoVenta::permitidos()->select('objetivos_ventas.*',DB::raw("str_to_date(concat_ws('-','01',mes,anio),'%d-%m-%Y') as fecha"), DB::raw('SUM(valor) as valor'))->groupBy('fecha');
                else
                    $objetivosVentas = ObjetivoVenta::permitidos();

                $objetivosVentas = $objetivosVentas->where(function ($q) use ($request) {
                        $q->whereBetween("anio", [$request->input("anio_inicio") + 1, $request->input("anio_fin") - 1])
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_inicio"))->where("anio", "<>", $request->input("anio_fin"))->where("mes", ">=", $request->input("mes_inicio"));
                            })
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_fin"))->where("anio", "<>", $request->input("anio_inicio"))->where("mes", "<=", $request->input("mes_fin"));
                            })
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_fin"))->where("anio", $request->input("anio_inicio"))->where("mes", "<=", $request->input("mes_fin"))->where("mes", ">=", $request->input("mes_inicio"));
                            });
                    })->orderBy("anio", "DESC")->orderBy("mes", "DESC");

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$objetivosVentas = $objetivosVentas->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $objetivosVentas = $objetivosVentas->where('almacen_id',$almacen);
                    }
                }


                $totalRegistros = $objetivosVentas->get()->count();
                // if($search['value'] != null){
                //       $objetivosVentas = $objetivosVentas->whereRaw(
                //           " ( LOWER(CONCAT(nombres,' ',apellidos)) LIKE '%".\strtolower($search["value"])."%' OR".
                //           " LOWER(alias) LIKE '%".\strtolower($search["value"])."%' OR".
                //           " LOWER(perfiles.nombre) LIKE '%".\strtolower($search["value"])."%')");
                //   }


                $parcialRegistros = $objetivosVentas->get()->count();
                $objetivosVentas = $objetivosVentas->skip($start)->take($length);

                $almacen = '';
                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    foreach ($objetivosVentas->get() as $o) {
                        if(Auth::user()->bodegas == 'si') {
                            if(Auth::user()->admin_bodegas == 'si') {
                                if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0)
                                    $acumulado = $o->valorAcumulado($o->almacen_id);
                                else
                                    $acumulado = $o->valorAcumulado();
                            }else {
                                $acumulado = $o->valorAcumulado($o->almacen_id);
                                $almacen = $o->almacen->nombre;
                            }
                        }else {
                            $acumulado = $o->valorAcumulado();
                        }


                        $myArray[] = (object)array(
                            'data_1' => "".$meses[$o->mes] . "/" . $o->anio,
                            'data_2' => "$" . number_format($o->valor, 0, ',', '.'),
                            'data_3' => "$" . number_format($acumulado, 2, ',', '.'),
                            'data_4' => number_format(($acumulado * 100) / $o->valor, 2, ',', '.') . "%",
                            'data_5' => $almacen
                        );
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $objetivosVentas->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'data' => $myArray,
                    'info' => $objetivosVentas->get()];
            }
            return response()->json($data);
        }
    }

    public function postGraficaObjetivosVentas(Request $request){
        $r = Reporte::where('nombre','Objetivos de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $meses = [
                1 => "Enero",
                2 => "Febrero",
                3 => "Marzo",
                4 => "Abril",
                5 => "Mayo",
                6 => "Junio",
                7 => "Julio",
                8 => "Agosto",
                9 => "Septiembre",
                10 => "Octubre",
                11 => "Noviembre",
                12 => "Diciembre",
            ];
            if ($request->has("anio_inicio") && $request->has("mes_inicio") && $request->has("anio_fin") && $request->has("mes_fin")) {

                $fecha_fin_str = $request->input("anio_fin") . "-" . $request->input("mes_fin");
                $fecha_inicio_str = $request->input("anio_inicio") . "-" . $request->input("mes_inicio");
                $fecha_fin = strtotime($fecha_fin_str);
                $fecha_inicio = strtotime($fecha_inicio_str);
                if ($fecha_inicio > $fecha_fin) {
                    return response(["error" => ["La fecha de inicio compuesta por año de inicio y mes de inicio no debe ser mayor a la fecha de fin compuesta por año fin y mes fin"]], 422);
                }
                if (!ObjetivoVenta::permitidos()->get()->count()) {
                    return response(["error" => ["Aún no han registrado objetivos de ventas."]], 422);
                }

                $primer_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio", "ASC")->orderBy("mes", "ASC");

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$primer_objetivo = $primer_objetivo->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $primer_objetivo = $primer_objetivo->where('almacen_id',$almacen);
                    }
                }
                $primer_objetivo = $primer_objetivo->first();

                if ($request->input("anio_inicio") < $primer_objetivo->anio) {
                    return response(["error" => ["El valor mínimo aceptado en el campo año inicio es " . $primer_objetivo->anio]], 422);
                } else if (($request->input("anio_inicio") == $primer_objetivo->anio)) {
                    if ($request->input("mes_inicio") < $primer_objetivo->mes) {
                        return response(["error" => ["La fecha de inicio mínima aceptada es " . $meses[$primer_objetivo->mes] . "/" . $primer_objetivo->anio]], 422);
                    }
                }

                $ultimo_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio", "DESC")->orderBy("mes", "DESC");

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj) $ultimo_objetivo = $ultimo_objetivo->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $ultimo_objetivo = $ultimo_objetivo->where('almacen_id',$almacen);
                    }
                }
                $ultimo_objetivo = $ultimo_objetivo->first();

                if ($request->input("anio_fin") > $ultimo_objetivo->anio) {
                    return response(["error" => ["El valor máximo aceptado en el campo año fin es " . $ultimo_objetivo->anio]], 422);
                } else if ($request->input("anio_fin") == $ultimo_objetivo->anio) {
                    if ($request->input("mes_fin") > $ultimo_objetivo->mes) {
                        return response(["error" => ["La fecha de fin máxima aceptada es " . $meses[$ultimo_objetivo->mes] . "/" . $ultimo_objetivo->anio]], 422);
                    }
                }

                if(Auth::user()->admin_bodegas == 'si')
                    $objetivosVentas = ObjetivoVenta::permitidos()->select('objetivos_ventas.*',DB::raw("str_to_date(concat_ws('-','01',mes,anio),'%d-%m-%Y') as fecha"), DB::raw('SUM(valor) as valor'))->groupBy('fecha');
                else
                    $objetivosVentas = ObjetivoVenta::permitidos();

                $objetivosVentas = $objetivosVentas
                    ->where(function ($q) use ($request) {
                        $q->whereBetween("anio", [$request->input("anio_inicio") + 1, $request->input("anio_fin") - 1])
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_inicio"))->where("anio", "<>", $request->input("anio_fin"))->where("mes", ">=", $request->input("mes_inicio"));
                            })
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_fin"))->where("anio", "<>", $request->input("anio_inicio"))->where("mes", "<=", $request->input("mes_fin"));
                            })
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_fin"))->where("anio", $request->input("anio_inicio"))->where("mes", "<=", $request->input("mes_fin"))->where("mes", ">=", $request->input("mes_inicio"));
                            });
                    })->orderBy("anio", "DESC")->orderBy("mes", "DESC");

                $almacen = '';
                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $almacen = $alm_obj->id;
                                $objetivosVentas = $objetivosVentas->where('almacen_id',$alm_obj->id);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $objetivosVentas = $objetivosVentas->where('almacen_id',$almacen);
                    }
                }
                $objetivosVentas = $objetivosVentas->get();
                return view("reportes.objetivos_ventas.grafica")->with("objetivosVentas", $objetivosVentas)->with('almacen',$almacen);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getExcelObjetivosVentas(Request $request){
        $r = Reporte::where('nombre','Objetivos de ventas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte objetivos de ventas";
            $meses = [
                1 => "Enero",
                2 => "Febrero",
                3 => "Marzo",
                4 => "Abril",
                5 => "Mayo",
                6 => "Junio",
                7 => "Julio",
                8 => "Agosto",
                9 => "Septiembre",
                10 => "Octubre",
                11 => "Noviembre",
                12 => "Diciembre",
            ];
            if ($request->has("anio_inicio") && $request->has("mes_inicio") && $request->has("anio_fin") && $request->has("mes_fin")) {
                $fecha_fin_str = $request->input("anio_fin") . "-" . $request->input("mes_fin");
                $fecha_inicio_str = $request->input("anio_inicio") . "-" . $request->input("mes_inicio");
                $fecha_fin = strtotime($fecha_fin_str);
                $fecha_inicio = strtotime($fecha_inicio_str);
                if ($fecha_inicio > $fecha_fin) {
                    return response(["error" => ["La fecha de inicio compuesta por año de inicio y mes de inicio no debe ser mayor a la fecha de fin compuesta por año fin y mes fin"]], 422);
                }
                if (!ObjetivoVenta::permitidos()->get()->count()) {
                    return response(["error" => ["Aún no han registrado objetivos de ventas."]], 422);
                }

                $primer_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio", "ASC")->orderBy("mes", "ASC");

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj)$primer_objetivo = $primer_objetivo->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $primer_objetivo = $primer_objetivo->where('almacen_id',$almacen);
                    }
                }
                $primer_objetivo = $primer_objetivo->first();

                if ($request->input("anio_inicio") < $primer_objetivo->anio) {
                    return response(["error" => ["El valor mínimo aceptado en el campo año inicio es " . $primer_objetivo->anio]], 422);
                } else if (($request->input("anio_inicio") == $primer_objetivo->anio)) {
                    if ($request->input("mes_inicio") < $primer_objetivo->mes) {
                        return response(["error" => ["La fecha de inicio mínima aceptada es " . $meses[$primer_objetivo->mes] . "/" . $primer_objetivo->anio]], 422);
                    }
                }

                $ultimo_objetivo = \App\Models\ObjetivoVenta::permitidos()->orderBy("anio", "DESC")->orderBy("mes", "DESC");

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj) $ultimo_objetivo = $ultimo_objetivo->where('almacen_id',$alm_obj->id);
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $ultimo_objetivo = $ultimo_objetivo->where('almacen_id',$almacen);
                    }
                }
                $ultimo_objetivo = $ultimo_objetivo->first();

                if ($request->input("anio_fin") > $ultimo_objetivo->anio) {
                    return response(["error" => ["El valor máximo aceptado en el campo año fin es " . $ultimo_objetivo->anio]], 422);
                } else if ($request->input("anio_fin") == $ultimo_objetivo->anio) {
                    if ($request->input("mes_fin") > $ultimo_objetivo->mes) {
                        return response(["error" => ["La fecha de fin máxima aceptada es " . $meses[$ultimo_objetivo->mes] . "/" . $ultimo_objetivo->anio]], 422);
                    }
                }

                if(Auth::user()->admin_bodegas == 'si')
                    $objetivosVentas = ObjetivoVenta::permitidos()->select('objetivos_ventas.*',DB::raw("str_to_date(concat_ws('-','01',mes,anio),'%d-%m-%Y') as fecha"), DB::raw('SUM(valor) as valor'))->groupBy('fecha');
                else
                    $objetivosVentas = ObjetivoVenta::permitidos();

                $objetivosVentas = $objetivosVentas->where(function ($q) use ($request) {
                        $q->whereBetween("anio", [$request->input("anio_inicio") + 1, $request->input("anio_fin") - 1])
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_inicio"))->where("anio", "<>", $request->input("anio_fin"))->where("mes", ">=", $request->input("mes_inicio"));
                            })
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_fin"))->where("anio", "<>", $request->input("anio_inicio"))->where("mes", "<=", $request->input("mes_fin"));
                            })
                            ->orWhere(function ($q) use ($request) {
                                $q->where("anio", $request->input("anio_fin"))->where("anio", $request->input("anio_inicio"))->where("mes", "<=", $request->input("mes_fin"))->where("mes", ">=", $request->input("mes_inicio"));
                            });
                    })->orderBy("anio", "DESC")->orderBy("mes", "DESC");

                $almacen = '';
                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $almacen = $alm_obj->id;
                                $objetivosVentas = $objetivosVentas->where('almacen_id',$alm_obj->id);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }

                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $objetivosVentas = $objetivosVentas->where('almacen_id',$almacen);
                    }
                }

                $objetivosVentas = $objetivosVentas->get();
                return Excel::create($nombre, function ($excel) use ($objetivosVentas,$almacen) {
                    $excel->sheet('Lista objetivos ventas', function ($sheet) use ($objetivosVentas,$almacen) {
                        $sheet->loadView('reportes.objetivos_ventas.listaExcel', ["objetivosVentas" => $objetivosVentas,"almacen"=>$almacen, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getInventarioMateriasPrimas(Request $request){
        $r = Reporte::where('nombre','Inventario materias primas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if (!$request->has("filtro")) {
                $filtro = 'todos';
            } else {
                $filtro = $request->input("filtro");
            }
            //$materias = MateriaPrima::permitidos()->groupby('materias_primas.nombre');
            //if ($filtro == "bajoUmbral")
            //	$materias = $materias->whereRaw('materias_primas.stock <= materias_primas.umbral');
            //else if ($filtro == 'altoUmbral')
            //	$materias = $materias->whereRaw('materias_primas.stock > materias_primas.umbral');
            //$materias = $materias->paginate(env('PAGINATE'));
            return view('reportes.inventario_materias_primas.index', compact('filtro'));
        }
        return redirect('/');
    }

    public function getListReporteInventarioMateriasPrimas(Request $request){
        $r = Reporte::where('nombre','Inventario materias primas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            switch ($orderBy) {
                case 'nombre':
                    $orderBy = "materias_primas.nombre";
                    break;
                case 'precio':
                    $orderBy = "precio_costo";
                    break;
                case 'unidad':
                    $orderBy = "unidad_id";
                    break;
                case 'proveedor':
                    $orderBy = "proveedores.nombre";
                    break;
            }

            $materias = MateriaPrima::permitidos()->groupBy("materias_primas.id");


            if (!$request->has("filtro"))
                $filtro = 1;
            else
                $filtro = $request->get("filtro");

            switch ($filtro) {
                case "bajoUmbral":
                    $materias = $materias->whereRaw('materias_primas.stock <= materias_primas.umbral');;
                    break;
                case "altoUmbral":
                    $materias = $materias->whereRaw('materias_primas.stock > materias_primas.umbral');
                    break;
            }

            $materias = $materias->orderBy($orderBy, $sortColumnDir);
            $totalRegistros = $materias->count();
            //BUSCAR
            if ($search['value'] != null) {

                $materias = $materias->whereRaw(
                    " ( codigo LIKE '%" . $search["value"] . "%' OR " .
                    " LOWER(materias_primas.nombre) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                    " LOWER(descripcion) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                    " umbral LIKE '%" . $search["value"] . "%' OR" .
                    " stock LIKE '%" . $search["value"] . "%' OR" .
                    " precio_costo LIKE '%" . $search["value"] . "%' OR" .
                    //" unidad LIKE '%".$search["value"]."%' OR".
                    " LOWER(proveedores.nombre) LIKE '%" . \strtolower($search["value"]) . "%')");
            }

            $parcialRegistros = $materias->count();
            $materias = $materias->skip($start)->take($length);


            $object = new \stdClass();
            if ($parcialRegistros > 0) {
                foreach ($materias->get() as $materia) {
                    $precio = 0;
                    if ($materia->precio_costo != '')
                        $precio = $materia->precio_costo;
                    $myArray[] = (object)array(
                        'codigo' => $materia->codigo,
                        'nombre' => \App\TildeHtml::TildesToHtml($materia->nombre),
                        'descripcion' => \App\TildeHtml::TildesToHtml($materia->descripcion),
                        'umbral' => $materia->umbral,
                        'stock' => $materia->stock,
                        'precio' => '$' . number_format($precio, 0, ",", "."),
                        'unidad' => $materia->unidad->sigla,
                        'proveedor' => $materia->proveedorActual->nombre);
                }
            } else {
                $myArray = [];
            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $materias->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'info' => $materias->get()];

            return response()->json($data);
        }
    }


    public function getExcelInventarioMateriasPrimas(Request $request){
        $r = Reporte::where('nombre','Inventario materias primas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if (!$request->has("filtro")) {
                $filtro = 'todos';
            } else {
                $filtro = $request->input("filtro");
            }
            $materias = MateriaPrima::permitidos()->groupby('materias_primas.nombre');
            $nombre_reporte = "Reporte inventario materias primas";
            switch ($filtro) {
                case 'bajoUmbral':
                    $materias = $materias->whereRaw('materias_primas.stock <= materias_primas.umbral');
                    $nombre_reporte .= "- bajo umbral";
                    break;
                case 'altoUmbral':
                    $materias = $materias->whereRaw('materias_primas.stock > materias_primas.umbral');
                    $nombre_reporte .= "- sobre umbral";
                    break;
            }
            $materias = $materias->get();

            return Excel::create($nombre_reporte, function ($excel) use ($materias) {
                $excel->sheet("Materias primas", function ($sheet) use ($materias) {
                    $sheet->loadView('reportes.inventario_materias_primas.lista', ['materias' => $materias, 'reporte' => true]);
                });
            })->export('xls');
        }
    }

    public function getControlEmpleados(){
        $r = Reporte::where('nombre','Control empleados')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.control_empleados.index');
        return redirect('/');
    }

    public function getListReporteControlEmpleados(Request $request){
        $r = Reporte::where('nombre','Control empleados')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $myArray = [];
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $empleados = ControlEmpleados::permitidos()
                    ->select('control_empleados.id AS id',
                        'control_empleados.nombre AS nombre',
                        'control_empleados.cedula AS cedula',
                        'control_empleados.estado_empleado AS estado_empleado',
                        'control_empleados_registros.estado_sesion AS estado_sesion',
                        'control_empleados_registros.fecha_llegada AS fecha_llegada',
                        'control_empleados_registros.fecha_salida AS fecha_salida',
                        'control_empleados_registros.bodega_id',
                        'control_empleados_registros.almacen_id',
                        'control_empleados_registros.created_at AS creacion_registro')
                    ->join('control_empleados_registros', 'control_empleados_registros.control_empleado_id', '=', 'control_empleados.id')
                    ->whereBetween("control_empleados_registros.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                    //->where('control_empleados_registros.fecha_llegada','like','%'.date("Y-m-d").'%')
                    ->where('control_empleados_registros.estado_sesion', '=', 'off')
                    ->orderBy('control_empleados_registros.created_at', 'DESC');
                //->orderBy($orderBy,$sortColumnDir);


                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0 && $request->input('almacen') != 'bodega'){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $almacen = $alm_obj->id;
                                $empleados = $empleados->where('almacen_id',$almacen);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }else if($request->has('almacen') && $request->input('almacen') == 'bodega'){
                            $empleados = $empleados->whereNotNull('bodega_id');
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $empleados = $empleados->where('almacen_id',$almacen);
                    }
                }

                $totalRegistros = $empleados->count();

                if ($request->input("select_nombre") != "")
                    $empleados = $empleados->where('control_empleados.id', '=', $request->input("select_nombre"));
                if ($search['value'] != null) {
                    $empleados = $empleados->whereRaw(
                        " ( LOWER(control_empleados.nombre) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(control_empleados.cedula) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(control_empleados_registros.estado_sesion) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(control_empleados_registros.fecha_llegada) LIKE '%" . \strtolower($search["value"]) . "%' OR" .
                        " LOWER(control_empleados_registros.fecha_salida) LIKE '%" . \strtolower($search["value"]) . "%'" .
                        ")");
                }
                $parcialRegistros = $empleados->count();
                $empleados = $empleados->skip($start)->take($length);
                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    foreach ($empleados->get() as $em) {
                        $lugar = null;
                        if(Auth::user()->bodegas == 'si'){
                            if($em->bodega_id){
                                $bodega = Bodega::find($em->bodega_id);
                                $lugar = $bodega->nombre;
                            }
                            if($em->almacen_id){
                                $almacen = Almacen::find($em->almacen_id);
                                $lugar = $almacen->nombre;
                            }
                        }
                        $myArray[] = (object)array('id' => $em->id,
                            'nombre' => $em->nombre,
                            'cedula' => $em->cedula,
                            'estado_empleado' => $em->estado_empleado,
                            'estado_sesion' => $em->estado_sesion,
                            'fecha_llegada' => $em->fecha_llegada,
                            'fecha_salida' => $em->fecha_salida,
                            'lugar' => $lugar,
                            'creacion_registro' => (String)date("Y-m-d", strtotime($em->creacion_registro)));
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    'last_query' => $empleados->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'data' => $myArray,
                    'info' => $empleados->get()
                ];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getExcelReporteControlEmpleados(Request $request){
        $r = Reporte::where('nombre','Control empleados')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte Control Empleados";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $empleados = ControlEmpleados::permitidos()
                    ->select('control_empleados.id AS id',
                        'control_empleados.nombre AS nombre',
                        'control_empleados.cedula AS cedula',
                        'control_empleados.estado_empleado AS estado_empleado',
                        'control_empleados_registros.estado_sesion AS estado_sesion',
                        'control_empleados_registros.fecha_llegada AS fecha_llegada',
                        'control_empleados_registros.fecha_salida AS fecha_salida',
                        'control_empleados_registros.bodega_id',
                        'control_empleados_registros.almacen_id',
                        'control_empleados_registros.created_at AS creacion_registro')
                    ->join('control_empleados_registros', 'control_empleados_registros.control_empleado_id', '=', 'control_empleados.id')
                    ->whereBetween("control_empleados_registros.created_at", [$request->input("fecha_inicio"), $fecha_fin])
                    ->where('control_empleados_registros.estado_sesion', '=', 'off')
                    ->orderBy('control_empleados_registros.created_at', 'DESC');

                if(Auth::user()->bodegas == 'si'){
                    if(Auth::user()->admin_bodegas == 'si'){
                        if($request->has('almacen') && !is_null($request->input('almacen')) && $request->input('almacen') != 0 && $request->input('almacen') != 'bodega'){
                            $alm_obj = Almacen::permitidos()->where('id',$request->input('almacen'))->first();
                            if($alm_obj){
                                $almacen = $alm_obj->id;
                                $empleados = $empleados->where('almacen_id',$almacen);
                            }
                            else return response(['error'=>['la información enviada es incorrecta']],422);
                        }else if($request->has('almacen') && $request->input('almacen') == 'bodega'){
                            $empleados = $empleados->whereNotNull('bodega_id');
                        }
                    }else{
                        $almacen = Auth::user()->almacenActual()->id;
                        $empleados = $empleados->where('almacen_id',$almacen);
                    }
                }
                //->orderBy($orderBy,$sortColumnDir);

                if ($request->input("select_nombre") != "")
                    $empleados = $empleados->where('control_empleados.id', '=', $request->input("select_nombre"));
                return Excel::create($nombre, function ($excel) use ($empleados) {
                    $excel->sheet('Lista de empleados', function ($sheet) use ($empleados) {
                        $sheet->loadView('reportes.control_empleados.lista_excel', ["empleados" => $empleados->get(), "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }
    public function getCompras(){
        $r = Reporte::where('nombre','Compras')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            return view('reportes.compras.index');
        }
        return redirect('/');
    }
    /*public function postListCompras(Request $request){
        if($request->has("fecha_inicio") && $request->has("fecha_fin")){
            $fecha_fin = date("Y-m-d",strtotime("+1days",strtotime($request->input("fecha_fin"))));
            $compras = Compra::permitidos()
                ->whereBetween("created_at",[$request->input("fecha_inicio"),$fecha_fin])
                ->orderby('created_at','DESC')
                ->paginate(env('PAGINATE'));
            return view("reportes.compras.lista")->with("compras",$compras);
        }else{
            return response(["La información enviada es incorrecta"],422);
        }
    }
    public function getListCompras(Request $request){
        if($request->has("fecha_inicio") && $request->has("fecha_fin")){
            $fecha_fin = date("Y-m-d",strtotime("+1days",strtotime($request->input("fecha_fin"))));
            $compras = Compra::permitidos()
                ->whereBetween("created_at",[$request->input("fecha_inicio"),$fecha_fin])
                ->orderby('created_at','DESC')
                ->paginate(env('PAGINATE'));
            return view("reportes.compras.lista")->with("compras",$compras);
        }else{
            return response(["La información enviada es incorrecta"],422);
        }
    }*/
    public function getListCompras(Request $request){
        $r = Reporte::where('nombre','Compras')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            // Datos de DATATABLE
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];

            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si'){
                    $compras = ABCompra::permitidos()
                        ->whereBetween("created_at", [$request->get("fecha_inicio"), $fecha_fin])/*->orderby('created_at', 'DESC')*/
                    ;
                }else {
                    $compras = Compra::permitidos()
                        ->whereBetween("created_at", [$request->get("fecha_inicio"), $fecha_fin])/*->orderby('created_at', 'DESC')*/
                    ;
                }

                $compras = $compras->orderBy($orderBy, $sortColumnDir);
                $totalRegistros = $compras->count();
                //BUSCAR
                if ($search['value'] != null) {
                    $compras = $compras->whereRaw(
                        " ( created_at LIKE '%" . $search["value"] . "%' OR " .
                        " estado LIKE '%" . $search["value"] . "%' OR" .
                        " numero LIKE '%" . $search["value"] . "%' OR" .
                        " estado_pago LIKE '%" . $search["value"] . "%' )");
                }

                $parcialRegistros = $compras->count();
                $compras = $compras->skip($start)->take($length);


                $object = new \stdClass();
                if ($parcialRegistros > 0) {
                    foreach ($compras->get() as $value) {
                        $devoluciones = "no";
                        if (count($value->cuentasPorCobrar) > 0) $devoluciones = "si";
                        $myArray[] = (object)array(
                            'id' => $value->id,
                            'numero' => $value->numero,
                            'valor' => "$ " . number_format($value->valor, 2, ',', '.'),
                            'fecha' => date('Y-m-d', strtotime($value->created_at)),
                            'proveedor' => $value->proveedor->nombre,
                            'usuario' => $value->usuario->nombres . " " . $value->apellidos,
                            'estado' => $value->estado,
                            'estado_pago' => $value->estado_pago,
                            'detalle' => '<a href="#!" class="detalle-compra" data-compra="' . $value->id . '"><i class="fa fa-list-alt"></i></a>',
                            'devoluciones' => $devoluciones,
                        );
                    }
                } else {
                    $myArray = [];
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $cajas->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'fecha_inicio' => $request->get("fecha_inicio"),
                    'count' => $parcialRegistros,
                    'data' => $myArray,//->where('id','"',22)->get(),
                    'info' => $compras->get()];

                return response()->json($data);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getExcelCompras(Request $request){
        $r = Reporte::where('nombre','Compras')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte compras";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if(Auth::user()->bodegas == 'si') {
                    $compras = ABCompra::permitidos()
                        ->whereBetween("created_at", [$request->get("fecha_inicio"), $fecha_fin])
                        ->orderby('created_at', 'DESC')
                        ->get();
                }else{
                    $compras = Compra::permitidos()
                        ->whereBetween("created_at", [$request->get("fecha_inicio"), $fecha_fin])
                        ->orderby('created_at', 'DESC')
                        ->get();
                }
                $myArray = [];
                foreach ($compras as $value) {
                    $devoluciones = "no";
                    if (count($value->cuentasPorCobrar) > 0) $devoluciones = "si";
                    $myArray[] = (object)array(
                        'id' => $value->id,
                        'numero' => $value->numero,
                        'valor' => number_format($value->valor, 2, ',', ''),
                        'fecha' => $value->created_at,
                        'proveedor' => $value->proveedor->nombre,
                        'usuario' => $value->usuario->nombres . " " . $value->apellidos,
                        'estado' => $value->estado,
                        'estado_pago' => $value->estado_pago,
                        'devoluciones' => $devoluciones
                    );
                }
                return Excel::create($nombre, function ($excel) use ($myArray) {
                    $excel->sheet('Lista compras', function ($sheet) use ($myArray) {
                        $sheet->loadView('reportes.compras.lista', ["compras" => $myArray, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function postDetalleCompra(Request $request){
        $r = Reporte::where('nombre','Compras')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("compra")) {
                if(Auth::user()->bodegas == 'si')
                    $compra = ABCompra::permitidos()->where("compras.id", $request->input("compra"))->first();
                else
                    $compra = Compra::permitidos()->where("compras.id", $request->input("compra"))->first();

                if ($compra) {
                    if(Auth::user()->bodegas == 'si'){
                        $productos = $compra->historialCostos()->select("productos.*", "compras_historial_costos.cantidad",
                            "historial_costos.precio_costo_nuevo",
                            //"historial_costos.utilidad_nueva",
                            "historial_costos.iva_nuevo", "productos.*")
                            ->join("productos", "historial_costos.producto_id", "=", "productos.id")->get();

                        $materias_primas = $compra->materias_primas_historial()->select("compras_materias_primas_historial.cantidad", "materias_primas.*", "materias_primas_historial.precio_costo_nuevo")
                            ->join("materias_primas", "materias_primas_historial.materia_prima_id", "=", "materias_primas.id")->get();
                    }else {
                        $productos = $compra->productosHistorial()->select("productos.*", "compras_productos_historial.cantidad",
                            "productos_historial.precio_costo_nuevo",
                            "productos_historial.utilidad_nueva",
                            "productos_historial.iva_nuevo", "productos.*")
                            ->join("productos", "productos_historial.producto_id", "=", "productos.id")->get();

                        $materias_primas = $compra->materias_primas_historial()->select("compras_materias_primas_historial.cantidad", "materias_primas.*", "materias_primas_historial.precio_costo_nuevo")
                            ->join("materias_primas", "materias_primas_historial.materia_prima_id", "=", "materias_primas.id")->get();
                    }

                    return view("reportes/compras/detalle_compra")->with("compra", $compra)->with("productos", $productos)->with("materias_primas", $materias_primas)->with("lista_devoluciones", $compra->cuentasPorCobrar)->render();
                }
            }
            return response(["error" => ["La información enviada en incorrecta"]], 422);
        }
    }


    public function getExcelDetalleCompra(Request $request){
        $r = Reporte::where('nombre','Compras')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("compra")) {
                if(Auth::user()->bodegas == 'si')
                    $compra = ABCompra::permitidos()->where("compras.id", $request->input("compra"))->first();
                else
                    $compra = Compra::permitidos()->where("compras.id", $request->input("compra"))->first();

                if ($compra) {
                    $nombre = "Reporte detalle de compra " . $compra->numero;
                    if(Auth::user()->bodegas == 'si'){
                        $productos = $compra->historialCostos()->select("productos.*", "compras_historial_costos.cantidad",
                            "historial_costos.precio_costo_nuevo",
                            //"historial_costos.utilidad_nueva",
                            "historial_costos.iva_nuevo", "productos.*")
                            ->join("productos", "historial_costos.producto_id", "=", "productos.id")->get();

                        $materias_primas = $compra->materias_primas_historial()->select("compras_materias_primas_historial.cantidad", "materias_primas.*", "materias_primas_historial.precio_costo_nuevo")
                            ->join("materias_primas", "materias_primas_historial.materia_prima_id", "=", "materias_primas.id")->get();
                    }else {
                        $productos = $compra->productosHistorial()->select("productos.*", "compras_productos_historial.cantidad",
                            "productos_historial.precio_costo_nuevo",
                            "productos_historial.utilidad_nueva",
                            "productos_historial.iva_nuevo", "productos.*")
                            ->join("productos", "productos_historial.producto_id", "=", "productos.id")->get();

                        $materias_primas = $compra->materias_primas_historial()->select("compras_materias_primas_historial.cantidad", "materias_primas.*", "materias_primas_historial.precio_costo_nuevo")
                            ->join("materias_primas", "materias_primas_historial.materia_prima_id", "=", "materias_primas.id")->get();
                    }

                    $lista_devoluciones = $compra->cuentasPorCobrar;

                    return Excel::create($nombre, function ($excel) use ($productos, $materias_primas, $lista_devoluciones) {
                        $excel->sheet('Detalles de la compra', function ($sheet) use ($productos, $materias_primas, $lista_devoluciones) {
                            $sheet->loadView('reportes.compras.detalle_compra', ["productos" => $productos, "materias_primas" => $materias_primas, "lista_devoluciones" => $lista_devoluciones, "reporte" => true]);
                        });
                    })->export("xls");
                }
            }

            return response(["La información enviada es incorrecta"], 422);
        }
    }

    public function getEntradasSalidas(){
        $r = Reporte::where('nombre','Entradas y salidas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.entradas_salidas.index');
        return redirect('/');
    }


    public function postListEntradasSalidas(Request $request){
        $r = Reporte::where('nombre','Entradas y salidas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {
                //$productos = Producto::topVentas($request->input("fecha_inicio"),$fecha_fin,$request->input("top"),$categoria,10);
                return view("reportes.entradas_salidas.lista");//->with("productos",$productos);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }

    public function getListReporteEntradasSalidas(Request $request){
        $r = Reporte::where('nombre','Entradas y salidas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $myArray = [];
            $fechaInicio = $request->input("fecha_inicio");
            $fechaFin = $request->has("fecha_fin");

            $data = ['length' => $length, 'start' => $start, 'buscar' => $search['value'], 'draw' => '', 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'info' => 0];
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {

                $fechaFin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));

                if(Auth::user()->bodegas == 'si')
                    $tabla = 'vendiendo_alm.v_entradas_salidas';
                else
                    $tabla = 'vendiendo.v_entradas_salidas';

                $result = DB::table($tabla)->where('usuario_id', Auth::user()->userAdminId())
                    ->whereBetween("fecha_creacion", [$fechaInicio, $fechaFin])
                    ->where(function ($q_) {
                        //espacio para facturas
                        $q_->where(function ($q) {
                            $q->whereNotNull('estado_facturas')
                                ->where(function ($q2) {
                                    $q2->where("estado_facturas", "Pagada")
                                        ->orWhere("estado_facturas", "Pendiente por pagar")
                                        ->orWhere("estado_facturas", "cerrada")
                                        ->orWhere("estado_facturas", "abierta");
                                });
                        })
                        //espacio para compras
                        ->orWhere(function ($q) {
                            $q->whereNotNull('estado_compra')
                                ->where("estado_compra", "recibida");
                        })
                        //espacio para traslados
                        ->orWhere(function ($q) {
                            if(Auth::user()->bodegas == 'si')
                            $q->whereNotNull('estado_traslado');
                        })
                        //espacio para remisiones
                        ->orWhere(function ($q) {
                            $q->whereNotNull('estado_remision')
                                ->where(function ($q2) {
                                    $q2->where("estado_remision", "facturada")
                                        ->orWhere("estado_remision", "registrada");
                                });
                        });
                    })->orderBy('fecha_creacion','DESC');


                    $totalRegistros = count($result->get());
                    $parcialRegistros = count($result->get());
                    $result = $result->skip($start)->take($length);
                    $object = new \stdClass();
                    if ($parcialRegistros > 0) {
                        $productos = $result->get();
                        foreach ($productos as $producto) {
                            $tipo = '';
                            $valor = $producto->costo_calculado + (($producto->costo_calculado * $producto->iva_calculado)/100);
                            if (!is_null($producto->estado_facturas) && $producto->estado_facturas != '') {
                                $tipo = 'Salida (Factura No. '.$producto->numero_documento.')';
                                $valor += (($valor * $producto->utilidad_calculada)/100);
                            }

                            if (!is_null($producto->estado_compra) && $producto->estado_compra != '') {
                                $tipo = 'Entrada (Compra No. '.$producto->numero_documento.')';
                            }
                            if (Auth::user()->bodegas == 'si' && !is_null($producto->estado_traslado) && $producto->estado_traslado != '') {
                                if($producto->estado_traslado == 'recibido')
                                    $tipo = 'Entrada (Traslado)';
                                else
                                    $tipo = 'Salida (Traslado)';
                                $valor += (($valor * $producto->utilidad_calculada)/100);
                            }
                            if (!is_null($producto->estado_remision) && $producto->estado_remision != '') {
                                $tipo = 'Salida (Remisión No. '.$producto->numero_documento.')';
                                $valor += (($valor * $producto->utilidad_calculada)/100);
                            }
                            if (!is_null($producto->estado_remision_ingreso) && $producto->estado_remision_ingreso != '') {
                                $tipo = 'Entrada (Remisión Ingreso No. '.$producto->numero_documento.')';
                                //$valor += (($valor * $producto->utilidad_calculada)/100);
                            }
                            if (!is_null($producto->estado_remision_salida) && $producto->estado_remision_salida != '') {
                                $tipo = 'Salida (Remisión Salida No. '.$producto->numero_documento.')';
                                //$valor += (($valor * $producto->utilidad_calculada)/100);
                            }

                            $valor = $valor * $producto->cantidad_vendida;
                            $myArray[] = (object)array(
                                'fecha'=>''.$producto->fecha_creacion,
                                'tipo' => $tipo,
                                'barcode' => $producto->barcode,
                                'descripcion' => \App\TildeHtml::TildesToHtml($producto->descripcion),
                                'cantidad' => $producto->cantidad_vendida,
                                'valor' => '$ '.number_format($valor,2,',','.'),
                                'origen' => $producto->origen,
                                'destino' => $producto->destino);
                        }
                    }


            }

            $data = ['length' => $length,
                'start' => $start,
                'buscar' => $search['value'],
                'draw' => $request->get('draw'),
                //'last_query' => $result->toSql(),
                'recordsTotal' => $totalRegistros,
                'recordsFiltered' => $parcialRegistros,
                'data' => $myArray,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'info' => $result->get()];


            return response()->json($data);
        }
    }

    public function getExcelEntradasSalidas(Request $request){
        $r = Reporte::where('nombre','Entradas y salidas')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte de movimiento de inventario";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin")) {

                $fechaFin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                $fechaInicio = $request->input("fecha_inicio");

                if(Auth::user()->bodegas == 'si')
                    $tabla = 'vendiendo_alm.v_entradas_salidas';
                else
                    $tabla = 'vendiendo.v_entradas_salidas';

                $result = DB::table($tabla)->where('usuario_id', Auth::user()->userAdminId())
                    ->whereBetween("fecha_creacion", [$fechaInicio, $fechaFin])
                    ->where(function ($q_) {
                        //espacio para facturas
                        $q_->where(function ($q) {
                            $q->whereNotNull('estado_facturas')
                                ->where(function ($q2) {
                                    $q2->where("estado_facturas", "Pagada")
                                        ->orWhere("estado_facturas", "Pendiente por pagar")
                                        ->orWhere("estado_facturas", "cerrada")
                                        ->orWhere("estado_facturas", "abierta");
                                });
                        })
                            //espacio para compras
                            ->orWhere(function ($q) {
                                $q->whereNotNull('estado_compra')
                                    ->where("estado_compra", "recibida");
                            })
                            //espacio para traslados
                            ->orWhere(function ($q) {
                                if(Auth::user()->bodegas == 'si')
                                    $q->whereNotNull('estado_traslado');
                            })
                            //espacio para remisiones
                            ->orWhere(function ($q) {
                                $q->whereNotNull('estado_remision')
                                    ->where(function ($q2) {
                                        $q2->where("estado_remision", "facturada")
                                            ->orWhere("estado_remision", "registrada");
                                    });
                            });
                    })->get();


                return Excel::create($nombre, function ($excel) use ($result) {
                    $excel->sheet('Lista de productos', function ($sheet) use ($result) {
                        $sheet->loadView('reportes.entradas_salidas.listaExcel', ["productos" => $result, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }


    public function getMovimientosBancarios(){
        $r = Reporte::where('nombre','movimientos bancarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        )
            return view('reportes.movimientos_bancarios.index');
        return redirect('/');
    }

    public function postListMovimientosBancarios(Request $request){
        $r = Reporte::where('nombre','movimientos bancarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("desde")) {

                if ($request->has("cuenta")) {
                    $cuenta = CuentaBancaria::permitidos()->find($request->input("cuenta"));
                    if (!$cuenta) {
                        return response(["La información enviada es incorrecta"], 422);
                    }
                }
                $fecha_fin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                //$productos = Producto::topVentas($request->input("fecha_inicio"),$fecha_fin,$request->input("top"),$categoria,10);
                return view("reportes.movimientos_bancarios.lista");//->with("productos",$productos);
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }
    /*** REVISAR LEO ***/
    public function getListReporteMovimientosBancarios(Request $request){
        $r = Reporte::where('nombre','movimientos bancarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $search = $request->get("search");
            $order = $request->get("order");
            $sortColumnIndex = $order[0]['column'];
            $sortColumnDir = $order[0]['dir'];
            $length = $request->get('length');
            $start = $request->get('start');
            $columna = $request->get('columns');
            $orderBy = $columna[$sortColumnIndex]['data'];
            $myArray = [];
            $data_caja = [];
            $data_consignacion = [];
            $fechaInicio = $request->input("fecha_inicio");
            $fechaFin = $request->has("fecha_fin");
            $desde = $request->input("desde");
            $tipo = $request->input("tipo");
            $cuenta = $request->input("cuenta");
            $categoria = null;
            $paginate = null;
            $data = ['length' => $length, 'start' => $start, 'buscar' => $search['value'], 'draw' => '', 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => [], 'info' => 0];
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("desde")) {

                $fechaFin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if ($desde == "caja maestra") {
                    $result = MovimientoCajaBanco::select("movimientos_cajas_bancos.*")
                        ->join("caja", "movimientos_cajas_bancos.caja_id", "=", "caja.id")
                        ->where("caja.usuario_id", Auth::user()->userAdminId())
                        ->whereBetween("movimientos_cajas_bancos.created_at", [$fechaInicio, $fechaFin]);

                    if ($tipo == "retiros") $result = $result->where("movimientos_cajas_bancos.tipo", "Retiro");
                    if ($tipo == "consignaciones") $result = $result->where("movimientos_cajas_bancos.tipo", "Consignación");

                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result = $result->where("movimientos_cajas_bancos.cuenta_banco_id", $cuenta);
                        }
                    }

                } else if ($desde == 'usuario') {
                    $result = Consignacion::select("consignaciones.*")
                        ->join("cuentas_bancos", "consignaciones.cuenta_id", "=", "cuentas_bancos.id")
                        ->where("cuentas_bancos.usuario_id", Auth::user()->userAdminId())
                        ->whereNull("consignaciones.factura_tipo_pago_id")
                        ->whereBetween("consignaciones.created_at", [$fechaInicio, $fechaFin]);
                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result = $result->where("consignaciones.cuenta_id", $cuenta);
                        }
                    }
                } else if ($desde == 'facturas') {
                    $result = Consignacion::select("consignaciones.*","facturas.numero","facturas_tipos_pago.codigo_verificacion","tipos_pago.nombre as tipo_pago")
                        ->join("cuentas_bancos", "consignaciones.cuenta_id", "=", "cuentas_bancos.id")
                        ->leftJoin("facturas_tipos_pago","consignaciones.factura_tipo_pago_id","=","facturas_tipos_pago.id")
                        ->leftJoin("facturas","facturas_tipos_pago.factura_id","=","facturas.id")
                        ->leftJoin("tipos_pago","facturas_tipos_pago.tipo_pago_id","=","tipos_pago.id")
                        ->where("cuentas_bancos.usuario_id", Auth::user()->userAdminId())
                        ->whereNotNull("consignaciones.factura_tipo_pago_id")
                        ->whereBetween("consignaciones.created_at", [$fechaInicio, $fechaFin]);
                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result = $result->where("consignaciones.cuenta_id", $cuenta);
                        }
                    }
                } else if ($desde == 'todo') {
                    $result_caja = MovimientoCajaBanco::select("movimientos_cajas_bancos.*")
                        ->join("caja", "movimientos_cajas_bancos.caja_id", "=", "caja.id")
                        ->where("caja.usuario_id", Auth::user()->userAdminId())
                        ->whereBetween("movimientos_cajas_bancos.created_at", [$fechaInicio, $fechaFin]);

                    if ($tipo == "retiros") $result_caja = $result_caja->where("movimientos_cajas_bancos.tipo", "Retiro");
                    if ($tipo == "consignaciones") $result_caja = $result_caja->where("movimientos_cajas_bancos.tipo", "Consignación");

                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result_caja = $result_caja->where("movimientos_cajas_bancos.cuenta_banco_id", $cuenta);
                        }
                    }

                    $result_caja = $result_caja->skip($start)->take($length)->get();

                    foreach ($result_caja as $obj) {
                        $cuenta = CuentaBancaria::lista()[$obj->cuenta_banco_id];

                        $data_caja[] = (object)array(
                            'id' => $obj->id,
                            'created_at' => '' . $obj->created_at,
                            'cuenta' => $cuenta,
                            'tipo' => $obj->tipo,
                            'desde' => 'caja maestra',
                            'valor' => '$ ' . number_format($obj->valor, 2, ',', '.'),
                            'saldo' => '$ ' . number_format($obj->saldo, 2, ',', '.'));
                    }
                    if ($tipo != "retiros") {
                        $result_consignaciones = Consignacion::select("consignaciones.*","facturas.numero","facturas_tipos_pago.codigo_verificacion","tipos_pago.nombre as tipo_pago")
                            ->join("cuentas_bancos", "consignaciones.cuenta_id", "=", "cuentas_bancos.id")
                            ->leftJoin("facturas_tipos_pago","consignaciones.factura_tipo_pago_id","=","facturas_tipos_pago.id")
                            ->leftJoin("facturas","facturas_tipos_pago.factura_id","=","facturas.id")
                            ->leftJoin("tipos_pago","facturas_tipos_pago.tipo_pago_id","=","tipos_pago.id")
                            ->where("cuentas_bancos.usuario_id", Auth::user()->userAdminId())
                            ->whereBetween("consignaciones.created_at", [$fechaInicio, $fechaFin]);
                        if ($cuenta != "") {
                            $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                            if ($cuenta_model) {
                                $result_consignaciones = $result_consignaciones->where("consignaciones.cuenta_id", $cuenta);
                            }
                        }

                        $result_consignaciones = $result_consignaciones->skip($start)->take($length)->get();

                        foreach ($result_consignaciones as $obj) {

                            $obj->tipo = "Consignación";
                            $cuenta = CuentaBancaria::lista()[$obj->cuenta_id];

                            if ($obj->factura_tipo_pago_id){
                                $obj->tipo = $obj->tipo_pago.($obj->codigo_verificacion?'/'.$obj->codigo_verificacion:'');
                                $desde_aux = 'factura No.'.$obj->numero;
                            }
                            else $desde_aux = 'usuario';

                            $data_consignacion[] = (object)array(
                                'id' => $obj->id,
                                'created_at' => '' . $obj->created_at,
                                'cuenta' => $cuenta,
                                'tipo' => $obj->tipo,
                                'desde' => $desde_aux,
                                'valor' => '$ ' . number_format($obj->valor, 2, ',', '.'),
                                'saldo' => '$ ' . number_format($obj->saldo, 2, ',', '.'));
                        }
                    }
                    $myArray_aux = array_merge($data_caja, $data_consignacion);
                    $coleccion = collect($myArray_aux);
                    $coleccion = $coleccion->sortBy('id')->sortBy('created_at');

                    $totalRegistros = count($myArray_aux);
                    $parcialRegistros = count($myArray_aux);

                    foreach ($coleccion as $obj) {

                        $myArray[] = (object)array(
                            'created_at' => '' . $obj->created_at,
                            'cuenta' => $obj->cuenta,
                            'tipo' => $obj->tipo,
                            'desde' => $obj->desde,
                            'valor' => $obj->valor,
                            'saldo' => $obj->saldo);
                    }
                }
                if ($desde != 'todo') {
                    $totalRegistros = $result->get()->count();
                    //BUSCAR
                    // if($search['value'] != null){

                    //     $result = $result->whereRaw(
                    //         " ( LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                    //         " LOWER(descripcion) LIKE '%".\strtolower($search["value"])."%' OR".
                    //         //" LOWER(categoria) LIKE '%".\strtolower($search["value"])."%' OR".
                    //         " cantidad_vendida LIKE '%".$search["value"]."%' )");
                    // }

                    $parcialRegistros = $result->get()->count();
                    $result = $result->skip($start)->take($length);
                    $object = new \stdClass();
                    if ($parcialRegistros > 0) {
                        $respuesta = $result->get();
                        foreach ($respuesta as $obj) {

                            if ($desde != "caja maestra") {
                                $obj->tipo = "Consignación";
                                $cuenta = CuentaBancaria::lista()[$obj->cuenta_id];
                            } else {
                                $cuenta = CuentaBancaria::lista()[$obj->cuenta_banco_id];
                            }

                            if ($obj->factura_tipo_pago_id){
                                $obj->tipo = $obj->tipo_pago.($obj->codigo_verificacion?'/'.$obj->codigo_verificacion:'');
                                $desde = 'factura No.'.$obj->numero;
                            }
                            $myArray[] = (object)array(
                                'created_at' => '' . $obj->created_at,
                                'cuenta' => $cuenta,
                                'tipo' => $obj->tipo,
                                'desde' => $desde,
                                'valor' => '$ ' . number_format($obj->valor, 2, ',', '.'),
                                'saldo' => '$ ' . number_format($obj->saldo, 2, ',', '.'));
                        }
                    }
                }

                $data = ['length' => $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $result->toSql(),
                    'recordsTotal' => $totalRegistros,
                    'recordsFiltered' => $parcialRegistros,
                    'data' => $myArray,
                    'fecha_inicio' => $fechaInicio,
                    'fecha_fin' => $fechaFin,
                    /*'info' =>$result->get()*/];
            }
            return response()->json($data);
        }
    }


    public function getExcelMovimientosBancarios(Request $request){
        $r = Reporte::where('nombre','movimientos bancarios')->first();
        if(
            (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'no')
            || (Auth::user()->perfil->nombre == 'administrador' && Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'si' && Auth::user()->reporteHabilitado($r->id))
            || (Auth::user()->permiso_reportes == 'si' && Auth::user()->bodegas == 'no')
        ) {
            $nombre = "Reporte de movimientos bancarios";
            if ($request->has("fecha_inicio") && $request->has("fecha_fin") && $request->has("desde")) {

                $fechaInicio = $request->input("fecha_inicio");
                $fechaFin = $request->has("fecha_fin");
                $desde = $request->input("desde");
                $tipo = $request->input("tipo");
                $cuenta = $request->input("cuenta");

                $fechaFin = date("Y-m-d", strtotime("+1days", strtotime($request->input("fecha_fin"))));
                if ($desde == "caja maestra") {
                    $result = MovimientoCajaBanco::select("movimientos_cajas_bancos.*")
                        ->join("caja", "movimientos_cajas_bancos.caja_id", "=", "caja.id")
                        ->where("caja.usuario_id", Auth::user()->userAdminId())
                        ->whereBetween("movimientos_cajas_bancos.created_at", [$fechaInicio, $fechaFin]);

                    if ($tipo == "retiros") $result = $result->where("movimientos_cajas_bancos.tipo", "Retiro");
                    if ($tipo == "consignaciones") $result = $result->where("movimientos_cajas_bancos.tipo", "Consignación");

                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result = $result->where("movimientos_cajas_bancos.cuenta_banco_id", $cuenta);
                        }
                    }

                } else if ($desde == 'usuario') {
                    $result = Consignacion::select("consignaciones.*")
                        ->join("cuentas_bancos", "consignaciones.cuenta_id", "=", "cuentas_bancos.id")
                        ->where("cuentas_bancos.usuario_id", Auth::user()->userAdminId())
                        ->whereNull('consignaciones.factura_tipo_pago_id')
                        ->whereBetween("consignaciones.created_at", [$fechaInicio, $fechaFin]);
                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result = $result->where("consignaciones.cuenta_id", $cuenta);
                        }
                    }
                } else if ($desde == 'facturas') {
                    $result = Consignacion::select("consignaciones.*")
                        ->join("cuentas_bancos", "consignaciones.cuenta_id", "=", "cuentas_bancos.id")
                        ->where("cuentas_bancos.usuario_id", Auth::user()->userAdminId())
                        ->whereNotNull('consignaciones.factura_tipo_pago_id')
                        ->whereBetween("consignaciones.created_at", [$fechaInicio, $fechaFin]);
                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result = $result->where("consignaciones.cuenta_id", $cuenta);
                        }
                    }
                } else if ($desde == 'todo') {
                    $result_caja = MovimientoCajaBanco::select("movimientos_cajas_bancos.*")
                        ->join("caja", "movimientos_cajas_bancos.caja_id", "=", "caja.id")
                        ->where("caja.usuario_id", Auth::user()->userAdminId())
                        ->whereBetween("movimientos_cajas_bancos.created_at", [$fechaInicio, $fechaFin]);

                    if ($tipo == "retiros") $result_caja = $result_caja->where("movimientos_cajas_bancos.tipo", "Retiro");
                    if ($tipo == "consignaciones") $result_caja = $result_caja->where("movimientos_cajas_bancos.tipo", "Consignación");

                    if ($cuenta != "") {
                        $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                        if ($cuenta_model) {
                            $result_caja = $result_caja->where("movimientos_cajas_bancos.cuenta_banco_id", $cuenta);
                        }
                    }

                    $result_caja = $result_caja->get();
                    $data_caja = array();
                    foreach ($result_caja as $obj) {
                        $cuenta = CuentaBancaria::lista()[$obj->cuenta_banco_id];

                        $data_caja[] = (object)array(
                            'id' => $obj->id,
                            'created_at' => '' . $obj->created_at,
                            'cuenta' => $cuenta,
                            'tipo' => $obj->tipo,
                            'desde' => 'caja maestra',
                            'valor' => $obj->valor,
                            'saldo' => $obj->saldo);
                    }
                    if ($tipo != "retiros") {
                        $result_consignaciones = Consignacion::select("consignaciones.*")
                            ->join("cuentas_bancos", "consignaciones.cuenta_id", "=", "cuentas_bancos.id")
                            ->where("cuentas_bancos.usuario_id", Auth::user()->userAdminId())
                            ->whereBetween("consignaciones.created_at", [$fechaInicio, $fechaFin]);
                        if ($cuenta != "") {
                            $cuenta_model = CuentaBancaria::permitidos()->find($cuenta);
                            if ($cuenta_model) {
                                $result_consignaciones = $result_consignaciones->where("consignaciones.cuenta_id", $cuenta);
                            }
                        }

                        $result_consignaciones = $result_consignaciones->get();

                        foreach ($result_consignaciones as $obj) {

                            $obj->tipo = "Consignación";
                            $cuenta = CuentaBancaria::lista()[$obj->cuenta_id];

                            if ($obj->factura_tipo_pago_id) $desde_aux = 'facturas';
                            else $desde_aux = 'usuario';

                            $data_consignacion[] = (object)array(
                                'id' => $obj->id,
                                'created_at' => '' . $obj->created_at,
                                'cuenta' => $cuenta,
                                'tipo' => $obj->tipo,
                                'desde' => $desde_aux,
                                'valor' => $obj->valor,
                                'saldo' => $obj->saldo);
                        }
                    }
                    $myArray_aux = array_merge($data_caja, $data_consignacion);
                    $coleccion = collect($myArray_aux);
                    $coleccion = $coleccion->sortBy('id')->sortBy('created_at');


                    $totalRegistros = count($myArray_aux);
                    $parcialRegistros = count($myArray_aux);

                    foreach ($coleccion as $obj) {

                        $myArray[] = (object)array(
                            'created_at' => '' . $obj->created_at,
                            'cuenta' => $obj->cuenta,
                            'tipo' => $obj->tipo,
                            'desde' => $obj->desde,
                            'valor' => $obj->valor,
                            'saldo' => $obj->saldo);
                    }
                }

                if ($desde != 'todo') {
                    foreach ($result->get() as $obj) {

                        if ($desde != "caja maestra") {
                            $obj->tipo = "Consignación";
                            $cuenta = CuentaBancaria::lista()[$obj->cuenta_id];
                        } else {
                            $cuenta = CuentaBancaria::lista()[$obj->cuenta_banco_id];
                        }
                        $myArray[] = (object)array(
                            'created_at' => '' . $obj->created_at,
                            'cuenta' => $cuenta,
                            'tipo' => $obj->tipo,
                            'desde' => $desde,
                            'valor' => number_format($obj->valor, 2, '', ''),
                            'saldo' => number_format($obj->saldo, 2, '', ''));
                    }
                }
                return Excel::create($nombre, function ($excel) use ($myArray) {
                    $excel->sheet('Items', function ($sheet) use ($myArray) {
                        $sheet->loadView('reportes.movimientos_bancarios.listaExcel', ["elementos" => $myArray, "reporte" => true]);
                    });
                })->export("xls");
            } else {
                return response(["La información enviada es incorrecta"], 422);
            }
        }
    }
}