<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;
use App\User;

class RequestCambiarInformacionNegocio extends Request {

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
		$admin = User::find(Auth::user()->userAdminId());
		return [
			"nombre_negocio"=>"required|max:200|unique:usuarios,nombre_negocio,".$admin->id.",id",
			"nit"=>"required|max:30|unique:usuarios,nit,".$admin->id.",id",
			"telefono"=>"required|max:15"
		];
	}

	public function messages()
	{
		return [
			"nombre_negocio.required"=>"El campo nombre es requerido.",
			"nombre_negocio.max"=>"El campo nombre debe tener máximo 200 caracteres.",
			"nombre_negocio.unique"=>"Ya existe registrado un negocio con el nombre ingresado.",
			"nit.required"=>"El campo nit es requerido.",
			"nit.max"=>"El campo nit debe tener máximo 30 caracteres.",
			"nit.unique"=>"Ya existe registrado un negocio con el nit ingresado.",
			"telefono.required"=>"El campo teléfono es requerido.",
			"telefono.max"=>"Ya existe registrado un negocio con el telefono ingresado.",
		];
	}

}
