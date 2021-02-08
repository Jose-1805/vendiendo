<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class RequestNuevaMateriaPrima extends Request {

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
		$user = Auth::user()->userAdminId();

		return [
			"nombre"=>"required|max:100",
			"codigo"=>"max:100|unique:materias_primas,codigo,NULL,id,usuario_id,".$user,
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
			"codigo.unique"=>"Este codigo ya se encuentra en uso",
			//direccion
			"descripcion.required"=>"El campo descripción es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",
			//telefono
			"imagen.mimes"=>"El archivo seleccionado debe ser una imagen (jpeg,jpg,bmp o png)",
			"imagen.max"=>"La imagen seleccionada es demaciado grande"
		];
	}


    public static function getRulesImportacion()
    {
        $user = Auth::user()->userAdminId();

        return [
            "nombre"=>"required|max:100|unique:importaciones_materias_primas,nombre,NULL,id,usuario_id,".$user,
            "codigo"=>"max:100|unique:importaciones_materias_primas,codigo,NULL,id,usuario_id,".$user,
            "descripcion"=>"required|max:500",
            "valor"=>"required|numeric",
            "umbral"=>"required|numeric",
            "cantidad"=>"required|numeric",
        ];
    }

    public static function getMessagesImportacion(){
        return [
            //nombre
            "nombre.required"=>"El campo nombre es requerido",
            "nombre.max"=>"El campo nombre debe tener máximo 100 caracteres",
            "nombre.unique"=>"El nombre ingresado, ya ha sido registrado",
            //codigo
            "codigo.max"=>"El campo codigo debe tener máximo 100 caracteres",
            "codigo.unique"=>"Este codigo ya se encuentra en uso",
            //direccion
            "descripcion.required"=>"El campo descripción es requerido",
            "descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",
            //telefono
            "valor.required"=>"EL campo valor es requerido",
            "valor.numeric"=>"EL campo valor debe ser numerico",
            //telefono
            "umbral.required"=>"EL campo umbral es requerido",
            "umbral.numeric"=>"EL campo umbral debe ser numerico",
            //telefono
            "cantidad.required"=>"EL campo cantidad es requerido",
            "cantidad.numeric"=>"EL campo cantidad debe ser numerico",
        ];
    }
}
