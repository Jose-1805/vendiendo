<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Producto;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestProductoProveedor extends Request {

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
			"nombre"=>"required|max:50",
			"descripcion"=>"required|max:500",
			"barcode"=>"unique:productos,barcode,NULL,id,usuario_id,".Auth::user()->id,
			"precio_costo"=>"required|numeric",
			"select-categoria"=>"required|exists:categorias,id,negocio,si",
			"select-unidad"=>"required|exists:unidades,id,superadministrador,si",
			"medida_venta" => "required",
			"imagen"=>"mimes:jpeg,jpg,bmp,png|max:5000",
			"tags" => "max:250",
		];
		
		if($this->has("id")){
			$producto = Producto::productosProveedor()->where("id",$this->input("id"))->first();
			if($producto){
				$data["barcode"]="required|unique:productos,barcode,".$producto->id.",id,usuario_id,".Auth::user()->id;
			}
		}

		return $data;
	}

	public function messages()
	{
		return [
			//nombre
			"nombre.required" => "El campo nombre es requerido",
			"nombre.max" => "El campo nombre debe tener máximo 50 caracteres",

			"descripcion.required" => "El campo descripción es requerido",
			"descripcion.max" => "El campo descripción debe tener máximo 500 caracteres",

			"barcode.unique" => "El código de barras ingresado, ya ha sido registrado",

			"precio_costo.required"=>"El campo precio es requerido",
			"precio_costo.numeric"=>"El campo precio debe ser numérico",

			"select-categoria.required" => "Debe seleccionar una categoria",
			"select-categoria.exists" => "La información enviada es incorrecta",

			"select-unidad.required" => "Debe seleccionar una unidad",
			"select-unidad.exists" => "La información enviada es incorrecta",

			"medida_venta.required" => "El tipo de medida venta es requerido",

			"imagen.mimes" => "El archivo seleccionado debe ser una imagen (jpeg,jpg,bmp o png)",
			"imagen.max" => "La imagen seleccionada es demaciado grande",

			"tags.max"=>"El campo tags debe contener máximo 250 caracteres"
		];
	}

	public static function getRulesImportacion()
	{
		return [
			"nombre"=>"required|max:50",
			"descripcion"=>"required|max:500",
			"barcode"=>"unique:importaciones_productos,barcode,NULL,id,usuario_id,".Auth::user()->id,
			"precio_costo"=>"required|numeric",
			"medida_venta" => "required|in:Unitaria,Fraccional",
			"tags" => "max:250",
		];
	}

	public static function getMessagesImportacion(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.max"=>"El campo nombre debe tener máximo 50 caracteres",

			"descripcion.required"=>"El campo descripción es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",

			"barcode.required"=>"El campo barcode es requerido",
			"barcode.unique"=>"El código de barras ingresado ya ha sido registrado",

			"precio_costo.required"=>"El campo precio costo es requerido",
			"precio_costo.numeric"=>"El campo precio costo debe ser numérico",
			
			"medida_venta.required"=>"El campo medida venta es requerido",
			"medida_venta.in"=>"Los valores permitidos para el campo tipo de medida son (Unitaria o Fraccional)",

			"tags.max"=>"El campo tags debe contener máximo 250 caracteres"
		];
	}
}
