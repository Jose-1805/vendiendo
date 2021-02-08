<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use Illuminate\Support\Facades\Auth;

class RequestNuevoUsuario extends Request {

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
        if (Auth::user()->bodegas == "si") {
            $data =  [
                "nombres"=>"required|max:100",
                "apellidos"=>"required|max:100",
                "alias"=>"required|max:30",
                "telefono"=>"required|digits_between:7,15",
                "email"=>"required|email|max:80",
                "perfil"=>"exists:perfiles,id",
                "almacen" => "required_if:perfil,3",//debe ir el id del perfil de usuario
            ];

            if($this->has("id")){
                $data["nit"] .= ",".$this->input("id");
            }
        }else{
            $data = [
                "nombre_negocio" => "required_if:perfil,2",//debe ir el id del perfil de administrador
                "categoria" => "required_if:perfil,2|exists:categorias,id",//debe ir el id del perfil de administrador
                "categorias" => "required_if:perfil,4",//debe ir el id del perfil de proveedor
                "municipio" => "required_if:perfil,4|exists:municipios,id",//debe ir el id del perfil de proveedor
                "nombres" => "required|max:100",
                "apellidos" => "required|max:100",
                "alias" => "required|max:30",
                "telefono" => "required|digits_between:7,15",
                "email" => "required|email|max:80",
                "perfil" => "exists:perfiles,id",
                "nit" => "required_if:perfil,2|unique:usuarios,nit",//debe ir el id del perfil de administrador
                "regimen" => "required_if:perfil,2|in:común,simplificado",//debe ir el id del perfil de administrador
                "plan" => "required_if:perfil,2|exists:planes,id",//debe ir el id del perfil de administrador
            ];

            if ($this->has("id")) {
                $data["nit"] .= "," . $this->input("id");
            }
        }


        return $data;
    }

    public function messages(){
        return [
            //nombre
            "nombres.required"=>"El campo nombres es requerido",
            "nombres.max"=>"El campo nombres debe tener máximo 100 caracteres",
            //apellidos
            "apellidos.required"=>"El campo apellidos es requerido",
            "apellidos.max"=>"El campo apellidos debe tener máximo 100 caracteres",
            //alias
            "alias.required"=>"El campo alias es requerido",
            "alias.max"=>"El campo alias debe tener máximo 30 caracteres",
            //telefono
            "telefono.required"=>"El campo teléfono es requerido",
            "telefono.digits_between"=>"El campo teléfono debe tener entre 7 y 15 caracteres",
            //correo
            "email.required"=>"El campo email es requerido",
            "email.max"=>"El campo email debe tener máximo 80 caracteres",
            "email.email"=>"El campo email no contiene un email válido",
            //perfil
            "perfil.exists"=>"La información enviada es incorrecta",

            "nit.required_if"=>"El campo NIT es requerido",
            "nit.unique"=>"El NIT ingresado ya se encuentra registrado en el sistema",

            "regimen.required_if"=>"El campo régimen es requerido",
            "regimen.in"=>"La información enviada es incorrecta",

            "plan.required_if"=>"El campo plan es requerido",
            "plan.exists"=>"La información enviada es incorrecta",

            "nombre_negocio.required_if"=>"El campo nombre de negocio es requerido",

            "categorias.required_if"=>"El campo categorias es requerido",

            "categoria.required_if"=>"El campo categoria es requerido",
            "categoria.exists" => "La información enviada es incorrecta",

            "municipio.required_if"=>"El campo municipio es requerido",
            "municipio.exists" => "La información enviada es incorrecta",

            "almacen.required_if"=>"El campo almacén es requerido",
        ];
    }

}