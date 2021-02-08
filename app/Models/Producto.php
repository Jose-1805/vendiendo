<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class Producto extends Model {

	protected $table = "productos";
    protected $fillable = [
        "nombre",
        "precio_costo",
        "iva",
        "utilidad",
        "stock",
        "umbral",
        "descripcion",
        "barcode",
        "tipo_producto",
        "proveedor_actual",
        "proveedor",
        "tags",
        "omitir_stock_mp",
        "imagen",
        "usuario_id",
        "usuario_id_creator",
        "unidad_id",
        "categoria_id",
        "medida_venta"];
    
    protected $guarded = "id";

    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Producto::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Producto::where("productos.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Producto::where('productos.usuario_id',$usuario_creador->id);
            }
        }
    }

    public function MateriasPrimas(){
        return $this->belongsToMany("App\Models\MateriaPrima","producto_materia_unidad");
            //->withPivot('id','cantidad');
    }
    public function Unidad(){
        return $this->belongsTo("App\Models\Unidad");
    }
    public function ProductoMaterias(){
        return $this->hasMany('App\Models\ProductoMateriaUnidad');
    }

    public function relacionesProveedores()
    {
        return $this->hasMany("App\Models\ProveedorMateriaPrima");
    }
    
    public function relacionesMateriasPrimas()
    {
        return $this->hasMany("App\Models\ProductoMateriaUnidad");
    }
    
    public function Categoria(){
        return $this->belongsTo('App\Models\Categoria');
    }
    public  static function ProductosPermitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        $usuario=null;
        if($perfil->nombre == 'administrador'){
            $usuario = $user;
        }else{
            $usuario = User::find(Auth::user()->usuario_creador_id);
        }
        $user_id = $usuario->id;
        $res = DB::table('v_productos_materia_proveedor')
            ->where('usuario_id',$user_id);
        return $res;
    }
    public  static function ProductosPermitidosById($id_producto){
        $user = Auth::user();
        $user_id = $user->id;
        $res= DB::select("select * from v_productos_materia_proveedor vp 
                            WHERE vp.usuario_id = '$user_id' 
                            AND (vp.materia_prima_usuario_id='$user_id' OR vp.proveedor_usuario_id='$user_id')
                            AND vp.producto_id = '$id_producto'");
        $collection = Collection::make($res);
        return $collection;
    }
    public static function materiaPorProducto($id_producto){

       $res = DB::select("select *from v_producto_materia_prima pmp
                    WHERE pmp.producto_id = '$id_producto'");
        $collection = Collection::make($res);
        return $collection;
    }
    public static function proveedorPorProducto($id_producto){
        $res = DB::select("select *from v_producto_proveedor pp
                    WHERE pp.producto_id = '$id_producto'");
        $collection = Collection::make($res);
        return $collection;
    }
    public static function ProductosPermitidosBySession(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Producto::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Producto::where("usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creator = User::find($user->usuario_id_creator);
            if($usuario_creator){
                return Producto::where('usuario_id',$usuario_creator->id);
            }
        }
    }
    public static function DeleteRelation($relacionproducto,$id_producto,$omitir_stock_mp = 'no'){
        $proveedorProducto="";
        $productoMateria="";
        switch ($relacionproducto){
            case 'Terminado':
                /*$proveedorProducto = ProveedorProducto::where('producto_id',$id_producto)->get();
                foreach ($proveedorProducto as $relacion){
                    $relacion->delete();
                }*/
                break;
            case 'Compuesto':
                $productoMateria = ProductoMateriaUnidad::where('producto_id',$id_producto)->get();
                foreach ($productoMateria as $key => $relacion){
                    if($omitir_stock_mp != 'si') {
                        $materia_prima = MateriaPrima::materiasPrimasPermitidas()->where('id', $relacion->materia_prima_id)->first();
                        $materia_prima->stock += $relacion->cantidad;
                        $materia_prima->save();
                    }
                    $relacion->delete();
                }
                break;
            case 'Preparado':
                $productoMateria = ProductoMateriaUnidad::where('producto_id',$id_producto)->get();
                foreach ($productoMateria as $relacion){
                    $relacion->delete();
                }
                break;
            default:
                $proveedorProducto="";
                $productoMateria="";
                break;
        }
    }

    public static function ProductosPermitidosRaw(){
        $user = Auth::user();
        $user_id = $user->id;
        $res= DB::select(DB::raw("select * from v_productos_materia_proveedor vp 
                            WHERE vp.usuario_id = '$user_id' 
                            AND (vp.materia_prima_usuario_id='$user_id' OR vp.proveedor_usuario_id='$user_id')"));

        $collection = Collection::make($res);
        return $collection;
    }


    public function facturas(){
        return $this->belongsToMany("App\Models\Factura");
    }

    public function facturasRelacionadas(){
        return Factura::join("facturas_productos_historial","facturas.id","=","facturas_productos_historial.factura_id")
            ->join("productos_historial","facturas_productos_historial.producto_historial_id","=","productos_historial.id")
            ->join("productos","productos_historial.producto_id","=","productos.id")
            ->where("productos.id",$this->id);
    }

    public function comprasRelacionadas(){
        return Compra::join("compras_productos_historial","compras.id","=","compras_productos_historial.compra_id")
            ->join("productos_historial","compras_productos_historial.producto_historial_id","=","productos_historial.id")
            ->join("productos","productos_historial.producto_id","=","productos.id")
            ->where("productos.id",$this->id);
    }

    public static function topVentas($fechaInicio,$fechaFin,$top,$categoria = null,$paginate = null){
        $perfil=Auth::user()->perfil;
        $usuario=null;
        if($perfil->nombre == 'administrador'){
            $usuario = Auth::user();
        }else{
            $usuario = User::find(Auth::user()->usuario_creador_id);
        }

        /*$sql = "SELECT productos.*, sum(facturas_productos.cantidad) as cantidad_vendida FROM productos
INNER JOIN facturas_productos on productos.id = facturas_productos.producto_id
INNER JOIN facturas on facturas.id = facturas_productos.factura_id
WHERE productos.usuario_id = ".$usuario->id."
AND facturas.created_at BETWEEN '".$fechaInicio."' AND '".$fechaFin."'";
        if($categoria != null){
            $sql .= " WHERE productos.categoria_id = ".$categoria;
        }

$sql .= " GROUP BY productos.id
ORDER BY cantidad_vendida DESC LIMIT ".$top;*/
        //se suma un dia para que la fecha de finalización de la consulta quede incluida
        $fechaFin = date("Y-m-d",strtotime("+1days",strtotime($fechaFin)));
        $result = Producto::select("productos.*",DB::raw("sum(facturas_productos_historial.cantidad) as cantidad_vendida"))
            ->join("productos_historial","productos.id","=","productos_historial.producto_id")
            ->join("facturas_productos_historial","productos_historial.id","=","facturas_productos_historial.producto_historial_id")
            ->join("facturas","facturas_productos_historial.factura_id","=","facturas.id")
            ->where("productos.usuario_id",Auth::user()->userAdminId())
            ->where(function($q){
                $q->where("facturas.estado","Pagada")
                    ->orWhere("facturas.estado","Pendiente por pagar")
                    ->orWhere("facturas.estado","cerrada")
                    ->orWhere("facturas.estado","abierta");
            })
            ->whereBetween("facturas.created_at",[$fechaInicio,$fechaFin]);
        if($categoria != null){
            $result = $result->where("productos.categoria_id",$categoria);
        }

        $result = $result->groupBy("productos.id")
            ->orderBy("cantidad_vendida","DESC")->take($top);
        if($paginate == null){
            $result = $result->get();
        }else{
            $result = $result->paginate($paginate);
        }
        return $result;
        //return DB::select($sql);
    }
    public function getValorProveedor($id_proveedor){
        $proveedor_producto = $this->proveedores()->select("proveedor_producto.*")->where("proveedores.id",$id_proveedor)->first();
        if ($proveedor_producto){
            return $proveedor_producto->valor;
        }
        return false;
    }
    public function proveedores(){
        return $this->belongsToMany("App\Models\Proveedor","productos_historial","producto_id","proveedor_id");
    }

    public function productoHistorial(){
        return $this->hasMany(ProductoHistorial::class,'producto_id');
    }

    /**
     * Retorna el ultimo historial registrado
     * Con el proveedor seleccionado como proveedor actual
     */
    public function ultimoHistorialProveedor(){
        return ProductoHistorial::where("proveedor_id",$this->proveedor_actual)
            ->where("producto_id",$this->id)->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
    }

    public function ultimoHistorialProveedorId($id_proveedor){
        return ProductoHistorial::where("proveedor_id",$id_proveedor)
            ->where("producto_id",$this->id)->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
    }

    public function ultimoHistorial(){
        return ProductoHistorial::where("producto_id",$this->id)->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
    }

    public function proveedoresPrecios(){
        $data = DB::select("select productos_historial.*,proveedores.*, CAST(productos_historial.precio_costo_nuevo as DECIMAL(20.10)) as valor from productos_historial
                    inner join proveedores on proveedores.id = productos_historial.proveedor_id
                    inner join (select max(created_at) as fecha, proveedor_id,producto_id from productos_historial group by proveedor_id,producto_id ) t on t.fecha = productos_historial.created_at and t.producto_id = productos_historial.producto_id 
                    where t.proveedor_id = productos_historial.proveedor_id
                    and productos_historial.producto_id = $this->id
                    group by productos_historial.proveedor_id
                    order By valor");
        return Collection::make($data);
    }

    public function proveedorActual(){
        return $this->belongsTo("App\Models\Proveedor","proveedor_actual");
    }
    public static function listaProductosByCategoriaNegocio($categoria_negocio_id,$pagina=1){
        $productos_por_pagina = config('options.paginate_api');

        $productos = Producto::where('categoria_id',$categoria_negocio_id)
            ->forPage($pagina,$productos_por_pagina)
            ->get();

        $productos_aux = [];
        $i=0;
        $precio_producto = 0;
        foreach ($productos as $c){
            $precio_producto = $c->precio_costo + (($c->precio_costo * $c->iva)/100) + (($c->precio_costo * $c->utilidad)/100);
            //$imagen = url("/app/public/img/productos/".$c->id."/".$c->imagen); //Desde el Servidor
            $imagen = url("/img/productos/".$c->id."/".$c->imagen);
            $productos_aux[$i] = [ "id" => $c->id, "nombre" => $c->nombre,"precio"=> $precio_producto,"imagen"=>$imagen];
            $i++;
        }
        return $productos_aux;

    }

    public static function productosProveedor(){
        return Producto::where("proveedor","si")->where("usuario_id",Auth::user()->id);
    }

    public static function listaProductosProveedorNombreCategoria(){
        $productos = Producto::productosProveedor()->where("estado","Activo")->get();
        $lista = [];
        foreach ($productos as $p){
            $lista[$p->id] = $p->nombre." - ".$p->categoria->nombre;
        }
        return $lista;
    }

    public static function busquedaPorProveedores($filtro = "",$skip = null,$take = null){
        $filtro = "%".$filtro."%";
        $admin = User::find(Auth::user()->userAdminId());
        $categoria = $admin->categoria;
        if($categoria){
            $productos = Producto::where("proveedor","si")->where("categoria_id",$categoria->id)->where(function ($q) use ($filtro){
                $q->where("nombre","like",$filtro)
                    ->orWhere("tags","like",$filtro);
            });

            if($skip && $take){
                $productos->skip($skip)->take($take);
            }
            return $productos->get();
        }
        return [];
    }

    public function usuarioProveedor(){
        return $this->belongsTo("App\User","usuario_id","id");
    }

    public function promociones(){
        return $this->hasMany(PromocionProveedor::class);
    }

    /*
     * Si el producto tiene una promoción activa
     * Y la fecha que se pasa como parametro esta entre el rango inicio y fin de la promocion
     */
    public function tienePromocionFecha($fecha){
        $producto = $this->promociones()
            ->where("estado","activo")
            ->where("fecha_inicio","<=",$fecha)
            ->where("fecha_fin",">=",$fecha)->first();
        if($producto)return true;
        return false;
    }

    public function promocionHoy(){
        return $this->promociones()->where("estado","activo")
            ->where("fecha_inicio","<=",date("Y-m-d"))
            ->where("fecha_fin",">=",date("Y-m-d"))->first();
    }

    /*
     * Si el producto tiene una promoción activa
     * Y la fecha de inicio o fin de la promocion se encuentra entre el rango recibido como parametro
     */
    public function tienePromocionFechaInicioFin($fechaInicio,$fechaFin){
        $producto = $this->promociones()
            ->where("estado","activo")
            ->where(function($q) use ($fechaInicio,$fechaFin){
                $q->whereBetween("fecha_inicio",[$fechaInicio,$fechaFin])
                    ->orWhere(function($query) use ($fechaInicio,$fechaFin){
                        $query->whereBetween("fecha_fin",[$fechaInicio,$fechaFin]);
                    });
            })
            ->first();
        if($producto)return true;
        return false;
    }

    public function permitirEliminar(){
        $materias_primas = [];//$this->MateriasPrimas;
        $pedidos_proveedor = Producto::join("pedidos_proveedor_productos","productos.id","=","pedidos_proveedor_productos.producto_id")
            ->where("productos.id",$this->id)->get();
        $historial = [];//ProductoHistorial::where("producto_id",$this->id)->get();
        $facturas = $this->facturasRelacionadas()->get();
        $compras = $this->comprasRelacionadas()->get();
        if(count($materias_primas) || count($historial) || count($pedidos_proveedor) || count($facturas) || count($compras))return false;

        return true;
    }

    /**
     * Actualiza el promedio ponderado del producto de acuerdo a la información de stock anterior y los datos nuevos que se envien como parametro
     *
     * FORMATO DE ARRAY DE DATOS
     *      valor,cantidad
     *   [
     *      [5000,10],
     *      [5500,5],
     *      [4800,15],
     *   ]
     */
    public function updatePromedioPonderado($data){
        $precios = 0;
        $cantidades = 0;
        $promedio_ponderado = 0;
        for($i = 0;$i < count($data);$i++){
            $cantidades += $data[$i][1];
            $precios += $data[$i][0]*$data[$i][1];
        }
        $precios += $this->promedio_ponderado * $this->stock;
        $cantidades += $this->stock;

        if($cantidades > 0){
            $promedio_ponderado = $precios / $cantidades;
        }
        $this->promedio_ponderado = $promedio_ponderado;
        $this->stock = $cantidades;
        $this->save();
        return $promedio_ponderado;
    }

    public function aparicionesVentasCompras(){
        $facturados = Factura::join("facturas_productos_historial","facturas.id","=","facturas_productos_historial.factura_id")
                            ->join("productos_historial","facturas_productos_historial.producto_historial_id","=","productos_historial.id")
                            ->join("productos","productos_historial.producto_id","=","productos.id")
                            ->where("productos.id",$this->id)->get()->count();
        if($facturados)return true;

        $compras = Compra::join("compras_productos_historial","compras.id","=","compras_productos_historial.compra_id")
            ->join("productos_historial","compras_productos_historial.producto_historial_id","=","productos_historial.id")
            ->join("productos","productos_historial.producto_id","=","productos.id")
            ->where("productos.id",$this->id)->get()->count();
        if($compras)return true;
        return false;
    }

    public function DisponibleOmitirStockMp(){
        $materrias_primas = $this->MateriasPrimas()->select('materias_primas.*','producto_materia_unidad.cantidad')->get();
        $disponibles = [];
        foreach ($materrias_primas as $mp){
            $disponibles[] = $mp->stock/$mp->cantidad;
        }
        $menor = $disponibles[0];
        foreach ($disponibles as $data){
            if($data < $menor)$menor = $data;
        }
        return $menor;
    }

    public function DisminuirStockMp($cantidad){
        $materrias_primas = $this->MateriasPrimas()->select('materias_primas.*','producto_materia_unidad.cantidad')->get();
        foreach ($materrias_primas as $mp){
            $mp->stock = $mp->stock - ($cantidad * $mp->cantidad);
            $mp->save();
        }
    }
}
