<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestObjetivoVenta extends Request {

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
			"valor"=>"required|numeric",
            "mes"=>"required|numeric|min:1|max:12",
            "anio"=>"required|numeric|min:".date('Y')."|max:".(date('Y')+3)
		];
		if(Auth::user()->bodegas == 'si')
		    $data['almacen']='required';
		return $data;
	}
	public function messages(){
		return [
            "valor.required"=>"El campo valor es requerido",
            "valor.numeric"=>"El campo valor debe ser numérico",

			"mes.required"=>"EL campo mes es requerido",
			"mes.numeric"=>"EL campo mes debe se numérico",
			"mes.min"=>"La información enviada es incorrecta",
			"mes.max"=>"La información enviada es incorrecta",

			"anio.required"=>"EL campo año es requerido",
			"anio.numeric"=>"EL campo año debe se numérico",
			"anio.min"=>"La información enviada es incorrecta",
			"anio.max"=>"La información enviada es incorrecta max",

            "almacen.required"=>"EL campo almacén es requerido",
        ];
	}

}
