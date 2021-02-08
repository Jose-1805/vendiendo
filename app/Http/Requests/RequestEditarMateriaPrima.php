<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestEditarMateriaPrima extends Request {

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
			"nombre"=>"required|max:100",
			"codigo"=>"max:100",
			"descripcion"=>"required|max:500",
			"imagen"=>"mimes:jpeg,jpg,bmp,png|max:5000"
		];
	}

	public function messages(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.max"=>"El campo nombre debe tener máximo 100 caracteres",
			//codigo
			"codigo.max"=>"El campo codigo debe tener máximo 100 caracteres",
			//direccion
			"descripcion.required"=>"El campo descripción es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",
			//telefono
			"imagen.mimes"=>"El archivo seleccionado debe ser una imagen (jpeg,jpg,bmp o png)",
			"imagen.max"=>"La imagen seleccionada es demaciado grande"
		];
	}

}
