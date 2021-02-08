<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Requests\RequestProducto;
use App\Models\ABAlmacenStockProducto;
use App\Models\ABBodegaStockProducto;
use App\Models\ABHistorialCosto;
use App\Models\ABHistorialUtilidad;
use App\Models\ABProductoMateriaUnidad;
use App\Models\Almacen;
use App\Models\Bodega;
use App\Models\Categoria;
use App\Models\ImportacionProducto;
use App\Models\MateriaPrima;
use App\Models\PedidoProveedor;
use App\Models\ABProducto;
use App\Models\ProductoApp;
use App\Models\ProductoHistorial;
use App\Models\ProductoMateriaUnidad;
use App\Models\PromocionProveedor;
use App\Models\Unidad;
use App\Models\VProductoMateriaProveedor;
use App\User;
use Illuminate\Http\Response;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\ProveedorProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\RequestPrecioProducto;
use Illuminate\Support\Facades\Validator;
//use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManagerStatic as Image;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Stmt\Echo_;

class ProductosController extends Controller {

	public function __construct(){
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("modProducto",["except"=>["postFiltro","postDatosProducto","getListFacturaProductos"]]);
		$this->middleware("terminosCondiciones");
	}

	public function getIndex(Request $request)
	{
	    if(Auth::user()->bodegas == 'si')
		    return view('productos_ab.index');
	    else
            return view('productos.index');
	}

    public function getListProductos(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];

		if($orderBy == "id")$orderBy = "tipo_producto";

		if(Auth::user()->bodegas == 'si')
            $productos = ABProducto::permitidos()/*->orderBy('nombre','ASC')*/;
        else
            $productos = Producto::permitidos()/*->orderBy('nombre','ASC')*/;

            $productos = $productos->orderBy($orderBy, $sortColumnDir);
            $totalRegistros = $productos->count();
            //BUSCAR
            if ($search['value'] != null) {
                $productos = $productos->whereRaw(
                    " ( id LIKE '%" . $search["value"] . "%' OR " .
                    " nombre LIKE '%" . $search["value"] . "%' OR" .
                    " precio_costo LIKE '%" . $search["value"] . "%' OR" .
                    " iva LIKE '%" . $search["value"] . "%' OR" .
                    " utilidad LIKE '%" . $search["value"] . "%' OR" .
                    " stock LIKE '%" . $search["value"] . "%' OR" .
                    " umbral LIKE '%" . $search["value"] . "%' OR" .
                    " descripcion LIKE '%" . $search["value"] . "%' OR" .
                    " unidad_id LIKE '%" . $search["value"] . "%' OR" .
                    " categoria_id LIKE '%" . $search["value"] . "%' )");
            }

        $parcialRegistros = $productos->count();
        $productos = $productos->skip($start)->take($length);

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($productos->get() as $value) {
                $data = ['id' => $value->id,
                    'tipo_producto'=>$value->tipo_producto,
                    'nombre'=>$value->nombre,
                    'precio_costo'=>'$ '.number_format($value->precio_costo,2,",","."),
                    'iva'=>number_format($value->iva,2,",",".").'%',
                    'stock'=>$value->stock,
                    'umbral'=>$value->umbral,
                    'descripcion'=>$value->descripcion,
                    'unidad_id' =>$value->unidad->sigla,
                    'estado'=>$value->estado,
                    'eliminar'=>$value->permitirEliminar(),
                    'categoria_id'=>$value->categoria->nombre,
                    'omitir_stock_mp'=>$value->omitir_stock_mp
                ];

                if(Auth::user()->bodegas != 'si'){
                    $data['utilidad']=number_format($value->utilidad,2,",",".").'%';
                    $precioV = $value->precio_costo + (($value->precio_costo * $value->utilidad)/100);
                    $data[] = $precioV;
                    $data['costo_venta']="$ ".number_format($precioV + (($precioV * $value->iva)/100), 0, "," , "." );
                }
                $myArray[]=(object) $data;
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $productos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' =>$myArray,
            'info' =>$productos->get()];

