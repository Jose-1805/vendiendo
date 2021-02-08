<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCajaRequest;
use App\Models\Almacen;
use App\Models\Caja;
use App\Models\CajaHistorialEstado;
use App\Models\Cajas;
use App\Models\CajaUsuarioTransaccion;
use App\Models\Compra;
use App\Models\CuentaBancaria;
use App\Models\MovimientoCajaBanco;
use App\Models\MovimientoCajaMaestra;
use App\Models\OperacionCaja;
use App\Models\Abono;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Redirect;
use Session;
USE App\Http\Requests\RequestCaja;
use App\Http\Requests\RequestCajaUsuarioTransaccion;
use App\Http\Requests\RequestOperacionCajaMaestra;

class CajaController extends Controller {
    public function __construct()
    {
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("modCaja");
        $this->middleware("terminosCondiciones");
    }

    public function getIndex(){
        //return Redirect('/caja/create');

        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            if (Auth::user()->permitirFuncion("Iniciar mayor","caja","configuracion"))
                return view('caja.lista_mayor');
        return view("caja.index");
    }


    public function getListCajas(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';

        if($orderBy == "estado")$orderBy = "cajas.estado";
        if($orderBy == "usuario")$orderBy = "usuarios.nombres";

        if(Auth::user()->bodegas == 'si')
            $cajas = Cajas::permitidosAlmacen()->select("cajas.*");
        else
            $cajas = Cajas::permitidos()->select("cajas.*");

        $cajas = $cajas->leftJoin("cajas_usuarios","cajas.id","=","cajas_usuarios.caja_id")
            ->leftJoin("usuarios","cajas_usuarios.usuario_id","=","usuarios.id")
            ->groupBy("cajas.id");

        $cajas = $cajas->orderBy($orderBy, $sortColumnDir);
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $cajas = $cajas->where(
                function($query) use ($f){
                    $query->where("nombre","like",$f)
                        ->orWhere("prefijo","like",$f)
                        ->orWhere(function($q) use ($f){
                            $q->where("usuarios.nombres","like",$f)
                              ->where("cajas_usuarios.estado","activo");
                        })
                        ->orWhere(function($q) use ($f){
                            $q->where("usuarios.apellidos","like",$f)
                                ->where("cajas_usuarios.estado","activo");
                        });
                }
            );
        }
        $cajas = $cajas->skip($start)->take($length)->get();

        $totalRegistros = $cajas->count();
        $parcialRegistros = $cajas->count();
        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($cajas as $value) {
                $user = $value->relacionUsuarioActual();
                $eliminar = "";
                $relacion = $value->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
                $editar = "<a href='#!'><i class='fa fa-pencil-square-o tooltipped' data-position='left' data-delay='50' data-tooltip='Editar'  style='margin: 5px;' onclick='getEditar($value->id)'></i></a>";
                if($relacion)$editar = "";
                if($value->permitirAccion()){
                    $eliminar = "<a href='#!' style='margin: 5px;'><i class='fa fa-trash red-text tooltipped' data-position='left' data-delay='50' data-tooltip='Eliminar'  onclick='eliminar($value->id)'></i></a>";
                }
                if(!$user)$user = new User();
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombre,
                    'prefijo'=>$value->prefijo,
                    'estado'=>$value->estado,
                    'usuario'=>$user->nombres.' '.$user->apellidos,
                    'valor_inicial'=>"$ ".number_format($user->valor_inicial,2,'.','.'),
                    'valor_final'=>"$ ".number_format($user->valor_final,2,'.','.'),
                    'historial'=>'<a href="#!" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Historial de asignaciones" ><i class="fa fa-users" onclick="cargarHistorial('.$value->id.')"></i></a>',
                    'historial_estados'=>'<a href="#!" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Historial de estados" ><i class="fa fa-list-alt" onclick="cargarHistorialEstados('.$value->id.')"></i></a>',
                    'transacciones'=>'<a href="#!" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Transacciones" ><i class="fa fa-money" onclick="cargarTransacciones('.$value->id.')"></i></a>',
                    'editar'=>$editar,
                    'eliminar'=>$eliminar
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $cajas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$cajas];

