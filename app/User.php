<?php namespace App;

use App\Models\ABCompra;
use App\Models\ABFactura;
use App\Models\ABProducto;
use App\Models\Almacen;
use App\Models\ActualizacionSistema;
use App\Models\Bodega;
use App\Models\Cajas;
use App\Models\Cliente;
use App\Models\Compra;
use App\Models\CostoFijo;
use App\Models\Factura;
use App\Models\GastoDiario;
use App\Models\MateriaPrima;
use App\Models\Modulo;
use App\Models\Municipio;
use App\Models\ObjetivoVenta;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Remision;
use App\Models\ReporteHabilitado;
use App\Models\Tarea;
use App\Models\TipoPago;
use App\Models\VReportesPermitidos;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class User extends Model implements AuthenticatableContract, CanResetPasswordContract {

    use Authenticatable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombres','apellidos','telefono','alias', 'email','nit','regimen','bodegas','admin_bodegas','nombre_negocio','categoria_id','municipio_id','almacen_id','permiso_reportes'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];


    /**
     * RELACIONES
     */

    public function modulos(){
        return $this->belongsToMany("App\Models\Modulo","usuarios_modulos","usuario_id","modulo_id");
    }

    public function perfil(){
        return $this->belongsTo("App\Models\Perfil");
    }

    public function funciones(){
        return $this->belongsToMany("App\Models\Funcion","usuarios_funciones","usuario_id","funcion_id");
    }

    public function tareasFuncion($idFuncion){
        return Tarea::select("tareas.*")
            ->join("usuarios_funciones_tareas","tareas.id","=","usuarios_funciones_tareas.tarea_id")
            ->join("usuarios_funciones","usuarios_funciones_tareas.usuario_funcion_id","=","usuarios_funciones.id")
            ->join("usuarios","usuarios_funciones.usuario_id","=","usuarios.id")
            ->where("usuarios_funciones.funcion_id",$idFuncion)
            ->where("usuarios.id",$this->id)->get();
    }

    /**
     * FUNCIONES DEL MODELO
     */
    public function userAdminId(){
        $perfil = $this->perfil->nombre;
        if($perfil == "administrador"){
            if($this->bodegas == "no" || $this->admin_bodegas == "si") {
                return $this->id;
            }
            if(Auth::user()->bodegas == 'si'){
                if(Auth::user()->admin_bodegas == 'si')return Auth::user()->id;
                if(Auth::user()->admin_bodegas != 'si')return Auth::user()->usuario_creador_id;
            }
            return $this->id;
        }else if($perfil == "usuario"){
            return $this->usuario_creador_id;
        }else{
            return $this->id;
        }
    }
    public function modulosActivos(){
        //modulos asignados manualmente
        $relacion = Modulo::select("modulos.*")
            ->join("usuarios_modulos","modulos.id","=","usuarios_modulos.modulo_id")
            ->join("usuarios","usuarios_modulos.usuario_id","=","usuarios.id")
            ->where("usuarios.id",$this->id)
            ->where("modulos.estado","activo")
            ->where("usuarios_modulos.estado","activo")
            ->where("usuarios_modulos.hasta",">",date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d")))))
            ->orderBy("modulos.categoria")->orderBy("modulos.orden")->get();

        //modulos activos por plan
        $plan = Modulo::select("modulos.*")
            ->join("planes_modulos","modulos.id","=","planes_modulos.modulo_id")
            ->join("planes","planes_modulos.plan_id","=","planes.id")
            ->join("planes_usuarios","planes.id","=","planes_usuarios.plan_id")
            ->join("usuarios","planes_usuarios.usuario_id","=","usuarios.id")
            ->where("usuarios.id",$this->id)
            ->where("planes_usuarios.estado","activo")
            ->where("planes_usuarios.hasta",">",date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d")))))
            ->where("modulos.estado","activo")
            ->orderBy("modulos.categoria")->orderBy("modulos.orden")->get();

        for($i = 0; $i < $plan->count(); $i++){
            $relacion->push($plan->get($i));
        }
        return $relacion->sortBy("seccion");
    }

    public function permitirModulo($modulo,$seccion){
        //es permitido por medio del plan
        $resultado = User::join("planes_usuarios","usuarios.id","=","planes_usuarios.usuario_id")
            ->join("planes","planes_usuarios.plan_id","=","planes.id")
            ->join("planes_modulos","planes.id","=","planes_modulos.plan_id")
            ->join("modulos","planes_modulos.modulo_id","=","modulos.id")
            ->where("usuarios.id",$this->id)
            ->where("modulos.nombre",$modulo)
            ->where("modulos.seccion",$seccion)
            ->where("planes_usuarios.estado","activo")
            ->where("planes_usuarios.hasta",">",date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d")))))
            ->where("modulos.estado","activo")->get();
        if($resultado && count($resultado))return true;


        //es permitido por asignacion manual
        $resultado = User::join("usuarios_modulos","usuarios.id","=","usuarios_modulos.usuario_id")
            ->join("modulos","usuarios_modulos.modulo_id","=","modulos.id")
            ->where("usuarios.id",$this->id)
            ->where("modulos.nombre",$modulo)
            ->where("modulos.seccion",$seccion)
            ->where("modulos.estado","activo")
            ->where("usuarios_modulos.estado","activo")
            ->where("usuarios_modulos.hasta",">",date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d")))))->get();
        if($resultado && count($resultado))return true;

        return false;
    }

    public function permitirFuncion($funcion,$modulo,$seccion){
        //Es permitido por medio del plan
        $resultado = User::join("planes_usuarios","usuarios.id","=","planes_usuarios.usuario_id")
            ->join("planes","planes_usuarios.plan_id","=","planes.id")
            ->join("planes_funciones","planes.id","=","planes_funciones.plan_id")
            ->join("funciones","planes_funciones.funcion_id","=","funciones.id")
            ->join("modulos","funciones.modulo_id","=","modulos.id")
            ->where("usuarios.id",$this->id)
            ->where("modulos.nombre",$modulo)
            ->where("modulos.seccion",$seccion)
            ->where("funciones.nombre",$funcion)
            ->where("planes_usuarios.estado","activo")
            ->where("planes_usuarios.hasta",">",date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d")))))
            ->where("modulos.estado","activo")->get();
        if($resultado && count($resultado))return true;

        //es permitido por asignacin manual
        $resultado = User::join("usuarios_modulos","usuarios.id","=","usuarios_modulos.usuario_id")
            ->join("modulos","usuarios_modulos.modulo_id","=","modulos.id")
            ->join("funciones","modulos.id","=","funciones.modulo_id")
            ->join("usuarios_funciones","funciones.id","=","usuarios_funciones.funcion_id")
            ->where("usuarios.id",$this->id)
            ->where("modulos.nombre",$modulo)
            ->where("modulos.seccion",$seccion)
            ->where("modulos.estado","activo")
            ->where("funciones.nombre",$funcion)
            ->where("usuarios_funciones.usuario_id",$this->id)
            ->where("usuarios_funciones.estado","activo")
            ->where("usuarios_funciones.hasta",">",date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d")))))->get();
        if($resultado && count($resultado))return true;

        return false;
    }

    public function permitirTarea($tarea,$funcion,$modulo,$seccion){
        //Es permitido por medio del plan

        $resultado = DB::select("SELECT t.* FROM v_tareas_activas_plan t ".
            "WHERE t.modulos_nombre = '".$modulo."' ".
            "AND t.modulos_seccion = '".$seccion."' ".
            "AND t.funciones_nombre = '".$funcion."' ".
            "AND t.tareas_nombre = '".$tarea."' ".
            "AND t.planes_usuarios_hasta > '".date("Y-m-d H:i:s",strtotime("-1days",strtotime(date("Y-m-d"))))."'".
            "AND t.usuarios_id =".$this->id);

        /*$resultado = User::join("planes_usuarios","usuarios.id","=","planes_usuarios.usuario_id")
            ->join("planes","planes_usuarios.plan_id","=","planes.id")
            ->join("planes_funciones","planes.id","=","planes_funciones.plan_id")
            ->join("planes_funciones_tareas","planes_funciones.id","=","planes_funciones_tareas.plan_funcion_id")
            ->join("tareas","planes_funciones_tareas.tarea_id","=","tareas.id")
            ->join("funciones_tareas","tareas.id","=","funciones_tareas.tarea_id")
            ->join("funciones","funciones_tareas.funcion_id","=","funciones.id")
            ->join("modulos","funciones.modulo_id","=","modulos.id")
            ->where("usuarios.id",$this->id)
            ->where("modulos.nombre",$modulo)
            ->where("modulos.seccion",$seccion)
            ->where("funciones.nombre",$funcion)
            ->where("planes_usuarios.estado","activo")
            ->where("planes_usuarios.hasta",">=",date("Y-m-d H:i:s"))
            ->where("modulos.estado","activo")
            ->where("planes_funciones_tareas.estado","activo")
            ->where("tareas.nombre",$tarea)->get();*/
        if($resultado && count($resultado))return true;

        //es permitido por asignacin manual
        $resultado = DB::select("SELECT t.* FROM v_tareas_activas t ".
            "WHERE t.modulos_nombre = '".$modulo."' ".
            "AND t.modulos_seccion = '".$seccion."' ".
            "AND t.funciones_nombre = '".$funcion."' ".
            "AND t.tareas_nombre = '".$tarea."' ".
            "AND t.usuarios_id =".$this->id);
        if($resultado && count($resultado))return true;

        return false;
    }

    public static function permitidos($incluir_modelo = false, $solo_administradores = false){
        $user = Auth::user();
        $perfil = $user->perfil;
        if($perfil->nombre == "superadministrador") {
            return User::select("usuarios.*")
                ->join("perfiles","usuarios.perfil_id","=","perfiles.id")
                ->where(function($q){
                    $q->where("perfiles.nombre","administrador")
                        ->orWhere("perfiles.nombre","proveedor");
                })
                ->where(function($q){
                    $q->where("usuarios.bodegas","no")
                        ->orWhere(function ($c){
                            $c->where('usuarios.bodegas','si')
                                ->where('usuarios.admin_bodegas','si');
                        });
                });
        }else if($perfil->nombre == "administrador" || $perfil->nombre == "usuario"){
            if($user->bodegas == 'si'){
                if($user->admin_bodegas == 'si'){
                    if($solo_administradores){
                        return User::where("usuarios.usuario_creador_id", $user->userAdminId())
                            ->join('perfiles','usuarios.perfil_id','=','perfiles.id')
                            ->where('perfiles.nombre','administrador');
                    }else {
                        if (!$incluir_modelo)
                            return User::where("usuario_creador_id", $user->userAdminId());
                        else
                            return User::where("usuario_creador_id", $user->userAdminId())->orWhere('id', $user->id);
                    }
                }else{
                    $almacen = null;
                    if($perfil->nombre == "administrador"){
                        $almacen = Almacen::where('administrador',$user->id)->first();
                    }else{
                        $almacen = Almacen::where('id',$user->almacen_id)->first();
                    }

                    if($almacen){
                        if(!$incluir_modelo)
                            return User::where("almacen_id", $almacen->id);
                        else
                            return User::where("almacen_id", $almacen->id)->orWhere('id', $user->id);
                    }
                }
            }else {
                if (!$incluir_modelo) {
                    if($perfil->nombre == "administrador"){
                        return User::where("usuario_creador_id", $user->id);
                    }else{
                        return User::where("usuario_creador_id", $user->userAdminId())->orWhere('id', $user->id);
                    }
                }else {
                    if($perfil->nombre == "administrador"){
                        return User::where("usuario_creador_id", $user->id)->orWhere('id', $user->id);
                    }else{
                        return User::where("usuario_creador_id", $user->userAdminId())->orWhere('id', $user->id);
                    }
                }
            }
        }
        return null;
    }

    public function generarClave(){
        $cantidad = rand(10,15);
        $cadena = "";
        for ($i = 0; $i < $cantidad;$i++){
            $cadena .= $this->getRandom();
        }
        $this->password = Hash::make($cadena);
        return $cadena;
    }

    public function generarApiKey(){
        $cantidad = rand(200,250);
        $cadena = "";
        for ($i = 0; $i < $cantidad;$i++){
            $cadena .= $this->getRandom();
        }
        $this->api_key = $cadena;
        return $cadena;
    }

    public  function getRandom(){
        $an = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz-_";
        $su = strlen($an) - 1;
        return substr($an, rand(0, $su), 1);
    }

    public function notificaciones(){
        return $this->belongsToMany("App\Models\Notificacion","usuarios_notificaciones","usuario_id","notificacion_id");
    }

    public function planes(){
        return $this->belongsToMany("App\Models\Plan","planes_usuarios","usuario_id","plan_id");
    }

    public function categoria(){
        return $this->belongsTo("App\Models\Categoria");
    }

    public function categorias(){
        return $this->belongsToMany("App\Models\Categoria","usuarios_categorias","usuario_id","categoria_id");
    }

    public function plan(){

        if($this->perfil->nombre != "usuario"){
            $user = $this;
            if($this->bodegas == "si"){
                $user = User::find($this->userAdminId());
            }
        }else {
            $user = User::find($this->userAdminId());
        }
        return $user->planes()->where("planes_usuarios.estado","activo")->where("planes_usuarios.hasta",">=",date("Y-m-d H:i:s"))->first();
    }

    public function caducidadPlan(){
        if($this->perfil->nombre != "usuario"){
            $user = $this;
            if($this->bodegas == "si"){
                $user = User::find($this->userAdminId());
            }
        }else {
            $user = User::find($this->userAdminId());
        }
        $data =  $user->planes()->select('planes_usuarios.hasta')->where("planes_usuarios.estado","activo")->where("planes_usuarios.hasta",">=",date("Y-m-d H:i:s"))->first();
        if($data)return $data->hasta;

        return false;
    }

    public function ultimoPlan(){
        $user = User::find($this->userAdminId());
        return $user->planes()->select("planes_usuarios.id as id_relacion","planes_usuarios.created_at as fecha_relacion","planes.*")->orderBy("id_relacion","DESC")->orderBy("fecha_relacion","DESC")->first();
    }

    public function adminFacturaAbierta(){
        $admin = User::find(Auth::user()->userAdminId());
        return $admin->factura_abierta;
    }

    public function municipio(){
        return $this->belongsTo(Municipio::class);
    }

    public function permitirReportes($id, $plan_id, $estado){

        $reportesPermitidos = VReportesPermitidos::select('*')
            ->where("id", $id)
            ->where("plan_id", $plan_id)
            ->where("estado", $estado)
            ->orderBy("categoria")
            ->get();

        return $reportesPermitidos;
    }

    public function countUsuariosAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        $usuarios = User::where("usuario_creador_id",$admin->id)->get()->count();
        return $usuarios;
    }

    public function countComprasAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        if($admin->bodegas == 'si')
            $compras = ABCompra::where("usuario_id",$admin->id)->get()->count();
        else
            $compras = Compra::where("usuario_id",$admin->id)->get()->count();
        return $compras;
    }

    public function countFacturasAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        if($admin->bodegas == 'si')
            $facturas = ABFactura::where('usuario_id',$admin->id)->where('plan_usuario_id',$admin->ultimoPlan()->id_relacion)->get()->count();
        else
            $facturas = Factura::where('usuario_id',$admin->id)->where('plan_usuario_id',$admin->ultimoPlan()->id_relacion)->get()->count();
        return $facturas;
    }

    public function countAlmacenesAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        if($admin->bodegas == 'si')
            $almacenes = Almacen::where('usuario_id',$admin->id)->get()->count();
        else
            return 0;
        return $almacenes;
    }

    public function countBodegasAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        if($admin->bodegas == 'si')
            $bodegas = Bodega::where('usuario_id',$admin->id)->get()->count();
        else
            return 0;
        return $bodegas;
    }

    public function countProveedoresAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        return Proveedor::where('usuario_id',$admin->id)->get()->count();
    }

    public function countClientesAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        return Cliente::where('usuario_id',$admin->id)->get()->count();
    }

    public function countProductosAdministrador(){
        $admin = User::find(Auth::user()->userAdminId());
        if($admin->bodegas == 'si')
            $pproductos = ABProducto::where('usuario_id',$admin->id)->get()->count();
        else
            $pproductos = Producto::where('usuario_id',$admin->id)->get()->count();
        return $pproductos;
    }


    public function cajas(){
        return $this->belongsToMany(Cajas::class,"cajas_usuarios","usuario_id","caja_id");
    }

    public function cajaAsignada(){
        return $this->cajas()->select('cajas.*','cajas_usuarios.id as relacion_id')->where("cajas_usuarios.estado","activo")->first();
    }

    public function almacenActual(){
        if(Auth::user()->bodegas == 'si'){
            if(Auth::user()->perfil->nombre == 'administrador'){
                return Almacen::where('administrador',Auth::user()->id)->first();
            }else{
                return Almacen::where('id',Auth::user()->almacen_id)->first();
            }
        }

        return false;
    }

    public function tiposPago(){
        return $this->belongsToMany(TipoPago::class,'usuarios_tipos_pago','usuario_id','tipo_pago_id');
    }

    public function hasTipoPago($id)
    {
        $admin = User::find(Auth::user()->userAdminid());
        $tipo_pago = $admin->tiposPago()->where('tipos_pago.id',$id)
            ->where('usuarios_tipos_pago.estado','habilitado')->first();
        if($tipo_pago)return true;

        return false;

    }

    public function actualizacionesSoftware(){
        return $this->belongsToMany(ActualizacionSistema::class,'usuarios_actualizaciones_sistema','usuario_id','actualizacion_sistema_id');
    }

    public function reporteHabilitado($reporte){
        $reporte = ReporteHabilitado::where('usuario_id',$this->userAdminId())->where('reporte_id',$reporte)->first();
        return $reporte?true:false;
    }

    /**
     * retorna true si se ha hecho la configuracion de migracion de version a almacenes y bodegas
     * retorna false si no se ha hecho
     */
    public function configuracionCambioAB(){
        $administrador = User::find($this->userAdminId());
        $configuracion_completa = true;
        if($administrador->bodegas == 'si' && $administrador->cambio_bodegas_almacenes == 'si'){
            if(
                $administrador->configuracion_usuarios == 'no'
                || $administrador->configuracion_productos == 'no'
                || $administrador->configuracion_materias_primas == 'no'
                || $administrador->configuracion_costos_fijos == 'no'
                || $administrador->configuracion_gastos_diarios == 'no'
                || $administrador->configuracion_objetivos_ventas == 'no'
                || $administrador->configuracion_compras == 'no'
                || $administrador->configuracion_facturas == 'no'
                || $administrador->configuracion_remisiones == 'no'
            )
                $configuracion_completa = false;
        }
        return $configuracion_completa;
    }

    /**
     * Indica si el usuario puede realizar la configuración que se pasa por parametro
     * de acuerdo al orden de configuracion necesaria para el cambio de version de bddegas a almacenes
     * @param $funcion
     */
    public function privilegiosConfigurarFuncion($funcion){
        $almacenes = Almacen::permitidos()->count();
        $bodegas = Bodega::permitidos()->count();
        $active = true;
        $configured = false;



        switch ($funcion){
            case 'usuarios':
                //si no se encuentran registros por configurar
                if(User::permitidos()->whereNull('usuarios.almacen_id')->count() == 0){
                    $active = false;
                    $configured = true;
                }else{
                    //se declaran dependencias para la configuracion
                    if($almacenes<1 || $bodegas<1)$active = false;
                }
                break;
            case 'costos fijos':
                //si no se encuentran registros por configurar
                if(CostoFijo::permitidos()->where('costos_fijos.migracion_realizada','no')->count() == 0){
                    $active = false;
                    $configured = true;
                }else{
                    //se declaran dependencias para la configuracion
                    if($almacenes<1 || $bodegas<1)$active = false;
                }
                break;
            case 'objetivos ventas':
                //si existen registros no configurados
                if(ObjetivoVenta::permitidos()->whereNull('objetivos_ventas.almacen_id')->count() == 0){
                    $active = false;
                    $configured = true;
                }else{
                    //se declaran dependencias para la configuracion
                    if($almacenes<1 || $bodegas<1)$active = false;
                }
                break;
            case 'productos':
                //si no existen registros no configurados
                if(Producto::permitidos()->whereNull('productos.migracion_producto_id')->count() == 0){
                    $active = false;
                    $configured = true;
                }else{
                    //se declaran dependencias para la configuracion
                    if($almacenes<1 || $bodegas<1)
                        $active = false;
                }
                break;
            case 'facturas':
                //si ya estan configuradas todas la facturas
                if(Factura::permitidos()->whereNull('facturas.migracion_factura_id')->count() == 0){
                    $active = false;
                    $configured = true;
                }else{
                    //se declaran dependencias para la configuracion
                    if($almacenes<1 || $bodegas<1 || Producto::permitidos()->whereNull('productos.migracion_producto_id')->count()>0)
                        $active = false;
                }
            break;
            case 'remisiones':
                //si ya estan configuradas todas las remisiones
                if(Remision::permitidos()->whereNull('remisiones.migracion_remision_id')->count() == 0){
                    $active = false;
                    $configured = true;
                }else{
                    //se declaran dependencias para la configuracion
                    if($almacenes<1 || $bodegas<1 || Factura::permitidos()->whereNull('facturas.migracion_factura_id')->count() > 0)
                        $active = false;
                }
            break;
        }

        return [$active,$configured];
    }
}