            return response()->json($data);

    }

    public function getTipos(){
	    if(Auth::user()->bodegas == 'si')
		    return view('productos_ab.sub_menu');
	    else
		    return view('productos.sub_menu');
	}
	public function getCreate(Request $request)
	{
		$pr_ = "";
        $barCodeProducto_ = "";
		if($request->has("pr_"))$pr_ = $request->input("pr_");
        if($request->has("barCodeProducto_"))$barCodeProducto_ = $request->input("barCodeProducto_");

		if(Auth::user()->permitirFuncion("Crear","productos","inicio") &&
            (
                (Auth::user()->bodegas == 'si' && count(Bodega::permitidos()->get()) > 0)
                || (Auth::user()->bodegas == 'no')
            )
            && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador())
            ) {
		    if(Auth::user()->bodegas == 'si')
    			$view = view('productos_ab.create')->with("producto", new Producto())->with(["pr_" => $pr_, "barCodeProducto_" => $barCodeProducto_]);
            else
	    		$view = view('productos.create')->with("producto", new Producto())->with(["pr_" => $pr_, "barCodeProducto_" => $barCodeProducto_]);

            if($request->has("noPrOpc")){
				$view = $view->with("noPrOpc",true);
			}
			return $view;
		}
		return redirect("/");
	}
	public function postStore(RequestProducto $request)
	{
	    if(!(Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador()))
            return response(['erroe'=>['No es posible crear más productos, su plan alcanzó el tope máximo de productos permitidos']],422);
		$data=$request->all();
		if($request->has('select-unidad'))
			$data['unidad_id']=$request->get('select-unidad');
		if($request->has('select-categoria'))
			$data['categoria_id']=$request->get('select-categoria');

		$admin = User::find(Auth::user()->userAdminId());

		$proveedor_actual = null;
		if($request->has('proveedor_actual')){
			$proveedor_actual = $data['proveedor_actual'];
		}
		unset($data['proveedor_actual']);

		if(!$request->has("tipo_producto"))$data["tipo_producto"] = "Terminado";
		DB::beginTransaction();
		    if(Auth::user()->bodegas == 'si')
		        $producto = new ABProducto();
		    else
			    $producto = new Producto();

			$producto->fill($data);
			$producto->usuario_id = Auth::user()->userAdminId();
			$producto->usuario_id_creator = Auth::user()->id;
			$producto->imagen ="";
			$producto->estado = "Activo";
			$producto->save();

		if($proveedor_actual != null)
			$data['proveedor_actual'] = $proveedor_actual;

		$aux = 1;
		$continuar = true;

		if($data['tipo_producto']=="Terminado"){
			$proveedor_actual = 0;
			if(!$request->has("proveedor_actual")){
				return response(['proveedor'=>['Seleccione el proveedor actual del producto']], 422);
			}

			$proveedor_actual_ok = false;
			$data_precios = [];
			if(Auth::user()->bodegas == 'si')$aux_stock = 0;
			while ($continuar){

				if($aux == 1){
					if(!$request->has("select-proveedor-".$aux)){
						return response(['proveedor'=>['Debe relacionar por lo menos un proveedor']], 422);
					}
				}

				if($request->has("select-proveedor-".$aux)) {
					$proveedor = Proveedor::find($request->get("select-proveedor-" . $aux));
					if ($proveedor && $proveedor->exists) {
						if($aux == $request->input("proveedor_actual")){
							$producto->proveedor_actual = $proveedor->id;
							$proveedor_actual_ok = true;
							$producto->save();
						}

						//se evaluan los productos, las cantidades y los precios en la tablas distintas
                        // dependiendo si es un usario que usa bodegas o no
						if(Auth::user()->bodegas == 'si'){
                            if (ABHistorialCosto::where("proveedor_id", $proveedor->id)->where("producto_id", $producto->id)->get()->count()) {
                                return response(['proveedor' => ['No es posible relacionar más de una vez un proveedor y un producto']], 422);
                            } else {
                                if (Proveedor::permitidos()->whereIn("id", [$proveedor->id])->get()->count()) {
                                    $errores = [];
                                    if (!$data["precio_costo_" . $aux] || $data["precio_costo_" . $aux] == "" || !is_numeric($data["precio_costo_" . $aux])) {
                                        $errores['precio_costo'] = ['El campo precio costo es requerido en el proveedor ' . $aux];
                                    }
                                    $admin = User::find(Auth::user()->userAdminId());
                                    if ($admin->regimen == "común") {
                                        if (!$data["iva_" . $aux] || $data["iva_" . $aux] == "" || !is_numeric($data["iva_" . $aux])) {
                                            $errores['iva'] = ['El campo iva es requerido en el proveedor ' . $aux];
                                        }
                                    }

                                    /**
                                     * SE VA ACTUALIZANDO EL STOCK Y FORMANDO EL ARRAY QUE CONTIENE LA INFORMACION DE PRECIOS Y CANTIDADES PARA CALCULAR EL PROMEDIO PONDERADO
                                     */
                                    $cant = 0;
                                    if ($data["cantidad_" . $aux]) $cant = $data["cantidad_" . $aux];
                                    $data_precios[] = [$data["precio_costo_" . $aux], $cant];
                                    $aux_stock += $cant;

                                    if (count($errores)) {
                                        return response($errores, 422);
                                    } else {
                                        $dataHistorial = [
                                            "precio_costo_nuevo" => $data["precio_costo_" . $aux],
                                            "precio_costo_anterior" => $data["precio_costo_" . $aux],
                                            "producto_id" => $producto->id,
                                            "proveedor_id" => $proveedor->id,
                                            "usuario_id" => Auth::user()->id,
                                            "stock" => $cant,
                                        ];

                                        if ($admin->regimen == "común") {
                                            $dataHistorial["iva_anterior"] = $data["iva_" . $aux];
                                            $dataHistorial{"iva_nuevo"} = $data["iva_" . $aux];
                                        }

                                        $historial = new ABHistorialCosto();
                                        $historial->fill($dataHistorial);
                                        $historial->save();

                                        if ($aux == $request->input("proveedor_actual")) {
                                            $producto->precio_costo = $historial->precio_costo_nuevo;
                                            $producto->iva = $historial->iva_nuevo;
                                            $producto->save();
                                        }
                                    }
                                } else {
                                    return response(['Unauthorized' => ['No tiene permisos para relacionar con el proveedor seleccionado']], 422);
                                }
                            }
                        }else {
                            if (ProductoHistorial::where("proveedor_id", $proveedor->id)->where("producto_id", $producto->id)->get()->count()) {
                                return response(['proveedor' => ['No es posible relacionar más de una vez un proveedor y un producto']], 422);
                            } else {
                                if (Proveedor::permitidos()->whereIn("id", [$proveedor->id])->get()->count()) {
                                    $errores = [];
                                    if (!$data["precio_costo_" . $aux] || $data["precio_costo_" . $aux] == "" || !is_numeric($data["precio_costo_" . $aux])) {
                                        $errores['precio_costo'] = ['El campo precio costo es requerido en el proveedor ' . $aux];
                                    }
                                    $admin = User::find(Auth::user()->userAdminId());
                                    if ($admin->regimen == "común") {
                                        if (!$data["iva_" . $aux] || $data["iva_" . $aux] == "" || !is_numeric($data["iva_" . $aux])) {
                                            $errores['iva'] = ['El campo iva es requerido en el proveedor ' . $aux];
                                        }
                                    }
                                    if (!$data["utilidad_" . $aux] || $data["utilidad_" . $aux] == "" || !is_numeric($data["utilidad_" . $aux])) {
                                        $errores['utilidad'] = ['El campo utilidad es requerido en el proveedor ' . $aux];
                                    }

                                    /**
                                     * SE VA ACTUALIZANDO EL STOCK Y FORMANDO EL ARRAY QUE CONTIENE LA INFORMACION DE PRECIOS Y CANTIDADES PARA CALCULAR EL PROMEDIO PONDERADO
                                     */
                                    $cant = 0;
                                    if ($data["cantidad_" . $aux]) $cant = $data["cantidad_" . $aux];
                                    $data_precios[] = [$data["precio_costo_" . $aux], $cant];


                                    if (count($errores)) {
                                        return response($errores, 422);
                                    } else {
                                        $dataHistorial = [
                                            "precio_costo_nuevo" => $data["precio_costo_" . $aux],
                                            "precio_costo_anterior" => $data["precio_costo_" . $aux],
                                            "utilidad_nueva" => $data["utilidad_" . $aux],
                                            "utilidad_anterior" => $data["utilidad_" . $aux],
                                            "producto_id" => $producto->id,
                                            "proveedor_id" => $proveedor->id,
                                            "usuario_id" => Auth::user()->id,
                                            "stock" => $cant,
                                        ];

                                        if ($admin->regimen == "común") {
                                            $dataHistorial["iva_anterior"] = $data["iva_" . $aux];
                                            $dataHistorial{"iva_nuevo"} = $data["iva_" . $aux];
                                        }

                                        $historial = new ProductoHistorial();
                                        $historial->fill($dataHistorial);
                                        $historial->save();

                                        if ($aux == $request->input("proveedor_actual")) {
                                            $producto->precio_costo = $historial->precio_costo_nuevo;
                                            $producto->utilidad = $historial->utilidad_nueva;
                                            $producto->iva = $historial->iva_nuevo;
                                            $producto->save();
                                        }
                                    }
                                } else {
                                    return response(['Unauthorized' => ['No tiene permisos para relacionar con el proveedor seleccionado']], 422);
                                }
                            }
                        }
					} else {
						return response(['error' => ['La información enviada es incorrecta']], 422);
					}
				}else{
					$continuar = false;
				}
				$aux++;
			}
			if(!$proveedor_actual_ok){
				return response(['error' => ['Debe seleccionar correctamente el proveedor actual del producto']], 422);
			}

			//actualiza el promedio ponderado y el stock del producto si no es la versionde bodegas
            if(Auth::user()->bodegas == 'no')
			    $producto->updatePromedioPonderado($data_precios);
		}else if($data['tipo_producto'] == "Compuesto" || $data['tipo_producto'] == "Preparado"){
			//dd($request->all());
            if($producto->omitir_stock_mp != 'si' && $producto->omitir_stock_mp != 'no'){
                $producto->omitir_stock_mp = 'no';
                $producto->save();
            }

            if($producto->omitir_stock_mp == 'si'){
                $producto->stock = 0;
                $producto->save();
            }

            $rules = [
				"precio_costo"=>"required|numeric",
				//"iva"=>"required|numeric",
				//"utilidad"=>"required|numeric",
			];

			if(Auth::user()->bodegas != 'si'){
                $rules["utilidad"] = "required|numeric";
            }

			if($admin->regimen == "común"){
				$rules["iva"] = "required|numeric";
			}
			$mensajes = [
				"precio_costo.required"=>"El campo precio costo es requerido",
				"precio_costo.numeric"=>"El campo precio costo debe ser numerico",
				"iva.required"=>"El campo iva es requerido",
				"iva.numeric"=>"El campo iva debe ser numerico",
				"utilidad.required"=>"El campo utilidad es requerido",
				"utilidad.numeric"=>"El campo utilidad debe ser numerico",
			];

			$validator = Validator::make($request->all(),$rules,$mensajes);
			if($validator->fails()){
				return response($validator->errors(),422);
			}

			$dataHistorial = [
				"precio_costo_nuevo" => $data["precio_costo"],
				"precio_costo_anterior" => $data["precio_costo"],
				"producto_id" => $producto->id,
				"usuario_id" => Auth::user()->id
			];

			if(Auth::user()->bodegas != 'si'){
                $dataHistorial["utilidad_nueva"] = $data["utilidad"];
				$dataHistorial["utilidad_anterior"] = $data["utilidad"];
            }

			if($admin->regimen == "común"){
				$dataHistorial["iva_anterior"] = $data["iva"];
				$dataHistorial["iva_nuevo"] = $data["iva"];
			}

            if(Auth::user()->bodegas == 'si')
			    $historial = new ABHistorialCosto();
			else
			    $historial = new ProductoHistorial();

			$historial->fill($dataHistorial);
			$historial->save();

			if($producto->omitir_stock_mp != 'si') {
                if (Auth::user()->bodegas == 'si') $aux_stock = $producto->stock;
            }

			while ($continuar){
				if ($aux==1){
					if(!$request->has('select-materia-prima-'.$aux)){
						return response(['materia-prima'=>['Debe relacionar por lo menos una materia prima al producto']], 422);
					}
				}

				if ($request->has('select-materia-prima-'.$aux)){
					$materia = MateriaPrima::find($request->get('select-materia-prima-'.$aux));
					if ($materia && $materia->exists){
					    if(Auth::user()->bodegas == 'si')
					    	$count = ABProductoMateriaUnidad::where('materia_prima_id',$materia->id)->where('producto_id',$producto->id)->get()->count();
						else
					        $count = ProductoMateriaUnidad::where('materia_prima_id',$materia->id)->where('producto_id',$producto->id)->get()->count();

						if ($count > 0){
							return response(['materia-prima' => ['No es posible relacionar mas de una vez una materia prima y un producto']], 422);
						}else{
							if (MateriaPrima::materiasPrimasPermitidas()->whereIn("id", [$materia->id])->get()->count()) {
								if (!$request->has("cantidad-" . $aux)) {
									return response(['cantidad' => ['Debe ingresar la cantidad de la materia prima ' . $aux]], 422);
								} else {
									if ($producto->omitir_stock_mp == 'si' || $materia->stock >= ($request->get("cantidad-" . $aux) * $producto->stock)){
										if(Auth::user()->bodegas == 'si')
	    								    $obj = new ABProductoMateriaUnidad();
									    else
    										$obj = new ProductoMateriaUnidad();

                                        $obj->producto_id = $producto->id;
										$obj->materia_prima_id = $materia->id;
										$obj->cantidad = $request->get("cantidad-" . $aux);
										$obj->save();
										if ($obj && $producto->omitir_stock_mp != 'si'){
											MateriaPrima::updateStockMateriaPrima($materia->id,$producto->id,($request->get("cantidad-" . $aux)*$producto->stock),'SAVE','');
										}
									}else{
										return response(['cantidad' => ["La existencia de " . "<FONT FACE='arial' SIZE=3><b><u>" . $materia->nombre . "</u></b></FONT> no es suficiente"]], 422);
									}
								}
							} else {
								return response(['Unauthorized' => ['No tiene permisos para utilizar la materia prima']], 422);
							}
						}
					}else {
						return response(['error' => ['La información enviada es incorrecta']], 422);
					}
				}else{
					$continuar = false;
				}
				$aux++;
			}
		}

		if($request->hasFile("imagen")){
			if ($request->file('imagen')->isValid()) {
				$img = $request->file("imagen");
				if(Auth::user()->bodegas == 'si')
				    $ruta= public_path("img/productos_ab/".$producto->id);
				else
				    $ruta= public_path("img/productos/".$producto->id);

				$img->move($ruta,$img->getClientOriginalName());
				$producto->imagen = $img->getClientOriginalName();
				$producto->save();

                $path = $ruta.'/'.$producto->imagen;
                $path_thumb = $ruta.'/thumb_'.$producto->imagen;
                $image = Image::make($path);
                $ancho = $image->width();
                $alto = $image->height();

                $redimendion = 500;

                if ($ancho >= $alto) {
                    $relacion = $alto / $ancho;
                    $image->resize($redimendion, intval($redimendion * $relacion));
                }else {
                    $relacion = $ancho / $alto;
                    $image->resize(intval($redimendion*$relacion), $redimendion);
                }
                //$image->save($path_new);
                $image->save($path);

                $redimendion_thumb = 200;
                if ($ancho >= $alto) {
                    $relacion = $alto / $ancho;
                    $image->resize($redimendion_thumb, intval($redimendion_thumb * $relacion));
                }else {
                    $relacion = $ancho / $alto;
                    $image->resize(intval($redimendion_thumb*$relacion), $redimendion_thumb);
                }
                //$image->save($path_new);
                $image->save($path_thumb);
			}else{
				return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
			}
		}

		//Se hace la distribución de las unidades entre los almacenes y la bodega
		if(Auth::user()->bodegas == 'si'){
            $almacenes = Almacen::permitidos()->get();
            $cantidad_distribuida = 0;

            foreach ($almacenes as $al){
                if($request->has('cantidad_al_'.$al->id) && $request->has('precio_venta_al_'.$al->id) && $request->input('cantidad_al_'.$al->id) > 0){

                    $cantidad = $request->input('cantidad_al_'.$al->id);
                    //(((precio_venta / ((100 + _iva) / 100)) / _precio_costo) - 1) * 100
                    $precio_venta = $request->input('precio_venta_al_'.$al->id);
                    $utilidad = ((($precio_venta / ((100 + $producto->iva) / 100)) / $producto->precio_costo) - 1) * 100;

                    $cantidad_distribuida += $cantidad;
                    if($cantidad_distribuida > $aux_stock){
                        return response(['error'=>['la información enviada es incorrecta ']],422);
                    }

                    $historial_utilidad = new ABHistorialUtilidad();
                    $historial_utilidad->utilidad = $utilidad;
                    $historial_utilidad->producto_id = $producto->id;
                    $historial_utilidad->almacen_id = $al->id;
                    $historial_utilidad->save();

                    $registro = new ABAlmacenStockProducto();
                    $registro->stock = $cantidad;
                    $registro->producto_id = $producto->id;
                    $registro->almacen_id = $al->id;
                    $registro->save();
                 }
            }

            $registro_bodega = new ABBodegaStockProducto();
            $registro_bodega->stock = $producto->stock - $cantidad_distribuida;
            $registro_bodega->producto_id = $producto->id;
            $registro_bodega->bodega_id = Bodega::permitidos()->first()->id;
            $registro_bodega->save();

            $producto->stock -= $cantidad_distribuida;
            $producto->save();

            if($data['tipo_producto']=="Terminado")
                //actualiza el promedio ponderado y el stock del producto si es la versionde bodegas
                    $producto->updatePromedioPonderado($data_precios);
        }

		DB::commit();
		$data = ["success" => true];
		if($request->has("noPrOpc")){
			$data["seleccion"]=$producto->id;
			$data["location"] = "compra";
		}else {
			Session::flash("mensaje", "El producto se creo de manera éxitosa");
		}
		return $data;
	}
	public function getEstado($id_producto,$estado)
	{
		if(Auth::user()->permitirFuncion("Estado","productos","inicio")){
			$nuevo_estado = "";
			if($estado=="Activo")
				$nuevo_estado ="Inactivo";
			if($estado=="Inactivo")
				$nuevo_estado = "Activo";

			if(Auth::user()->bodegas == 'si') {
                $producto = ABProducto::ProductosPermitidosBySession()->where('id', $id_producto)
                    ->update([
                        "estado" => $nuevo_estado
                    ]);
            }else{
                $producto = Producto::ProductosPermitidosBySession()->where('id', $id_producto)
                    ->update([
                        "estado" => $nuevo_estado
                    ]);
            }

			if ($producto)
				return response()->json(['response' => 'Se cambio satisfactoriamente el estado del producto']);
			else
				return response()->json(['response' => 'Ocurrio un error']);

			}
		return response("Unauthorized",401);
	}

	public function getEdit($id_producto){
		if(Auth::user()->permitirFuncion("Editar","productos","inicio")){
			//$producto = VProductoMateriaProveedor::findOrNew($id_producto);
			//$producto = VProductoMateriaProveedor::where('producto_id',$id_producto)->firstOrFail();
            if(Auth::user()->bodegas == 'si')
			    $producto = ABProducto::permitidos()->where('id',$id_producto)->first();
			else
                $producto = Producto::permitidos()->where('id',$id_producto)->first();

			if(!$producto){
				return redirect()->back();
			}
			//dd($producto);
			switch ($producto->tipo_producto){
				case 'Terminado':
					$detalles_producto = $producto->proveedoresPrecios();
					break;
				case 'Compuesto':
					$detalles_producto = $producto->MateriasPrimas()->select("materias_primas.*","producto_materia_unidad.cantidad")->get();
					break;
				case 'Preparado':
					$detalles_producto = $producto->MateriasPrimas()->select("materias_primas.*","producto_materia_unidad.cantidad")->get();
					break;
				default:
					$detalles_producto='';
					break;
			}
			//echo count($detalles_producto);
			//dd($detalles_producto);

            if(Auth::user()->bodegas == 'si')
			    return view('productos_ab.edit')->with('producto',$producto)->with('detalle_producto',$detalles_producto);
			else
			    return view('productos.edit')->with('producto',$producto)->with('detalle_producto',$detalles_producto);
		}
		return response("Unauthorized",401);

	}

	public function postEditPrecio(Request $request){
		if(Auth::user()->permitirFuncion("Editar","productos","inicio")){
			//$producto = VProductoMateriaProveedor::findOrNew($id_producto);
            if(Auth::user()->bodegas == 'si')
			    $producto = ABProducto::permitidos()->where('id',$request->input("id_producto"))->first();
            else
			    $producto = Producto::permitidos()->where('id',$request->input("id_producto"))->first();
			//dd($producto);
			$proveedor = Proveedor::permitidos()->where("id",$request->input("id_proveedor"))->first();
			if($producto) {
			    if(!$proveedor){
			        $proveedor = $producto->proveedorActual()->first();
                }

                if($proveedor) {
                    $historial = $producto->ultimoHistorialProveedorId($proveedor->id);
                    if ($historial) {
                        //echo count($detalles_producto);
                        //dd($detalles_producto);
                        if (Auth::user()->bodegas == 'si')
                            return view('productos_ab.edit_precio')->with('producto', $producto)->with("proveedor", $proveedor)->with("historial", $historial);
                        else
                            return view('productos.edit_precio')->with('producto', $producto)->with("proveedor", $proveedor)->with("historial", $historial);
                    }
                }
			}
			return response(["Error"=>["La información envidada es incorrecta"]],422);
		}
		return response("Unauthorized",401);

	}

	public function postUpdatePrecio(RequestPrecioProducto $request){
		if(Auth::user()->permitirFuncion("Editar","productos","inicio")) {
			if(Auth::user()->bodegas == 'si')
		        $producto = ABProducto::permitidos()->where("id",$request->input("id"))->first();
		    else
			    $producto = Producto::permitidos()->where("id",$request->input("id"))->first();

			$proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();
			if($producto && $proveedor){
				$cant = 0;
				if(!$producto->aparicionesVentasCompras()){
					$cant = $request->input("stock");
				}

				$lastHistorial = $producto->ultimoHistorialProveedorId($proveedor->id);
				if($lastHistorial) {
					$dataHistorial = [
						"precio_costo_nuevo" => $request->input("precio_costo_nuevo"),
						"precio_costo_anterior" => $lastHistorial->precio_costo_nuevo,
						"producto_id" => $producto->id,
						"proveedor_id" => $proveedor->id,
						"usuario_id" => Auth::user()->id,
						"stock" => $cant
					];

					if(Auth::user()->bodegas == 'no'){
                        $dataHistorial["utilidad_nueva"] = $request->input("utilidad_nueva");
						$dataHistorial["utilidad_anterior"] = $lastHistorial->utilidad_nueva;
                    }

					if ($request->has("iva_nuevo")) {
						$dataHistorial["iva_nuevo"] = $request->input("iva_nuevo");
						$dataHistorial["iva_anterior"] = $lastHistorial->iva_nuevo;
					}

					if(Auth::user()->bodegas == 'si')
					    $historial = new ABHistorialCosto();
					else
                        $historial = new ProductoHistorial();

					$historial->fill($dataHistorial);
					$historial->save();

					$precio_costo_inicial = $producto->precio_costo;
					$iva_inicial = $producto->iva;

					$producto->precio_costo = $historial->precio_costo_nuevo;
					$producto->iva = $historial->iva_nuevo;
					if(Auth::user()->bodegas == 'no')
					    $producto->utilidad = $historial->utilidad_nueva;

					if(!$producto->aparicionesVentasCompras()){
						$producto->stock = $cant;
						$producto->promedio_ponderado = $request->input("precio_costo_nuevo");
					}
					$producto->save();

					if(Auth::user()->bodegas == 'si') {
                        //historial de utilidad del producto en diferentes almacenes
                        $historialUtilidadProducto = ABHistorialUtilidad::where('producto_id', $producto->id)->orderBy('created_at', 'DESC')->get();

                        $almacenes_actualizados = [];

                        //se debe actualizar la utilidad de acuerdo al nuevo precio de costo e iva del producto si se ha actualizado
                        foreach ($historialUtilidadProducto as $h) {
                            //cada producto en cada almacén debe seguir teniendo el mismo precio de venta
                            if(!in_array($h->almacen_id,$almacenes_actualizados)){
                                $almacenes_actualizados[] = $h->almacen_id;

                                $utilidad = ($precio_costo_inicial * $h->utilidad)/100;
                                $precio_venta = $precio_costo_inicial + $utilidad;
                                $precio_venta = $precio_venta + (($precio_venta * $iva_inicial)/100);

                                $utilidad_nueva = ((($precio_venta / ((100 + $producto->iva) / 100)) / $producto->precio_costo) - 1) * 100;

                                $historialUtilidad = new ABHistorialUtilidad();
                                $historialUtilidad->utilidad = $utilidad_nueva;
                                $historialUtilidad->producto_id = $producto->id;
                                $historialUtilidad->almacen_id = $h->almacen_id;
                                if($h->actualizacion_utilidad){
                                    $historialUtilidad->actualizacion_utilidad = $h->actualizacion_utilidad;
                                }
                                $historialUtilidad->save();
                            }

                        }
                    }
					return ["success" => true, "producto" => $producto];
				}
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

    public function postUpdatePrecioActual(Requests\RequestPrecioActual $request){
        if(Auth::user()->permitirFuncion("Editar","productos","inicio")) {
            if(Auth::user()->bodegas == 'si')
                $producto = ABProducto::permitidos()->where("id",$request->input("id"))->first();
            else
                $producto = Producto::permitidos()->where("id",$request->input("id"))->first();

            $proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();
            if($producto){
                if(!$proveedor){
                    $proveedor = $producto->proveedorActual()->first();
                }

                if($proveedor) {
                    $cant = 0;
                    if (!$producto->aparicionesVentasCompras()) {
                        $cant = $request->input("stock");
                    }

                    $lastHistorial = $producto->ultimoHistorialProveedorId($proveedor->id);
                    if ($lastHistorial) {
                        $dataHistorial = [
                            "precio_costo_nuevo" => $request->input("precio_costo_nuevo"),
                            "precio_costo_anterior" => $lastHistorial->precio_costo_nuevo,
                            "producto_id" => $producto->id,
                            "proveedor_id" => $proveedor->id,
                            "usuario_id" => Auth::user()->id,
                            "stock" => $cant
                        ];

                        if (Auth::user()->bodegas == 'no') {
                            $dataHistorial["utilidad_nueva"] = $request->input("utilidad_nueva");
                            $dataHistorial["utilidad_anterior"] = $lastHistorial->utilidad_nueva;
                        }

                        if ($request->has("iva_nuevo")) {
                            $dataHistorial["iva_nuevo"] = $request->input("iva_nuevo");
                            $dataHistorial["iva_anterior"] = $lastHistorial->iva_nuevo;
                        }

                        if (Auth::user()->bodegas == 'si')
                            $historial = new ABHistorialCosto();
                        else
                            $historial = new ProductoHistorial();

                        $historial->fill($dataHistorial);
                        $historial->save();

                        $precio_costo_inicial = $producto->precio_costo;
                        $iva_inicial = $producto->iva;

                        $producto->precio_costo = $historial->precio_costo_nuevo;
                        $producto->iva = $historial->iva_nuevo;
                        if (Auth::user()->bodegas == 'no')
                            $producto->utilidad = $historial->utilidad_nueva;

                        if (!$producto->aparicionesVentasCompras()) {
                            $producto->stock = $cant;
                            $producto->promedio_ponderado = $request->input("precio_costo_nuevo");
                        }
                        $producto->save();

                        if (Auth::user()->bodegas == 'si') {
                            //historial de utilidad del producto en diferentes almacenes
                            $historialUtilidadProducto = ABHistorialUtilidad::where('producto_id', $producto->id)->orderBy('created_at', 'DESC')->get();

                            $almacenes_actualizados = [];

                            //se debe actualizar la utilidad de acuerdo al nuevo precio de costo e iva del producto si se ha actualizado
                            foreach ($historialUtilidadProducto as $h) {
                                //cada producto en cada almacén debe seguir teniendo el mismo precio de venta
                                if (!in_array($h->almacen_id, $almacenes_actualizados)) {
                                    $almacenes_actualizados[] = $h->almacen_id;

                                    $utilidad = ($precio_costo_inicial * $h->utilidad) / 100;
                                    $precio_venta = $precio_costo_inicial + $utilidad;
                                    $precio_venta = $precio_venta + (($precio_venta * $iva_inicial) / 100);

                                    $utilidad_nueva = ((($precio_venta / ((100 + $producto->iva) / 100)) / $producto->precio_costo) - 1) * 100;

                                    $historialUtilidad = new ABHistorialUtilidad();
                                    $historialUtilidad->utilidad = $utilidad_nueva;
                                    $historialUtilidad->producto_id = $producto->id;
                                    $historialUtilidad->almacen_id = $h->almacen_id;
                                    if ($h->actualizacion_utilidad) {
                                        $historialUtilidad->actualizacion_utilidad = $h->actualizacion_utilidad;
                                    }
                                    $historialUtilidad->save();
                                }

                            }
                        }
                        return ["success" => true, "producto" => $producto];
                    }
                }
            }
        }
        return response(["Error"=>["La información enviada es incorrecta"]],422);
    }

	public function postUpdate(RequestProducto $request, $id)
	{
		if(Auth::user()->permitirFuncion("Editar","productos","inicio")){
			$data=$request->all();
			if($request->has('select-unidad'))
				$data['unidad_id']=$request->get('select-unidad');
			if($request->has('select-categoria'))
				$data['categoria_id']=$request->get('select-categoria');

            if(Auth::user()->bodegas == 'si')
			    $producto = ABProducto::permitidos()->where("id",$id)->first();
            else
			    $producto = Producto::permitidos()->where("id",$id)->first();
			if($producto && $producto->exists){
				$admin = User::find(Auth::user()->userAdminId());

				$proveedor_actual = null;
				if($request->has('proveedor_actual')){
					$proveedor_actual = $data['proveedor_actual'];
				}
				unset($data['proveedor_actual']);
				unset($data['omitir_stock_mp']);
				DB::beginTransaction();
				$producto->fill($data);
				$producto->save();

                if(Auth::user()->bodegas == 'si') {
                    $relacion_producto_materia_prima = ABProductoMateriaUnidad::where('producto_id', $producto->id)->get();
                    ABProducto::DeleteRelation($request->get('tipo_producto'),$producto->id);
                }else {
                    $relacion_producto_materia_prima = ProductoMateriaUnidad::where('producto_id', $producto->id)->get();
                    Producto::DeleteRelation($request->get('tipo_producto'),$producto->id,$producto->omitir_stock_mp);
                }


				if($proveedor_actual != null)
				$data['proveedor_actual'] = $proveedor_actual;
				$aux = 1;
				$continuar = true;



				if($request->get('tipo_producto') == "Terminado"){
					$proveedor_actual = 0;
					if(!$request->has("proveedor_actual")){
						return response(['proveedor'=>['Seleccione el proveedor actual del producto']], 422);
					}
					$proveedor_actual_ok = false;
					$proveedores = [];
					while ($continuar){
						if($aux == 1){
							if(!$request->has("select-proveedor-".$aux)){
								return response(['proveedor'=>['Debe relacionar por lo menos un proveedor']], 422);
							}
						}
						//dd($request->all());
						if($request->has("select-proveedor-".$aux)) {
							$proveedor = Proveedor::find($request->get("select-proveedor-" . $aux));
							if ($proveedor && $proveedor->exists) {
								if($aux == $request->input("proveedor_actual")){
									$producto->proveedor_actual = $proveedor->id;
									$proveedor_actual_ok = true;
									$producto->save();
								}
								if(in_array($proveedor->id, $proveedores)){
									return response(['proveedor' => ['No es posible relacionar más de una vez un proveedor y un producto']], 422);
								}else {
									$proveedores[] = $proveedor->id;
									if (Proveedor::permitidos()->whereIn("id", [$proveedor->id])->get()->count()) {
										$errores = [];
										if (!$data["precio_costo_" . $aux] || $data["precio_costo_" . $aux] == "" || !is_numeric($data["precio_costo_" . $aux])) {
											$errores['precio_costo'] = ['El campo precio costo es requerido en el proveedor ' . $aux];
										}
										$admin = User::find(Auth::user()->userAdminId());
										if($admin->regimen == "común") {
											if (!$data["iva_" . $aux] || $data["iva_" . $aux] == "" || !is_numeric($data["iva_" . $aux])) {
												$errores['iva'] = ['El campo iva es requerido en el proveedor ' . $aux];
											}
										}

										if(Auth::user()->bodegas != 'si') {
                                            if (!isset($data["utilidad_" . $aux]) || $data["utilidad_" . $aux] == "" || !is_numeric($data["utilidad_" . $aux])) {
                                                $errores['utilidad'] = ['El campo utilidad es requerido en el proveedor ' . $aux];
                                            }
                                        }

										$cant = 0;
										if(!$producto->aparicionesVentasCompras()){
											if($data["cantidad_" . $aux])$cant = $data["cantidad_" . $aux];
											$data_precios[] = [$data["precio_costo_".$aux],$cant];
										}
										if(count($errores)){
											return response($errores,422);
										}else {

										    if(Auth::user()->bodegas == 'si'){
                                                $lastHistorial = ABHistorialCosto::where("producto_id",$producto->id)->where("proveedor_id",$proveedor->id)
                                                    ->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
                                            }else{
                                                $lastHistorial = ProductoHistorial::where("producto_id",$producto->id)->where("proveedor_id",$proveedor->id)
                                                    ->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
                                            }
											if($lastHistorial) {
												$dataHistorial = [
													"precio_costo_nuevo" => $data["precio_costo_" . $aux],
													"precio_costo_anterior" => $lastHistorial->precio_costo_nuevo,
													"producto_id" => $producto->id,
													"proveedor_id" => $proveedor->id,
													"usuario_id" => Auth::user()->id,
													"stock" => $cant,
												];

												if(Auth::user()->bodegas != 'si'){
                                                    $dataHistorial["utilidad_nueva"] = $data["utilidad_" . $aux];
													$dataHistorial["utilidad_anterior"] = $lastHistorial->utilidad_nueva;
                                                }

												if($admin->regimen == "común"){
													$dataHistorial["iva_anterior"] = $lastHistorial->iva_nuevo;
													$dataHistorial["iva_nuevo"] = $data["iva_" . $aux];
												}
											}else{
												$dataHistorial = [
													"precio_costo_nuevo" => $data["precio_costo_" . $aux],
													"precio_costo_anterior" => $data["precio_costo_" . $aux],
													"producto_id" => $producto->id,
													"proveedor_id" => $proveedor->id,
													"usuario_id" => Auth::user()->id,
													"stock" => $cant,
												];

                                                if(Auth::user()->bodegas != 'si'){
                                                    $dataHistorial["utilidad_nueva"] = $data["utilidad_" . $aux];
                                                    $dataHistorial["utilidad_anterior"] = $data["utilidad_" . $aux];
                                                }

												if($admin->regimen == "común"){
													$dataHistorial["iva_anterior"] = $data["iva_".$aux];
													$dataHistorial["iva_nuevo"] = $data["iva_" . $aux];
												}
											}

											if($lastHistorial) {
										        if(Auth::user()->bodegas == 'si')
												    $historial = new ABHistorialCosto();
										        else
												    $historial = new ProductoHistorial();

												$historial->fill($dataHistorial);
												$historial->save();
												if ($aux == $request->input("proveedor_actual")) {
													$producto->precio_costo = $historial->precio_costo_nuevo;
													if(Auth::user()->bodegas != 'si')
													$producto->utilidad = $historial->utilidad_nueva;
													$producto->iva = $historial->iva_nuevo;
													$producto->save();
												}
											}else{
												$historial = new ProductoHistorial();
												$historial->fill($dataHistorial);
												$historial->save();
												if ($aux == $request->input("proveedor_actual")) {
													$producto->precio_costo = $historial->precio_costo_nuevo;
                                                    if(Auth::user()->bodegas != 'si')
													$producto->utilidad = $historial->utilidad_nueva;
													$producto->iva = $historial->iva_nuevo;
													$producto->save();
												}
											}
										}
									} else {
										return response(['Unauthorized' => ['No tiene permisos para relacionar con el proveedor seleccionado']], 422);
									}
								}
							} else {
								return response(['error' => ['La información enviada es incorrecta']], 422);
							}
						}else{
							$continuar = false;
						}
						$aux++;
					}
					if(!$proveedor_actual_ok){
						return response(['error' => ['Debe seleccionar correctamente el proveedor actual del producto']], 422);
					}
					if(!$producto->aparicionesVentasCompras()){
						$producto->promedio_ponderado = 0;
						$producto->stock = 0;
						$producto->updatePromedioPonderado($data_precios);
					}
				}else if($request->get('tipo_producto') == "Compuesto" || $request->get('tipo_producto') == "Preparado"){
					//dd($request->all());
					$rules = [
						"precio_costo"=>"required|numeric",
						"iva"=>"required|numeric",
						//"utilidad"=>"required|numeric",
					];
					if(Auth::user()->bodegas != 'si')
					    $rules['utilidad'] = 'required|numeric';

					$mensajes = [
						"precio_costo.required"=>"El campo precio costo es requerido",
						"precio_costo.numeric"=>"El campo precio costo debe ser numerico",
						"iva.required"=>"El campo iva es requerido",
						"iva.numeric"=>"El campo iva debe ser numerico",
						"utilidad.required"=>"El campo utilidad es requerido",
						"utilidad.numeric"=>"El campo utilidad debe ser numerico",
					];

					$validator = Validator::make($request->all(),$rules,$mensajes);
					if($validator->fails()){
						return response($validator->errors(),422);
					}

					if(Auth::user()->bodegas == 'si')
					    $lastHistorial = ABHistorialCosto::where("producto_id",$producto->id)->orderBy("created_at","DESC")->orderBy("id","DESC")->first();
					else
					    $lastHistorial = ProductoHistorial::where("producto_id",$producto->id)->orderBy("created_at","DESC")->orderBy("id","DESC")->first();

					$dataHistorial = [
						"precio_costo_nuevo" => $data["precio_costo"],
						"precio_costo_anterior" => $lastHistorial->precio_costo_nuevo,
						"iva_anterior" => $lastHistorial->iva_nuevo,
						"producto_id" => $producto->id,
						"usuario_id" => Auth::user()->id
					];

					if(Auth::user()->bodegas != 'si'){
                        $dataHistorial["utilidad_nueva"] = $data["utilidad"];
						$dataHistorial["utilidad_anterior"] = $lastHistorial->utilidad_nueva;
                    }

					if($admin->regimen == "común"){
						$dataHistorial["iva_nuevo"] = $data["iva"];
					}

					if($lastHistorial->precio_costo_nuevo != $dataHistorial["precio_costo_nuevo"]
						|| (Auth::user()->bodegas != 'si' && $lastHistorial->utilidad_nueva != $dataHistorial["utilidad_nueva"])
						||$lastHistorial->iva_nuevo != $dataHistorial["iva_nuevo"]) {

					    if(Auth::user()->bodegas == 'si')
					        $historial = new ABHistorialCosto();
					    else
					        $historial = new ProductoHistorial();

						$historial->fill($dataHistorial);
						$historial->save();
					}

                    while ($continuar){
						if ($aux==1){
							if(!$request->has('select-materia-prima-'.$aux)){
								return response(['materia-prima'=>['Debe relacionar por lo menos una materia prima al producto']], 422);
							}
						}

						if ($request->has('select-materia-prima-'.$aux)){
							$materia = MateriaPrima::find($request->get('select-materia-prima-'.$aux));
							if ($materia && $materia->exists){
							    if(Auth::user()->bodegas == 'si')
								    $count = ABProductoMateriaUnidad::where('materia_prima_id',$materia->id)->where('producto_id',$producto->id)->get()->count();
                                else
							        $count = ProductoMateriaUnidad::where('materia_prima_id',$materia->id)->where('producto_id',$producto->id)->get()->count();

                                if ($count > 0){
									return response(['materia-prima' => ['No es posible relacionar mas de una vez una materia prima y un producto']], 422);
								}else{
									if (MateriaPrima::materiasPrimasPermitidas()->whereIn("id", [$materia->id])->get()->count()) {
										if (!$request->has("cantidad-" . $aux)) {
											return response(['cantidad' => ['Debe ingresar la cantidad de la materia prima ' . $aux]], 422);
										} else {
											if ($materia->stock >= $request->get("cantidad-" . $aux)){
												if(Auth::user()->bodegas == 'si')
	    										    $obj = new ABProductoMateriaUnidad();
												else
    												$obj = new ProductoMateriaUnidad();

												$obj->producto_id = $producto->id;
												$obj->materia_prima_id = $materia->id;
												$obj->cantidad = $request->get("cantidad-" . $aux);
												$obj->save();
												if ($obj){
													//MateriaPrima::updateStockMateriaPrima($materia->id,$producto->id,$request->get("cantidad-" . $aux),'SAVE','');
												}
											}else{
												return response(['cantidad' => ["La existencia de " . "<FONT FACE='arial' SIZE=3><b><u>" . $materia->nombre . "</u></b></FONT> no es suficiente"]], 422);
											}
										}
									} else {
										return response(['Unauthorized' => ['No tiene permisos para utilizar la materia prima']], 422);
									}
								}
							}else {
								return response(['error' => ['La información enviada es incorrecta']], 422);
							}
						}else{
							$continuar = false;
						}
						$aux++;
					}
				}
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

                        $path = $ruta.'/'.$producto->imagen;
                        $image = Image::make($path);
                        $ancho = $image->width();
                        $alto = $image->height();

                        $redimendion = 500;

                        if ($ancho >= $alto) {
                            $relacion = $alto / $ancho;
                            $image->resize($redimendion, intval($redimendion * $relacion));
                        }else {
                            $relacion = $ancho / $alto;
                            $image->resize(intval($redimendion*$relacion), $redimendion);
                        }

                        //$image->save($path_new);
                        $image->save($path);
					}else{
						return response(['error' => ['Ocurrio un error al subir la imagen, intente nuevamente']], 422);
					}
				}
				DB::commit();
				Session::flash("mensaje","El producto se actualizo de manera exitosa");
				return ["success"=>true,"href"=>url('/productos')];
			}
		}
		return response("Unauthorized",401);
	}
	public function postDestroy(Request $request)
	{
		if(Auth::user()->permitirFuncion("Eliminar","productos","inicio") && $request->has("producto")){
			$producto = Producto::permitidos()->where("productos.id",$request->input("producto"))->first();
			if($producto && $producto->permitirEliminar()){
				$producto->delete();
				//Session::flash("mensaje","El producto ha sido eliminado con éxito");
				return ["success"=>true];
			}
		}
		return response(["error"=>["La información enviada es incorrecta."]],422);
	}
	public function postFiltro(Request $request){
		$categoria = null;
		if($request->has("categoria"))$categoria = $request->input("categoria");
		if($request->has('filtro') || $categoria != null){
			$productos = $this->listaFiltro($request->get('filtro'),$categoria);
		}else{
			$productos = Producto::permitidos()->where("estado","Activo")->orderBy('nombre','ASC')->paginate(env('PAGINATE'));
		}
		if($request->has("vista")){
			$productos->setPath(url('/productos'));
			return view($request->input("vista"))->with("productos",$productos);
		}
		$productos->setPath(url('/productos'));
		return view("productos.lista")->with("productos",$productos);
	}

	public function getListFacturaProductos(Request $request){
		// Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = 'productos.nombre';//$columna[$sortColumnIndex]['data'];

        if(Auth::user()->bodegas == 'si')
       	    $productos = ABProducto::permitidos();
        else
            $productos = Producto::permitidos();

        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no'){
            $almacen = Auth::user()->almacenActual();
            if(!$almacen)return response(['error'=>['No se ha encontrado un almacén relacionado con el usuario']],422);
            $productos = $productos->select(
                'productos.id',
                'productos.nombre AS nombre_producto',
                'productos.umbral',
                'almacenes_stock_productos.stock',
                'productos.precio_costo',
                'historial_utilidad.utilidad',
                'productos.iva',
                'productos.tipo_producto',
                'productos.omitir_stock_mp',
                DB::RAW('(productos.precio_costo + ((productos.precio_costo*historial_utilidad.utilidad)/100))* productos.iva AS valor'),
                'vendiendo.unidades.sigla',
                'vendiendo.categorias.nombre AS nombre_categoria',
                'productos.estado')
                ->join("almacenes_stock_productos","productos.id","=" ,"almacenes_stock_productos.producto_id")
                ->join("historial_utilidad","productos.id","=" ,"historial_utilidad.producto_id")
                ->join("vendiendo.unidades","productos.unidad_id","=" ,"vendiendo.unidades.id")
                ->join("vendiendo.categorias","productos.categoria_id","=","vendiendo.categorias.id")
                ->where("productos.estado","Activo")
                ->where("almacenes_stock_productos.almacen_id",$almacen->id)
                //->where("historial_utilidad.almacen_id",$almacen->id)
                ->whereRaw("historial_utilidad.id in (select max(historial_utilidad.id) as ph_id from historial_utilidad where almacen_id='" . $almacen->id . "' group by historial_utilidad.producto_id )");
        }else {
            $productos = $productos->select(
                'productos.id',
                'productos.nombre AS nombre_producto',
                'productos.umbral',
                'productos.stock',
                'productos.precio_costo',
                'productos.utilidad',
                'productos.iva',
                'productos.tipo_producto',
                'productos.omitir_stock_mp',
                DB::RAW('(productos.precio_costo + ((productos.precio_costo*productos.utilidad)/100))* productos.iva AS valor'),
                'vendiendo.unidades.sigla',
                'vendiendo.categorias.nombre AS nombre_categoria',
                'productos.estado')
                ->join("vendiendo.unidades", "productos.unidad_id", "=", "vendiendo.unidades.id")
                ->join("vendiendo.categorias", "productos.categoria_id", "=", "vendiendo.categorias.id")
                ->where("productos.estado", "Activo");
        }

       	$productos = $productos->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $productos->count();
        //BUSCAR
        if($search['value'] != null){
            $productos = $productos->whereRaw(
                " ( LOWER(productos.nombre) LIKE '%".\strtolower($search["value"])."%' OR".
                " (productos.precio_costo + ((productos.precio_costo*productos.utilidad)/100))* productos.iva LIKE '%".$search["value"]."%' OR".
                " productos.stock LIKE '%".$search["value"]."%' OR".
                " productos.umbral LIKE '%".$search["value"]."%' OR".
                " vendiendo.unidades.sigla LIKE '%".$search["value"]."%' OR".
                " LOWER(vendiendo.categorias.nombre) LIKE '%".\strtolower($search["value"])."%')");
        }

        $parcialRegistros = $productos->count();
        $productos = $productos->skip($start)->take($length);
 		$object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($productos->get() as $producto) {
            	 $precioV = $producto->precio_costo + (($producto->precio_costo * $producto->utilidad)/100);
                    $precioV += ($precioV * $producto->iva)/100;
                $stock = $producto->stock;
                if($producto->tipo_producto == 'Compuesto' && $producto->omitir_stock_mp == 'si'){
                    $stock = $producto->DisponibleOmitirStockMp();
                }
            	$myArray[]=(object) array(
            		'id' => $producto->id,
            		'nombre'=>\App\TildeHtml::TildesToHtml($producto->nombre_producto),
            		'valor'=>'$'.number_format($precioV, 0, "," , "." ),
            		'stock'=>$stock,
            		'umbral'=>$producto->umbral,
            		'unidad'=>$producto->sigla,
            		'categoria'=>$producto->nombre_categoria);
            }
        }else{
        	$myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $productos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'fecha_inicio' => $request->get("fecha_inicio"),
            'count' => $parcialRegistros,
            'data' =>$myArray];

        return response()->json($data);

	}

	public function listaFiltro($filtro,$categoria = null){
		$f = "%".$filtro."%";
		$productos = Producto::permitidos()->where(
			function($query) use ($f,$categoria){
				$query->where("nombre","like",$f);
				//if($categoria == null)
					//$query = $query->orWhere("categoria","like",$f);
			}
		);
		if($categoria != null){
			$productos = $productos->where("categoria_id",$categoria);
		}
		return $productos->where("estado","Activo")->orderBy("nombre","ASC")->paginate(env('PAGINATE'));
	}
	public function getDetalle($id,$tipo_producto,Request $request){

	    if(Auth::user()->bodegas == 'si')
		    $producto = ABProducto::permitidos()->where("id",$id)->first();
	    else
		    $producto = Producto::permitidos()->where("id",$id)->first();

		switch ($tipo_producto){
			case 'Terminado':
				$detalles_producto = $producto->proveedoresPrecios();
				break;
			case 'Compuesto':
				$detalles_producto = $producto->MateriasPrimas()->select("materias_primas.*","producto_materia_unidad.cantidad")->get();
				break;
			case 'Preparado':
				$detalles_producto = $producto->MateriasPrimas()->select("materias_primas.*","producto_materia_unidad.cantidad")->get();
				break;
			default:
				$detalles_producto='';
				break;

		}
		if(!empty($detalles_producto)){
		    if(Auth::user()->bodegas == 'si')
			    return view('productos_ab.lista_detalle')->with("detalle_productos",$detalles_producto)->with('tipo_producto',$tipo_producto)->with('producto',$producto);
		    else
			    return view('productos.lista_detalle')->with("detalle_productos",$detalles_producto)->with('tipo_producto',$tipo_producto)->with('producto',$producto);
		}
		return redirect('/');
	}
	public function getContenido($tipo,$accion){
		//dd($tipo);
		if($tipo=='Terminado') {
            if (Auth::user()->bodegas == 'si')
                return view('productos_ab.forms.form_proveedores')->with('accion', $accion);
            else
                return view('productos.forms.form_proveedores')->with('accion', $accion);
        }else if($tipo == "Compuesto" || $tipo == "Preparado" ) {
            if(Auth::user()->bodegas == 'si')
                return view('productos_ab.forms.form_materias_primas')->with('accion', $accion);
            else
                return view('productos.forms.form_materias_primas')->with('accion', $accion);
        }
	}
	public function postSelect(Request $request){
		$id = $request->get('id');
		$html = "<select class='select-materia-prima' id='".$id."' name='".$id."'><option disabled selected>Seleccione una materia prima</option>";

		foreach(MateriaPrima::materiasPrimasPermitidas()->get() as $pr) {
			$html .= "<option data-valor='".$pr->precioActual()."' data-unidad='".$pr->unidad->nombre."' value='$pr->id'>" . $pr->nombre . "</option>";
		}
		$html .= "</select>";
		return $html;
	}
	public function postCount(){
		return MateriaPrima::materiasPrimasPermitidas()->get()->count();
	}

	/*public function postDatosProducto(Request $request){
		if($request->ajax()){
			if($request->has("id")){
				if($request->has("proveedor")){
					$proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();
					$producto = $proveedor->productos()->select("productos.id","productos.nombre","productos_historial.precio_costo_nuevo as precio_costo","productos_historial.iva_nuevo as iva","unidades.sigla as sigla")
						->join("unidades","productos.unidad_id","=","unidades.id")->where("productos.id",$request->input("id"))
						->orderBy("productos_historial.created_at","DESC")->orderBy("productos_historial.id","DESC")->first();
					if($producto)
						return ["success"=>true,"producto"=>$producto];
				}
				$producto = Producto::permitidos()->select("productos.*","unidades.sigla")
					->join("unidades","productos.unidad_id","=","unidades.id")->where("productos.id",$request->input("id"))->first();
				if($producto){
					return ["success"=>true,"producto"=>$producto];
				}
				return ["success"=>false];
			}
		}else{
			return redirect("/");
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}*/

	public function postDatosProducto(Request $request){
		$barCode = $request->input("barCodeProducto");
        $proveedorCompra = $request->has("proveedor");
		if($request->ajax()){

			if($request->has("id") && $request->input('id') != ''){
				if($request->has("proveedor") ){
                    /*compras*/
					$proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();

                    if(Auth::user()->bodegas == 'si'){
                        $producto = $proveedor->productos()->select("productos.barcode", "productos.id","productos.nombre","productos.tipo_producto","productos.omitir_stock_mp","historial_costos.precio_costo_nuevo as precio_costo","historial_costos.iva_nuevo as iva","vendiendo.unidades.sigla as sigla")
                            ->join("vendiendo.unidades","productos.unidad_id","=","vendiendo.unidades.id")->where("productos.id",$request->input("id"))
                            ->orderBy("historial_costos.created_at","DESC")->orderBy("historial_costos.id","DESC")->first();
                    }else{
                        $producto = $proveedor->productos()->select("productos.barcode", "productos.id","productos.nombre","productos.tipo_producto","productos.omitir_stock_mp","productos_historial.precio_costo_nuevo as precio_costo","productos_historial.iva_nuevo as iva","unidades.sigla as sigla")
                            ->join("unidades","productos.unidad_id","=","unidades.id")->where("productos.id",$request->input("id"))
                            ->orderBy("productos_historial.created_at","DESC")->orderBy("productos_historial.id","DESC")->first();
                    }

                    if($producto)
						return ["success"=>true,"producto"=>$producto, "hasProveedor" => $proveedorCompra, "ID" => $request->input('id')];
				}

				if(Auth::user()->bodegas == 'si') {
                    if(Auth::user()->admin_bodegas != 'si') {
                        $almacen = Auth::user()->almacenActual();
                        $producto = ABProducto::permitidos()->select("productos.*", "almacenes_stock_productos.stock as stock", "historial_utilidad.utilidad as utilidad", "vendiendo.unidades.sigla")
                            ->join("vendiendo.unidades", "productos.unidad_id", "=", "vendiendo.unidades.id")
                            ->join("vendiendo_alm.almacenes_stock_productos", "productos.id", "=", "vendiendo_alm.almacenes_stock_productos.producto_id")
                            ->leftJoin("historial_utilidad", "productos.id", "=", "historial_utilidad.producto_id")
                            ->where("almacenes_stock_productos.almacen_id", $almacen->id)
                            ->where("productos.id", $request->input("id"));
                    }else{
                        $producto = ABProducto::permitidos()->select("productos.*",  "historial_utilidad.utilidad as utilidad", "vendiendo.unidades.sigla")
                            ->join("vendiendo.unidades", "productos.unidad_id", "=", "vendiendo.unidades.id")
                            ->leftJoin("historial_utilidad", "productos.id", "=", "historial_utilidad.producto_id")
                            ->where("productos.id", $request->input("id"));
                    }
				    if(Auth::user()->admin_bodegas != 'si') {
                        $almacen = Auth::user()->almacenActual();
                        if (!$almacen) return response(['error' => ['No se ha encontrado un almacén relacionado con el usuario']], 422);
                        $producto = $producto->whereRaw("historial_utilidad.id in (select max(historial_utilidad.id) as ph_id from historial_utilidad where almacen_id='" . $almacen->id . "' group by historial_utilidad.producto_id )");
                    }
                    $producto = $producto->first();
                }else{
                    $producto = Producto::permitidos()->select("productos.*","unidades.sigla")
                        ->join("unidades","productos.unidad_id","=","unidades.id")->where("productos.id",$request->input("id"))->first();

                    if($producto->tipo_producto == 'Compuesto' && $producto->omitir_stock_mp == 'si'){
                        $producto->stock = $producto->DisponibleOmitirStockMp();
                    }
                }

				if($producto){
					return ["success"=>true,"producto"=>$producto, "productos" => "productos"];
				}
				return ["success"=>false, "mensaje" => "Ocurrio un error al seleccionar el producto, por favor intente nuevamente", "request" => $request];
			}elseif ($barCode != '' && (!$request->has("proveedor"))){
                /*factura*/
                if(Auth::user()->bodegas == 'si'){
                    $producto = ABProducto::permitidos()->select("productos.*","vendiendo.unidades.sigla","historial_utilidad.utilidad as utilidad")
                        ->join("vendiendo.unidades","productos.unidad_id","=","vendiendo.unidades.id")
                        ->leftJoin("historial_utilidad", "productos.id", "=", "historial_utilidad.producto_id")
                        ->where("productos.barcode",$barCode);
                    if(Auth::user()->admin_bodegas == 'no'){
                        $almacen = Auth::user()->almacenActual();
                        $producto = $producto->where('historial_utilidad.almacen_id',$almacen->id);
                    }
                    $producto = $producto->first();
                }else {
                    $producto = Producto::permitidos()->select("productos.*", "unidades.sigla")
                        ->join("unidades", "productos.unidad_id", "=", "unidades.id")->where("productos.barcode", $barCode)->first();
                }
				if($producto){
					return ["success"=>true,"producto"=>$producto, "barcode" => "barcode", "proveedor" => $proveedorCompra];
				}else{
					return ["success"=>false, "mensaje" => "No se encontró ningún producto con el código barras ingresado.<br><br>Por favor Intenta de nuevo con otro código"];
				}
			}elseif ($barCode != '' && $request->has("proveedor")){
                /*Compras*/
                $proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();

                if(Auth::user()->bodegas == 'si'){
                    $producto = $proveedor->productos()->select("productos.barcode", "productos.id","productos.nombre","historial_costos.precio_costo_nuevo as precio_costo","historial_costos.iva_nuevo as iva","vendiendo.unidades.sigla as sigla","historial_utilidad.utilidad as utilidad")
                        ->join("vendiendo.unidades","productos.unidad_id","=","vendiendo.unidades.id")
                        ->leftJoin("historial_utilidad", "productos.id", "=", "historial_utilidad.producto_id")
                        ->where("productos.barcode",$barCode)
                        ->orderBy("historial_costos.created_at","DESC")->orderBy("historial_costos.id","DESC")->first();
                }else{
                    $producto = $proveedor->productos()->select("productos.barcode", "productos.id","productos.nombre","productos_historial.precio_costo_nuevo as precio_costo","productos_historial.iva_nuevo as iva","unidades.sigla as sigla")
                        ->join("unidades","productos.unidad_id","=","unidades.id")->where("productos.barcode",$barCode)
                        ->orderBy("productos_historial.created_at","DESC")->orderBy("productos_historial.id","DESC")->first();
                }

                if($producto) {
                    return ["success" => true, "producto" => $producto, "hasProveedor_BarCode" => $proveedorCompra];
                }else{
                    $producto = $proveedor->productosOtrosProveedores()->select("productos.*")
                        ->where("productos.barcode", $barCode)->first();
                    if($producto)
                        return ["success" => false, "mensaje" => "Debes relacionar este producto con el proveedor actual", "producto" => $producto, "otro_Proveedor_BarCode" => $proveedorCompra];
                    return ["success" => false, "mensaje" => "Debes crear este producto"];
                }
            }
		}else{
			return redirect("/");
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	function postRelacionProveedor(Requests\RequestRelacionProductoProveedor $request){
	    if(Auth::user()->bodegas == 'si')
		    $producto = ABProducto::permitidos()->where("id",$request->input("id"))->first();
		else
	        $producto = Producto::permitidos()->where("id",$request->input("id"))->first();

		$proveedor = Proveedor::permitidos()->where("id",$request->input("proveedor"))->first();
		if($producto->exists && $proveedor->exists) {
			if(Auth::user()->bodegas == 'si')
		        $productoHistorial = new ABHistorialCosto();
		    else
			    $productoHistorial = new ProductoHistorial();

			$productoHistorial->precio_costo_anterior = $request->input("precio_costo_proveedor");
			$productoHistorial->precio_costo_nuevo = $request->input("precio_costo_proveedor");
			$productoHistorial->iva_anterior = $request->input("iva_proveedor");
			$productoHistorial->iva_nuevo = $request->input("iva_proveedor");
            if(Auth::user()->bodegas == 'no') {
                $productoHistorial->utilidad_anterior = $request->input("utilidad_proveedor");
                $productoHistorial->utilidad_nueva = $request->input("utilidad_proveedor");
            }
			$productoHistorial->producto_id = $producto->id;
			$productoHistorial->proveedor_id = $proveedor->id;
			$productoHistorial->usuario_id = Auth::user()->id;
			$productoHistorial->save();
			return ["success" => true,"producto_id" => $producto->id];
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function getRevisionInventario(Request $request){
		$filtro ="";
		if($request->has('filtro')){
			$productos = $this->listaFiltroApp($request->get('filtro'));
			$filtro = $request->get('filtro');
		}else{
			$productos = ProductoApp::permitidos()->where(
				function($q){
					$q->where("estado","pendiente")
						->orWhere("estado","editado");
				}
			)->orderBy('nombre','ASC')->paginate(env("PAGINATE"));
			//dd($productos);
		}
		return view('productos.lista_revision_inventario')->with('productos',$productos)->with('filtro',$filtro);
	}

	public function getListRevisionInventario(Request $request){
			$search = $request->get("search");
	        $order = $request->get("order");
	        $sortColumnIndex = $order[0]['column'];
	        $sortColumnDir = $order[0]['dir'];
	        $length = $request->get('length');
	        $start = $request->get('start');
	        $columna = $request->get('columns');
	        $orderBy = $columna[$sortColumnIndex]['data'];
	        // if($orderBy == null){$orderBy= 'nombre';}
	    	$myArray=[];

			$productos = ProductoApp::permitidos()->where(
						function($q){
							$q->where("productos_app.estado","pendiente")->orWhere("productos_app.estado","editado");
						 })->orderBy('productos_app.nombre','ASC')
			->join('unidades','productos_app.unidad_id','=','unidades.id')
			->join('categorias','productos_app.categoria_id','=','categorias.id')
			->select('productos_app.id',
					'productos_app.barcode',
					'productos_app.nombre' ,
					'productos_app.stock',
					'productos_app.medida_venta',
					'unidades.nombre AS unidad',
					'categorias.nombre AS categoria',
					'productos_app.estado');



			$totalRegistros = $productos->count();
		 	if($search['value'] != null){
	            $productos = $productos->whereRaw(
	                " ( productos_app.barcode LIKE '%".$search["value"]."%' OR".
	                " LOWER(productos_app.nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " productos_app.stock LIKE '%".$search["value"]."%' OR".
	                " LOWER(unidades.nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " LOWER(categorias.nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " productos_app.medida_venta LIKE '%".$search["value"]."%' ".
	                ")");
	        }

	        $parcialRegistros = $productos->count();
	        $productos = $productos->skip($start)->take($length);
		    $object = new \stdClass();
		        if($parcialRegistros > 0){
		            foreach ($productos->get() as $p){
			            	$myArray[]=(object) array(
		            		'id' => $p->id,
		            		'barcode' => $p->barcode,
		            		'nombre' => $p->nombre,
		            		'stock' => $p->stock,
		            		'medida_venta' => $p->medida_venta,
		            		'unidad' => $p->unidad,
		            		'categoria' => $p->categoria,
		            		'estado' => $p->estado,
		            		'url_estado_pendiente' => url("/productos/revision-producto-inventario/".$p->id)
	            		);
		            }
		        }

	        $data = ['length'=> $length,
	            'start' => $start,
	            'buscar' => $search['value'],
	            'draw' => $request->get('draw'),
	           // 'last_query' => $productos->toSql(),
                'recordsTotal' =>$totalRegistros,
                'recordsFiltered' =>$parcialRegistros,
	            'data' =>$myArray,
	            'info' =>$productos->get()];

	        return response()->json($data);
	}

	public function getRevisionProductoInventario($id){
		$producto = ProductoApp::permitidos()->where("id",$id)->first();
		if($producto) {
			return view('productos.revision_producto_inventario')->with('producto', $producto);
		}
		return redirect("/");
	}

	public function postStoreRevisionProductoInventario(RequestProducto $request){
		if(!$request->has("producto_app"))return response(["Error"=>["La información enviada es incorrecta"]],422);
		$data=$request->all();
		if($request->has('select-unidad'))
			$data['unidad_id']=$request->get('select-unidad');
		if($request->has('select-categoria'))
			$data['categoria_id']=$request->get('select-categoria');

		$admin = User::find(Auth::user()->userAdminId());

		$proveedor_actual = null;
		if($request->has('proveedor_actual')){
			$proveedor_actual = $data['proveedor_actual'];
		}
		unset($data['proveedor_actual']);

		if(!$request->has("tipo_producto"))$data["tipo_producto"] = "Terminado";

		DB::beginTransaction();
		$producto = new Producto();
		$producto->fill($data);
		$producto->usuario_id = Auth::user()->userAdminId();
		$producto->usuario_id_creator = Auth::user()->id;
		$producto->imagen ="";
		$producto->estado = "Activo";
		$producto->save();

		$productoApp = ProductoApp::permitidos()->where("id",$request->input("producto_app"))->first();
		if(!$productoApp)return response(["Error"=>["La información enviada es incorrecta"]],422);
		$productoApp->estado = "procesado";
		$productoApp->save();

		if($proveedor_actual != null)
			$data['proveedor_actual'] = $proveedor_actual;

		$aux = 1;
		$continuar = true;

		$proveedor_actual = 0;
		if(!$request->has("proveedor_actual")){
			return response(['proveedor'=>['Seleccione el proveedor actual del producto']], 422);
		}

		$proveedor_actual_ok = false;
		while ($continuar){

			if($aux == 1){
				if(!$request->has("select-proveedor-".$aux)){
					return response(['proveedor'=>['Debe relacionar por lo menos un proveedor']], 422);
				}
			}
			//dd($request->all());

			if($request->has("select-proveedor-".$aux)) {
				$proveedor = Proveedor::find($request->get("select-proveedor-" . $aux));
				if ($proveedor && $proveedor->exists) {
					if($aux == $request->input("proveedor_actual")){
						$producto->proveedor_actual = $proveedor->id;
						$proveedor_actual_ok = true;
						$producto->save();
					}
					if(ProductoHistorial::where("proveedor_id",$proveedor->id)->where("producto_id",$producto->id)->get()->count()){
						return response(['proveedor' => ['No es posible relacionar más de una vez un proveedor y un producto']], 422);
					}else {
						if (Proveedor::permitidos()->whereIn("id", [$proveedor->id])->get()->count()) {
							$errores = [];
							if (!$data["precio_costo_" . $aux] || $data["precio_costo_" . $aux] == "" || !is_numeric($data["precio_costo_" . $aux])) {
								$errores['precio_costo'] = ['El campo precio costo es requerido en el proveedor ' . $aux];
							}
							$admin = User::find(Auth::user()->userAdminId());
							if($admin->regimen == "común") {
								if (!$data["iva_" . $aux] || $data["iva_" . $aux] == "" || !is_numeric($data["iva_" . $aux])) {
									$errores['iva'] = ['El campo iva es requerido en el proveedor ' . $aux];
								}
							}
							if (!$data["utilidad_" . $aux] || $data["utilidad_" . $aux] == "" || !is_numeric($data["utilidad_" . $aux])) {
								$errores['utilidad'] = ['El campo utilidad es requerido en el proveedor ' . $aux];
							}

							if(count($errores)){
								return response($errores,422);
							}else {
								$dataHistorial = [
									"precio_costo_nuevo" => $data["precio_costo_".$aux],
									"precio_costo_anterior" =>  $data["precio_costo_".$aux],
									"utilidad_nueva" =>  $data["utilidad_".$aux],
									"utilidad_anterior" => $data["utilidad_".$aux],
									"producto_id" => $producto->id,
									"proveedor_id" => $proveedor->id,
									"usuario_id" => Auth::user()->id
								];

								if($admin->regimen == "común"){
									$dataHistorial["iva_anterior"] = $data["iva_".$aux];
									$dataHistorial{"iva_nuevo"} = $data["iva_".$aux];
								}

								$historial = new ProductoHistorial();
								$historial->fill($dataHistorial);
								$historial->save();

								if($aux == $request->input("proveedor_actual")) {
									$producto->precio_costo = $historial->precio_costo_nuevo;
									$producto->utilidad = $historial->utilidad_nueva;
									$producto->iva = $historial->iva_nuevo;
									$producto->save();
								}
							}
						} else {
							return response(['Unauthorized' => ['No tiene permisos para relacionar con el proveedor seleccionado']], 422);
						}
					}
				} else {
					return response(['error' => ['La información enviada es incorrecta']], 422);
				}
			}else{
				$continuar = false;
			}
			$aux++;
		}

		if(!$proveedor_actual_ok){
			return response(['error' => ['Debe seleccionar correctamente el proveedor actual del producto']], 422);
		}

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
		DB::commit();
		$data = ["success" => true];
		Session::flash("mensaje", "El producto ha sido revisado y procesado con exito");
		return $data;
	}

	public function listaFiltroApp($filtro){
		$f = "%".$filtro."%";
		$productos = ProductoApp::permitidos()->where(
			function($query) use ($f){
				$query->where("nombre","like",$f);
				//if($categoria == null)
				//$query = $query->orWhere("categoria","like",$f);
			}
		)->where(
			function($q){
				$q->where("estado","pendiente")
					->orWhere("estado","editado");
			}
		);
		return $productos->orderBy("nombre","ASC")->paginate(env("PAGINATE"));
	}

	public function postAprobarEdicion(Request $request){
		if($request->has("producto")){
			$pr = ProductoApp::permitidos()->where("id",$request->input("producto"))->first();
			if($pr && $pr->estado == "editado" && $pr->producto_id != ""){
				$producto = Producto::permitidos()->where("id",$pr->producto_id)->first();
				if($producto){
					$pr->estado = "procesado";

					$producto->nombre = $pr->nombre;
					$producto->stock = $pr->stock;
					$producto->descripcion = $pr->descripcion;
					$producto->barcode = $pr->barcode;
					$producto->unidad_id = $pr->unidad_id;
					$producto->categoria_id = $pr->categoria_id;
					$producto->medida_venta = $pr->medida_venta;
					$producto->save();
					$pr->save();
					Session::flash("mensaje","El producto ha sido aprobado y editado con éxito");
					return ["success"=>true];
				}
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function postEliminarProductoInventario(Request $request){
		if($request->has("producto")){
			$pr = ProductoApp::permitidos()->where("id",$request->input("producto"))->first();
			if($pr){
                $pr->delete();
                Session::flash("mensaje","El producto ha sido eliminado con éxito");
                return ["success"=>true];

			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function postCambiarStock(Request $request){
		if($request->has("tarea") && $request->has("cantidad") && $request->has("producto")){
			if($request->input("cantidad") <= 0){return response(["Error"=>["Ingrese únicamente números positivos"]],422);}
            if(Auth::user()->bodegas == 'si')
			    $producto = ABProducto::permitidos()->where("id",$request->input("producto"))->first();
			else
			    $producto = Producto::permitidos()->where("id",$request->input("producto"))->first();
			if($producto && $producto->tipo_producto == "Compuesto"){
				if($request->input("tarea") == "agregar") {
					$materias = $producto->MateriasPrimas()->select("producto_materia_unidad.cantidad as cantidad_materia", "materias_primas.*")->get();
					DB::beginTransaction();
					foreach ($materias as $m) {
						if ($m->stock >= ($m->cantidad_materia * $request->input("cantidad"))) {
							$m->stock = $m->stock - ($m->cantidad_materia * $request->input("cantidad"));
							$m->save();
						} else {
							return response(["Error" => ["No es posible agregar " . $request->input("cantidad") . " " . $producto->nombre . ", la cantidad de " . $m->nombre . " no es suficiente para crear la cantidad deseada"]],422);
						}
					}
					$producto->stock = $producto->stock + $request->input("cantidad");
					$producto->save();

					//si el producto se encuentra en bodega
                    if(Auth::user()->bodegas == 'si'){
                        $registro = ABBodegaStockProducto::where('producto_id',$producto->id)->first();
                        if($registro){
                            $registro->stock += $request->input("cantidad");
                            $registro->save();
                        }
                    }
					DB::commit();
					Session::flash("mensaje","La edición de stock se ha realizado con éxito");
					return ["success" => true];
				}else if($request->input("tarea") == "quitar"){
					if($producto->stock >= $request->input("cantidad")){
						$producto->stock = $producto->stock - $request->input("cantidad");
						$producto->save();

                        //si el producto se encuentra en bodega
                        if(Auth::user()->bodegas == 'si'){
                            $registro = ABBodegaStockProducto::where('producto_id',$producto->id)->first();
                            if($registro){
                                $registro->stock -= $request->input("cantidad");
                                $registro->save();
                            }
                        }
						Session::flash("mensaje","La edición de stock se ha realizado con éxito");
						return ["success" => true];
					}else{
						return response(["Error"=>["No es posible quitar mas de ".$producto->stock." ".$producto->nombre]],422);
					}
				}
			}
			return response(["Error"=>["La información enviada es incorrecta"]],422);
		}
		return response(["Error"=>["Todos los campos son requeridos"]],422);
	}

	public function getImportacion(){
		if(Auth::user()->plan()->importacion_productos == "si" && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador())) {
			//$importaciones = ImportacionProducto::permitidos()->where("estado","pendiente")->get();
			return view("importacion_productos.index");//->with("importaciones",$importaciones);
		}
		return redirect("/");
	}

	public function getListImportacionProductos(Request $request){
		$search = $request->get("search");
	        $order = $request->get("order");
	        $sortColumnIndex = $order[0]['column'];
	        $sortColumnDir = $order[0]['dir'];
	        $length = $request->get('length');
	        $start = $request->get('start');
	        $columna = $request->get('columns');
	        $orderBy = $columna[$sortColumnIndex]['data'];
	        // if($orderBy == null){$orderBy= 'nombre';}
	    	$myArray=[];
			$productos = ImportacionProducto::permitidos()->where("estado","pendiente")->orderBy($orderBy, $sortColumnDir);
			$totalRegistros = $productos->count();
		 	if($search['value'] != null){
	            $productos = $productos->whereRaw(
	                " ( barcode LIKE '%".$search["value"]."%' OR".
	                " LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " stock LIKE '%".$search["value"]."%' OR".
	                " umbral LIKE '%".$search["value"]."%' ".
	                ")");
	        }

	        $parcialRegistros = $productos->count();
	        $productos = $productos->skip($start)->take($length);
	        $data = ['length'=> $length,
	            'start' => $start,
	            'buscar' => $search['value'],
	            'draw' => $request->get('draw'),
	            //'last_query' => $productos->toSql(),
                'recordsTotal' =>$totalRegistros,
                'recordsFiltered' =>$parcialRegistros,
	            'data' =>$productos->get()];

	        return response()->json($data);
	}

	public function postStoreImportacion(Request $request){
		if(Auth::user()->plan()->importacion_productos == "si" && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador())) {
			$admin = User::find(Auth::user()->userAdminId());
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
								if(!(isset($d["nombre"]) &&
									isset($d["descripcion"])&&
									//isset($d["barcode"])&&
									isset($d["precio_costo"])&&
									isset($d["iva"])&&
                                    ((Auth::user()->bodegas != 'si' && isset($d["precio_venta"])) || Auth::user()->bodegas == 'si')&&
									isset($d["stock"])&&
									isset($d["umbral"])&&
									isset($d["medida_venta"]))){
									break;
								}

									$validator = Validator::make($d,RequestProducto::getRulesImportacion(),RequestProducto::getMessagesImportacion());
									if($validator->fails()){
										//dd($validator->errors()->all());
										return response($validator->errors()->all()+["Error"=>"Error en fila #".$i],422);
									}else{
                                        $barCodeImp = false;

                                        if($d["barcode"] != "")
                                            $barCodeImp = Producto::permitidos()->where("barcode",$d["barcode"])->first();

                                        if($barCodeImp)
											return response(["Error"=>["Ya existe un producto con el código de barras '".$d["barcode"]."'"],"linea"=>["Error en la linea #".$i]],422);

										if($admin->regimen != "común"){
											$d["iva"] = 0;
										}

										if(Auth::user()->bodegas != 'si' && isset($d["precio_venta"])) {
                                            $d["utilidad"] = (string) floatval(((($d["precio_venta"] / ((100 + $d["iva"]) / 100)) / $d["precio_costo"]) - 1) * 100);
                                        }
										$importacion = new ImportacionProducto();
										$importacion->fill($d);
										$importacion->usuario_id = Auth::user()->userAdminId();
										$importacion->usuario_creador_id = Auth::user()->id;
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
		}else{
			return response(["Error" => "Usted no tiene permisos para realizar esta acción"], 401);
		}
	}

	public function postInfoImportacion(Request $request){
		if($request->input("id")){
			$importacion = ImportacionProducto::permitidos()->where("estado","pendiente")->where("id",$request->input("id"))->first();
			if($importacion){
				return view("importacion_productos.info_importacion")->with("importacion",$importacion);
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function postProcesarImportacion(Request $request){
		if(Auth::user()->plan()->importacion_productos != "si" && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador())){
			return response(["Error"=>["Usted no tiene permisos para realizar esta acción"]],401);
		}

		if(Auth::user()->bodegas == 'si'){
		    if(count(Bodega::permitidos()->get()) <= 0)
                return response(["Error"=>["Para procesar las importaciones es necesario registrar por lo menos una bodega"]],422);
        }

		if($request->has("id") && $request->has("categoria") && $request->has("unidad") && $request->has("proveedor")){
			if(Categoria::permitidos()->where("id",$request->input("categoria"))->first() && Unidad::unidadesPermitidas()->where("id",$request->input("unidad"))->first() && Proveedor::permitidos()->where("id",$request->input("proveedor"))->first()) {
				$importacion = ImportacionProducto::find($request->input("id"));
				if ($importacion) {
					$data = $importacion->toArray();
                    $prodBarcode = false;

                    if($data["barcode"] != "") {
                        if (Auth::user()->bodegas == 'si')
                            $prodBarcode = ABProducto::permitidos()->where("barcode", $data["barcode"])->first();
                        else
                            $prodBarcode = Producto::permitidos()->where("barcode", $data["barcode"])->first();
                    }

					if($prodBarcode){
						return response(["Error"=>["Ya se ha registrado un producto con el código de barras '".$data["barcode"]."'"]],422);
					}

					$data["categoria_id"] = $request->input("categoria");
					$data["unidad_id"] = $request->input("unidad");
					$data["proveedor_actual"] = $request->input("proveedor");
					$data["usuario_id"] = Auth::user()->userAdminId();
					$data["usuario_id_creator"] = Auth::user()->id;
					$data["estado"] = "Activo";
					$data["tipo_producto"] = "Terminado";

					if(Auth::user()->bodegas == 'si')
					    $producto = new ABProducto();
					else
					    $producto = new Producto();

                    if(!$data['barcode'])$data['barcode'] = "";

					$producto->fill($data);
					$producto->promedio_ponderado = $data["precio_costo"];
					$producto->save();

					$dataHistorial = [
						"precio_costo_nuevo" => $data["precio_costo"],
						"precio_costo_anterior" => $data["precio_costo"],
						"producto_id" => $producto->id,
						"stock" => $producto->stock,
						"proveedor_id" => $request->input("proveedor"),
						"usuario_id" => Auth::user()->id
					];

					if(Auth::user()->bodegas != 'si') {
                        $dataHistorial["utilidad_nueva"] = $data["utilidad"];
                        $dataHistorial["utilidad_anterior"] = $data["utilidad"];
                    }

					$admin = User::find(Auth::user()->userAdminId());

					if ($admin->regimen == "común") {
						$dataHistorial["iva_anterior"] = $data["iva"];
						$dataHistorial{"iva_nuevo"} = $data["iva"];
					}

					if(Auth::user()->bodegas == 'si')
					    $historial = new ABHistorialCosto();
					else
					    $historial = new ProductoHistorial();

					$historial->fill($dataHistorial);
					$historial->save();

					$importacion->estado = "procesado";
					$importacion->save();

					//se almacena el stock en bodega
					if(Auth::user()->bodegas == 'si'){
                        $registro = new ABBodegaStockProducto();
                        $registro->stock = $producto->stock;
                        $registro->producto_id  = $producto->id;
                        $registro->bodega_id = Bodega::permitidos()->first()->id;
                        $registro->save();
                    }

					return ["success" => true];
				}
			}
		}
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function postProcesarImportacionTodo(Request $request){
		if(Auth::user()->plan()->importacion_productos != "si" && (Auth::user()->plan()->n_productos == 0 || Auth::user()->plan()->n_productos > Auth::user()->countProductosAdministrador())){
			return response(["Error"=>["Usted no tiene permisos para realizar esta acción"]],401);
		}

		if($request->has("categoria") && $request->has("unidad") && $request->has("proveedor")){
			if(Categoria::permitidos()->where("id",$request->input("categoria"))->first() && Unidad::unidadesPermitidas()->where("id",$request->input("unidad"))->first() && Proveedor::permitidos()->where("id",$request->input("proveedor"))->first()) {
				$importaciones = ImportacionProducto::permitidos()->where('estado','pendiente')->get();
                DB::beginTransaction();
                $bodega = Bodega::permitidos()->first();
                foreach($importaciones as $importacion) {
                    if ($importacion) {
                        $data = $importacion->toArray();
                        $prodBarcode = false;

                        if($data["barcode"] != "") {
                            if (Auth::user()->bodegas == 'si')
                                $prodBarcode = ABProducto::permitidos()->where("barcode", $data["barcode"])->first();
                            else
                                $prodBarcode = Producto::permitidos()->where("barcode", $data["barcode"])->first();
                        }

                        if ($prodBarcode) {
                            return response(["Error" => ["Ya se ha registrado un producto con el código de barras '" . $data["barcode"] . "'"]], 422);
                        }


                        $data["categoria_id"] = $request->input("categoria");
                        $data["unidad_id"] = $request->input("unidad");
                        $data["proveedor_actual"] = $request->input("proveedor");
                        $data["usuario_id"] = Auth::user()->userAdminId();
                        $data["usuario_id_creator"] = Auth::user()->id;
                        $data["estado"] = "Activo";
                        $data["tipo_producto"] = "Terminado";

                        if(Auth::user()->bodegas == 'si')
                            $producto = new ABProducto();
                        else
                            $producto = new Producto();

                        if(!$data['barcode'])$data['barcode'] = "";

                        $producto->fill($data);
                        $producto->promedio_ponderado = $data["precio_costo"];
                        $producto->save();

                        $dataHistorial = [
                            "precio_costo_nuevo" => $data["precio_costo"],
                            "precio_costo_anterior" => $data["precio_costo"],
                            "producto_id" => $producto->id,
                            "stock" => $producto->stock,
                            "proveedor_id" => $request->input("proveedor"),
                            "usuario_id" => Auth::user()->id
                        ];

                        if(Auth::user()->bodegas != 'si') {
                            $dataHistorial["utilidad_nueva"] = $data["utilidad"];
                            $dataHistorial["utilidad_anterior"] = $data["utilidad"];
                        }

                        $admin = User::find(Auth::user()->userAdminId());

                        if ($admin->regimen == "común") {
                            $dataHistorial["iva_anterior"] = $data["iva"];
                            $dataHistorial{"iva_nuevo"} = $data["iva"];
                        }

                        if(Auth::user()->bodegas == 'si')
                            $historial = new ABHistorialCosto();
                        else
                            $historial = new ProductoHistorial();

                        $historial->fill($dataHistorial);
                        $historial->save();

                        $importacion->estado = "procesado";
                        $importacion->save();

                        //se almacena el stock en bodega
                        if(Auth::user()->bodegas == 'si'){
                            $registro = new ABBodegaStockProducto();
                            $registro->stock = $producto->stock;
                            $registro->producto_id  = $producto->id;
                            $registro->bodega_id = $bodega->id;
                            $registro->save();
                        }

                    }

                }

                DB::commit();
                Session::flash("mensaje","Se han procesado todas las importaciones con éxito");
                return ["success" => true];
            }
		}else{
            return response(['error'=>['Para procesar todas las importaciones debe seleccionar un valor de categoria, unidad y proveedor']],422);
        }
		return response(["Error"=>["La información enviada es incorrecta"]],422);
	}

	public function postRechazarImportacion(Request $request){
		if(Auth::user()->plan()->importacion_productos != "si"){
			return response(["Error"=>["Usted no tiene permisos para realizar esta acción"]],401);
		}

		if($request->has("id")){
			$importacion = ImportacionProducto::permitidos()->where("id",$request->input("id"))->first();
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
	    if(Auth::user()->bodegas == 'si')
		    $path = storage_path() . '/app/sistema/formatos/ABPlantillaProductos.xlsx';
	    else
		    $path = storage_path() . '/app/sistema/formatos/PlantillaProductos.xlsx';

		if(!File::exists($path)) abort(404);

		$file = File::get($path);
		$type = File::mimeType($path);

		$response = response($file, 200);
		$response->header("Content-Type", $type);

		return $response;
	}

	public function getBusquedaProveedor(Request $request){
		if($request->has("filtro")){
			$filtro = $request->input("filtro");
		}else{
			$filtro = "";
		}

		$take = 18;
		$fullView = true;//si se debe cargar la vista completa o solo los productos
		if($request->has("skip")){
			$skip = $request->input("skip");
			$fullView = false;
		}else{
			$skip = -1;
		}

		$productos = Producto::busquedaPorProveedores($filtro,$skip,$take);
		$promocionHoy = PromocionProveedor::promocionHoy(1);
		if ($skip == -1) $skip++;
		$skip += count($productos);
		if($fullView) {
			return view("productos.busqueda_proveedor")->with("productos", $productos)->with("filtro", $filtro)->with("skip", $skip)->with("promocionHoy",$promocionHoy);
		}else{
			$view = view("productos.lista_busqueda_proveedor")->with("productos", $productos);
			return ["view"=>$view->render(),"skip"=>$skip,"results"=>count($productos)];
		}
	}

	public function postInfoProductoProveedor(Request $request){
		if(!Auth::user()->permitirFuncion("Pedido","Búsqueda en proveedor","inicio")) {
			return response(["error"=>["Usted no tiene permisos para realizar esta función"]],401);
		}
		if($request->has("producto") && $request->has("cantidad")){
			$categoria = User::find((Auth::user()->userAdminid()))->categoria;
			$producto = Producto::select("productos.*","usuarios.nombres","usuarios.apellidos")
				->join("usuarios","productos.usuario_id","=","usuarios.id")
				->where("proveedor","si")->where("productos.categoria_id",$categoria->id)->where("productos.id",$request->input("producto"))->first();
			if($producto){
				$producto->cantidad = $request->input("cantidad");
				if($producto->tienePromocionFecha(date("Y-m-d"))){
					$producto->promocion = true;
					$producto->promocion_id = $producto->promocionHoy()->id;
					$producto->valor_con_descuento = $producto->promocionHoy()->valor_con_descuento;
				}
			};
			return ["success"=>true,"producto"=>$producto];
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function postStorePedidoProveedor(Request $request){
        if(!Auth::user()->permitirFuncion("Pedido","Búsqueda en proveedor","inicio")) {
            return response(["error"=>["Usted no tiene permisos para realizar esta función"]],401);
        }
        if($request->has("productos") && is_array($request->input("productos"))) {
            $proveedores = [];
            foreach($request->input("productos") as $producto){
                if(!in_array($producto["usuario_id"],$proveedores)){

					$proveedor = User::join("perfiles","usuarios.perfil_id","=","perfiles.id")
						->where("usuarios.id",$producto["usuario_id"])
						->where("perfiles.nombre","proveedor")->first();
					if(!$proveedor)return response(["error"=>["La información enviada es incorrecta"]],422);

					$prod = Producto::where("proveedor","si")
						->where("id",$producto["id"])->first();
					if(!$prod)return response(["error"=>["La información enviada es incorrecta"]],422);

                    $proveedores[] = $producto["usuario_id"];
                }
            }
			DB::beginTransaction();
            foreach($proveedores as $proveedor_id) {
                $aux = 0;
				$total = 0;
                foreach ($request->input("productos") as $producto) {
                    if($producto["usuario_id"] == $proveedor_id) {
                        $aux++;
                        if($aux == 1) {
                            $pedido = new PedidoProveedor();
                            $pedido->proveedor_id = $proveedor_id;
                            $pedido->generarConsecutivo();
                            $pedido->valor_total = 0;
                            $pedido->administrador_id = Auth::user()->userAdminId();
							$pedido->save();
                        }

						if(isset($producto["promocion"])) {
							$promocion = PromocionProveedor::find($producto["promocion_id"]);
							if($promocion) {
								if(isset($producto["cantidad"]) && $producto["cantidad"] > 0) {
									$total += ($promocion->valor_con_descuento * $producto["cantidad"]);
									$pedido->productos()->save(Producto::find($producto["id"]), ["promocion_proveedor_id" => $producto["promocion_id"], "cantidad" => $producto["cantidad"], "valor_actual" => $promocion->valor_con_descuento]);
								}else{
									return response(["error"=>["La información enviada es incorrecta"]],422);
								}
							}else{
								return response(["error"=>["La información enviada es incorrecta"]],422);
							}
						}else{
							if(isset($producto["cantidad"]) && $producto["cantidad"] > 0) {
								$pedido->productos()->save(Producto::find($producto["id"]), ["cantidad" => $producto["cantidad"], "valor_actual" => $producto["precio_costo"]]);
								$total += ($producto["precio_costo"] * $producto["cantidad"]);
							}else{
								return response(["error"=>["La información enviada es incorrecta"]],422);
							}
						}

                    }
                }
				if($pedido){
					$pedido->valor_total = $total;
					$pedido->save();
				}

				$proveedor = User::join("perfiles","usuarios.perfil_id","=","perfiles.id")
					->where("usuarios.id",$proveedor_id)
					->where("perfiles.nombre","proveedor")->first();
				$data = [
					"usuario"=>User::find(Auth::user()->userAdminId()),
					"pedido"=>$pedido
				];

				Mail::send('emails.nuevo_pedido_proveedor', $data, function ($m) use ($proveedor) {
					$m->from('notificaciones@vendiendo.co', 'Vendiendo.co');

					$m->to($proveedor->email, $proveedor->nombres." ".$proveedor->apellidos)->subject('Vendiendo.co - Nuevo pedido');
				});
            }
			DB::commit();
			return ["success"=>true];
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	public function getMisPedidos(){
		if(Auth::user()->permitirFuncion("Pedido","Búsqueda en proveedor","inicio")) {
			//$pedidos = PedidoProveedor::where("administrador_id",Auth::user()->userAdminId())->orderBy("created_at","DESC")->paginate(env("PAGINATE"));
			return view("productos.mis_pedidos");//->with("pedidos",$pedidos);
		}

		return redirect("/");
	}

	public function getListMisPedidos(Request $request){
		$search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];
        // if($orderBy == null){$orderBy= 'nombre';}
    	$myArray=[];

		$pedidos = PedidoProveedor::select(
			'pedidos_proveedor.id',
			'pedidos_proveedor.consecutivo',
			DB::RAW(" CONCAT(usuarios.nombres,' ',usuarios.apellidos) AS n_proveedor"),
			'pedidos_proveedor.valor_total',
			'pedidos_proveedor.created_at',
			'pedidos_proveedor.estado')
			->where("pedidos_proveedor.administrador_id",Auth::user()->userAdminId())
			->join("usuarios","pedidos_proveedor.proveedor_id","=","usuarios.id")
			->orderBy("pedidos_proveedor.created_at","DESC");



		$totalRegistros = $pedidos->count();
	 	if($search['value'] != null){
            $pedidos = $pedidos->whereRaw(
                " ( pedidos_proveedor.consecutivo LIKE '%".$search["value"]."%' OR".
                " LOWER(CONCAT(usuarios.nombres,' ',usuarios.apellidos)) LIKE '%".\strtolower($search["value"])."%' OR".
                " pedidos_proveedor.valor_total LIKE '%".$search["value"]."%' OR".
                " LOWER(pedidos_proveedor.created_at) LIKE '%".\strtolower($search["value"])."%' OR".
                " LOWER(pedidos_proveedor.estado) LIKE '%".\strtolower($search["value"])."%'  ".
                ")");
        }

        $parcialRegistros = $pedidos->count();
        $pedidos = $pedidos->skip($start)->take($length);
	    $object = new \stdClass();
	        if($parcialRegistros > 0){
	            foreach ($pedidos->get() as $pedido){
		            	$myArray[]=(object) array(
	            		'id' => $pedido->id,
	            		'consecutivo' => "00".$pedido->consecutivo,
	            		'valor_total' => "$".number_format($pedido->valor_total, 0, "," , "." ),
	            		'proveedor' => \App\TildeHtml::TildesToHtml($pedido->n_proveedor),
	            		'fecha' => date("Y-m-d",strtotime($pedido->created_at)),
	            		'estado' => $pedido->estado
            		);
	            }
	        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $pedidos->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' =>$myArray,
            'info' =>$pedidos->get()];

        return response()->json($data);
	}

	public function postDetallePedido(Request $request){
		if($request->has("id")){
			$pedido = PedidoProveedor::permitidos()->where("id",$request->input("id"))->first();

			return view("productos.detalle_pedido")->with("pedido",$pedido);
		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}

	/*
	 * lista de componentes de un producto compuesto
	 */
	public function postComponentes(Request $request)
	{
		if ($request->has("id")) {
		    if(Auth::user()->bodegas == 'si')
			    $producto = ABProducto::permitidos()->where("id", $request->input("id"))->first();
		    else
			    $producto = Producto::permitidos()->where("id", $request->input("id"))->first();
			if($producto && $producto->tipo_producto == "Compuesto"){
				$componentes = $producto->MateriasPrimas()->select("materias_primas.*", "producto_materia_unidad.cantidad")->get();
			}

            if(Auth::user()->bodegas == 'si')
			    return view('productos_ab.componentes')->with("componentes", $componentes)->with('producto', $producto)->render();
			else
			    return view('productos.componentes')->with("componentes", $componentes)->with('producto', $producto)->render();

		}
		return response(["error"=>["La información enviada es incorrecta"]],422);
	}
}