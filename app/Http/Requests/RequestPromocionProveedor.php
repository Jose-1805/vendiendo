<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Producto;

class RequestPromocionProveedor extends Request {

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
			"descripcion"=>"required|max:100",
			"fecha_inicio"=>"required|date",
			"fecha_fin"=>"required|date",
			"valor_con_descuento"=>"required|numeric",
			"producto"=>"required|exists:productos,id",
		];

		return $data;
	}

	public function messages(){
		return [
			//nombre
			"descripcion.required"=>"El campo descripciòn es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 100 caracteres",

			"fecha_inicio.required"=>"El campo fecha inicio es requerido",
			"fecha_inicio.date"=>"El campo fecha inicio debe ser de tipo fecha",

			"fecha_fin.required"=>"El campo fecha fin es requerido",
			"fecha_fin.date"=>"El campo fecha fin debe ser de tipo fecha",

			"valor_con_descuento.required"=>"El campo valor con descuento es requerido",
			"valor_con_descuento.numeric"=>"El campo valor con descuento debe ser de tipo numerico",

			"producto.required"=>"El campo producto es requerido",
			"producto.exists"=>"La información enviada es incorrecta",
		];
	}
}
