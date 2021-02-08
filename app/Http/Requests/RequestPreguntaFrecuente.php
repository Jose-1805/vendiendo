<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestPreguntaFrecuente extends Request {

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
			"pregunta"=>"required|max:200",
			"respuesta"=>"required|max:500"
		];
	}
	public function messages(){
		return [
			"pregunta.required"=>"El campo pregunta es requerido",
			"pregunta.max"=>"El campo pregunta puede tener máximo 200 caracteres",
			"respuesta.required"=>"El campo respuesta es requerido",
			"respuesta.max"=>"El campo respuesta puede tener máximo 500 caracteres",

		];
	}

}
