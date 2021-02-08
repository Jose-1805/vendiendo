<?php namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class PromocionProveedor extends Model {

	protected $table = "promociones_proveedores";
    protected $fillable =['descripcion','fecha_inicio','fecha_fin','valor_actual','valor_con_descuento','estado','producto_id'];

	public static function permitidos(){
		return PromocionProveedor::select("promociones_proveedores.*")->join("productos","promociones_proveedores.producto_id","=","productos.id")
									->where("productos.usuario_id",Auth::user()->id);
	}

	public function producto(){
		return $this->belongsTo(Producto::class);
	}



	public static function promocionHoy($random){
		$admin = User::find(Auth::user()->userAdminId());
		$categoria = $admin->categoria;
		if($categoria){
			$promocion = PromocionProveedor::select("promociones_proveedores.*")
				->join("productos","promociones_proveedores.producto_id","=","productos.id")
				->where("productos.categoria_id",$categoria->id)
				->where("promociones_proveedores.estado","activo")
				->where("promociones_proveedores.fecha_inicio","<=",date("Y-m-d"))
				->where("promociones_proveedores.fecha_fin",">=",date("Y-m-d"))->get()->random($random);
			return $promocion;
		}
		return null;
	}

}
