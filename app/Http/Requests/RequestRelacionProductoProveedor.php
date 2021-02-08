<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestRelacionProductoProveedor extends Request {

	/**
	 * Determine if the user is authorized to make this request.
	 *
	 * @return bool
	 */
	public function authorize()
	{
		return true;
	}

	/**
	 * Get the validation rules that apply to the request.
	 *
	 * @return array
	 */
	public function rules()
	{
		$data = [
			"precio_costo_proveedor"=>"required|numeric",
			"iva_proveedor" => "numeric",
			"id"=>"required|exists:productos,id",
			"proveedor"=>"required|exists:proveedores,id"
		];

        if(Auth::user()->bodegas == 'si')
            $data["id"]="required|exists:vendiendo_alm.productos,id";

		if(Auth::user()->bodegas == 'no')
		    $data["utilidad_proveedor"] = "required|numeric";

		$admin = User::find(Auth::user()->userAdminId());
		if($admin->regimen == "común")
			$data["iva_proveedor"] .= "|required";
		return $data;
	}
	public function messages(){
		return [
			//nombre
			"precio_costo_proveedor.required"=>"El campo precio costo es requerido",
			"precio_costo_proveedor.numeric"=>"El campo precio costo debe ser numerico",
			"iva_proveedor.required"=>"El campo iva es requerido",
			"iva_proveedor.numeric"=>"El campo iva debe ser numerico",
			"utilidad_proveedor.required"=>"El campo utilidad es requerido",
			"utilidad_proveedor.numeric"=>"El campo utilidad debe ser numerico",
			"id.required"=>"La información enviada es incorrecta",
			"id.exists"=>"La información enviada es incorrecta",
			"proveedor.required"=>"La información enviada es incorrecta",
			"proveedor.exists"=>"La información enviada es incorrecta"
		];
	}

}
