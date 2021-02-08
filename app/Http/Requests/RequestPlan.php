<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestPlan extends Request {

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
			"nombre"=>"required|max:100",
			"duracion"=>"required|in:1,3,6,9,12",
			"valor"=>"required|numeric|max:999999999",
			"n_usuarios"=>"required|numeric",
			"n_compras"=>"required|numeric",
			"n_facturas"=>"required|numeric",
			"n_almacenes"=>"required|numeric",
			"n_bodegas"=>"required|numeric",
			"n_proveedores"=>"required|numeric",
			"n_clientes"=>"required|numeric",
			"n_productos"=>"required|numeric",
			"n_promociones_sms"=>"required|numeric",
			/*"puntos"=>"required",
			"cliente_predeterminado"=>"required",
			"notificaciones"=>"required",*/
		];

		if(!$this->has("plan")){
			$data["nombre"] .= "|unique:planes,nombre";
		}

		return $data;
	}
	public function messages(){
		return [
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.unique"=>"Ya ha sido registrado un plan con el nombre ingresado",
			"nombre.max"=>"El campo nombre puede contener máximo 100 caracteres",

			"duracion.required"=>"El campo duración es requerido",
			"duracion.in"=>"La información enviada es incorrecta",

			"valor.required"=>"El campo valor es requerido",
			"valor.numeric"=>"El campo valor debe ser numérico",
			"valor.max"=>"El valor máximo en el campo valor es 999999999",

			"n_usuarios.required"=>"El campo número usuarios es requerido",
			"n_usuarios.numeric"=>"El campo número usuarios debe ser numérico",

			"n_compras.required"=>"El campo número compras es requerido",
			"n_compras.numeric"=>"El campo número compras debe ser numérico",

			"n_facturas.required"=>"El campo número facturas es requerido",
			"n_facturas.numeric"=>"El campo número facturas debe ser numérico",

			"n_almacenes.required"=>"El campo número almacenes es requerido",
			"n_almacenes.numeric"=>"El campo número almacenes debe ser numérico",

			"n_bodegas.required"=>"El campo número bodegas es requerido",
			"n_bodegas.numeric"=>"El campo número bodegas debe ser numérico",

			"n_proveedores.required"=>"El campo número proveedores es requerido",
			"n_proveedores.numeric"=>"El campo número proveedores debe ser numérico",

			"n_clientes.required"=>"El campo número clientes es requerido",
			"n_clientes.numeric"=>"El campo número clientes debe ser numérico",

			"n_productos.required"=>"El campo número productos es requerido",
			"n_productos.numeric"=>"El campo número productos debe ser numérico",

			"n_promociones_sms.required"=>"El campo número promociones es requerido",
			"n_promociones_sms.numeric"=>"El campo número promociones debe ser numérico",

		];
	}

}
