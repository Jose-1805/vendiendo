<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestPrecioProducto extends Request {

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
			"precio_costo_nuevo"=>"required|numeric",
			"iva_nuevo" => "numeric",
			"id"=>"required",
			"proveedor"=>"required"
		];

		if(Auth::user()->bodegas == 'no')
            $data["utilidad_nueva"] = "required|numeric";
		$admin = User::find(Auth::user()->userAdminId());
		if($admin->regimen == "común")
			$data["iva_nuevo"] .= "|required";
		return $data;
	}
	public function messages(){
		return [
			//nombre
			"precio_costo_nuevo.required"=>"El campo precio costo es requerido",
			"precio_costo_nuevo.numeric"=>"El campo precio costo debe ser numerico",
			"iva_nuevo.required"=>"El campo iva es requerido",
			"iva_nuevo.numeric"=>"El campo iva debe ser numerico",
			"utilidad_nueva.required"=>"El campo utilidad es requerido",
			"utilidad_nueva.numeric"=>"El campo utilidad debe ser numerico",
			"id.required"=>"La información enviada es incorrecta",
			"proveedor.required"=>"La información enviada es incorrecta"
		];
	}

}
