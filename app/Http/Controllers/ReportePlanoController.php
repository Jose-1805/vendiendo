<?php namespace App\Http\Controllers;

use App\General;
use App\Http\Requests;
use App\Http\Requests\ReportePlanoCreateRequest;
use App\Http\Controllers\Controller;
use App\Models\Compra;
use App\Models\PagoCostoFijo;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\ReportePlano;
use App\Models\Factura;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ReportePlanoController extends Controller {
	public function __construct()
	{
		$this->middleware("auth");
		$this->middleware("modConfiguracion");
		$this->middleware("terminosCondiciones");
	}

	public function getIndex(){
		$reportes_planos = ReportePlano::permitidos()
			->orderby('created_at','DESC')
			->paginate(env('PAGINATE'));
		//return view("reportes.planos.lista")->with("reportes_planos",$reportes_planos);
		return view('reportes.planos.index')->with("reportes_planos",$reportes_planos);
	}
	/*public function postListarReportesPlanos(Request $request){
		//dd($request->all());
		if($request->has("fecha_inicio") && $request->has("fecha_fin")){
			$fecha_fin = date("Y-m-d",strtotime("+1days",strtotime($request->get("fecha_fin"))));
			$reportes_planos = ReportePlano::whereBetween("created_at",[$request->get("fecha_inicio"),$fecha_fin])
				->where("usuario_id",Auth::user()->userAdminId())
				->orderby('created_at','DESC')
				->paginate(env('PAGINATE'));
			return view("reportes.planos.lista")->with("reportes_planos",$reportes_planos);
		}else{
			return response(["La información enviada es incorrecta"],422);
		}
	}*/

	public function getListReportesPlanos(Request $request){
			$search = $request->get("search");
	        $order = $request->get("order");
	        $sortColumnIndex = $order[0]['column'];
	        $sortColumnDir = $order[0]['dir'];
	        $length = $request->get('length');
	        $start = $request->get('start');
	        $columna = $request->get('columns');
	        // $orderBy = $columna[$sortColumnIndex]['data'];   
	        // if($orderBy == null){$orderBy= 'nombre';}
	    	$myArray=[];
			$reportes_planos = ReportePlano::permitidos()->orderby('created_at','DESC');

			$totalRegistros = $reportes_planos->count();  
		 	if($search['value'] != null){
	            $reportes_planos = $reportes_planos->whereRaw(
	                " ( LOWER(nombre) LIKE '%".\strtolower($search["value"])."%' OR".
	                " LOWER(seccion) LIKE '%".\strtolower($search["value"])."%')");
	        }

	        $parcialRegistros = $reportes_planos->count();
	        $reportes_planos = $reportes_planos->skip($start)->take($length);
		    $object = new \stdClass();
		        if($parcialRegistros > 0){
		            foreach ($reportes_planos->get() as $rp){		            	
		        	   $aux="campo";
			            $cantidad_campos = explode('-',$rp->campos);
			            if (count($cantidad_campos)> 1){
			                $aux = "campos";
			            }

		            	$myArray[]=(object) array(
		            		'id' => $rp->id,
		            		'nombre' => $rp->nombre,
		            		'seccion' => $rp->seccion,
		            		'campos' => $rp->campos,
		            		'cantidad_aux' =>count($cantidad_campos)." ". $aux,		            		
		            		'url_edit'=>url('/reporte-plano/edit/'.$rp->id)
	            		);	
		            }
		        }

	        $data = ['length'=> $length,
	            'start' => $start,
	            'buscar' => $search['value'],
	            'draw' => $request->get('draw'),
	            //'last_query' => $reportes_planos->toSql(),              
                'recordsTotal' =>$totalRegistros,
                'recordsFiltered' =>$parcialRegistros,
	            'data' =>$myArray,
	            'info' =>$reportes_planos->get()];
		    
	        return response()->json($data);
	}

	public function getCreate(){
		$titulo='Configurar reporte plano';
		$secciones = DB::table('campos_reportes_planos')->select('seccion')->get();
		$reporte_plano = new ReportePlano();
		return view('reportes.planos.create', compact('titulo','reporte_plano','secciones'));
	}
	public function getShow($seccion){
		$campos = DB::table('campos_reportes_planos')->where('seccion',$seccion)->take(1)->first();
		return view('reportes.planos.form.lista_campos')->with('campos',$campos);
	}
	public function postStore(ReportePlanoCreateRequest $request){
		$reporte_plano = new ReportePlano();
		if(Auth::user()->permitirFuncion("Crear","Reportes planos","reportes")) {
			DB::beginTransaction();
			$reporte_plano->nombre = $request->get('nombre');
			$reporte_plano->seccion = $request->get('seccion');
			$reporte_plano->campos = $request->get('campos');

			$reporte_plano->usuario_creator_id = Auth::user()->id;

			if (Auth::user()->perfil->nombre == "usuario") {
				$reporte_plano->usuario_id = Auth::user()->usuario_creador_id;
			} else {
				$reporte_plano->usuario_id = Auth::user()->id;
			}
			$reporte_plano->save();
			$data = [
				"success" => true,
				"mensaje" => "El mensaje ha sido registrado con éxito."
			];
			DB::commit();
			return $data;
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}

	}
	public function getEdit($id){
		$reporte_plano = ReportePlano::permitidos()->where('id',$id)->first();
		$titulo='Editar reporte plano';

		$campos = DB::table('campos_reportes_planos')->where('seccion',$reporte_plano->seccion)->take(1)->first();

		return view('reportes.planos.create', compact('titulo','reporte_plano','campos'));
	}
	public function postUpdate(ReportePlanoCreateRequest $request, $id){
		$reporte_plano = ReportePlano::permitidos()->where('id',$id)->first();
		if(Auth::user()->permitirFuncion("Editar","Reportes planos","reportes")) {
			DB::beginTransaction();
			$reporte_plano->nombre = $request->get('nombre');
			$reporte_plano->seccion = $request->get('seccion');
			$reporte_plano->campos = $request->get('campos');

			$reporte_plano->usuario_creator_id = Auth::user()->id;

			if (Auth::user()->perfil->nombre == "usuario") {
				$reporte_plano->usuario_id = Auth::user()->usuario_creador_id;
			} else {
				$reporte_plano->usuario_id = Auth::user()->id;
			}
			$reporte_plano->save();
			$data = [
				"success" => true,
				"mensaje" => "El mensaje ha sido actualizado con éxito."
			];
			DB::commit();
			return $data;
		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}

	}
	public function postDestroy(Request $request){
		if(Auth::user()->permitirFuncion("Eliminar","Reportes planos","reportes")) {
			$reporte_plano = ReportePlano::permitidos()->where('id',$request->get('id'))->first();

			if ($reporte_plano && $reporte_plano->exists){
				DB::beginTransaction();
				$reporte_plano->delete();
				DB::commit();
				Session::flash("mensaje", "El reporte plano ha sido eliminado con éxito");
				return ["success" => true];
			}else{
				return ["error" => "La información es incorrecta"];
			}

		}else{
			return response(["Error","Usted no tiene permisos para realizar esta tarea."],401);
		}

	}
	public function getExcel($id,$fecha_inicio,$fecha_fin){
		$reporte_plano = ReportePlano::permitidos()->where('id',$id)->first();
		$columnas = $reporte_plano->campos;	//columnas seleccionas por el usuario
		$columnas_array = explode('-',$columnas);
		$nombre = "Reporte de ".$reporte_plano->seccion;
		$array_titulos = array();
		$array_campos_consulta = array();

		for ($i=0; $i < count($columnas_array); $i++){
			$array_titulos[$i] = ucwords(str_replace('_',' ',$columnas_array[$i]));
			$aux = $reporte_plano->seccion;
			if($reporte_plano->seccion == 'Costos fijos')
				$aux = "pagos costos fijos";

			$array_campos_consulta[$i] =  str_replace(' ','_',strtolower($aux).".".$columnas_array[$i]);
		}
		$array_facturas=array();
		$data = array(
			$array_titulos
		);
		switch ($reporte_plano->seccion){
			case 'Facturas':
				if (in_array('facturas.resolucion',$array_campos_consulta)){
					array_push($array_campos_consulta,'facturas.resolucion_id');
					General::deleteFromArray($array_campos_consulta,'facturas.resolucion',false);
					array_push($array_campos_consulta,'resoluciones.numero as resolucion');
				}
				if (in_array('facturas.valor',$array_campos_consulta)){
					array_push($array_campos_consulta,'facturas.subtotal','facturas.iva');
					General::deleteFromArray($array_campos_consulta,'facturas.valor',false);
					array_push($array_campos_consulta,DB::raw('(facturas.subtotal + facturas.iva) as valor'));
				}
				if (in_array('facturas.cliente',$array_campos_consulta)){
					array_push($array_campos_consulta,'facturas.cliente_id');
					General::deleteFromArray($array_campos_consulta,'facturas.cliente',false);
					array_push($array_campos_consulta,'clientes.nombre as cliente');
				}
				if (in_array('facturas.abonos',$array_campos_consulta)){
					General::deleteFromArray($array_campos_consulta,'facturas.abonos');
					array_push($array_campos_consulta,DB::raw("(SELECT sum(valor) from abonos where abonos.tipo_abono_id = facturas.id and abonos.tipo_abono = 'factura') as abonos"));
				}
				$facturas = Factura::permitidos()->select($array_campos_consulta)
					->leftJoin('resoluciones', 'facturas.resolucion_id', '=', 'resoluciones.id')
					->leftJoin('clientes', 'facturas.cliente_id', '=', 'clientes.id')
					->whereBetween("facturas.created_at",[$fecha_inicio,$fecha_fin])
					->where("facturas.usuario_id",Auth::user()->userAdminId());

				$facturas = $facturas->get();
				$array_facturas = $facturas->toArray();
				foreach($array_facturas as $subKey => $subArray){
					unset($subArray['iva'],$subArray['resolucion_id'],$subArray['subtotal'],$subArray['cliente_id']);
					$array_facturas[$subKey] = $subArray;
					array_push($data,$array_facturas[$subKey]);
				}
				break;
			case 'Compras':
				if (in_array('compras.proveedor',$array_campos_consulta)){
					array_push($array_campos_consulta,'compras.proveedor_id');
					General::deleteFromArray($array_campos_consulta,'compras.proveedor',false);
					array_push($array_campos_consulta,'proveedores.nombre as proveedor');
				}
				if (in_array('compras.abonos',$array_campos_consulta)){
					General::deleteFromArray($array_campos_consulta,'compras.abonos');
					array_push($array_campos_consulta,DB::raw("(SELECT sum(valor) from abonos where abonos.tipo_abono_id = compras.id and abonos.tipo_abono = 'compra') as abonos"));
				}
				$compras = Compra::permitidos()->select($array_campos_consulta)
					->leftJoin('proveedores', 'compras.proveedor_id', '=', 'proveedores.id')
					->whereBetween("compras.created_at",[$fecha_inicio,$fecha_fin])
					->where("compras.usuario_id",Auth::user()->userAdminId());
				$compras = $compras->get();
				$array_compras = $compras->toArray();
				foreach ($array_compras as $subKey => $subArray){
					unset($subArray['proveedor_id']);
					$array_compras[$subKey] = $subArray;
					array_push($data,$array_compras[$subKey]);
				}
				break;
			case 'Costos fijos':
				if (in_array('pagos_costos_fijos.nombre_costo_fijo',$array_campos_consulta)){
					array_push($array_campos_consulta,'pagos_costos_fijos.costo_fijo_id');
					General::deleteFromArray($array_campos_consulta,'pagos_costos_fijos.nombre_costo_fijo',false);
					array_push($array_campos_consulta,'costos_fijos.nombre as nombre');
				}
				$costos_fijos = PagoCostoFijo::permitidos()->select($array_campos_consulta)
					->leftJoin('costos_fijos','pagos_costos_fijos.costo_fijo_id','=','costos_fijos.id')
					->whereBetween("pagos_costos_fijos.created_at",[$fecha_inicio,$fecha_fin])
					->where("pagos_costos_fijos.usuario_id",Auth::user()->userAdminId());
				$costos_fijos = $costos_fijos->get();
				$array_costos_fijos = $costos_fijos->toArray();
				foreach ($array_costos_fijos as $subKey => $subArray){
					unset($subArray['costo_fijo_id']);
					$array_costos_fijos[$subKey] = $subArray;
					array_push($data,$array_costos_fijos[$subKey]);
				}
				break;
			default:
				$data = null;
				break;
		}
		//dd($data);
		return Excel::create($nombre, function($excel) use ($reporte_plano,$nombre,$data,$columnas) {
			$excel->sheet($nombre, function($sheet) use ($reporte_plano,$data,$columnas) {

				$sheet->rows($data);
				$sheet->row(1, function($row) {
					$row->setBackground('#84d1e8');
				});
			});
		})->export("xls");
	}
}
