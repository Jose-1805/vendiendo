<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class UnidadUpdateRequest extends Request {

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
			"nombre" => "required|max:20|unique:unidades,nombre,".$this->input('unidad').",id,usuario_id,".Auth::user()->userAdminId(),
			"sigla"	 => "required|max:5"
		];
	}
	public function messages(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.max"=>"El campo nombre debe tener máximo 20 caracteres",
			"nombre.unique"=>"Ya existe una unidad con el nombre ingresado.",

			"sigla.required"=>"El campo sigla es requerido",
			"sigla.max"=>"El campo sigla debe tener máximo 5 caracteres",

		];
	}

}
