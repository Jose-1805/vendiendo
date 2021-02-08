<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestSistemaPuntos extends Request {

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
		return [
			"caducidad_puntos"=>"required|numeric",
			"pesos_venta"=>"required|numeric",
			"puntos_venta"=>"required|numeric",
			"pesos_pago"=>"required|numeric",
			"puntos_pago"=>"required|numeric",
		];
	}

	public function messages()
	{
		return[
			"caducidad_puntos.required"=>"El campo caducidad es requerido",
			"caducidad_puntos.numeric"=>"El campo caducidad debe ser de tipo numérico",

			"pesos_venta.required"=>"El campo Ventas/peso(s) es requerido",
			"pesos_venta.numeric"=>"El campo Ventas/peso(s) venta debe ser de tipo numérico",

			"puntos_venta.required"=>"El campo Ventas/punto(s) es requerido",
			"puntos_venta.numeric"=>"El campo Ventas/punto(s) debe ser de tipo numérico",

			"pesos_pago.required"=>"El campo Pagos/peso(s) es requerido",
			"pesos_pago.numeric"=>"El campo Pagos/peso(s) debe ser de tipo numérico",

			"puntos_pago.required"=>"El campo Pagos/punto(s) es requerido",
			"puntos_pago.numeric"=>"El campo Pagos/punto(s) debe ser de tipo numérico",
		];
	}

}
