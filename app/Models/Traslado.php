<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection as Collection;


class Traslado extends Model {

    protected $connection = "mysql_alm";
	protected $table = "traslados";
    protected $fillable = ["numero","estado","almacen_id","usuario_id","usuario_creador_id","factura_id"];


    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Traslado::whereNotNull('id');
        }else if($perfil->nombre == 'administrador'){
            if(Auth::user()->admin_bodegas == 'si')
                return Traslado::where("traslados.usuario_id",$user->id);
            return Traslado::where("traslados.usuario_id",$user->userAdminId());
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Traslado::where('traslados.usuario_id',$usuario_creador->id);
            }
        }
    }

    public function usuario(){
        return $this->belongsTo("App\User");
    }

    public function usuarioCreador(){
        return $this->belongsTo("App\User","usuario_creador_id","id");
    }


    public static function ultimoTraslado(){
        return Traslado::permitidos()->orderBy("id","DESC")->orderBy("created_at","DESC")->orderBy("numero","DESC")->first();
    }

    public function precios(){
        return $this->hasMany(TrasladoPrecio::class);
    }

    public function getValor(){
        $valor = 0;
        $precios = $this->precios()->select("traslados_precios.*")->get();
        foreach ($precios as $p){
            $h_c = $p->historialCosto;
            $h_u = $p->historialUtilidad;
            if(!$h_u){
                $h_u = new ABHistorialUtilidad();
                $h_u->actualizacion_utilidad = 0;
                $h_u->utilidad = 0;
            }

            $cant_recibida = $p->cantidad;
            if($p->cantidad_recibida)$cant_recibida = $p->cantidad_recibida;
            $cant_devuelta = $p->cantidad - $cant_recibida;

            if($this->estado == 'recibido')$utilidad = $h_u->utilidad;
            else $utilidad = $h_u->actualizacion_utilidad;
            $precio = $h_c->precio_costo_nuevo + (($h_c->precio_costo_nuevo * $utilidad)/100);
            $valor += ($precio + (($precio * $h_c->producto->iva)/100))*($p->cantidad - $cant_devuelta);
        }
        return $valor;
    }

    public function almacen()
    {
        return $this->belongsTo(Almacen::class,'almacen_id');
    }

    public function almacenRemitente()
    {
        return $this->belongsTo(Almacen::class,'almacen_remitente_id');
    }
}