        return response()->json($data);

    }

    /**
     * @return Redirect 
     */
	public function getCreate(){
        $user = Auth::user();

        $almacen_user = null;

        if($user->bodegas == 'si' && $user->admin_bodegas == 'no'){
            $almacen_user = Almacen::where('administrador',$user->id)->first();
        }

        if ((!Caja::cajasPermitidas()->where('estado', 'abierta')->first()
            || (!Caja::cajasPermitidas()->where('principal_ab','si')->where('estado', 'abierta')->first() && $user->admin_bodegas == 'si')
            || ($almacen_user && !Caja::cajasPermitidas()->where('almacen','si')->where('almacen_id',$almacen_user->id)->where('estado', 'abierta')->first())
            ) && $user->permitirFuncion("Iniciar mayor","caja","configuracion")){


            $ultima_caja = Caja::cajasPermitidas()->orderBy("caja.created_at","DESC")->first();
            if($user->bodegas == 'si'){
                if($user->admin_bodegas == 'si'){
                    $ultima_caja = Caja::cajasPermitidas()->where('principal_ab','si')->orderBy("caja.created_at","DESC")->first();
                }else{
                    $ultima_caja = Caja::cajasPermitidas()->where('principal_ab','no')->where('almacen','si')->where('almacen_id',$almacen_user->id)->orderBy("caja.created_at","DESC")->first();
                }
            }

            if(!Caja::abierta() && (Auth::user()->bodegas == 'no' || (Auth::user()->bodegas == 'si' && AUth::user()->admin_bodegas == 'si')))
                return view('caja.create')->with('ultima_caja',$ultima_caja);
        }
        return Redirect('/caja');
    }


	public function getMaestra(){
        $user = Auth::user();
        if ($user->permitirFuncion("Iniciar mayor","caja","configuracion")){
                return view('caja.lista_mayor');
        }
        return Redirect('/caja');
    }

    public function getListCajaMayor(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
        if($orderBy == "fecha"){
            if($sortColumnDir == "desc")$sortColumnDir = "asc";
            else$sortColumnDir = "desc";
        }
        if($orderBy == "estado")$orderBy = "caja.estado";
        if($orderBy == "usuario")$orderBy = "usuarios.nombres";

        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si') {
            $cajas = Caja::cajasPermitidas()->where('principal_ab', 'si')->select("caja.*", "usuarios.nombres", "usuarios.apellidos")
                ->join("usuarios", "caja.usuario_creador_id", "=", "usuarios.id");
        }else if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no') {
            $almacen = Almacen::permitidos()->where('administrador',Auth::user()->id)->first();
            if($almacen){
                $cajas = Caja::cajasPermitidas()->where('principal_ab', 'no')
                    ->where('caja.almacen','si')
                    ->where('caja.almacen_id',$almacen->id)
                    ->select("caja.*", "usuarios.nombres", "usuarios.apellidos")
                    ->join("usuarios", "caja.usuario_creador_id", "=", "usuarios.id");
            }

        }else {
            $cajas = Caja::cajasPermitidas()->select("caja.*", "usuarios.nombres", "usuarios.apellidos")
                ->join("usuarios", "caja.usuario_creador_id", "=", "usuarios.id");
        }

        $cajas = $cajas->orderBy($orderBy, $sortColumnDir);
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $cajas = $cajas->where(
                function($query) use ($f){
                    $query->where("efectivo_inicial","like",$f)
                        ->orWhere("efectivo_final","like",$f)
                        ->orWhere("estado","like",$f)
                        ->orWhere("usuarios.nombres","like",$f)
                        ->orWhere("usuarios.apellidos","like",$f)
                        ->orWhere("fecha","like",$f);
                }
            );
        }
        $cajas = $cajas->skip($start)->take($length)->get();

        $totalRegistros = $cajas->count();
        $parcialRegistros = $cajas->count();
        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($cajas as $value) {
                $opciones = '<a href="#" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Historial de operaciones caja" ><i class="fa fa-list-alt" onclick="cargarHistorialOperacionesCaja('.$value->id.')"></i></a>';
                if($value->estado == "abierta"){
                    $opciones = '<a href="#modal-operacion-caja" style="margin: 5px;" class="tooltipped modal-trigger" data-position="left" data-delay="50" data-tooltip="Operaciones de caja" ><i class="fa fa-money"></i></a>'
                                .'<a href="#" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Historial de operaciones caja" ><i class="fa fa-list-alt" onclick="cargarHistorialOperacionesCaja('.$value->id.')"></i></a>'
                                .'<a href="#" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Cerrar caja" ><i class="fa fa-window-close-o red-text" onclick="cerrarCajaMaestra()"></i></a>';

                    if(Auth::user()->bodegas == 'si'){
                        $opciones = '<a href="#modal-operacion-caja" style="margin: 5px;" class="tooltipped modal-trigger" data-position="left" data-delay="50" data-tooltip="Operaciones de caja" ><i class="fa fa-money"></i></a>'
                            .'<a href="#" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Historial de operaciones caja" ><i class="fa fa-list-alt" onclick="cargarHistorialOperacionesCaja('.$value->id.')"></i></a>';
                        if(Auth::user()->admin_bodegas == 'no')
                            $opciones .= '<a href="#" style="margin: 5px;" class="tooltipped" data-position="left" data-delay="50" data-tooltip="Cerrar caja" ><i class="fa fa-window-close-o red-text" onclick="cerrarCajaMaestra()"></i></a>';
                    }
                }
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'fecha'=>$value->fecha,
                    'efectivo_inicial'=>"$ ".number_format($value->efectivo_inicial,2,'.','.'),
                    'efectivo_final'=>"$ ".number_format($value->efectivo_final,2,'.','.'),
                    'estado'=>$value->estado,
                    'usuario'=>$value->nombres.' '.$value->apellidos,
                    'opciones'=>$opciones
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $cajas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$cajas];

        return response()->json($data);

    }

    public function postStore(CreateCajaRequest $request){
        $user = Auth::user();
        $almacen_user = null;

        if($user->bodegas == 'si' && $user->admin_bodegas == 'no'){
            $almacen_user = Almacen::where('administrador',$user->id)->first();
        }
        if ((!Caja::cajasPermitidas()->where('estado', 'abierta')->first()
                || (!Caja::cajasPermitidas()->where('principal_ab','si')->where('estado', 'abierta')->first() && $user->admin_bodegas == 'si')
                || ($almacen_user && !Caja::cajasPermitidas()->where('almacen','si')->where('almacen_id',$almacen_user->id)->where('estado', 'abierta')->first())
            ) && Auth::user()->permitirFuncion("Iniciar mayor","caja","configuracion")) {

            $perfil = $user->perfil;

            if($user->bodegas == 'si' && $user->admin_bodegas == 'si')//Caja global del negocio
                $ultima_caja = Caja::cajasPermitidas()->where('principal_ab','si')->orderBy("caja.created_at","DESC")->first();
            else if($user->bodegas == 'si' && $user->admin_bodegas == 'no')
                $ultima_caja = Caja::cajasPermitidas()->where('almacen','si')->where('almacen_id',$almacen_user->id)->orderBy("caja.created_at","DESC")->first();
            else
                $ultima_caja = Caja::cajasPermitidas()->orderBy("caja.created_at","DESC")->first();

            $data = $request->all();

            if ($perfil->nombre == "usuario") {
                $usuarioCreador = User::select('usuario_creador_id')->where('id', $user->id)->first();
                $data['usuario_id'] = $usuarioCreador->usuario_creador_id;
            } else {
                if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')
                    $data['usuario_id'] = $user->userAdminId();
                else
                    $data['usuario_id'] = $user->id;
            }

            $data["usuario_creador_id"] = $user->id;
            DB::beginTransaction();
            if($ultima_caja) {
                $data['efectivo_inicial'] = $ultima_caja->efectivo_final;
                $data['efectivo_final'] = $ultima_caja->efectivo_final;
            }else{
                $data['efectivo_inicial'] = $request->get('efectivo_inicial');
                $data['efectivo_final'] = $request->get('efectivo_inicial');
            }
            $data["fecha"] = date("Y-m-d H:i:s");
            $caja = new Caja();
            $caja->fill($data);
            if($user->bodegas == 'si' && $user->admin_bodegas == 'si')
                $caja->principal_ab = 'si';
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no'){
                $caja->almacen = 'si';
                $caja->almacen_id = $almacen_user->id;
            }

            $caja->save();
            DB::commit();
            Session::flash("mensaje", "El efectivo inicial para el dia de hoy fue ingresado correctamente");
            return "1";
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }
    public function postStoreCaja(RequestCaja $request){
        if(Auth::user()->permitirFuncion("Crear","caja","configuracion")) {
            $data = $request->all();
            $user = Auth::user();
            $data["usuario_creador_id"] = $user->id;
            $data["usuario_id"] = $user->userAdminId();
            $data["estado"] = "cerrada";
            $caja = new Cajas();
            $caja->fill($data);
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no'){
                $almacen = Almacen::where('administrador',$user->id)->first();
                if(!$almacen)return response(["error"=>["No se encontraron almacenes relacionados con su información"]],422);

                $caja->almacen = 'si';
                $caja->almacen_id = $almacen->id;
            }
            $caja->save();
            Session::flash("mensaje", "La caja ha sido almacenda con éxito");
            return ["success" => true];
        }else{
            return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],422);
        }
    }
    public function postOperacionCaja(Request $request){

        DB::beginTransaction();
        $operacion_caja = OperacionCaja::saveOperacionCaja($request);
        if ($operacion_caja){
            $caja = Caja::updateCaja($request->get('tipo_movimiento'),$request->get('valor'));
        }
        DB::commit();

        $efectico_caja = Caja::cajasPermitidas()->where('estado',"abierta")->first();
        
        /*$compra = Compra::permitidos()->where("id", $request->get('tipo_abono_id'))->first();

        if ($compra) {
            $suma_abonos = $compra->abonos()->sum('valor');
            $saldo = $compra->valor - $suma_abonos;
            //DB::commit();
            $abonos = $compra->abonos()->orderby('id','DESC')->orderby('fecha','DESC')->get();
            return view('compras.abonos.lista')->with("compra", $compra)->with('abonos',$abonos)->with('saldo',$saldo)->with('efectivo_caja',$efectico_caja);
        }else{
            abort('503');
        }*/
        if (Auth::user()->permitirFuncion("Crear", "compras", "inicio")){
            return response()->json([
                'efectivo_caja' => $efectico_caja->efectivo_final

            ]);
        }
        return redirect("/");
    }
    public function postOperacionCajaCompra(Request $request){
        DB::beginTransaction();
        $operacion_caja = OperacionCaja::saveOperacionCaja($request);
        if ($operacion_caja){
            $caja = Caja::updateCaja($request->get('tipo_movimiento'),$request->get('valor'));
        }
        DB::commit();
        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        $efectico_caja = Caja::cajasPermitidas()->where('fecha',$fecha_actual)->take(1)->first();
        if (Auth::user()->permitirFuncion("Crear", "compras", "inicio")){
            return response()->json([
                'efectivo_caja' => $efectico_caja->efectivo_final

            ]);
        }
        return redirect("/");
    }
    public function postOperacionCajaCambioEstado(Request $request){

        DB::beginTransaction();
        $operacion_caja = OperacionCaja::saveOperacionCaja($request);
           if ($operacion_caja){
                $caja = Caja::updateCaja($request->get('tipo_movimiento'),$request->get('valor'));
            }

        DB::commit();
        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        $efectico_caja = Caja::cajasPermitidas()->where('fecha',$fecha_actual)->take(1)->first();
        if (Auth::user()->permitirFuncion("Crear", "compras", "inicio")){
            return response()->json([
                'efectivo_caja' => $efectico_caja->efectivo_final

            ]);
        }
        return redirect("/");
    }

    public function postFormEditar(Request $request){
        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja){
                return view("caja.form")->with("caja",$caja)->render();
            }
        }

        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postUpdateCaja(RequestCaja $request){

        if(Auth::user()->permitirFuncion("Editar","caja","configuracion")) {
            if($request->has("caja")) {
                if(Auth::user()->bodegas == 'si')
                    $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
                else
                    $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
                if($caja) {
                    $data = $request->all();

                    if(!$caja->permitirAccion()){
                        if($caja->estado != $data["estado"]) {
                            $historial = new CajaHistorialEstado();
                            $historial->estado_anterior = $caja->estado;
                            $historial->estado_nuevo = $data["estado"];
                            if($data["estado"] == "otro") {
                                //SE INACTIVAN TODOS LOS USUARIOS ASIGNADOS
                                $sql = "UPDATE cajas_usuarios SET estado = 'inactivo' where caja_id = ".$caja->id;
                                DB::statement($sql);
                                $historial->razon_estado = $data["razon_estado"];
                            }

                            $historial->caja_id = $caja->id;
                            $historial->usuario_id = Auth::user()->id;
                            $historial->save();
                            $caja->estado = $data["estado"];
                        }

                    }else{
                        $caja->nombre = $data["nombre"];
                        $caja->prefijo = $data["prefijo"];
                    }
                    $caja->save();
                    Session::flash("mensaje", "La caja ha sido editada con éxito");
                    return ["success" => true];
                }
            }
            return response(["error"=>["La información enviada es incorrecta"]],422);
        }else{
            return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],422);
        }
    }

    public function postDestroy(Request $request){

        if(Auth::user()->permitirFuncion("Eliminar","caja","configuracion")) {
            if($request->has("caja")) {
                if(Auth::user()->bodegas == 'si')
                    $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
                else
                    $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
                if($caja) {
                    if($caja->permitirAccion()) {
                        $caja->delete();
                        Session::flash("mensaje", "La caja ha sido eliminada con éxito");
                        return ["success" => true];
                    }
                }
            }
            return response(["error"=>["La información enviada es incorrecta"]],422);
        }else{
            return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],422);
        }
    }

    public function getAsignar(){
        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            return redirect('/caja');
        if(Auth::user()->permitirFuncion("Asignar","caja","configuracion")
            && (
                (Caja::cajasPermitidas()->where('estado', 'abierta')->first() && Auth::user()->bodegas == 'no')
                ||(Caja::cajasPermitidasAlmacen()->where('estado', 'abierta')->first() && Auth::user()->bodegas == 'si')
            )
        ) {
            if(Auth::user()->bodegas == 'si')
                $cajas = Cajas::permitidosAlmacen()->get();
            else
                $cajas = Cajas::permitidos()->get();

            $usuarios_no_permitidos = [];
            $usuarios_permisos = User::permitidos()->get();
            foreach ($cajas as $c) {
                $u = $c->relacionUsuarioActual();
                if ($u) $usuarios_no_permitidos[] = $u->id;
            }
            foreach ($usuarios_permisos as $us) {
                if(!$us->permitirFuncion("Crear","facturas","inicio"))
                    $usuarios_no_permitidos[] = $us->id;

                if(Auth::user()->bodegas == 'si'){
                    $almacen = Almacen::where('administrador',Auth::user()->id)->first();
                    if($almacen->id != $us->almacen_id && $us->perfil->nombre != 'administrador')$usuarios_no_permitidos[] = $us->id;
                }
            }


            $usuarios = User::permitidos()->whereNotIn("usuarios.id", $usuarios_no_permitidos)->get();
            if(Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'no') {
                if (Auth::user()->permitirFuncion("Crear", "facturas", "inicio") && !in_array(Auth::user()->id, $usuarios_no_permitidos)) {
                    $usuarios->push(Auth::user());
                }
            }else{
                if(Auth::user()->bodegas == 'no') {
                    $admin = User::find(Auth::user()->userAdminId());
                    if ($admin->permitirFuncion("Crear", "facturas", "inicio") && !in_array($admin->id, $usuarios_no_permitidos)) {
                        $usuarios->push($admin);
                    }
                }
            }

            if(Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si') {
                if (Auth::user()->permitirFuncion("Crear", "facturas", "inicio") && !in_array(Auth::user()->id, $usuarios_no_permitidos)) {
                    $usuarios->push(Auth::user());
                }
            }
            return view("caja.asignar")->with("usuarios", $usuarios)->with("cajas", $cajas);
        }else{
            return redirect("/caja");
        }
    }

    public function postAsignar(Request $request){
        if(Auth::user()->bodegas == 'si')
        $caja_mayor = Caja::cajasPermitidasAlmacen()->where('estado', 'abierta')->first();
        else
        $caja_mayor = Caja::cajasPermitidas()->where('estado', 'abierta')->first();

        if(Auth::user()->permitirFuncion("Asignar","caja","configuracion") && $caja_mayor) {
            if($request->has("usuario")){
                $usuario = User::permitidos()->where("usuarios.id",$request->input("usuario"))->first();

                //si se esta intentando asignar a un administrador
                if(Auth::user()->userAdminId() == $request->input("usuario")
                || (Auth::user()->bodegas == 'si' && Auth::user()->id == $request->input("usuario") && Auth::user()->perfil->nombre == 'administrador')){
                    $usuario = User::find($request->input("usuario"));
                }
                DB::beginTransaction();
                //SI NO SE ENVIA EL ID DE NINGUNA CAJA ES PORQUE SE ESTA QUITANDO A UN USUARIO QUE SE ENCONTRABA ASIGNADO A UNA CAJA
                //A CONTINUACIÓN SE DEBEN INACTIVAR LAS RELACIONES DE EL USUARIO ENVIADO CON CUALQUIER CAJA
                //SE DEBE PASAR EL VALOR FINAL A LA CAJA MAYOR
                if(!$request->has("caja")){
                    $relacion = Cajas::select("cajas_usuarios.*")
                        ->join("cajas_usuarios","cajas.id","=","cajas_usuarios.caja_id")
                        ->where("cajas_usuarios.usuario_id",$usuario->id)->where("cajas_usuarios.estado","activo")->first();
                    $caja_mayor->efectivo_final += $relacion->valor_final;
                    $caja_mayor->save();
                    $sql = "UPDATE cajas_usuarios SET estado = 'inactivo', efectivo_real=".$relacion->valor_final.",updated_at = '".date("Y-m-d H:i:s")."' where usuario_id = ".$usuario->id." AND estado = 'activo'";
                    DB::statement($sql);
                    DB::commit();
                    return ["success"=>true];
                }else{
                    if(Auth::user()->bodegas == 'si')
                    $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
                    else
                    $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();

                    if($caja && $caja->estado == "abierta"){
                        $relacion_actual = $caja->relacionUsuarioActual();
                        /*//SI TIENE UN USUARIO ASIGNADO
                        if($relacion_actual){
                            //EL MISMO USUARIO Y LA MISMA CAJA
                            if($relacion_actual->id == $usuario->id){
                                return ["success"=>true];
                            }else{
                                $caja_mayor->efectivo_final += $relacion_actual->valor_final;
                                //SE INACTIVAN TODAS LAS RELACIONES DEL USUARIO Y DE LA CAJA
                                $sql = "UPDATE cajas_usuarios SET estado = 'inactivo',updated_at = '".date("Y-m-d H:i:s")."' where usuario_id = ".$usuario->id
                                        .";UPDATE cajas_usuarios SET estado = 'inactivo',efectivo_real=".$relacion_actual->valor_final." where caja_id = ".$caja->id;
                                DB::statement($sql);
                            }
                        }else{*/

                            $relacion_usuario = Cajas::select("cajas_usuarios.*")
                                ->join("cajas_usuarios","cajas.id","=","cajas_usuarios.caja_id")
                                ->where("cajas_usuarios.estado","activo")
                                ->where("cajas_usuarios.usuario_id",$usuario->id)->first();
                            $valor_final = 0;
                            //SI EL USUARIO ESTA RELACIONADO CON OTRA CAJA
                            if($relacion_usuario && $relacion_usuario->caja_id != $caja->id){

                                $caja_mayor->efectivo_final += $relacion_usuario->valor_final;
                                $valor_final = $relacion_usuario->valor_final;
                                //SE INACTIVAN TODAS LAS RELACIONES DEL USUARIO CON CUALQUIER CAJA
                                $sql = "UPDATE cajas_usuarios SET estado = 'inactivo',efectivo_real=".$valor_final.",updated_at = '".date("Y-m-d H:i:s")."' where usuario_id = ".$usuario->id;
                                DB::statement($sql);
                            }

                            //si se esta relacionando con la misma caja
                            if($relacion_usuario && $relacion_usuario->caja_id == $caja->id){
                                return ["success"=>true];
                            }
                        //}

                        if($request->input("valor") > $caja_mayor->efectivo_final) {
                            Session::flash("mensaje_error", "No es posible utilizar el valor inicial ingresado para la caja, el efectivo actual en la caja mayor es $ ".number_format($caja_mayor->efectivo_final,2,',','.'));
                            return response(["error" => ["error"]], 422);
                        }
                        $caja->usuarios()->save($usuario,["valor_inicial"=>$request->input("valor"),"valor_final"=>$request->input("valor"),"estado"=>"activo","caja_mayor_id"=>$caja_mayor->id,"created_at"=>date("Y-m-d H:i:s"),"updated_at"=>date("Y-m-d H:i:s")]);
                        $caja_mayor->efectivo_final -= $request->input("valor");
                        $caja_mayor->save();
                        DB::commit();
                        return ["success"=>true];
                    }
                }
            }
        }else{
            return response(["error"=>["Usted no tiene permisos para realizar esta acción"]],422);
        }
        return response(["error"=>["La información enviada es incorrecta."]],422);
    }

    public function postHistorial(Request $request){
        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
             $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja){
                return view("caja.lista_historial",["caja"=>$caja])->render();
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getListaHistorial(Request $request){

        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();

            if($caja){

                // Datos de DATATABLE
                $search = $request->get("search");
                $order = $request->get("order");
                $sortColumnIndex = $order[0]['column'];
                $sortColumnDir = $order[0]['dir'];
                $length = $request->get('length');
                $start = $request->get('start');
                $columna = $request->get('columns');
                $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
                if($orderBy == "id"){
                    $orderBy = "cajas_usuarios.id";
                    $sortColumnDir = "DESC";
                }else if($orderBy == "usuario"){
                    $orderBy = "usuarios.nombres";
                }else{
                    $orderBy = "cajas_usuarios.".$orderBy;
                }

                $usuarios = $caja->usuarios()->select("usuarios.nombres","usuarios.apellidos","cajas_usuarios.*")
                    ->where("cajas_usuarios.estado","inactivo");
                    //->orderBy("cajas_usuarios.created_at","DESC");

                $usuarios = $usuarios->orderBy($orderBy, $sortColumnDir);
                if ($search['value'] != null) {
                    $f = "%".$search['value']."%";
                    $usuarios = $usuarios->where(
                        function($query) use ($f){
                            $query->where("usuarios.nombres","like",$f)
                                ->orWhere("usuarios.apellidos","like",$f)
                                ->orWhere("valor_inicial","like",$f)
                                ->orWhere("valor_final","like",$f)
                                ->orWhere("cajas_usuarios.created_at","like",$f)
                                ->orWhere("cajas_usuarios.updated_at","like",$f);
                        }
                    );
                }
                $parcialRegistros = $usuarios->count();
                $usuarios = $usuarios->skip($start)->take($length)->get();
                $totalRegistros = $usuarios->count();

                $object = new \stdClass();
                if($parcialRegistros > 0){
                    foreach ($usuarios as $value) {
                        $myArray[]=(object) array(
                            'id'=>$value->id,
                            'usuario'=>$value->nombres." ".$value->apellidos,
                            'valor_inicial'=>"$ ".number_format($value->valor_inicial,2,',','.'),
                            'valor_final'=>"$ ".number_format($value->valor_final,2,',','.'),
                            'valor_final_real'=>"$ ".number_format($value->efectivo_real,2,',','.'),
                            'created_at'=>"".$value->created_at,
                            'updated_at'=>"".$value->updated_at
                        );
                    }
                }else{
                    $myArray=[];
                }

                $data = ['length'=> $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $usuarios->toSql(),
                    'recordsTotal' =>$totalRegistros,
                    'recordsFiltered' =>$parcialRegistros,
                    'data' => $myArray,
                    'info' =>$usuarios];

                return response()->json($data);
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postHistorialEstados(Request $request){
        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja){
                return view("caja.lista_historial_estados",["caja"=>$caja])->render();
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getListaHistorialEstados(Request $request){

        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja){

                // Datos de DATATABLE
                $search = $request->get("search");
                $order = $request->get("order");
                $sortColumnIndex = $order[0]['column'];
                $sortColumnDir = $order[0]['dir'];
                $length = $request->get('length');
                $start = $request->get('start');
                $columna = $request->get('columns');
                $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
                if($orderBy == "id"){
                    $orderBy = "cajas_historial_estados.id";
                    $sortColumnDir = "DESC";
                }else if($orderBy == "usuario"){
                    $orderBy = "usuarios.nombres";
                }else{
                    $orderBy = "cajas_historial_estados.".$orderBy;
                }

                $historial = $caja->historial()->select("cajas_historial_estados.*","usuarios.nombres","usuarios.apellidos")
                    ->join("usuarios","cajas_historial_estados.usuario_id","=","usuarios.id");

                //->orderBy("cajas_usuarios.created_at","DESC");

                $historial = $historial->orderBy($orderBy, $sortColumnDir);
                if ($search['value'] != null) {
                    $f = "%".$search['value']."%";
                    $historial = $historial->where(
                        function($query) use ($f){
                            $query->where("usuarios.nombres","like",$f)
                                ->orWhere("usuarios.apellidos","like",$f)
                                ->orWhere("estado_anterior","like",$f)
                                ->orWhere("estado_nuevo","like",$f)
                                ->orWhere("razon_estado","like",$f)
                                ->orWhere("cajas_historial_estados.created_at","like",$f);
                        }
                    );
                }
                $parcialRegistros = $historial->count();
                $historial = $historial->skip($start)->take($length)->get();
                $totalRegistros = $historial->count();

                $object = new \stdClass();
                if($parcialRegistros > 0){
                    foreach ($historial as $value) {
                        $myArray[]=(object) array(
                            'id'=>$value->id,
                            'estado_anterior'=>$value->estado_anterior,
                            'estado_nuevo'=>$value->estado_nuevo,
                            'razon_estado'=>$value->razon_estado,
                            'usuario'=>$value->nombres." ".$value->apellidos,
                            'created_at'=>"".$value->created_at
                        );
                    }
                }else{
                    $myArray=[];
                }

                $data = ['length'=> $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $historial->toSql(),
                    'recordsTotal' =>$totalRegistros,
                    'recordsFiltered' =>$parcialRegistros,
                    'data' => $myArray,
                    'info' =>$historial];

                return response()->json($data);
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }


    public function postTransacciones(Request $request){
        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja){
                return view("caja.lista_transacciones",["caja"=>$caja])->render();
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getListaTransacciones(Request $request){

        if($request->has("caja")){
            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja){

                // Datos de DATATABLE
                $search = $request->get("search");
                $order = $request->get("order");
                $sortColumnIndex = $order[0]['column'];
                $sortColumnDir = $order[0]['dir'];
                $length = $request->get('length');
                $start = $request->get('start');
                $columna = $request->get('columns');
                $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
                if($orderBy == "id"){
                    $orderBy = "cajas_usuarios_transacciones.id";
                    $sortColumnDir = "DESC";
                }else if($orderBy == "usuario"){
                    $orderBy = "usuarios.nombres";
                }else{
                    $orderBy = "cajas_usuarios_transacciones.".$orderBy;
                }

                $transacciones = $caja->transacciones()->select("cajas_usuarios_transacciones.*","usuarios.nombres","usuarios.apellidos")
                    ->join("usuarios","cajas_usuarios.usuario_id","=","usuarios.id");

                //->orderBy("cajas_usuarios.created_at","DESC");

                $transacciones = $transacciones->orderBy($orderBy, $sortColumnDir);
                if ($search['value'] != null) {
                    $f = "%".$search['value']."%";
                    $transacciones = $transacciones->where(
                        function($query) use ($f){
                            $query->where("usuarios.nombres","like",$f)
                                ->orWhere("usuarios.apellidos","like",$f)
                                ->orWhere("tipo","like",$f)
                                ->orWhere("valor","like",$f)
                                ->orWhere("comentario","like",$f)
                                ->orWhere("cajas_usuarios_transacciones.created_at","like",$f);
                        }
                    );
                }
                $parcialRegistros = $transacciones->count();
                $transacciones = $transacciones->skip($start)->take($length)->get();
                $totalRegistros = $transacciones->count();

                $object = new \stdClass();
                if($parcialRegistros > 0){
                    foreach ($transacciones as $value) {
                        $myArray[]=(object) array(
                            'id'=>$value->id,
                            'tipo'=>$value->tipo,
                            'valor'=>"$ ".number_format($value->valor,0,',','.'),
                            'comentario'=>$value->comentario,
                            'usuario'=>$value->nombres." ".$value->apellidos,
                            'creador'=>$value->creador->nombres." ".$value->creador->apellidos,
                            'created_at'=>"".$value->created_at
                        );
                    }
                }else{
                    $myArray=[];
                }

                $data = ['length'=> $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $transacciones->toSql(),
                    'recordsTotal' =>$totalRegistros,
                    'recordsFiltered' =>$parcialRegistros,
                    'data' => $myArray,
                    'info' =>$transacciones];

                return response()->json($data);
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postAbrir(Request $request){
        if(Auth::user()->permitirFuncion("Editar","caja","configuracion")){
            if($request->has("caja")){
                if(Auth::user()->bodegas == 'si')
                    $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
                else
                    $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
                if($caja && ($caja->estado == "cerrada" || $caja->estado == "otro")){
                    $historial = new CajaHistorialEstado();
                    $historial->estado_anterior = $caja->estado;
                    $historial->estado_nuevo = "abierta";

                    $historial->caja_id = $caja->id;
                    $historial->usuario_id = Auth::user()->id;
                    $historial->save();

                    $caja->estado = "abierta";
                    $caja->save();
                    return ["success"=>true];
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postVistaCerrar(Request $request){
        if(Auth::user()->permitirFuncion("Editar","caja","configuracion")){
            if($request->has("caja")){
                if(Auth::user()->bodegas == 'si')
                    $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
                else
                    $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();

                $relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
                if($relacion) {
                    if ($caja && $caja->estado == "abierta") {
                        return view("caja.vista_cerrar")->with("caja", $caja)->with("relacion", $relacion)->render();
                    }
                }else{
                    $historial = new CajaHistorialEstado();
                    $historial->estado_anterior = $caja->estado;
                    $historial->estado_nuevo = "cerrada";

                    $historial->caja_id = $caja->id;
                    $historial->usuario_id = Auth::user()->id;
                    $historial->save();
                    
                    $caja->estado = "cerrada";
                    $caja->save();
                    return ["success"=>true];
                }
            }
        }
    }

    public function postCerrar(Request $request){
        if(Auth::user()->permitirFuncion("Editar","caja","configuracion")){
            if($request->has("caja")){
                if($request->has("efectivo_real")) {
                    if(Auth::user()->bodegas == 'si')
                        $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
                    else
                        $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
                    if ($caja && ($caja->estado == "abierta" || $caja->estado == "otro")) {
                        $relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
                        $sql = "UPDATE cajas_usuarios SET estado = 'inactivo',efectivo_real=" . $request->input("efectivo_real") . " where caja_id = " . $caja->id." AND estado = 'activo'";
                        DB::statement($sql);
                        if(Auth::user()->bodegas == 'si')
                            $caja_mestra = Caja::cajasPermitidasAlmacen()->where("estado","abierta")->first();
                        else
                            $caja_mestra = Caja::cajasPermitidas()->where("estado","abierta")->first();
                        $caja_mestra->efectivo_final += $request->input("efectivo_real");
                        $caja_mestra->save();

                        $historial = new CajaHistorialEstado();
                        $historial->estado_anterior = $caja->estado;
                        $historial->estado_nuevo = "cerrada";

                        $historial->caja_id = $caja->id;
                        $historial->usuario_id = Auth::user()->id;
                        $historial->save();

                        $caja->estado = "cerrada";
                        $caja->save();
                        return ["success" => true];
                    }
                }else{
                    return response(["error"=>["El campo efectivo real es requerido"]],422);
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postRealizarTransaccion(RequestCajaUsuarioTransaccion $request){

        if(Auth::user()->permitirFuncion("Editar","caja","configuracion") &&
            (
                (Caja::cajasPermitidas()->where("estado","abierta")->first() && Auth::user()->bodegas == 'no')
                || (Caja::cajasPermitidasAlmacen()->where("estado","abierta")->first())
            )

        ){

            if(Auth::user()->bodegas == 'si')
                $caja = Cajas::permitidosAlmacen()->where("cajas.id",$request->input("caja"))->first();
            else
                $caja = Cajas::permitidos()->where("cajas.id",$request->input("caja"))->first();
            if($caja) {
                if($caja->estado == "abierta") {
                    $relacion = $caja->usuarios()->select("cajas_usuarios.*")->where("cajas_usuarios.estado","activo")->first();
                    if($relacion) {
                        $error = false;
                        $errores = [];
                        $valor = intval($request->input("valor"));
                        if($valor < 1){
                            return response(["error"=>["Ingrese unicamente valores positivos en el campo valor"]],422);
                        }
                        DB::beginTransaction();
                        switch ($request->input("tipo")){
                            /*case 'Retiro':
                                if($valor > $relacion->valor_final){
                                    $errores[] = "No es posible retirar la cantidad de efectivo ingresada, valor máximo $ ".number_format($relacion->valor_final,0,',','.');
                                    $error = true;
                                }else{
                                    $relacion->valor_final -= $valor;
                                }
                                break;
                            case 'Deposito':
                                $relacion->valor_final += $valor;
                                break;*/
                            case 'Envio a caja maestra':
                                if($relacion->valor_final < $valor){
                                    return response(["error"=>["El valor máximo para enviar a la caja mestra es $ ".number_format($relacion->valor_final,0,',','.')]],422);
                                }

                                $relacion->valor_final -= $valor;
                                if(Auth::user()->bodegas == 'si')
                                    $caja_maestra = Caja::cajasPermitidasAlmacen()->where("estado","abierta")->first();
                                else
                                    $caja_maestra = Caja::cajasPermitidas()->where("estado","abierta")->first();
                                $caja_maestra->efectivo_final += $valor;
                                $caja_maestra->save();
                                break;
                        }

                        if($error){
                            return response(["error"=>$errores],422);
                        }
                        $sql = "UPDATE cajas_usuarios SET valor_final = ".$relacion->valor_final." where id = ".$relacion->id;
                        DB::statement($sql);
                        $transaccion = new CajaUsuarioTransaccion($request->all());
                        $transaccion->caja_usuario_id = $relacion->id;
                        $transaccion->usuario_creador_id = Auth::user()->id;
                        $transaccion->save();
                        DB::commit();
                        return ["success" => true];
                    }else{
                        return response(["error"=>["Actualmente no existe ningún cajero asociado a la caja"]],422);
                    }
                }else{
                    return response(["error"=>["El caja no se encuentra abierta, imposible realizar la transacción"]],422);
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postCerrarCajaMaestra(Request $request){
        $user = Auth::user();
        if($user->permitirFuncion("Iniciar mayor","caja","configuracion")){
            $caja_maestra = null;
            if(Auth::user()->bodegas == 'si'){
                if(Auth::user()->admin_bodegas == 'si') {
                    $caja_maestra = Caja::cajasPermitidas()->where('principal_ab','si')->where("estado", "abierta")->first();
                }else{
                    $almacen = Almacen::permitidos()->where('administrador',Auth::user()->id)->first();
                    if($almacen){
                        $caja_maestra = Caja::cajasPermitidas()->where('almacen','si')->where('almacen_id',$almacen->id)->where("estado", "abierta")->first();
                    }
                }
            }else{
                $caja_maestra = Caja::cajasPermitidas()->where("estado","abierta")->first();
            }
            if($caja_maestra){

                if($caja_maestra->almacen == 'si'){
                    $almacen = Almacen::permitidos()->where('id',$almacen->id)->first();

                    $cajas_activas = Cajas::permitidos()->join("cajas_usuarios","cajas.id","=","cajas_usuarios.caja_id")
                        ->where("cajas_usuarios.estado","activo")->where('cajas.almacen_id',$almacen->id)->get()->count();
                }else {
                    $cajas_activas = Cajas::permitidos()->join("cajas_usuarios", "cajas.id", "=", "cajas_usuarios.caja_id")
                        ->where("cajas_usuarios.estado", "activo")->get()->count();
                }
                if(!$cajas_activas){
                    DB::beginTransaction();
                    if($caja_maestra->almacen == 'si'){
                        $cajas_abiertas = Cajas::permitidos()->where("estado", "abierta")->where('almacen_id',$almacen->id)->get();
                    }else {
                        $cajas_abiertas = Cajas::permitidos()->where("estado", "abierta")->get();
                    }

                    //se guarda historial de todas las cajas abiertas que ahora serán cerradas
                    foreach ($cajas_abiertas as $c_a){
                        $historial = new CajaHistorialEstado();
                        $historial->estado_anterior = $c_a->estado;
                        $historial->estado_nuevo = "cerrada";

                        $historial->caja_id = $c_a->id;
                        $historial->usuario_id = Auth::user()->id;
                        $historial->save();
                    }

                    if($caja_maestra->almacen == 'si') {
                        $sql = "UPDATE cajas SET estado = 'cerrada' where almacen_id = " . $almacen->id . " AND estado = 'abierta'";
                    }else{
                        $sql = "UPDATE cajas SET estado = 'cerrada' where usuario_id = ".$user->userAdminId()." AND estado = 'abierta'";
                    }
                    DB::statement($sql);
                    $caja_maestra->estado = "cerrada";
                    $caja_maestra->save();
                    DB::commit();
                    Session::flash("mensaje","La caja maestra ha sido cerrada con éxito");
                    return ["success"=>true];
                }else{
                    return response(["error"=>["No es posible cerrar la caja maestra, existen cajas activas con usuarios asignados actualmente"]],422);
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postOperacionCajaMaestra(RequestOperacionCajaMaestra $request){
        $user = Auth::user();
        DB::beginTransaction();
        if($user->permitirFuncion("Iniciar mayor","caja","configuracion")){

            $caja_maestra = null;
            if(Auth::user()->bodegas == 'si'){
                if(Auth::user()->admin_bodegas == 'si') {
                    $caja_maestra = Caja::cajasPermitidas()->where('principal_ab','si')->where("estado", "abierta")->first();
                }else{
                    $almacen = Almacen::permitidos()->where('administrador',Auth::user()->id)->first();
                    if($almacen){
                        $caja_maestra = Caja::cajasPermitidas()->where('almacen','si')->where('almacen_id',$almacen->id)->where("estado", "abierta")->first();
                    }
                }
            }else{
                $caja_maestra = Caja::cajasPermitidas()->where("estado","abierta")->first();
            }
            if($caja_maestra){
                $cuenta = CuentaBancaria::permitidos()->where("id",$request->input("cuenta_bancaria"))->first();
                if($cuenta){
                    $data = $request->all();

                    if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no')
                        $data['tipo'] = 'Consignación';

                    $data["cuenta_banco_id"] = $cuenta->id;
                    $data["caja_id"] = $caja_maestra->id;
                    $data["usuario_creador_id"] = Auth::user()->id;
                    $movimiento = new MovimientoCajaBanco($data);
                    $movimiento->save();



                    if($data["tipo"] == "Retiro"){
                        if($cuenta->saldo < $data["valor"])
                            return response(["error"=>["El valor ingresado debe ser menor o igual al saldo de la cuenta."]],422);
                        $cuenta->saldo -= $data["valor"];
                        $caja_maestra->efectivo_final += $data["valor"];
                    }else if($data["tipo"] == "Consignación"){
                        if($caja_maestra->efectivo_final < $data["valor"])
                            return response(["error"=>["El valor ingresado debe ser menor o igual al efectivo existente en la caja maestra."]],422);
                        $cuenta->saldo += $data["valor"];
                        $caja_maestra->efectivo_final -= $data["valor"];
                    }
                    $movimiento->saldo = $cuenta->saldo;
                    $movimiento->save();
                    $caja_maestra->save();
                    $cuenta->save();
                    DB::commit();
                    Session::flash("mensaje","La operación de caja ha sido registrada con éxito");
                    return ["success"=>true];
                }
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function postHistorialOperacionesCaja(Request $request){
        if($request->has("caja")){
            $caja = Caja::cajasPermitidas()->where("caja.id",$request->input("caja"))->first();
            if($caja){
                return view("caja.lista_operaciones_caja",["caja"=>$caja])->render();
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getListaHistorialOperacionesCaja(Request $request){

        if($request->has("caja")){
            $caja = Caja::cajasPermitidas()->where("caja.id",$request->input("caja"))->first();
            if($caja){

                // Datos de DATATABLE
                $search = $request->get("search");
                $order = $request->get("order");
                $sortColumnIndex = $order[0]['column'];
                $sortColumnDir = $order[0]['dir'];
                $length = $request->get('length');
                $start = $request->get('start');
                $columna = $request->get('columns');
                $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
                if($orderBy == "banco"){
                    $orderBy = "bancos.nombre";
                }else if($orderBy == "numero"){
                    $orderBy = "cuentas_bancos.numero";
                }else if($orderBy == "usuario"){
                    $orderBy = "usuarios.nombres";
                }else{
                    $orderBy = "movimientos_cajas_bancos.".$orderBy;
                }

                $historial = MovimientoCajaBanco::select("movimientos_cajas_bancos.*","bancos.nombre","usuarios.nombres","usuarios.apellidos","cuentas_bancos.numero")
                    ->join("usuarios","movimientos_cajas_bancos.usuario_creador_id","=","usuarios.id")
                    ->join("cuentas_bancos","movimientos_cajas_bancos.cuenta_banco_id","=","cuentas_bancos.id")
                    ->join("bancos","cuentas_bancos.banco_id","=","bancos.id")
                    ->where("movimientos_cajas_bancos.caja_id",$caja->id);

                //->orderBy("cajas_usuarios.created_at","DESC");

                $historial = $historial->orderBy($orderBy, $sortColumnDir);
                if ($search['value'] != null) {
                    $f = "%".$search['value']."%";
                    $historial = $historial->where(
                        function($query) use ($f){
                            $query->where("usuarios.nombres","like",$f)
                                ->orWhere("usuarios.apellidos","like",$f)
                                ->orWhere("movimientos_cajas_bancos.created_at","like",$f)
                                ->orWhere("movimientos_cajas_bancos.observacion","like",$f)
                                ->orWhere("movimientos_cajas_bancos.valor","like",$f)
                                ->orWhere("movimientos_cajas_bancos.tipo","like",$f)
                                ->orWhere("cuentas_bancos.numero","like",$f)
                                ->orWhere("bancos.nombre","like",$f);
                        }
                    );
                }
                $parcialRegistros = $historial->count();
                $historial = $historial->skip($start)->take($length)->get();
                $totalRegistros = $historial->count();

                $object = new \stdClass();
                if($parcialRegistros > 0){
                    foreach ($historial as $value) {
                        $myArray[]=(object) array(
                            'banco'=>$value->nombre,
                            'numero'=>$value->numero,
                            'tipo'=>$value->tipo,
                            'valor'=>'$ '.number_format($value->valor,2,',','.'),
                            'observacion'=>$value->observacion,
                            'created_at'=>' '.$value->created_at,
                            'usuario'=>$value->nombres." ".$value->apellidos,
                        );
                    }
                }else{
                    $myArray=[];
                }

                $data = ['length'=> $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $historial->toSql(),
                    'recordsTotal' =>$totalRegistros,
                    'recordsFiltered' =>$parcialRegistros,
                    'data' => $myArray,
                    'info' =>$historial];

                return response()->json($data);
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getAlmacen(){
        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si'){
            $caja = Caja::cajasPermitidas()->where('principal_ab','si')->where('estado', 'abierta')->first();
            if($caja){
                return view('caja.almacen')->with('caja',$caja);
            }
        }
        return redirect('/caja');
    }

    public function getListCajaAlmacenes(Request $request)
    {
        // Datos de DATATABLE
        $search = $request->get("search");
        $order = $request->get("order");
        $sortColumnIndex = $order[0]['column'];
        $sortColumnDir = $order[0]['dir'];
        $length = $request->get('length');
        $start = $request->get('start');
        $columna = $request->get('columns');
        $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
        if($orderBy == "fecha"){
            if($sortColumnDir == "desc")$sortColumnDir = "asc";
            else$sortColumnDir = "desc";
        }
        if($orderBy == "almacen")$orderBy = "vendiendo_alm.almacenes.nombre";

        $cajas = Almacen::permitidos()->select("v1.*","vendiendo_alm.almacenes.nombre","vendiendo_alm.almacenes.id as almacen_id")
            //->leftJoin("vendiendo.caja","vendiendo.caja.almacen_id","=","vendiendo_alm.almacenes.id")
            ->leftJoin(DB::raw('(select * from vendiendo.caja where vendiendo.caja.id in (select MAX(id) from vendiendo.caja as c1 where !ISNULL(vendiendo.caja.almacen_id) GROUP BY c1.almacen_id )) as v1'),"v1.almacen_id","=","vendiendo_alm.almacenes.id");
        //dd($cajas->toSql());
        $cajas = $cajas->orderBy($orderBy, $sortColumnDir);
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $cajas = $cajas->where(
                function($query) use ($f){
                    $query->where("vendiendo.caja.efectivo_inicial","like",$f)
                        ->orWhere("vendiendo.caja.efectivo_final","like",$f)
                        ->orWhere("vendiendo_alm.almacenes.nombre","like",$f)
                        ->orWhere("vendiendo.caja.fecha","like",$f);
                }
            );
        }
        $cajas = $cajas->skip($start)->take($length)->get();

        $totalRegistros = $cajas->count();
        $parcialRegistros = $cajas->count();
        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($cajas as $value) {
                $opciones = '';
                if($value->estado == "abierta"){
                    $opciones = '<a href="#" data-almacen="'.$value->almacen_id.'" style="margin: 5px;" class="tooltipped movimiento-caja-maestra" data-position="left" data-delay="50" data-tooltip="Enviar efectivo" ><i class="fa fa-usd"></i></a>';
                    $opciones .= '<a href="#" data-caja="'.$value->id.'" style="margin: 5px;" class="tooltipped lista-movimientos-cajas-maestras" data-position="left" data-delay="50" data-tooltip="Movimientos de caja" ><i class="fa fa-list-alt"></i></a>';
                }else{
                    if(!Caja::where('almacen_id',$value->almacen_id)->get()->count())
                        $opciones = '<a href="#" data-almacen="'.$value->almacen_id.'" style="margin: 5px;" class="tooltipped iniciar-caja-almacen" data-position="left" data-delay="50" data-tooltip="Iniciar" ><i class="fa fa-play-circle"></i></a>';

                }
                $myArray[]=(object) array(
                    'id'=>$value->almacen_id,
                    'almacen'=>$value->nombre,
                    'fecha'=>$value->fecha,
                    'efectivo_inicial'=>"$ ".number_format($value->efectivo_inicial,2,'.','.'),
                    'efectivo_final'=>"$ ".number_format($value->efectivo_final,2,'.','.'),
                    'opciones'=>$opciones
                );
            }
        }else{
            $myArray=[];
        }

        $data = ['length'=> $length,
            'start' => $start,
            'buscar' => $search['value'],
            'draw' => $request->get('draw'),
            //'last_query' => $cajas->toSql(),
            'recordsTotal' =>$totalRegistros,
            'recordsFiltered' =>$parcialRegistros,
            'data' => $myArray,
            'info' =>$cajas];

        return response()->json($data);

    }

    public function postIniciarCajaAlmacen(Request $request)
    {
        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si'){
            $caja_principal = Caja::cajasPermitidas()->where('principal_ab','si')->where('estado','abierta')->first();
            if(!$caja_principal)return response(['error'=>['No se ha inicializado la caja principal en el sistema']],422);

            if($request->has('valor') && is_numeric($request->input('valor')) && $request->has('almacen')){
                $caja_almacen = Caja::where('almacen','si')
                    ->where('almacen_id',$request->input('almacen'))
                    ->where('estado','abierta')->first();
                if(!$caja_almacen){
                    $almacen = Almacen::permitidos()->where('id',$request->input('almacen'))->first();

                    if($almacen) {

                        if (($caja_principal->efectivo_final - $request->input('valor')) >= 0) {
                            $caja_almacen = new Caja();
                            $caja_almacen->efectivo_inicial = $request->input('valor');
                            $caja_almacen->efectivo_final = $request->input('valor');
                            $caja_almacen->estado = 'abierta';
                            $caja_almacen->usuario_id = Auth::user()->userAdminid();
                            $caja_almacen->usuario_creador_id = Auth::user()->id;
                            $caja_almacen->fecha = date('Y-m-d H:i:s');
                            $caja_almacen->almacen = 'si';
                            $caja_almacen->almacen_id = $almacen->id;
                            $caja_almacen->save();

                            $caja_principal->efectivo_final -= $request->input('valor');
                            $caja_principal->save();

                            return ['success'=>true];
                        } else {
                            return response(['error' => ['El valor ingresado es incorrecto, la valor máximo permitido es $ ' . number_format($caja_principal->efectivo_final, 2, ',', '.')]], 422);
                        }
                    }
                }

            }

            return response(['error'=>['La infoarción enviada es incorrecta']],422);
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }
    }

    public function postRealizarMovimientoCajaMaestra(Request $request)
    {
        if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si'){

            if(!$request->has('valor'))return response(['error'=>['El campo valor es requerido']],422);
            if(!is_numeric($request->input('valor')))return response(['error'=>['El campo valor debe ser numérico']],422);

            if(!$request->has('almacen_destinatario'))return response(['error'=>['La información enviada es incorrecta']],422);

            $caja_principal = Caja::cajasPermitidas()->where('principal_ab','si')->where('estado','abierta')->first();

            $caja_destinataria = Caja::cajasPermitidas()->where('principal_ab','no')->where('estado','abierta')->where('almacen_id',$request->input('almacen_destinatario'))->first();

            if($caja_principal && $caja_destinataria){
                if($caja_principal->efectivo_final - $request->input('valor') < 0)
                    return response(['error'=>['La cantidad de dinero que desea enviar es incorrecta. El saldo actual en caja es $ '.number_format($caja_principal->efectivo_final,0,',','.')]],422);
                $historial = new MovimientoCajaMaestra();
                $historial->valor = $request->input('valor');
                if($request->has('observacion'))
                    $historial->observacion = $request->input('observacion');
                $historial->caja_remitente_id = $caja_principal->id;
                $historial->caja_destinataria_id = $caja_destinataria->id;
                $historial->save();

                $caja_principal->efectivo_final -= $request->input('valor');
                $caja_destinataria->efectivo_final += $request->input('valor');
                $caja_principal->save();
                $caja_destinataria->save();
                return ['success'=>true];
            }


            return response(['error'=>['La infoarción enviada es incorrecta']],422);
        }else{
            return response(['error'=>['Unauthorized.']],401);
        }
    }

    public function postHistorialMovimientosCajaMaestra(Request $request){
        if($request->has("caja")){
            $caja = Caja::cajasPermitidas()->where("caja.id",$request->input("caja"))->first();
            if($caja){
                return view("caja.lista_movimientos_caja_maestra",["caja"=>$caja])->render();
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }

    public function getListaHistorialMovimientosCajaMaestra(Request $request){

        if($request->has("caja")){
            $caja = Caja::cajasPermitidas()->where("caja.id",$request->input("caja"))->first();
            if($caja){

                // Datos de DATATABLE
                $search = $request->get("search");
                $order = $request->get("order");
                $sortColumnIndex = $order[0]['column'];
                $sortColumnDir = $order[0]['dir'];
                $length = $request->get('length');
                $start = $request->get('start');
                $columna = $request->get('columns');
                $orderBy = $columna[$sortColumnIndex]['data'];//'facturas.id';
                if($orderBy == "fecha"){
                    $orderBy = "movimientos_cajas_maestras.created_at";
                }

                $historial = MovimientoCajaMaestra::where("movimientos_cajas_maestras.caja_destinataria_id",$caja->id);

                //->orderBy("cajas_usuarios.created_at","DESC");

                $historial = $historial->orderBy($orderBy, $sortColumnDir);
                if ($search['value'] != null) {
                    $f = "%".$search['value']."%";
                    $historial = $historial->where(
                        function($query) use ($f){
                            $query->where("created_at","like",$f)
                                ->orWhere("valor","like",$f)
                                ->orWhere("observacion","like",$f);
                        }
                    );
                }
                $parcialRegistros = $historial->count();
                $historial = $historial->skip($start)->take($length)->get();
                $totalRegistros = $historial->count();

                $object = new \stdClass();
                if($parcialRegistros > 0){
                    foreach ($historial as $value) {
                        $myArray[]=(object) array(
                            'fecha'=>' '.$value->created_at,
                            'valor'=>'$ '.number_format($value->valor,2,',','.'),
                            'observacion'=>$value->observacion,
                        );
                    }
                }else{
                    $myArray=[];
                }

                $data = ['length'=> $length,
                    'start' => $start,
                    'buscar' => $search['value'],
                    'draw' => $request->get('draw'),
                    //'last_query' => $historial->toSql(),
                    'recordsTotal' =>$totalRegistros,
                    'recordsFiltered' =>$parcialRegistros,
                    'data' => $myArray,
                    'info' =>$historial];

                return response()->json($data);
            }
        }
        return response(["error"=>["La información enviada es incorrecta"]],422);
    }
}
