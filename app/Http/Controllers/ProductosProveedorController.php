<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\RequestProductoProveedor;
use App\Models\Categoria;
use App\Models\ImportacionProducto;
use App\Models\Producto;
use App\Models\Unidad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;

class ProductosProveedorController extends Controller {

	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modProductoProveedor");
		$this->middleware("modConfiguracion");
		$this->middleware("terminosCondiciones");
	}

	public function getIndex(Request $request){
		$filtro ="";
		if($request->has('filtro')){
			$productos = $this->listaFiltro($request->get('filtro'));
			$filtro = $request->get('filtro');
		}else{
			$productos = Producto::productosProveedor()->paginate(env("PAGINATE"));
		}

		return view("productos_proveedor.index")->with("productos",$productos)->with("filtro",$filtro);
	}

	public function getCreate(){
		return view("productos_proveedor.create")->with("producto",new Producto());
	}

	public function postStore(RequestProductoProveedor $request){
		$data=$request->all();
		if($request->has('select-unidad'))
			$data['unidad_id']=$request->get('select-unidad');
		if($request->has('select-categoria'))
			$data['categoria_id']=$request->get('select-categoria');

		DB::beginTransaction();
		$producto = new Producto();
		$producto->fill($data);
		$producto->usuario_id = Auth::user()->id;
		$producto->imagen ="";
		$producto->estado = "Activo";
		$producto->proveedor = "si";
		$producto->save();


		$aux = 1;
		$continuar = true;


		if($request->hasFile("imagen")){
			if ($request->file('imagen')->isValid()) {
				$img = $request->file("imagen");
				$ruta= public_path("img/productos/".$producto->id);
				$img->move($ruta,$img->getClientOriginalName());
				$producto->imagen = $img->getClientOriginalName();
				$producto->save();
			}else{
				return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
			}
		}
		Session::flash("mesaje","El producto ha sido creado con éxito");
		DB::commit();
		return ["success" => true];
	}

	public function getEdit($id){
		$producto = Producto::productosProveedor()->where("id",$id)->first();
		if($producto) {
			return view("productos_proveedor.edit")->with("producto", $producto);
		}
		return redirect("/");
	}

	public function postUpdate(RequestProductoProveedor $request, $id){
		$data=$request->all();
		if($request->has('select-unidad'))
			$data['unidad_id']=$request->get('select-unidad');
		if($request->has('select-categoria'))
			$data['categoria_id']=$request->get('select-categoria');

		$producto = Producto::productosProveedor()->where("id",$id)->first();

		if($producto && $producto->exists){
			DB::beginTransaction();
			$producto->fill($data);
			$producto->save();

			if($request->hasFile("imagen")){
				if ($request->file('imagen')->isValid()) {
					$img = $request->file("imagen");
					$ruta= public_path("img/productos/".$producto->id);

					if($producto->imagen){
						@unlink($ruta.'/'.$producto->imagen);
					}
					$img->move($ruta,$img->getClientOriginalName());
					$producto->imagen = $img->getClientOriginalName();
					$producto->save();
				}else{
					return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
				}
			}
			DB::commit();
			Session::flash("mensaje","El producto se actualizó de manera exitosa");
			return ["success"=>true];
		}else{
			return response(["error"=>["La información enviada es incorrecta"]],422);
		}

	}

	public function getEstado($id_producto,$estado)
	{
		$nuevo_estado = "";
		if($estado=="Activo")
			$nuevo_estado ="Inactivo";
		if($estado=="Inactivo")
			$nuevo_estado = "Activo";
		$producto = Producto::productosProveedor()->where('id',$id_producto)
			->update([
				"estado"=>$nuevo_estado
			]);

		if ($producto)
			return response()->json(['response' => 'Se cambio satisfactoriamente el estado del producto']);
		else
			return response()->json(['response' => 'Ocurrio un error']);

		return response("Unauthorized",401);
	}

	public function postDetalle(Request $request){
		if($request->has("id")){
			$producto = Producto::productosProveedor()->where("id",$request->input("id"))->first();
			if($producto){
				return view("productos_proveedor.detalle")->with("producto",$producto);
			}
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function postFiltro(Request $request){
		$categoria = null;
		if($request->has("categoria"))$categoria = $request->input("categoria");
		if($request->has('filtro') || $categoria != null){
			$productos = $this->listaFiltro($request->get('filtro'),$categoria);
		}else{
			$productos = Producto::productosProveedor()->where("estado","Activo")->orderBy('nombre','ASC')->paginate(env("PAGINATE"));
		}
		/*if($request->has("vista")){
			$productos->setPath(url('/productos'));
			return view($request->input("vista"))->with("productos",$productos);
		}*/
		$productos->setPath(url('/productos-proveedor'));
		return view("productos_proveedor.lista")->with("productos",$productos);
	}

	public function listaFiltro($filtro,$categoria = null){
		$f = "%".$filtro."%";
		$productos = Producto::productosProveedor()->where(
			function($query) use ($f,$categoria){
				$query->where("nombre","like",$f);
				//if($categoria == null)
				//$query = $query->orWhere("categoria","like",$f);
			}
		);
		if($categoria != null){
			$productos = $productos->where("categoria_id",$categoria);
		}
		return $productos->where("estado","Activo")->orderBy("nombre","ASC")->paginate(env("PAGINATE"));
	}

	public function getImportacion(){
		$importaciones = ImportacionProducto::importacionesProveedor()->where("estado","pendiente")->get();
		return view("productos_proveedor.importacion")->with("importaciones",$importaciones);
	}

	public function postStoreImportacion(Request $request){
			if ($request->hasFile("archivo")) {
				if ($request->file('archivo')->isValid()) {
					$img = $request->file("archivo");
					$ext = $img->getClientOriginalExtension();
					if($ext == "xls" || $ext == "xlsx"){
						$ruta = storage_path("app/sistema/users/temporal/".Auth::user()->id);
						$img->move($ruta, $img->getClientOriginalName());
						$datos = [];
						Excel::load($ruta."/".$img->getClientOriginalName(),function($reader) use (&$datos){
							$datos = $reader->toArray();
						});
						@unlink($ruta."/".$img->getClientOriginalName());
						//dd($datos);
						if(count($datos)){
							$i = 1;
							DB::beginTransaction();
							foreach ($datos as $d){
								$i++;
								//todos la fila contiene el formato correcto
								/*if(isset($d["nombre"]) &&
									isset($d["descripcion"])&&
									isset($d["barcode"])&&
									isset($d["precio_costo"])&&
									isset($d["iva"])&&
									isset($d["utilidad"])&&
									isset($d["stock"])&&
									isset($d["umbral"])&&
									isset($d["medida_venta"])){*/

								$validator = Validator::make($d,RequestProductoProveedor::getRulesImportacion(),RequestProductoProveedor::getMessagesImportacion());
								if($validator->fails()){
									//dd($validator->errors()->all());
									return response($validator->errors()->all()+["Error"=>"Error en fila #".$i],422);
								}else{
									$barCodePro = Producto::productosProveedor()->where("barcode",$d["barcode"])->first();
									if($barCodePro)
										return response(["Error"=>["Ya existe un producto con el código de barras '".$d["barcode"]."'"],"linea"=>["Error en la linea #".$i]],422);

									$importacion = new ImportacionProducto();
									$importacion->fill($d);
									$importacion->usuario_id = Auth::user()->id;
									$importacion->proveedor = "si";
									$importacion->save();
								}

								/*}else{
									return response(["Error"=>["El formato enviado es incorrecto, asegurese de utilizar unicamente el formato generado por el sistema"]],422);
								}*/
							}
							DB::commit();
							Session::flash("mensaje","La importación de productos se realizó con éxito");
							return ["success" => true];
						}else{
							return response(["Error" => "El archivo enviado se encuentra vacio"], 422);
						}
					}else{
						return response(["Error" => "Seleccione únicamente archivos con extensión xls o xlsx"], 422);
					}
				}
			} else {
				return response(["Error" => "Seleccione un archivo"], 422);
			}
			return response(["Error" => "La información enviada es incorrecta"], 422);
	}

	public function postProcesarImportacion(Request $request){
		if($request->has("id") && $request->has("categoria") && $request->has("unidad")){
			if(Categoria::where("negocio","si")->where("id",$request->input("categoria"))->first() && Unidad::where("superadministrador","si")->where("id",$request->input("unidad"))->first()) {
				$importacion = ImportacionProducto::find($request->input("id"));
				if ($importacion) {
					$data = $importacion->toArray();
					$prodBarcode = Producto::productosProveedor()->where("barcode",$data["barcode"])->first();

					if($prodBarcode){
						return response(["Error"=>["Ya se ha registrado un producto con el código de barras '".$data["barcode"]."'"]],422);
					}

					$data["categoria_id"] = $request->input("categoria");
					$data["unidad_id"] = $request->input("unidad");
					$data["usuario_id"] = Auth::user()->id;
					$data["estado"] = "Activo";
					$data["proveedor"] = "si";
					$producto = new Producto();
					$producto->fill($data);
					$producto->save();

					$importacion->estado = "procesado";
					$importacion->save();

					return ["success" => true];
				}
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}
	
	public function postRechazarImportacion(Request $request){

		if($request->has("id")){
			$importacion = ImportacionProducto::importacionesProveedor()->where("id",$request->input("id"))->first();
			if ($importacion) {
				$importacion->delete();/*
				$importacion->estado = "rechazado";
				$importacion->save();*/
				return ["success" => true];
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function getFormatoImportacion(){
		$path = storage_path() . '/app/sistema/formatos/PlantillaProductosProveedor.xlsx';

		if(!File::exists($path)) abort(404);

		$file = File::get($path);
		$type = File::mimeType($path);

		$response = response($file, 200);
		$response->header("Content-Type", $type);

		return $response;
	}
}
