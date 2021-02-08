<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestPqrs extends Request {

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
		return ["tipo_identificacion"=>"required|in:C.C,C.E,NIT,T.I",
            "identificacion"=>"required|max:15",
            "nombre"=>"required|max:100",
            "email"=>"max:100|email",
            "direccion"=>"max:150",
            "telefono"=>"digits_between:7,20",
            "tipo"=>"required|in:Queja,Sugerencia,Consulta,Petición,Solicitud de información",
            "queja"=>"required|max:1000"];
	}
	public function messages(){
		return [
            "tipo_identificacion.required"=>"El campo tipo identificación es requerido",
            "tipo_identificacion.in"=>"El campo tipo identificación debe contener uno de los siguientes valores (C.C, C.E, NIT o T.I)",

            "identificacion.required"=>"El campo identificación es requerido",
            "identificacion.max"=>"El campo identificación puede contener máximo 15 caracteres",

            "nombre.required"=>"El campo nombre es requerido",
            "nombre.max"=>"El campo nombre puede contener máximo 100 caracteres",

            "email.required"=>"El campo email es requerido",
            "email.max"=>"El campo email puede contener máximo 100 caracteres",
            "email.email"=>"Formato de email no válido",

            "direccion.required"=>"El campo dirección es requerido",
            "direccion.max"=>"El campo dirección puede contener máximo 150 caracteres",

            "telefono.required"=>"El campo teléfono es requerido",
            "telefono.digits_between"=>"El campo teléfono debe contener entre 7 y 20 dígitos",

            "tipo.required"=>"El campo tipo es requerido",
            "tipo.in:Queja,Sugerencia,Consulta,Petición,Solicitud de información"=>"El campo",

            "queja.required"=>"El campo queja es requerido",
            "queja.max"=>"El campo queja puede contener máximo 1000 caracteres",

        ];
	}

}
