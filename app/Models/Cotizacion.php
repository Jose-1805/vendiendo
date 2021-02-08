<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class Cotizacion extends Model {

	protected $table = "cotizaciones";
    protected $fillable = ["numero","estado","dias_vencimiento","cliente_id","usuario_id","usuario_creador_id"];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Cotizacion::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            return Cotizacion::where("cotizaciones.usuario_id",$user->id);
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Cotizacion::where('cotizaciones.usuario_id',$usuario_creador->id);
            }
        }
    }

    public static function ultimaCotizacion(){
        return Cotizacion::permitidos()->orderBy("id","DESC")->orderBy("created_at","DESC")->orderBy("numero","DESC")->first();
    }
    public function cliente(){
        return $this->belongsTo("App\Models\Cliente");
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }

    public function usuarioCreador(){
        return $this->belongsTo("App\User","usuario_creador_id","id");
    }

    public function productosHistorial(){
        return $this->belongsToMany("App\Models\ProductoHistorial","cotizaciones_productos_historial");
    }

    public function getValor(){
        $productos_historial = $this->productosHistorial()->select("cotizaciones_productos_historial.cantidad","productos_historial.*")->get();
        $valor = 0;
        foreach ($productos_historial as $p){
            $precio_venta_sin_iva =  $p->precio_costo_nuevo + (($p->precio_costo_nuevo * $p->utilidad_nueva)/100);
            $valor += ($precio_venta_sin_iva + (($precio_venta_sin_iva * $p->iva_nuevo)/100)) * $p->cantidad;
        }
        return $valor;
    }

    public function getDatosValor(){

        $productos_historial = $this->productosHistorial()->select("cotizaciones_productos_historial.cantidad","productos_historial.*")->get();
        $subtotal = 0;
        $iva = 0;
        foreach ($productos_historial as $p){
            $subtotal_aux = ($p->precio_costo_nuevo + (($p->precio_costo_nuevo * $p->utilidad_nueva)/100)) * $p->cantidad;
            $subtotal +=  $subtotal_aux;
            $iva += (($subtotal_aux * $p->iva_nuevo)/100);
        }
        return ["subtotal"=>$subtotal,"iva"=>$iva];
    }

    public function historial(){
        return $this->hasMany(CotizacionHistorial::class);
    }
}
