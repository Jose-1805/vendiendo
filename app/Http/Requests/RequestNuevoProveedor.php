<?php namespace App\Http\Requests;

use App\Http\Requests\Request;

class RequestNuevoProveedor extends Request {

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
			"nombre"=>"required|max:30",
			//"nit"=>"unique:proveedores,nit,NULL,id,usuario_id,".Auth::user()->userAdminId(),
            "contacto"=>"required|max:30",
            "direccion"=>"required|max:50",
            "telefono"=>"required|digits_between:7,15",
            "correo"=>"required|email|max:80"
		];
	}

    public function messages(){
        return [
            //nombre
            "nombre.required"=>"El campo nombre es requerido",
            "nombre.max"=>"El campo nombre debe tener máximo 30 caracteres",
            //contacto
            "contacto.required"=>"El campo contacto es requerido",
            "contacto.max"=>"El campo contacto debe tener máximo 30 caracteres",
            //direccion
            "direccion.required"=>"El campo dirección es requerido",
            "direccion.max"=>"El campo dirección debe tener máximo 50 caracteres",
            //telefono
            "telefono.required"=>"El campo teléfono es requerido",
            "telefono.digits_between"=>"El campo teléfono debe tener entre 7 y 15 caracteres",
            //correo
            "correo.required"=>"El campo correo es requerido",
            "correo.max"=>"El campo correo debe tener máximo 80 caracteres",
            "correo.email"=>"El campo correo no contiene un email válido"
        ];
    }

}
