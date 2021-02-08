<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class CreateSedeRequest extends Request {

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
			"nombre" => "required",
			"direccion"	 => "required",
			"descripcion" => "required",
			"latitud" => "required",
			"longitud" => "required",
			"municipio_id" => "required",
			"usuario_id" => "required",

		];
	}
	public function messages(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"direccion.required"=>"El campo direccion es requerido",
			"descripcion.required"=>"El campo descripcion es requerido",
			"latitud.required"=>"El campo latitud es requerido",
			"longitud.required"=>"El campo longitud es requerido",
			"municipio_id.required"=>"El campo municipio es requerido",
			"usuario_id.required"=>"El campo usuario es requerido",

		];
	}

}
