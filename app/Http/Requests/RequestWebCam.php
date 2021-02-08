<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestWebCam extends Request {

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
			"nombre"=>"required|max:50",
			"alias"=>"required|max:50",
			"url"=>"required|max:50",
			"ubicacion_id"=>"required"
		];
	}

	public function messages()
	{
		return [
			"nombre.required"=>"El campo nombre es requerido.",
			"nombre.max"=>"El campo nombre debe tener máximo 50 caracteres.",
			"alias.required"=>"El campo alias es requerido.",
			"alias.max"=>"El campo alias debe tener máximo 50 caracteres.",
			"url.required"=>"El campo url es requerido.",
			"url.max"=>"El campo url debe tener máximo 50 caracteres.",
			"ubicacion_id.required"=>"El campo ubicacion es requerido."
		];
	}

}


