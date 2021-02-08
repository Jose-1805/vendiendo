<?php namespace App\Http\Requests;

use App\Http\Requests\Request;
use App\Models\Producto;
use App\User;
use Illuminate\Support\Facades\Auth;

class RequestProducto extends Request {

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
			"nombre"=>"required|max:50|unique:productos,nombre,NULL,id,usuario_id,".Auth::user()->userAdminId(),
			"select-categoria"=>"required",
			//"precio_venta"=>"required|numeric",
			"umbral"=>"required|numeric",
			//"stock"=>"required|numeric",
			"descripcion"=>"required|max:500",
			"select-unidad"=>"required",
			"medida_venta" => "required",
			"imagen"=>"mimes:jpeg,jpg,bmp,png|max:1024",
			//"barcode"=>"required",//|unique:productos,barcode,NULL,id,usuario_id,".Auth::user()->userAdminId()
			"barcode"=>"unique:productos,barcode,NULL,id,usuario_id,".Auth::user()->userAdminId(),
		];

		if($this->has("producto")){
			$data["barcode"]="unique:productos,barcode,".$this->input("producto").",id,usuario_id,".Auth::user()->userAdminId();
			$data["nombre"]="required|max:50|unique:productos,nombre,".$this->input("producto").",id,usuario_id,".Auth::user()->userAdminId();
		}

		return $data;
	}


	public static function getRulesApp()
	{
		$data = [
			"nombre"=>"required|max:50|unique:productos_app,nombre,NULL,id,usuario_id,".Auth::user()->userAdminId(),
			"stock"=>"required|numeric",
			//"descripcion"=>"required|max:500",
			"medida_venta" => "required|in:Unitaria,Fraccional",
			//"barcode"=>"required",//unique:productos_app,barcode,NULL,id,usuario_id,".Auth::user()->userAdminId(),
			"barcode"=>"unique:productos_app,barcode,NULL,id,usuario_id,".Auth::user()->userAdminId(),
			"unidad"=>"required",
			"categoria"=>"required",
			"estado"=>"required|in:agregado,editado"
		];

		return $data;
	}

	public static function getRulesImportacion()
	{
		$admin = User::find(Auth::user()->userAdminId());
		$data = [
			"nombre"=>"required|max:50|unique:importaciones_productos,nombre,NULL,id,usuario_id,".Auth::user()->userAdminId(),
			"descripcion"=>"required|max:500",
			"barcode"=>"unique:importaciones_productos,barcode,NULL,id,usuario_id,".Auth::user()->userAdminId(),
			"precio_costo"=>"required|numeric",
			//"utilidad"=>"required|numeric",
			"stock"=>"required|numeric",
			"umbral"=>"required|numeric",
			"medida_venta" => "required|in:Unitaria,Fraccional",

		];

		if(Auth::user()->bodegas != 'si')
		    $data["precio_venta"]="required|numeric";
		
		if($admin->regimen == "común"){
			$data["iva"]="required|numeric|max:100";
		}

		return $data;
	}
	public function messages(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.unique"=>"El nombre ingresado, ya ha sido registrado",
			"nombre.max"=>"El campo nombre debe tener máximo 50 caracteres",
			"select-categoria.required"=>"Debe seleccionar una categoria",
			/*"precio_venta.required"=>"El campo precio costo es requerido",
			"precio_venta.numeric"=>"El campo precio costo debe ser numérico",*/
			"umbral.required"=>"El campo umbral es requerido",
			"umbral.numeric"=>"El campo umbral debe ser numérico",
			"stock.required"=>"El campo stock es requerido",
			"stock.numeric"=>"El campo stock debe ser numérico",
			"descripcion.required"=>"El campo descripción es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",
			"imagen.mimes"=>"El archivo seleccionado debe ser una imagen (jpeg,jpg,bmp o png)",
			"imagen.max"=>"La imagen seleccionada es demasiado grande",
			"select-unidad.required"=>"Debe seleccionar una unidad",
			"medida_venta.required"=>"El tipo de medida venta es requerido",
			"barcode.unique"=>"El código de barras ingresado, ya ha sido registrado"
		];
	}

	public static function getMessagesApp(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.max"=>"El campo nombre debe tener máximo 50 caracteres",
			"nombre.unique"=>"El nombre ingresado, ya ha sido registrado",

			"stock.required"=>"El campo stock es requerido",
			"stock.numeric"=>"El campo stock debe ser numérico",

			"descripcion.required"=>"El campo descripción es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",

			"barcode.unique"=>"El código de barras ingresado, ya ha sido registrado",

			"medida_venta.required"=>"El tipo de medida venta es requerido",
			"medida_venta.in"=>"Los valores permitidos para el campo tipo de medida son (Unitaria o Fraccional)",

			"unidad.required"=>"Debe seleccionar una unidad",
			"barcode.unique"=>"El código de barras ingresado, ya ha sido registrado",
			"estado.required"=>"El campo estado es requerido",
			"estado.in"=>"El campo estado debe contener los valores (agregado o editado)"
		];
	}

	public static function getMessagesImportacion(){
		return [
			//nombre
			"nombre.required"=>"El campo nombre es requerido",
			"nombre.max"=>"El campo nombre debe tener máximo 50 caracteres",
			"nombre.unique"=>"El nombre ingresado, ya ha sido registrado",

			"descripcion.required"=>"El campo descripción es requerido",
			"descripcion.max"=>"El campo descripción debe tener máximo 500 caracteres",

			"barcode.required"=>"El campo barcode es requerido",
			"barcode.unique"=>"El código de barras ingresado ya ha sido registrado",

			"precio_costo.required"=>"El campo precio costo es requerido",
			"precio_costo.numeric"=>"El campo precio costo debe ser numérico",

			"iva.required"=>"El campo iva es requerido",
			"iva.numeric"=>"El campo iva debe ser numérico",

			"precio_venta.required"=>"El campo precio venta es requerido",
			"precio_venta.numeric"=>"El campo precio venta debe ser numérico",

			"stock.required"=>"El campo stock es requerido",
			"stock.numeric"=>"El campo stock costo debe ser numérico",

			"umbral.required"=>"El campo umbral es requerido",
			"umbral.numeric"=>"El campo umbral debe ser numérico",

			"medida_venta.required"=>"El campo medida venta es requerido",
			"medida_venta.in"=>"Los valores permitidos para el campo tipo de medida son (Unitaria o Fraccional)",
		];
	}

}
