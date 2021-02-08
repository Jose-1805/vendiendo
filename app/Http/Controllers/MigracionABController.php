<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ABAbono;
use App\Models\ABAlmacenStockProducto;
use App\Models\ABBodegaStockProducto;
use App\Models\ABCompra;
use App\Models\ABCuentaPorCobrar;
use App\Models\ABFactura;
use App\Models\ABHistorialCosto;
use App\Models\ABHistorialDevolucion;
use App\Models\ABHistorialUtilidad;
use App\Models\Abono;
use App\Models\ABProducto;
use App\Models\ABProductoMateriaUnidad;
use App\Models\ABRemision;
use App\Models\Almacen;
use App\Models\Bodega;
use App\Models\Caja;
use App\Models\Cajas;
use App\Models\Compra;
use App\Models\CostoFijo;
use App\Models\CuentaPorCobrar;
use App\Models\Factura;
use App\Models\MateriaPrimaHistorial;
use App\Models\ObjetivoVenta;
use App\Models\Producto;
use App\Models\ProductoHistorial;
use App\Models\Remision;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

class MigracionABController extends Controller {

    public function __construct()
    {
        $this->middleware("auth");
    }

    /**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function getIndex()
	{
	    $admin = User::find(Auth::user()->userAdminId());
	    if($admin->migracion_ab_completa == 'si')return redirect('/');
        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si'){
            return view('migracion_ab.index');
        }else{
            return view('migracion_ab.no_admin');
        }
	}

	public function getUsuarios(){
	    //si esta activa la configuracion de usuario
        if(!Auth::user()->privilegiosConfigurarFuncion('usuarios')[0])
            return redirect('/');
        $usuarios = User::permitidos()->get();
        return view('migracion_ab.configuraciones.usuarios.index')
            ->with('usuarios',$usuarios);
    }

    public function getListUsuarios(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';

        if($orderBy == "nombre")$orderBy = "nombres";
        if($orderBy == "correo")$orderBy = "email";

        $usuarios = User::permitidos()->whereNull('usuarios.almacen_id');

        $usuarios = $usuarios->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $usuarios->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $usuarios = $usuarios->where(
                function($query) use ($f){
                    $query->where("nombres","like",$f)
                        ->orWhere("apellidos","like",$f)
                        ->orWhere("alias","like",$f)
                        ->orWhere("email","like",$f)
                        ->orWhere("telefono","like",$f);
                }
            );
        }

        $parcialRegistros = $usuarios->count();
        $usuarios = $usuarios->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            $almacenes = Almacen::permitidos()->select('almacenes.nombre','almacenes.id')->get();
            foreach ($usuarios as $value) {
                $almacen = "<select name='almacen_$value->id' id='almacen_$value->id' class='select_almacen_usuario'><option value=''>Seleccione un almacén</option>";

                foreach ($almacenes as $a){
                    $almacen .= "<option value='$a->id'>$a->nombre</option>";
                }
                $almacen .= "</select>";
                $administrador = "<p class='center-align'><input type='checkbox' name='administrador_$value->id' id='administrador_$value->id' /><label for='administrador_$value->id'></label></p>";

                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombres." ".$value->apellidos,
                    'perfil'=>$value->perfil->nombre,
                    'correo'=>$value->email,
                    'telefono'=>$value->telefono,
                    'alias'=>$value->alias,
                    'almacen'=>$almacen,
                    'administrador'=>$administrador
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $usuarios->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$usuarios];
        return response()->json($data);
    }

    public function postConfigurarUsuarios(Request $request){
	    $usuarios = User::permitidos()->whereNull('usuarios.almacen_id')->get();
	    $reload = false;
	    DB::beginTransaction();
	    foreach ($usuarios as $u){
            if($request->has('almacen_'.$u->id)){
                $almacen = Almacen::permitidos()->where('id',$request->input('almacen_'.$u->id))->first();
                if($almacen) {
                    $u->almacen_id = $almacen->id;
                    if(!$almacen->administrador){
                        if($request->has('administrador_'.$u->id)){
                            $u->perfil_id = Auth::user()->perfil->id;
                            $almacen->administrador = $u->id;
                            $almacen->save();
                        }
                    }
                    $u->save();
                }else{
                    return response(['error'=>['La información enviada es incorrecta']],422);
                }
            }else{
                $reload = true;
            }
        }
        DB::commit();
	    return ['success'=>true,'reload'=>$reload];
    }

    public function getCostosFijos(){
        //si esta activa la configuracion de costos fijos
        if(!Auth::user()->privilegiosConfigurarFuncion('costos fijos')[0])
            return redirect('/');
        return view('migracion_ab.configuraciones.costos_fijos.index');
    }

    public function getListCostosFijos(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';


        $costos_fijos = CostoFijo::permitidos()->where('costos_fijos.migracion_realizada','no');

        $costos_fijos = $costos_fijos->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $costos_fijos->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $costos_fijos = $costos_fijos->where(
                function($query) use ($f){
                    $query->where("nombre","like",$f)
                        ->orWhere("estado","like",$f);
                }
            );
        }

        $parcialRegistros = $costos_fijos->count();
        $costos_fijos = $costos_fijos->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            $almacenes = Almacen::permitidos()->select('almacenes.nombre','almacenes.id')->get();
            foreach ($costos_fijos as $value) {
                $almacen = "<select name='almacen_$value->id' id='almacen_$value->id' class='select_almacen_costo_fijo'><option value=''>Seleccione un almacén</option>";

                foreach ($almacenes as $a){
                    $almacen .= "<option value='$a->id'>$a->nombre</option>";
                }
                $almacen .= "</select>";
                $bodega = "<p class='center-align'><input class='check_bodega' type='checkbox' name='bodega_$value->id' id='bodega_$value->id' /><label for='bodega_$value->id'></label></p>";

                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'estado'=>$value->estado,
                    'almacen'=>$almacen,
                    'bodega'=>$bodega
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $costos_fijos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$costos_fijos];
        return response()->json($data);
    }

    public function postConfigurarCostosFijos(Request $request){
        $costos_fijos = CostoFijo::permitidos()->where('costos_fijos.migracion_realizada','no')->get();
        $reload = false;
        DB::beginTransaction();
        foreach ($costos_fijos as $cf){
            if($request->has('almacen_'.$cf->id)){
                $almacen = Almacen::permitidos()->where('id',$request->input('almacen_'.$cf->id))->first();
                if($almacen) {
                    $cf->almacen_id = $almacen->id;
                    $cf->migracion_realizada = 'si';
                    $cf->save();
                }else{
                    return response(['error'=>['La información enviada es incorrecta']],422);
                }
            }else if($request->has('bodega_'.$cf->id)){
                $cf->migracion_realizada = 'si';
                $cf->save();
            }else{
                $reload = true;
            }
        }
        DB::commit();
        return ['success'=>true,'reload'=>$reload];
    }

    public function getObjetivosVentas(){
        //si esta activa la configuracion de costos fijos
        if(!Auth::user()->privilegiosConfigurarFuncion('objetivos ventas')[0])
            return redirect('/');
        return view('migracion_ab.configuraciones.objetivos_ventas.index');
    }

    public function getListObjetivosVentas(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';


        $objetivos_ventas = ObjetivoVenta::permitidos()->whereNull('objetivos_ventas.almacen_id');

        $objetivos_ventas = $objetivos_ventas->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $objetivos_ventas->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $objetivos_ventas = $objetivos_ventas->where(
                function($query) use ($f){
                    $query->where("valor","like",$f)
                        ->orWhere("mes","like",$f)
                        ->orWhere("anio","like",$f);
                }
            );
        }

        $parcialRegistros = $objetivos_ventas->count();
        $objetivos_ventas = $objetivos_ventas->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            $almacenes = Almacen::permitidos()->select('almacenes.nombre','almacenes.id')->get();
            foreach ($objetivos_ventas as $value) {
                $almacen = "<select name='almacen_$value->id' id='almacen_$value->id' class='select_almacen_objetivo_venta'><option value=''>Seleccione un almacén</option>";

                foreach ($almacenes as $a){
                    $almacen .= "<option value='$a->id'>$a->nombre</option>";
                }
                $almacen .= "</select>";

                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'valor'=>number_format($value->valor,2,',','.'),
                    'fecha'=>$value->mes.'/'.$value->anio,
                    'almacen'=>$almacen
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $objetivos_ventas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$objetivos_ventas];
        return response()->json($data);
    }

    public function postConfigurarObjetivosVentas(Request $request){
        $objetivos_ventas = ObjetivoVenta::permitidos()->whereNull('objetivos_ventas.almacen_id')->get();
        $reload = false;
        DB::beginTransaction();
        foreach ($objetivos_ventas as $ov){
            if($request->has('almacen_'.$ov->id)){
                $almacen = Almacen::permitidos()->where('id',$request->input('almacen_'.$ov->id))->first();
                if($almacen) {
                    $ov->almacen_id = $almacen->id;
                    $ov->save();
                }else{
                    return response(['error'=>['La información enviada es incorrecta']],422);
                }
            }else{
                $reload = true;
            }
        }
        DB::commit();
        return ['success'=>true,'reload'=>$reload];
    }

    public function getProductos(){
        //si esta activa la configuracion de costos fijos
        if(!Auth::user()->privilegiosConfigurarFuncion('productos')[0])
            return redirect('/');
        $almacenes = Almacen::permitidos()->get();
        return view('migracion_ab.configuraciones.productos.index')
            ->with('almacenes',$almacenes);
    }

    public function getListProductos(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';


        $productos = Producto::permitidos()->whereNull('productos.migracion_producto_id');

        $productos = $productos->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $productos->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $productos = $productos->where(
                function($query) use ($f){
                    $query->where("nombre","like",$f)
                        ->orWhere("stock","like",$f)
                        ->orWhere("iva","like",$f)
                        ->orWhere("precio_costo","like",$f);
                }
            );
        }

        $parcialRegistros = $productos->count();
        $productos = $productos->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            $almacenes = Almacen::permitidos()->select('almacenes.nombre','almacenes.id')->get();
            foreach ($productos as $value) {
                $bodega = "<p class='center-align'><input class='check_bodega' type='checkbox' name='bodega_$value->id' id='bodega_$value->id' /><label for='bodega_$value->id'></label></p>";

                $data = [
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'stock'=>$value->stock,
                    'precio_costo'=>'$ '.number_format($value->precio_costo,2,',','.'),
                    'iva'=>number_format($value->iva,2,',','.').'%',
                    'bodega'=>$bodega
                ];

                foreach ($almacenes as $a){
                    $data['almacen_'.$a->id] = "<div class='row'>"
                        ."<div class='col s6' style='padding: 3px;'><input name='cantidad_".$value->id."_".$a->id."' type='text' class='num-entero params-producto' style='margin-top: 25px !important;'></div>"
                        ."<div class='col s6' style='padding: 3px;'><input name='precio_venta_".$value->id."_".$a->id."' type='text' class='num-entero params-producto' style='margin-top: 25px !important;'></div>"
                    ."</div>";
                }
                $myArray[]=(object) $data;
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $productos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$productos];
        return response()->json($data);
    }

    public function postConfigurarProductos(Request $request){
        $productos = Producto::permitidos()->whereNull('productos.migracion_producto_id')->get();
        $reload = false;
        DB::connection('mysql_alm')->beginTransaction();
        DB::beginTransaction();
        $almacenes = Almacen::permitidos()->get();
        $bodega = Bodega::permitidos()->first();

        foreach ($productos as $p){
            //determina si se debe establecer el producto por lo menos en un almacen
            $en_almacen = false;
            //se consulta si se enviaron datos de cantidad o precio de venta del producto
            //en relacion con por lo menos un almacén
            foreach ($almacenes as $a){
                if($request->has('cantidad_'.$p->id.'_'.$a->id) || $request->has('precio_venta_'.$p->id.'_'.$a->id))$en_almacen = true;
            }

            //si el producto se debe agregar a las bodegas y/o a los almacenes
            if($request->has('bodega_'.$p->id) || $en_almacen){

                //se crea una copia del producto en la db de bodegas y almacenes
                $producto_ab = new ABProducto();
                $producto_ab->nombre = $p->nombre;
                $producto_ab->tipo_producto = $p->tipo_producto;
                $producto_ab->precio_costo = $p->precio_costo;
                $producto_ab->iva = $p->iva;
                $producto_ab->utilidad = $p->utilidad;
                $producto_ab->stock = $p->stock;
                $producto_ab->umbral = $p->umbral;
                $producto_ab->descripcion = $p->descripcion;
                $producto_ab->barcode = $p->barcode;
                $producto_ab->estado = $p->estado;
                $producto_ab->tags = $p->tags;
                $producto_ab->promedio_ponderado = $p->promedio_ponderado;
                $producto_ab->medida_venta = $p->medida_venta;
                $producto_ab->proveedor_actual = $p->proveedor_actual;
                $producto_ab->unidad_id = $p->unidad_id;
                $producto_ab->categoria_id = $p->categoria_id;
                $producto_ab->usuario_id = $p->usuario_id;
                $producto_ab->usuario_id_creator = $p->usuario_id_creator;
                $producto_ab->save();
                //se relacionan los dos productos
                $p->migracion_producto_id = $producto_ab->id;
                $p->save();

                //se crea copia de la imagen (si el producto la tiene)
                if($p->imagen && $p->imagen != ''){
                    $producto_ab->imagen = $p->imagen;
                    $producto_ab->save();
                    $ruta_old = public_path("img/productos/".$p->id."/".$p->imagen);
                    $ruta_new = public_path("img/productos_ab/".$producto_ab->id."/".$p->imagen);
                    $ruta_old_thumb = public_path("img/productos/".$p->id."/thumb_".$p->imagen);
                    $ruta_new_thumb = public_path("img/productos_ab/".$producto_ab->id."/thumb_".$p->imagen);

                    //se crean los directorios
                    if(!is_dir(public_path("img/productos_ab")))
                        File::makeDirectory(public_path("img/productos_ab"));

                    File::makeDirectory(public_path("img/productos_ab/".$producto_ab->id));

                    $image = Image::make($ruta_old);
                    $image->save($ruta_new);

                    $image_thumb = Image::make($ruta_old_thumb);
                    $image_thumb->save($ruta_new_thumb);
                }

                //se guarda la relacion con las materias primas en la nueva db (si es un producto compuesto)
                if($producto_ab->tipo_producto == 'Compuesto'){
                    $materias_primas = $p->MateriasPrimas()->select('materias_primas.id','producto_materia_unidad.cantidad')->get();

                    foreach ($materias_primas as $mt){
                        $relacion = new ABProductoMateriaUnidad();
                        $relacion->producto_id = $producto_ab->id;
                        $relacion->materia_prima_id = $mt->id;
                        $relacion->cantidad = $mt->cantidad;
                        $relacion->save();
                    }
                }

                //se registran todos los historiales de costos y utilidad del producto
                //los historiales de utilidad se relacionan con cada uno de los almacenes
                $historiales = $p->productoHistorial;
                foreach ($historiales as $h){
                    $historia_costo_nuevo = new ABHistorialCosto();
                    $historia_costo_nuevo->precio_costo_anterior = $h->precio_costo_anterior;
                    $historia_costo_nuevo->precio_costo_nuevo = $h->precio_costo_nuevo;
                    $historia_costo_nuevo->iva_anterior = $h->iva_anterior;
                    $historia_costo_nuevo->iva_nuevo = $h->iva_nuevo;
                    $historia_costo_nuevo->stock = $h->stock;
                    $historia_costo_nuevo->producto_id = $producto_ab->id;
                    $historia_costo_nuevo->proveedor_id = $h->proveedor_id;
                    $historia_costo_nuevo->usuario_id = $h->usuario_id;
                    $historia_costo_nuevo->save();
                    $h->migracion_ab_historial_costo_id = $historia_costo_nuevo->id;

                    //se crean historiales de utilidad por cada almacén
                    //si el historial del producto tiene utilidad
                    if($h->utilidad_nueva){
                        foreach ($almacenes as $a){
                            $historial_utilidad_almacen = new ABHistorialUtilidad();
                            $historial_utilidad_almacen->utilidad = $h->utilidad_nueva;
                            $historial_utilidad_almacen->producto_id = $producto_ab->id;
                            $historial_utilidad_almacen->almacen_id = $a->id;
                            $historial_utilidad_almacen->save();

                            $h->migracion_ab_historial_utilidad_id = $historial_utilidad_almacen->id;
                        }
                    }
                    $h->save();
                }

                //se envian los stocks a las bodegas y almacenes correspondientes
                //si se ingresa un nuevo precio de venta se guarda la nueva utilidad con el almacén registrado

                //se envia todo el stock a bodega
                if($request->has('bodega_'.$p->id)){
                    $bodega_stock = new ABBodegaStockProducto();
                    $bodega_stock->bodega_id = $bodega->id;
                    $bodega_stock->producto_id = $producto_ab->id;
                    $bodega_stock->stock = $p->stock;
                    $bodega_stock->save();
                }else{
                    //contador de cantidad de stock distribuida entre los almacenes
                    $acumulado_stock = 0;
                    //se recorren todos los almacenes y se busca si se registro una cantidad Y/O un nuevo precio de venta
                    foreach ($almacenes as $a){
                        if($request->has('cantidad_'.$p->id.'_'.$a->id) && $request->input('cantidad_'.$p->id.'_'.$a->id) > 0){
                            if(($acumulado_stock+$request->input('cantidad_'.$p->id.'_'.$a->id))<=$p->stock){
                                $acumulado_stock += $request->input('cantidad_'.$p->id.'_'.$a->id);

                                $stock_almacen = ABAlmacenStockProducto::where('producto_id',$producto_ab->id)->where('almacen_id',$a->id)->first();
                                if(!$stock_almacen)$stock_almacen = new ABAlmacenStockProducto();

                                $stock_almacen->stock = $request->input('cantidad_'.$p->id.'_'.$a->id);
                                $stock_almacen->almacen_id = $a->id;
                                $stock_almacen->producto_id = $producto_ab->id;
                                $stock_almacen->save();
                            }else{
                                return response(['error'=>['La cantidad distribuida en los almacenes es mayor al stock existente']],422);
                            }
                        }

                        //se guarda la utilidad si se registró
                        if($request->has('precio_venta_'.$p->id.'_'.$a->id) && $request->input('precio_venta_'.$p->id.'_'.$a->id) > 0){
                            $utilidad = ((($request->input('precio_venta_'.$p->id.'_'.$a->id) / ((100 + $producto_ab->iva) / 100)) / $producto_ab->precio_costo) - 1) * 100;

                            $historial_utilidad = new ABHistorialUtilidad();
                            $historial_utilidad->utilidad = $utilidad;
                            $historial_utilidad->producto_id = $producto_ab->id;
                            $historial_utilidad->almacen_id = $a->id;
                            $historial_utilidad->save();
                        }
                    }

                    //se guarda el stock restante en la bodega
                    $bodega_stock = new ABBodegaStockProducto();
                    $bodega_stock->bodega_id = $bodega->id;
                    $bodega_stock->producto_id = $producto_ab->id;
                    $bodega_stock->stock = $p->stock - $acumulado_stock;
                    $bodega_stock->save();
                }
            }else{
                $reload = true;
            }
        }

        //se realizó la migración de todos los productos
        if(!$reload){
            //se debe realizar la migración de las compras
            $compras = Compra::permitidos()->get();
            $caja = Caja::abierta();

            foreach ($compras as $c){
                $compra_ab = new ABCompra();
                $compra_ab->numero = $c->numero;
                $compra_ab->valor = $c->valor;
                $compra_ab->estado = $c->estado;
                $compra_ab->estado_pago = $c->estado_pago;
                $compra_ab->usuario_creador_id = $c->usuario_creador_id;
                $compra_ab->usuario_id = $c->usuario_id;
                $compra_ab->proveedor_id = $c->proveedor_id;
                $compra_ab->numero_cuotas = $c->numero_cuotas;
                $compra_ab->fecha_primera_notificacion = $c->fecha_primera_notificacion;
                $compra_ab->tipo_periodicidad_notificacion = $c->tipo_periodicidad_notificacion;
                $compra_ab->periodicidad_notificacion = $c->periodicidad_notificacion;
                $compra_ab->dias_credito = $c->dias_credito;
                $compra_ab->caja_maestra_id = $caja->id;
                $compra_ab->save();


                $h_productos = $c->productosHistorial()->select('productos_historial.*','compras_productos_historial.cantidad')->get();

                //se recorren los productos relacionados en la compra
                foreach ($h_productos as $h_p){
                    $historial_costo_ab = ABHistorialCosto::find($h_p->migracion_ab_historial_costo_id);
                    $compra_ab->historialCostos()->save($historial_costo_ab,['cantidad'=>$h_p->cantidad]);
                }

                $cuentas_cobrar = $c->cuentasPorCobrar;
                foreach ($cuentas_cobrar as $c_c){
                    $cuenta_cobrar_ab = new ABCuentaPorCobrar();
                    $cuenta_cobrar_ab->compra_id = $compra_ab->id;
                    $historial_producto = ProductoHistorial::find($c_c->producto_historial_id);
                    $historial_costo_ab = ABHistorialCosto::find($historial_producto->migracion_ab_historial_costo_id);

                    $cuenta_cobrar_ab->historial_costo_id = $historial_costo_ab->id;
                    $cuenta_cobrar_ab->elemento_id = $historial_costo_ab->producto_id;
                    $cuenta_cobrar_ab->cantidad_devolucion = $c_c->cantidad_devolucion;
                    $cuenta_cobrar_ab->valor_devolucion = $c_c->valor_devolucion;
                    $cuenta_cobrar_ab->motivo = $c_c->motivo;
                    $cuenta_cobrar_ab->tipo_compra = $c_c->tipo_compra;
                    $cuenta_cobrar_ab->estado = $c_c->estado;
                    $cuenta_cobrar_ab->fecha_devolucion = $c_c->fecha_devolucion;
                    $cuenta_cobrar_ab->usuario_id = $c_c->usuario_id;
                    $cuenta_cobrar_ab->proveedor_id = $c_c->proveedor_id;
                    $cuenta_cobrar_ab->forma_pago = $c_c->forma_pago;
                    $cuenta_cobrar_ab->save();
                }

                $devoluciones = $c->historialDevoluciones;
                foreach ($devoluciones as $d){
                    $devolucion_ab = new ABHistorialDevolucion();
                    if($d->tipo_elemento == 'producto'){
                        $p_ = Producto::find($d->elemento_id);
                        $p_ab = ABProducto::find($p_->migracion_producto_id);
                        $devolucion_ab->elemento_id = $p_ab->id;
                    }else{
                        $devolucion_ab->elemento_id = $d->elemento_id;
                    }
                    $devolucion_ab->compra_id = $compra_ab->id;
                    $devolucion_ab->cantidad = $d->cantidad;
                    $devolucion_ab->tipo_elemento = $d->tipo_elemento;
                    $devolucion_ab->motivo = $d->motivo;
                    $devolucion_ab->usuario_id = $d->usuario_id;
                    $devolucion_ab->proveedor_id = $d->proveedor_id;
                    $devolucion_ab->save();
                }

                $abonos = Abono::permitidos()->where('tipo_abono','compra')
                    ->where('tipo_abono_id',$c->id)->get();
                foreach ($abonos as $a){
                    $abono_ab = new ABAbono();
                    $abono_ab->valor = $a->valor;
                    $abono_ab->nota = $a->nota;
                    $abono_ab->fecha = $a->fecha;
                    $abono_ab->usuario_id = $a->usuario_id;
                    $abono_ab->tipo_abono = $a->tipo_abono;
                    $abono_ab->tipo_abono_id = $compra_ab->id;
                    $abono_ab->caja_maestra_id = $caja->id;
                    $abono_ab->save();
                }
            }
        }

        DB::connection('mysql_alm')->commit();
        DB::commit();
        return ['success'=>true,'reload'=>$reload];
    }

    public function getFacturas(){
        //si esta activa la configuracion de costos fijos
        if(!Auth::user()->privilegiosConfigurarFuncion('facturas')[0])
            return redirect('/');
        return view('migracion_ab.configuraciones.facturas.index');
    }

    public function getListFacturas(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';


        $facturas = Factura::permitidos()->whereNull('facturas.migracion_factura_id')
            ->select("facturas.id", "facturas.numero", "facturas.created_at","facturas.subtotal", "facturas.iva","facturas.descuento",
                "facturas.estado", "clientes.nombre as cliente", DB::RAW("CONCAT(usuarios.nombres, ' ', usuarios.apellidos) as usuario"))
            ->join("usuarios", "usuarios.id", "=", "facturas.usuario_creador_id")
            ->leftJoin("clientes", "clientes.id", "=", "facturas.cliente_id");

        $facturas = $facturas->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $facturas->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $facturas = $facturas->where(
                function($query) use ($f){
                    $query->where("facturas.numero","like",$f)
                        ->orWhere("facturas.created_at","like",$f)
                        ->orWhere("clientes.nombre","like",$f)
                        ->orWhere("usuarios.nombres","like",$f)
                        ->orWhere("usuarios.apellidos","like",$f)
                        ->orWhere("facturas.estado","like",$f);
                }
            );
        }

        $parcialRegistros = $facturas->count();
        $facturas = $facturas->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            $almacenes = Almacen::permitidos()->select('almacenes.nombre','almacenes.id')->get();
            foreach ($facturas as $value) {
                $almacen = "<select name='almacen_$value->id' id='almacen_$value->id' class='select_almacen_factura'><option value=''>Seleccione un almacén</option>";

                foreach ($almacenes as $a){
                    $almacen .= "<option value='$a->id'>$a->nombre</option>";
                }
                $almacen .= "</select>";

                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'numero'=>$value->numero,
                    'valor'=>"$ " . number_format($value->subtotal + $value->iva - $value->descuento, 2, ',', '.'),
                    'fecha'=>''.$value->created_at,
                    'cliente'=>$value->cliente,
                    'usuario'=>$value->usuario,
                    'estado'=>$value->estado,
                    'almacen'=>$almacen
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $facturas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$facturas];
        return response()->json($data);
    }

    public function postConfigurarFacturas(Request $request){
        $facturas = Factura::permitidos()->whereNull('facturas.migracion_factura_id')->get();
        $reload = false;
        DB::connection('mysql_alm')->beginTransaction();
        DB::beginTransaction();

        foreach ($facturas as $f){
            if($request->has('almacen_'.$f->id)){
                $almacen_seleccionado = Almacen::permitidos()->find($request->input('almacen_'.$f->id));

                //se buscan cajas relacionadas con el almacén
                //si no existe se crea una
                $caja_almacen = Cajas::permitidos()->where('almacen_id',$almacen_seleccionado->id)->first();
                if(!$caja_almacen){
                    $caja_almacen = new Cajas();
                    $caja_almacen->nombre = 'CMigracion '.$almacen_seleccionado->id;
                    $caja_almacen->prefijo = 'CM '.$almacen_seleccionado->id;
                    $caja_almacen->estado = 'cerrada';
                    $caja_almacen->almacen = 'si';
                    $caja_almacen->almacen_id = $almacen_seleccionado->id;
                    $caja_almacen->usuario_id = Auth::user()->id;
                    $caja_almacen->usuario_creador_id = Auth::user()->id;
                    $caja_almacen->save();

                    //se agrega la relacion con el usuario
                    $sql = "INSERT INTO cajas_usuarios (valor_inicial,valor_final,efectivo_real,estado,caja_id,caja_mayor_id,usuario_id,created_at,updated_at)"
                                                ." VALUES (0,0,0,'inactivo',$caja_almacen->id,".Caja::abierta()->id.",".Auth::user()->id.",'".date('Y-m-d h:i:s')."','".date('Y-m-d h:i:s')."')";
                    DB::statement($sql);
                }

                //se crea la copia de la factura en la db de almacenes
                $factura_ab = new ABFactura();
                $factura_ab->cliente_id = $f->cliente_id;
                $factura_ab->numero = $f->numero;
                $factura_ab->subtotal = $f->subtotal;
                $factura_ab->iva = $f->iva;
                $factura_ab->descuento = $f->descuento;
                $factura_ab->estado = $f->estado;
                $factura_ab->observaciones = $f->observaciones;
                $factura_ab->numero_cuotas = $f->numero_cuotas;
                $factura_ab->fecha_primera_notificacion = $f->fecha_primera_notificacion;
                $factura_ab->tipo_periodicidad_notificacion = $f->tipo_periodicidad_notificacion;
                $factura_ab->periodicidad_notificacion = $f->periodicidad_notificacion;
                $factura_ab->dias_credito = $f->dias_credito;

                $relacion_caja_usuario = Auth::user()->cajas()->select("cajas_usuarios.id")->where("cajas_usuarios.caja_id",$caja_almacen->id)->first();
                $factura_ab->caja_usuario_id = $relacion_caja_usuario->id;
                $factura_ab->plan_usuario_id = Auth::user()->ultimoPlan()->id_relacion;
                $factura_ab->usuario_id = Auth::user()->id;
                $factura_ab->usuario_creador_id = Auth::user()->id;
                $factura_ab->almacen_id = $almacen_seleccionado->id;
                $factura_ab->resolucion_id = $f->resolucion_id;
                $factura_ab->save();

                $f->migracion_factura_id = $factura_ab->id;
                $f->save();

                $h_productos = $f->productosHistorial()->select('productos_historial.*','facturas_productos_historial.cantidad')->get();

                //se recorren los productos relacionados en la factura
                foreach ($h_productos as $h_p){
                    $historial_costo_ab = ABHistorialCosto::find($h_p->migracion_ab_historial_costo_id);
                    $historial_utilidad_ab = ABHistorialUtilidad::find($h_p->migracion_ab_historial_utilidad_id);
                    $factura_ab->productosHistorialCosto()->save($historial_costo_ab);
                    $factura_ab->productosHistorialUtilidad()->save($historial_utilidad_ab,['cantidad'=>$h_p->cantidad]);
                }

                //se relacionan los tipos de pago
                $tipos_pago = $f->tiposPago()->select('tipos_pago.*','facturas_tipos_pago.valor','facturas_tipos_pago.codigo_verificacion','facturas_tipos_pago.comentario')->get();
                foreach ($tipos_pago as $tp){
                    $factura_ab->tiposPago()->save($tp,['valor'=>$tp->valor,'codigo_verificacion'=>$tp->codigo_verificacion,'comentario'=>$tp->comentario,'created_at'=>date('Y-m-d h:i:s'),'updated_at'=>date('Y-m-d h:i:s')]);
                }

                //se sincronizan los abonos
                $abonos = ABAbono::where('tipo_abono','factura')
                    ->where('tipo_abono_id',$f->id)->get();

                foreach ($abonos as $a){
                    $abono_ab = new ABAbono();
                    $abono_ab->valor = $a->valor;
                    $abono_ab->nota = $a->nota;
                    $abono_ab->fecha = $a->fecha;
                    $abono_ab->usuario_id = $a->usuario_id;
                    $abono_ab->tipo_abono = $a->tipo_abono;
                    $abono_ab->tipo_abono_id = $factura_ab->id;
                    $abono_ab->caja_usuario_id = $relacion_caja_usuario->id;
                    $abono_ab->save();
                }
            }else{
                $reload = true;
            }
        }

        //se realizó la migración de todas las facturas
        if(!$reload){
            //se cierran todas las cajas y sus relaciones
            $caja_maestra = Caja::abierta();
            $caja_maestra->estado = "cerrada";
            $caja_maestra->save();

            $sql = "UPDATE cajas_usuarios SET estado='inactivo' WHERE caja_mayor_id = $caja_maestra->id OR usuario_id = ".Auth::user()->id;
            DB::statement($sql);
        }

        DB::connection('mysql_alm')->commit();
        DB::commit();
        return ['success'=>true,'reload'=>$reload];
    }

    public function getRemisiones(){
        //si esta activa la configuracion de costos fijos
        if(!Auth::user()->privilegiosConfigurarFuncion('remisiones')[0])
            return redirect('/');
        return view('migracion_ab.configuraciones.remisiones.index');
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
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';


        $remisiones = Remision::permitidos()->whereNull('remisiones.migracion_remision_id')
            ->select("remisiones.id", "remisiones.numero", "remisiones.created_at",
                "remisiones.estado", "clientes.nombre as cliente", DB::RAW("CONCAT(usuarios.nombres, ' ', usuarios.apellidos) as usuario"))
            ->join("usuarios", "usuarios.id", "=", "remisiones.usuario_creador_id")
            ->leftJoin("clientes", "clientes.id", "=", "remisiones.cliente_id");

        $remisiones = $remisiones->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $remisiones->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $remisiones = $remisiones->where(
                function($query) use ($f){
                    $query->where("remisiones.numero","like",$f)
                        ->orWhere("remisiones.created_at","like",$f)
                        ->orWhere("clientes.nombre","like",$f)
                        ->orWhere("usuarios.nombres","like",$f)
                        ->orWhere("usuarios.apellidos","like",$f)
                        ->orWhere("remisiones.estado","like",$f);
                }
            );
        }

        $parcialRegistros = $remisiones->count();
        $remisiones = $remisiones->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            $almacenes = Almacen::permitidos()->select('almacenes.nombre','almacenes.id')->get();
            foreach ($remisiones as $value) {
                $almacen = "<select name='almacen_$value->id' id='almacen_$value->id' class='select_almacen_remision'><option value=''>Seleccione un almacén</option>";

                foreach ($almacenes as $a){
                    $almacen .= "<option value='$a->id'>$a->nombre</option>";
                }
                $almacen .= "</select>";

                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'numero'=>$value->numero,
                    'valor'=>"$ ".number_format($value->getValor(),2,',','.'),
                    'fecha'=>$value->created_at->format('Y-m-d H:i:s'),
                    'cliente'=> $value->cliente,
                    'usuario'=> $value->usuario,
                    'estado'=>$value->estado,
                    'almacen'=>$almacen
                );
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
            'info' =>$remisiones];
        return response()->json($data);
    }

    public function postConfigurarRemisiones(Request $request){
        $remisiones = Remision::permitidos()->whereNull('remisiones.migracion_remision_id')->get();
        $reload = false;
        DB::connection('mysql_alm')->beginTransaction();
        DB::beginTransaction();

        foreach ($remisiones as $r){
            if($request->has('almacen_'.$r->id)){
                $almacen_seleccionado = Almacen::permitidos()->find($request->input('almacen_'.$r->id));



                //se crea la copia de la factura en la db de almacenes
                $remision_ab = new ABRemision();
                $remision_ab->numero = $r->numero;
                $remision_ab->fecha_vencimiento = $r->fecha_vencimiento;
                $remision_ab->estado = $r->estado;
                $remision_ab->cliente_id = $r->cliente_id;
                $remision_ab->almacen_id = $almacen_seleccionado->id;
                $remision_ab->usuario_id = Auth::user()->id;
                $remision_ab->usuario_creador_id = Auth::user()->id;

                if($r->factura_id){
                    $factura = Factura::find($r->factura_id);
                    $remision_ab->factura_id = $factura->migracion_factura_id;
                }

                $remision_ab->save();

                $r->migracion_remision_id = $remision_ab->id;
                $r->save();

                $productos = $r->productos()->select('productos_remisiones.*')->get();

                //se recorren los productos relacionados en la remisión
                foreach ($productos as $p_){
                    $producto_ = Producto::find($p_->producto_id);
                    $producto_ab = ABProducto::find($producto_->migracion_producto_id);
                    $ultimo_historial_costo = $producto_ab->ultimoHistorial();
                    $ultimo_historial_utilidad_almacen = $producto_ab->ultimoHistorialUtilidadAlmacen($almacen_seleccionado->id);

                    //se crean historiales nuevos para relacionar con la remisión
                    $nuevo_h_c = new ABHistorialCosto();
                    $nuevo_h_c->precio_costo_anterior = $ultimo_historial_costo->precio_costo_anterior;
                    $nuevo_h_c->precio_costo_nuevo = $p_->precio_costo;
                    $nuevo_h_c->iva_anterior = $ultimo_historial_costo->iva_anterior;
                    $nuevo_h_c->iva_nuevo = $p_->iva;
                    $nuevo_h_c->proveedor_id = $ultimo_historial_costo->proveedor_id;
                    $nuevo_h_c->usuario_id = $ultimo_historial_costo->usuario_id;
                    $nuevo_h_c->producto_id = $ultimo_historial_costo->producto_id;
                    $nuevo_h_c->save();

                    $nuevo_h_u = new ABHistorialUtilidad();
                    $nuevo_h_u->utilidad = $p_->utilidad;
                    $nuevo_h_u->producto_id = $ultimo_historial_utilidad_almacen->producto_id;
                    $nuevo_h_u->almacen_id = $ultimo_historial_utilidad_almacen->almacen_id;
                    $nuevo_h_u->save();

                    //se relaciona la remision con los nuevos historiales
                    $remision_ab->historialCostos()->save($nuevo_h_c,['cantidad'=>$p_->cantidad,'historial_utilidad_id'=>$nuevo_h_u->id]);

                    //se registran nuevamente los historiales anteriores para que sean nuevamente los ultimos
                    $nuevo_ultimo_h_c = new ABHistorialCosto();
                    $nuevo_ultimo_h_c->precio_costo_anterior = $ultimo_historial_costo->precio_costo_anterior;
                    $nuevo_ultimo_h_c->precio_costo_nuevo = $ultimo_historial_costo->precio_costo_nuevo;
                    $nuevo_ultimo_h_c->iva_anterior = $ultimo_historial_costo->iva_anterior;
                    $nuevo_ultimo_h_c->iva_nuevo = $ultimo_historial_costo->iva_nuevo;
                    $nuevo_ultimo_h_c->stock = $ultimo_historial_costo->stock;
                    $nuevo_ultimo_h_c->producto_id = $ultimo_historial_costo->producto_id;
                    $nuevo_ultimo_h_c->proveedor_id = $ultimo_historial_costo->proveedor_id;
                    $nuevo_ultimo_h_c->usuario_id = $ultimo_historial_costo->usuario_id;
                    $nuevo_ultimo_h_c->save();

                    $nuevo_ultimo_h_u = new ABHistorialUtilidad();
                    $nuevo_ultimo_h_u->utilidad = $ultimo_historial_utilidad_almacen->utilidad;
                    $nuevo_ultimo_h_u->actualizacion_utilidad = $ultimo_historial_utilidad_almacen->actualizacion_utilidad;
                    $nuevo_ultimo_h_u->producto_id = $ultimo_historial_utilidad_almacen->producto_id;
                    $nuevo_ultimo_h_u->almacen_id = $ultimo_historial_utilidad_almacen->almacen_id;
                    $nuevo_ultimo_h_u->save();
                }
            }else{
                $reload = true;
            }
        }

        //se ha completado toda la configuración de migración
        if(!$reload){
            $user = Auth::user();
            $user->migracion_ab_completa = 'si';
            $user->save();
        }

        DB::connection('mysql_alm')->commit();
        DB::commit();
        return ['success'=>true,'reload'=>$reload];
    }

}
