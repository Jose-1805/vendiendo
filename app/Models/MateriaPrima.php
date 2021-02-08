<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\ProductoMateriaUnidad;
use App\User;
class MateriaPrima extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'materias_primas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre','codigo' ,'descripcion', 'imageb','usuario_id','unidad_id','stock','umbral','proveedor_actual','promedio_ponderado','precio_costo'];

    protected $guarded = ['id'];

    public function relacionesProveedor(){
        return $this->hasMany("App\Models\ProveedorMateriaPrima");
    }

    public function proveedores(){
        return $this->belongsToMany(Proveedor::class,"materias_primas_historial","materia_prima_id","proveedor_id")
            ->whereRaw("materias_primas_historial.id in (select max(materias_primas_historial.id) as mp_id from materias_primas_historial group by materias_primas_historial.proveedor_id,materias_primas_historial.materia_prima_id )");
        //->whereRaw("materias_primas_historial.proveedor_id = materias_primas.proveedor_actual")->get();
    }

    public function proveedores_eloquent(){
        return $this->belongsToMany(Proveedor::class, "materias_primas_historial", "materia_prima_id", "proveedor_id");
    }

    public function ultimoHistorial($proveedor = null){
        if($proveedor){
            return MateriaPrimaHistorial::where("materia_prima_id",$this->id)
                ->where("proveedor_id",$proveedor)->orderBy("created_at","DESC")->first();
        }else{
            return MateriaPrimaHistorial::where("materia_prima_id",$this->id)
                ->orderBy("created_at","DESC")->first();
        }
    }

    public function unidad(){
        return $this->belongsTo("App\Models\Unidad");
    }
    public function MateriasProducto(){
        return $this->hasMany('App\Models\ProductoMateriaUnidad');
    }
    //relacion con la tabla pivot ProveedorMateriaPrima
    public function MateriaPrimaProveedores(){
        return $this->hasMany('App\Models\ProveedorMateriaPrima');
    }

    public static function permitidos(){
        return MateriaPrima::select(
            "materias_primas.*",
            "materias_primas_historial.precio_costo_nuevo as valor_mp",
            "materias_primas_historial.materia_prima_id as materia_prima_id",

            "proveedores.nombre as proveedor_nombre",
            "proveedores.direccion as proveedor_direccion",
            "proveedores.telefono as proveedor_telefono",
            "materias_primas_historial.id as relacion_id")
                ->join("materias_primas_historial","materias_primas.id","=","materias_primas_historial.materia_prima_id")
                ->join("proveedores","materias_primas_historial.proveedor_id","=","proveedores.id")
                ->where("materias_primas.usuario_id",Auth::user()->userAdminId())
                ->whereRaw("materias_primas_historial.id in (select max(materias_primas_historial.id) as mp_id from materias_primas_historial group by materias_primas_historial.proveedor_id,materias_primas_historial.materia_prima_id )");
    }

    public static function materiasPrimasPermitidas(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return MateriaPrima::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return MateriaPrima::where("materias_primas.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creator = User::find($user->usuario_creador_id);
            if($usuario_creator){
                return MateriaPrima::where('materias_primas.usuario_id',$usuario_creator->id);
            }
        }
    }

    public function precioActual(){
        return $this->getValorProveedor($this->proveedor_actual);
    }

    public function getValorProveedor($id_proveedor){
        $proveedor_materia = $this->proveedores()->select("materias_primas_historial.*")->where("proveedores.id",$id_proveedor)->orderBy("materias_primas_historial.created_at","DESC")->first();
        if ($proveedor_materia){
            return $proveedor_materia->precio_costo_nuevo;
        }
        return false;
    }

    public function compras(){
        return $this->belongsToMany(Compra::class,"compras_materias_primas");
    }

    public function proveedorActual(){
        return $this->belongsTo(Proveedor::class,'proveedor_actual','id');
    }
    public static function updateStockMateriaPrima($materia_prima_id,$producto_id,$cantidad,$action,$relation_table){

        $materia_prima = MateriaPrima::materiasPrimasPermitidas()->where('id',$materia_prima_id)->first();
        if ($action == 'SAVE'){
            $materia_prima->stock -= $cantidad;
        }
        $materia_prima->save();

        return $materia_prima;
    }

    public function permitirEliminar(){
        $compras = $this->compras;
        $productos_materia_unidad = ProductoMateriaUnidad::where("materia_prima_id",$this->id)->get();
        $proveedores = [];//$this->proveedores;

        if(count($proveedores) || count($productos_materia_unidad) || count($compras))return false;
        return true;
    }

    public function aparicionesProductosCompras(){
        $productos = Producto::join("producto_materia_unidad","productos.id","=","producto_materia_unidad.producto_id")
            ->join("materias_primas","producto_materia_unidad.materia_prima_id","=","materias_primas.id")
            ->where("materias_primas.id",$this->id)->get()->count();
        if($productos)return true;

        $compras = Compra::join("compras_materias_primas_historial","compras.id","=","compras_materias_primas_historial.compra_id")
            ->join("materias_primas_historial","compras_materias_primas_historial.materia_prima_historial_id","=","materias_primas_historial.id")
            ->join("materias_primas","materias_primas_historial.materia_prima_id","=","materias_primas.id")
            ->where("materias_primas.id",$this->id)->get()->count();
        if($compras)return true;
        return false;
    }

}
