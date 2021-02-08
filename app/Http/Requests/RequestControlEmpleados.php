<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;


class RequestControlEmpleados extends Request {

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
			"nombre"=>"required",
			"cedula"=>"required",
			"codigo_barras"=>"required",
			"estado_empleado"=>"required"
		];
		
		if($this->has("accion") && $this->input("accion") == "Agregar"){
			$data["nombre"] .= "|unique:control_empleados";
			$data["cedula"] .= "|unique:control_empleados";
			$data["codigo_barras"] .= "|unique:control_empleados";
		}

		return $data;
	}

	public function messages()
	{
		return [
			"nombre.required"=>"El campo nombre es requerido.",
			"nombre.unique"=>"Ya existe un empleado con el nombre ingresado.",

			"cedula.required"=>"El campo cédula es requerido.",
			"cedula.unique"=>"Ya existe un empleado con la cédula ingresada.",

			"codigo_barras.required"=>"El campo codigo de barras es requerido.",
			"codigo_barras.unique"=>"Ya existe un empleado con el codigo de barras ingresado.",
		];
	}

}