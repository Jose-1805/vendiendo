<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\MateriaPrima;

class Proveedor extends Model {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'proveedores';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombre','nit' ,'contacto', 'direccion','telefono','correo'];

    protected $guarded = ['id'];

    public static function lista(){
        $proveedores = Proveedor::permitidos()->get();
        $proveedores_aux = [];
        foreach ($proveedores as $p){
            $proveedores_aux[$p->id] = $p->nombre;
        }
        return $proveedores_aux;
    }

    public static function permitidos(){
        $user = Auth::user();
        $perfil = $user->perfil;
        if($perfil->nombre == "superadministrador") {
            return Proveedor::whereNotNull("id");
        }else if($perfil->nombre == "administrador") {
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no')
                return Proveedor::where("usuario_id", $user->userAdminId());
            return Proveedor::where("usuario_id", $user->id);
        }else if($perfil->nombre == "usuario"){
            $usuarioCreador = User::find($user->usuario_creador_id);
            if($usuarioCreador){
                return Proveedor::where("usuario_id", $usuarioCreador->id);
            }
        }

        return Proveedor::whereNull("id");
    }

    public function productos(){
        if(Auth::user()->bodegas == 'si')
            return $this->belongsToMany("App\Models\ABProducto","vendiendo_alm.historial_costos","proveedor_id","producto_id");
        else
            return $this->belongsToMany("App\Models\Producto","productos_historial");
    }

    /**
     * Retorna colleccion de objetos tipo producto que no esta relacionados con el objeto proveedor
     */
    public function productosOtrosProveedores(){
        if(Auth::user()->bodegas == 'si'){
            if(Auth::user()->admin_bodegas == 'si')
                $userId = Auth::user()->id;
            else
                $userId = Auth::user()->userAdminId();

            //dd(DB::select("SELECT producto_id from productos_historial WHERE productos_historial.proveedor_id = ".$this->id));
            $data = ABHistorialCosto::select("producto_id")->where("proveedor_id",$this->id)->get();
            $ids = [];
            foreach($data as $d){
                $ids[] = $d->producto_id;
            }
        }else{
            $userId = Auth::user()->userAdminId();
            //dd(DB::select("SELECT producto_id from productos_historial WHERE productos_historial.proveedor_id = ".$this->id));
            $data = ProductoHistorial::select("productos_historial.producto_id")->where("proveedor_id",$this->id)->get();
            $ids = [];
            foreach($data as $d){
                $ids[] = $d->producto_id;
            }
        }


        if(Auth::user()->bodegas == 'si')
        return ABProducto::whereNotIn("productos.id",$ids)
            ->where("productos.usuario_id",$userId);
        else

            return Producto::whereNotIn("productos.id",$ids)
                ->where("productos.usuario_id",$userId);
        /**
         * SELECT * FROM `productos` where productos.id not in (SELECT producto_id from productos_historial WHERE productos_historial.proveedor_id = 1)
         */
    }

    public function materiasPrimasOtrosProveedores(){
        $userId = Auth::user()->userAdminId();
        $otros_proveedores = Proveedor::permitidos()->get();

        $data = MateriaPrima::select("*")->where();

    }

    public function materiasPrimas(){
        return $this->belongsToMany(MateriaPrima::class,"materias_primas_historial");
    }

    public function compras(){
        return $this->hasMany(Compra::class);
    }

    public function productosPrecios($nombreLike = null){

        $strSql = "select productos_historial.*,productos.* from productos_historial
                    inner join proveedores on proveedores.id = productos_historial.proveedor_id
                    inner join productos on productos.id = productos_historial.producto_id
                    inner join (select max(created_at) as fecha, proveedor_id from productos_historial group by proveedor_id ) t on t.fecha = productos_historial.created_at 
                    where t.proveedor_id = productos_historial.proveedor_id
                    and t.proveedor_id = $this->id";
        if($nombreLike != null){
            $strSql.=" and productos.nombre LIKE '".$nombreLike."'";
        }

         $strSql .= " group by productos_historial.proveedor_id";

        $data = DB::select($strSql);
        return Collection::make($data);
    }

    public function permitirEliminar(){
        $productosHistorial = $this->productos;
        $productosProveedorActual = Producto::where("proveedor_actual",$this->id)->get();
        $materiasPrimasProveedorActual = MateriaPrima::where("proveedor_actual",$this->id)->get();
        $materiasPrimas = $this->materiasPrimas;
        $compras = $this->compras;

        if(count($productosHistorial) || count($productosProveedorActual) || count($materiasPrimasProveedorActual) || count($materiasPrimas) || count($compras))return false;
        return true;

    }

}
