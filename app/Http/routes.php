<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/
// Contraseña petición de enlace de restablecimiento routes...
use App\Models\Producto;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

Route :: get ( 'password/email' ,  'Auth\PasswordController@getEmail' ) ;
Route :: post ( 'password/email' ,  'Auth\PasswordController@postEmail' ) ;

// Rutas de restablecimiento de contraseña ...
Route :: get ( 'password/reset/{token}' ,  'Auth\PasswordController@getReset' ) ;
Route :: post ( 'password/reset' ,  'Auth\PasswordController@postReset' ) ;

Route::group(['middleware' => ['SessionDataCheckMiddleware','SessionDemo']], function () {
	Route::get('/', 'InicioController@getIndex');
	Route::controllers([
		'auth' => 'Auth\AuthController',
		'password' => 'Auth\PasswordController',
		'home' => 'InicioController',
		'proveedor' => 'ProveedorController',
		'materia-prima' => 'MateriaPrimaController',
		'usuario' => 'UsuariosController',
		'modulo' => 'ModuloController',
		'unidades' => 'UnidadesController',
		'productos' => 'ProductosController',
		'categoria' => 'CategoriasController',
		'costo-fijo' => 'CostosFijosController',
		'facturacion' => 'FacturacionController',
		'factura' => 'FacturaController',
		'pedido' => 'PedidoController',
		'reporte' => 'ReporteController',
		'compra' => 'CompraController',
		'caja' => 'CajaController',
		'notificacion' => 'NotificacionesController',
		'plan' => 'PlanController',
		'pqrs' => 'PqrsController',
		'objetivos-ventas' => 'ObjetivosVentasController',
		'sms'	=> 'SmsController',
		'reporte-plano'	=> 'ReportePlanoController',
		'webcam'	=> 'WebCamController',
		'configuracion'	=> 'ConfiguracionController',
		'sede' => 'SedeController',
		'control-empleados' => 'ControlEmpleadosController',
		'cliente' => 'ClientesController',
		'anuncio' => 'AnunciosController',
		'cotizacion' => 'CotizacionesController',
		'remision' => 'RemisionesController',
		'traslado' => 'TrasladosController',
		//RUTAS PARA EL ROL DE PROVEEDOR
		'productos-proveedor'	=> 'ProductosProveedorController',
		'promociones-proveedor'	=> 'PromocionesProveedorController',
		'pedidos-proveedor'	=> 'PedidosProveedorController',
		'gastos' => 'GastosController',
		'cuenta-bancaria' => 'CuentaBancariaController',
		'mispedidos' => "MisPedidosController",
		'bodega' => "BodegasController",
		'almacen' => "AlmacenesController",
        'evento' => 'EventsController',
        'migracion-ab' => 'MigracionABController',
        'remision-ingreso' => 'RemisionIngresoController',
        'remision-salida' => 'RemisionSalidaController',
	]);

	Route::post('set-length-rows-tables',function (\Illuminate\Http\Request $request){
	    if($request->has('length') && is_numeric($request->input('length'))) {
            $user = Auth::user();
            $user->length_rows_tables = $request->input('length');
            $user->save();
        }

    });

    Route::get('api','EventsController@listEvents');
    Route::get('api-costos-fijos','EventsController@listCostosFijos');

	Route::get('redimensionar-imagenes-productos',function (){
        if(Auth::user()->perfil->nombre == 'superadministrador') {
            $productos = Producto::whereNotNull('imagen')->where('imagen', '<>', '')->get();
            $ruta = public_path('img/productos/');
            foreach ($productos as $p) {
                $path = $ruta . $p->id . '/' . $p->imagen;
                $path_thumb = $ruta . $p->id . '/thumb_' . $p->imagen;
                //$path_new = $ruta.$p->id.'/_min_'.$p->imagen;
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

                $redimendion_thumb = 500;

                if ($ancho >= $alto) {
                    $relacion = $alto / $ancho;
                    $image->resize($redimendion_thumb, intval($redimendion_thumb * $relacion));
                }else {
                    $relacion = $ancho / $alto;
                    $image->resize(intval($redimendion_thumb*$relacion), $redimendion_thumb);
                }


                //$image->save($path_new);
                $image->save($path_thumb);
                //return $image->response();
                echo $p->nombre.'<br><br>';
            }
            echo 'Todas las imágenes han sido redimensionadas con éxito !!';
        }else{
            return redirect('/');
        }
    });
});

Route::post('session/ajaxCheck', ['uses' => 'SessionController@ajaxCheck', 'as' => 'session.ajax.check']);
Route::post('session/ajaxEstadoDemo', ['uses' => 'SessionController@ajaxEstadoDemo', 'as' => 'session.estado.demo']);
Route::post('session/resetConteo', ['uses' => 'SessionController@resetConteo']);
//Route::resource('unidades','UnidadesController');
Route :: get ('pdf' ,  function(){
	$id = '41';

	$factura = \App\Models\Factura::permitidos()->where("id",$id)->first();
	$pdf = PDF::loadView('factura.factura_carta.index',['factura'=>$factura]);
	//$pdf = PDF::loadView('factura.factura_carta.index', ['factura'=>$factura]);
	return $pdf->stream();

	/*con libreria snapy
			$pdf = \PDFS::loadView('factura.factura_carta.index', ['factura'=>$factura]);
			//return \PDFS::loadFile('http://www.github.com')->stream('github.pdf');
			$pdf->setOption('footer-right','Pag.[page]/[toPage]');
	*/

});

Route::get('session-1', function(){

});

Route::group(["prefix"=>"api/v1"],function(){
	Route::controller("authenticate", "Api\AuthController");

	Route::group(["middleware"=>"authApi"],function() {
		Route::controller("producto", "Api\ProductoController");
		Route::controller("factura", "Api\FacturaController");
		Route::controller("lista", "Api\ListasController");
	});
});
Route::group(["prefix"=>"api/domiciles"],function(){

	Route::controller("authenticate", "Api\Domiciles\AuthController");

	Route::group(["middleware"=>"authApiDomicilio"],function() {
		Route::controller("list", "Api\Domiciles\ListasController");

	});
});
//Route::get('calendar', "EventsController@index");

Route::get('/prueba-cache',function (){

    //\Illuminate\Support\Facades\Cache::flush();
    if(!\Illuminate\Support\Facades\Cache::has('p_obj')) {
        dd('error');
        $producto = Producto::permitidos()->whereNotNull('imagen')->where('imagen', '<>', '')->get();
        /*$ruta = public_path('img/productos/' . $producto->id . '/' . $producto->imagen);
        $image = Image::make($ruta);*/
        \Illuminate\Support\Facades\Cache::put('p_obj',$producto,20);
    }
    return \Illuminate\Support\Facades\Cache::get('p_obj');
});