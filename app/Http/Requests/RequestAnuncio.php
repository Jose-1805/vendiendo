<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestAnuncio extends Request {

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
			"titulo"=>"required|max:200",
			"descripcion"=>"required",
			"valor"=>"required|numeric|digits_between:3,20",
			"contacto"=>"required|max:200",
			"categoria"=>"required",
			"otras"=>"required_if:categoria,otras",
			"imagen_1"=>"image|max:1000|mimes:jpeg,png,jpg,svg",
			"imagen_2"=>"image|max:1000|mimes:jpeg,png,jpg,svg",
			"imagen_3"=>"image|max:1000|mimes:jpeg,png,jpg,svg",
		];

		return $data;
	}
	public function messages(){
		return [

		];
	}

}
