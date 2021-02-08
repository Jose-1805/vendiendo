<?php namespace App\Http;

use App\Http\Middleware\AuthApiDomicilio;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel {

	/**
	 * The application's global HTTP middleware stack.
	 *
	 * @var array
	 */
	protected $middleware = [
		'Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode',
		'Illuminate\Cookie\Middleware\EncryptCookies',
		'Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse',
		'Illuminate\Session\Middleware\StartSession',
		'Illuminate\View\Middleware\ShareErrorsFromSession',
		'App\Http\Middleware\VerifyCsrfToken',
	];

	/**
	 * The application's route middleware.
	 *
	 * @var array
	 */
	protected $routeMiddleware = [
		'auth' => 'App\Http\Middleware\Authenticate',
		'terminosCondiciones' => 'App\Http\Middleware\TerminosCondiciones',
		'auth.basic' => 'Illuminate\Auth\Middleware\AuthenticateWithBasicAuth',
		'guest' => 'App\Http\Middleware\RedirectIfAuthenticated',


        //Acceso a modulos
        'modProveedor' 		=> "App\Http\Middleware\PermisoModuloProveedor",
        'modMateriaPrima' 	=> "App\Http\Middleware\PermisoModuloMateriaPrima",
        'modUsuario' 		=> "App\Http\Middleware\PermisoModuloUsuarios",
        'modModulo' 		=> "App\Http\Middleware\PermisoModuloModulos",
        'modCategoria' 		=> "App\Http\Middleware\PermisoModuloCategorias",
        'modCuentaBancaria'	=> "App\Http\Middleware\PermisoModuloCuentaBancaria",
		'modUnidad' 		=> 'App\Http\Middleware\Unidades',
		'modProducto' 		=> 'App\Http\Middleware\Productos',
		'modCostoFijo' 		=> 'App\Http\Middleware\PermisoModuloCostosFijos',
		'modClientes' 		=> 'App\Http\Middleware\PermisoModuloClientes',
		'modFacturacion' 	=> 'App\Http\Middleware\PermisoModuloFacturacion',
		'modFacturas' 		=> 'App\Http\Middleware\PermisoModuloFacturas',
		'modCotizaciones'	=> 'App\Http\Middleware\PermisoModuloCotizaciones',
		'modRemisiones'		=> 'App\Http\Middleware\PermisoModuloRemisiones',
		'modTraslados'		=> 'App\Http\Middleware\PermisoModuloTraslados',
		'modPedido'			=> 'App\Http\Middleware\Pedido',
		'modReportes'		=> 'App\Http\Middleware\PermisoModuloReportes',
		'modCompras'		=> 'App\Http\Middleware\PermisoModuloCompras',
		'modGastos'		=> 'App\Http\Middleware\PermisoModuloGastos',
		'modEventos'		=> 'App\Http\Middleware\PermisoModuloEventos',
		'modPlan'			=> 'App\Http\Middleware\PermisoModuloPlanes',
		'modCaja'			=> 'App\Http\Middleware\CajaPermiso',
		'modWebCam'			=> 'App\Http\Middleware\PermisoModuloWebCam',
		'modConfiguracion'	=> 'App\Http\Middleware\ConfiguracionInicial',
		'modMisPedidos' 	=> 'App\Http\Middleware\PermisoModuloMisPedidos',
		'modPqrs'	        => 'App\Http\Middleware\PermisoModuloPqrs',
		'authApi'	        => 'App\Http\Middleware\AuthApi',
		'SessionDataCheckMiddleware' => \App\Http\Middleware\SessionDataCheckMiddleware::class,
		'SessionDemo' => \App\Http\Middleware\SessionDemoMiddleware::class,
		'authApiDomicilio' => \App\Http\Middleware\AuthApiDomicilio::class,
		'SessionDemo' => \App\Http\Middleware\SessionDemoMiddleware::class,
		//control-empledo
		'modEmpleado'			=> 'App\Http\Middleware\PermisoModuloEmpleados',			
		'modControlEmpleados'	=> 'App\Http\Middleware\PermisoModuloControlEmpleado',
		'modBodegas'	=>  'App\Http\Middleware\PermisoModuloBodegas',
		'modAlmacenes'	=>  'App\Http\Middleware\PermisoModuloAlmacenes',
		//Middleware para proveedores
		'modProductoProveedor' 		=> 'App\Http\Middleware\ProductosProveedor',
		'modPromocionesProveedor' 		=> 'App\Http\Middleware\PromocionesProveedor',
		'modPedidosProveedor' 		=> 'App\Http\Middleware\PedidosProveedor',
        'modRemisionIn'		=> 'App\Http\Middleware\PermisoModuloRemisionIngreso',
        'modRemisionS'		=> 'App\Http\Middleware\PermisoModuloRemisionSalida',
	];

}
