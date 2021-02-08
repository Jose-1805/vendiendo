<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Abono extends Model {

	protected $table ="abonos";
    protected $fillable =['valor','fecha','nota','usuario_id','tipo_abono','tipo_abono_id','caja_usuario_id'];

    function factura(){
        return $this->belongsTo("App\Models\Factura","tipo_abono_id");
    }
    
    function compra(){
        return $this->belongsTo("App\Models\Compra","tipo_abono_id");
    }

    public static function permitidos(){
        return Abono::where("abonos.usuario_id",Auth::user()->userAdminId());
    }
    /*public static function getAbonos($id_compra){
        $abonos = Abono::select('*')
            ->where('tipo_abono_id',$id_compra)
            ->where('tipo_abono','compra')
            ->where('usuario_id',Auth::user()->id)
            ->orderBy("updated_at")
            ->get();
        return $abonos;
    }*/

    public function usuario(){
        return $this->belongsTo(User::class,"usuario_id");
    }

    public function caja(){
        return Cajas::join("cajas_usuarios","cajas.id","=","cajas_usuarios.caja_id")
            ->where("cajas_usuarios.id",$this->caja_usuario_id)->first();
    }

    public static function getEfectivoFacturasByCajaMaestra($caja_maestra){
        $abonos = Abono::permitidos()->select('valor')->where('abonos.tipo_abono','factura')
            ->join("cajas_usuarios","abonos.caja_usuario_id","=","cajas_usuarios.id")
            ->join("caja","cajas_usuarios.caja_mayor_id","=","caja.id")
            ->where("caja.id",$caja_maestra)->sum('valor');
        return $abonos;
    }

    public static function getEfectivoComprasByCajaMaestra($caja_maestra){
        $abonos = Abono::permitidos()->select('valor')->where('abonos.tipo_abono','compra')
            ->where("abonos.caja_maestra_id",$caja_maestra)->sum('valor');
        return $abonos;
    }
}
