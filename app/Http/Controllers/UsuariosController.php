<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Almacen;
use App\Models\Caja;
use App\Models\Cajas;
use App\Models\Categoria;
use App\Models\Cliente;
use App\Models\Perfil;
use App\Models\Plan;
use App\Models\PlanUsuario;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RequestNuevoUsuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;

class UsuariosController extends Controller {



    public function __construct(){
        $this->middleware("auth");
        if(Auth::user()->perfil->nombre != "superadministrador")
            $this->middleware("modConfiguracion");
        $this->middleware("modUsuario",["except"=>["postCambiarContrasena"]]);
        $this->middleware("terminosCondiciones");
    }
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getIndex(Request $request)
    {
        $filtro = "";
        if($request->has("filtro")){
            $usuarios = $this->listaFiltro($request->get("filtro"));
            $filtro = $request->get("filtro");
        }else {
            $usuarios = User::permitidos()->orderBy("updated_at", "DESC")->paginate(10);
        }
        return view('usuario.index')->with("usuarios",$usuarios)->with("filtro",$filtro);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function getCreate()
    {
        if(Auth::user()->permitirFuncion("Crear","usuarios","configuracion"))
            if(Auth::user()->perfil->nombre == "superadministrador" || Auth::user()->plan()->n_usuarios == 0 || Auth::user()->plan()->n_usuarios > Auth::user()->countUsuariosAdministrador())
                return view('usuario.create')->with("usuario",new User());

        return redirect("/");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function postStore(RequestNuevoUsuario $request)
    {
        if(Auth::user()->permitirFuncion("Crear","usuarios","configuracion")) {
            if(Auth::user()->perfil->nombre == "superadministrador" || Auth::user()->plan()->n_usuarios == 0 || Auth::user()->plan()->n_usuarios > Auth::user()->countUsuariosAdministrador()) {
                if ((Auth::user()->perfil->nombre == "superadministrador" || (Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si')) && !$request->has("perfil")) {
                    return response(['error' => ['El campo perfil es requerido']], 422);
                }

                /*if((Auth::user()->perfil->nombre == "administrador" && Auth::user()->bodegas == 'si') && !$request->has("almacen"))
                    return response(['error' => ['El campo almacén es requerido']], 422);*/

                $input_contrasena = false;
                if($request->has('password')){
                    if(!$request->has('password_confirm')){
                        return response(['error' => ['El campo de verificación de contraseña es requerido']], 422);
                    }else{
                        if($request->input('password') != $request->input('password_confirm')){
                            return response(['error' => ['La contraseña y su verificación no coinciden']], 422);
                        }
                    }
                    $input_contrasena = true;
                }

                DB::beginTransaction();
                $perfil = new Perfil();
                if (Auth::user()->perfil->nombre == "superadministrador") {
                    $perfil = Perfil::find($request->input("perfil"));

                    if ($perfil) {
                        if (($perfil->nombre == "administrador" || $perfil->nombre == "proveedor" || $perfil->nombre == "superadministrador") && (Auth::user()->perfil->nombre != "superadministrador")) {
                            return response(['error' => ['La información enviada es incorrecta']], 422);
                        }

                        if ($perfil->nombre != "administrador" && $perfil->nombre != "superadministrador" && $perfil->nombre != "proveedor") {
                            return response(['error' => ['La información enviada es incorrecta']], 422);
                        }
                    }
                }

                if (Auth::user()->bodegas == 'si' ) {
                    $perfil = Perfil::find($request->input("perfil"));

                    if ($perfil) {
                        if ($perfil->nombre != "administrador" && $perfil->nombre != "usuario" ) {
                            return response(['error' => ['La información enviada es incorrecta']], 422);
                        }
                    }

                    if($perfil->nombre == "usuario" ) {
                        $almacen = Almacen::permitidos()->where('id', $request->input('almacen'))->first();

                        if (!$almacen) {
                            return response(['error' => ['La información enviada es incorrecta']], 422);
                        }
                    }
                }

                $usuario = new User();
                $user_mail = User::where("email", $request->input("email"))->first();
                if ($user_mail && $user_mail->exists) {
                    return response(['error' => ['El email ingresado ya existe en el sistema']], 422);
                }
                $data = $request->all();
                if (Auth::user()->bodegas == 'si' ) {
                    $data['bodegas'] = 'si';
                }
                if (Auth::user()->perfil->nombre == "superadministrador") {
                    if ($perfil->nombre == "administrador") {
                        $data["categoria_id"] = $data["categoria"];
                        $data["permiso_reportes"] = "si";
                        if(array_key_exists('bodegas',$data)){
                            $data["admin_bodegas"] = "si";
                        }
                    }
                    $usuario->perfil_id = $perfil->id;
                } else {
                    if(Auth::user()->bodegas == "si"){
                        $usuario->perfil_id = $perfil->id;
                    }else{
                        $usuario->perfil_id = Perfil::where("nombre", "usuario")->first()->id;
                    }
                }

                if(array_key_exists('nit',$data) && $data['nit'] == '')unset($data['nit']);
                $usuario->fill($data);

                //Si es un usuario el usuario creador es el administrador (Aunque puede ser creado por un usuario normal)
                if (Auth::user()->perfil->nombre == "usuario")
                    $usuario->usuario_creador_id = Auth::user()->usuario_creador_id;
                else
                    $usuario->usuario_creador_id = Auth::user()->userAdminId();

                if(!$input_contrasena){
                    $clave = $usuario->generarClave();
                }else{
                    $clave = $request->input('password');
                    $usuario->password = Hash::make($clave);
                }

                if(Auth::user()->bodegas == 'si' && $perfil->nombre == 'usuario'){
                    $usuario->almacen_id = $almacen->id;
                }

                $usuario->save();

                /*
                     Se agrega el plan 2 por defecto
                 */
                if (Auth::user()->bodegas != 'si' && ($usuario->perfil->nombre == "administrador" || $usuario->perfil->nombre == "proveedor")) {

                    $plan = null;
                    if ($usuario->perfil->nombre == "administrador") {
                        $plan = Plan::find($request->input("plan"));
                        //if($plan->cliente_predeterminado == "si") {
                        $cliente = new Cliente();
                        $cliente->nombre = "Predeterminado";
                        $cliente->predeterminado = "si";
                        $cliente->usuario_creador_id = $usuario->id;
                        $cliente->usuario_id = $usuario->id;
                        $cliente->save();
                        //}
                    }

                    if ($usuario->perfil->nombre == "proveedor") {
                        $usuario->municipio_id = $request->input("municipio");
                        $usuario->save();
                        foreach ($request->input("categorias") as $c) {
                            $categoria = Categoria::where("negocio", "si")->where("id", $c)->first();
                            if ($categoria) {
                                $usuario->categorias()->save($categoria);
                            }
                        }
                        $plan = Plan::where("nombre", "proveedor")->first();//debe existir este plan en la DB
                    }

                    $planUsuario = new PlanUsuario();
                    $planUsuario->usuario_id = $usuario->id;
                    $planUsuario->plan_id = $plan->id;
                    $planUsuario->estado = "activo";
                    $hasta = date("Y-m-d H:i:s", strtotime("+" . $plan->duracion . "month", strtotime(date("Y-m-d H:i:s"))));
                    $planUsuario->hasta = $hasta;
                    $planUsuario->save();
                }


                $data = ["usuario" => $usuario, "clave" => $clave];
                Session::flash("mensaje", "Usuario creado con éxito");
                Mail::send('emails.nueva_cuenta', $data, function ($m) use ($usuario) {
                    $m->from('notificaciones@vendiendo.co', 'Vendiendo.co');

                    $m->to($usuario->email, $usuario->nombres . " " . $usuario->apellidos)->subject('Vendiendo.co - cuenta de usuario');
                });
                DB::commit();
                return ["success" => true];
            }else{
                return response(['error' => ['No es posible crear más usuarios, su plan permite crear máximo '.Auth::user()->plan()->n_usuarios.' usuarios.']], 422);
            }
        }else{
            return response(['error' => ['Usted no tiene permisos para realizar esta acción']], 422);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function getEdit($id)
    {
        if(Auth::user()->permitirFuncion("Editar","usuarios","configuracion")) {
            $usuario = User::permitidos()->where("usuarios.id", $id)->first();
            if ($usuario && $usuario->exists)
                return view('usuario.edit')->with("usuario", $usuario);
        }
        return redirect("/");
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function postUpdate(RequestNuevoUsuario $request)
    {
        if(Auth::user()->permitirFuncion("Editar","usuarios","configuracion")) {
            DB::beginTransaction();
            $id = $request->input("id");
            $usuario = User::permitidos()->where("usuarios.id",$id)->first();
            if($usuario && $usuario->exists){

                if($request->has("plan") && $usuario->perfil->nombre == "administrador"){
                    $plan = Plan::find($request->input("plan"));
                    if($plan){
                        $plan_actual = $usuario->plan();
                        if(!$plan_actual){
                            DB::statement("UPDATE planes_usuarios SET estado = 'inactivo' WHERE usuario_id = ".$usuario->userAdminId());
                            $planUsuario = new PlanUsuario();
                            $planUsuario->usuario_id = $usuario->id;
                            $planUsuario->plan_id = $plan->id;
                            $planUsuario->estado = "activo";
                            $hasta = date("Y-m-d H:i:s", strtotime("+" . $plan->duracion . "month", strtotime(date("Y-m-d H:i:s"))));
                            $planUsuario->hasta = $hasta;
                            $planUsuario->save();

                            DB::statement("UPDATE usuarios_modulos SET hasta = '".$hasta."' WHERE estado = 'activo' AND usuario_id = ".$usuario->userAdminId());

                            $usuarios = User::where('usuario_creador_id',$usuario->id)->get();
                            if(count($usuarios)) {
                                $ids = '';
                                foreach ($usuarios as $u) {
                                    $ids .= $u->id.',';
                                }

                                $ids = substr($ids, 0, -1);

                                DB::statement("UPDATE usuarios_modulos SET hasta = '".$hasta."' WHERE estado = 'activo' AND usuario_id IN (".$ids.")");
                                DB::statement("UPDATE usuarios_funciones SET hasta = '".$hasta."' WHERE estado = 'activo' AND usuario_id IN (".$ids.")");
                            }
                        }else{
                            if($plan_actual->id != $plan->id){
                                $planUsuario = PlanUsuario::where("usuario_id",$usuario->userAdminId())->where("estado","activo")->first();
                                if($planUsuario) {
                                    $planUsuario->estado = "inactivo";
                                    $planUsuario->save();

                                    $planUsuarioNew = new PlanUsuario();
                                    $planUsuarioNew->usuario_id = $usuario->id;
                                    $planUsuarioNew->plan_id = $plan->id;
                                    $planUsuarioNew->estado = "activo";
                                    $planUsuarioNew->hasta = $planUsuario->hasta;
                                    if($request->has("nueva_fecha")){
                                        $planUsuarioNew->hasta = $request->input("nueva_fecha");
                                    }

                                    $planUsuarioNew->save();
                                }else{
                                    return response(['error' => ['La información enviada es incorrecta']], 422);
                                }
                            }else{//si no se cambia de plan es posible actualizar la fecha del plan actual
                                $planUsuario = PlanUsuario::where("usuario_id",$usuario->userAdminId())->where("estado","activo")->first();
                                if($request->has("nueva_fecha") && $planUsuario->hasta != $request->input("nueva_fecha")){
                                    $planUsuario->hasta = $request->input("nueva_fecha");
                                    $planUsuario->save();
                                }
                            }
                        }
                        $admin = User::find($usuario->userAdminId());
                        $admin->notificacion_renovar_plan = "no";
                        $admin->save();
                    }else{
                        return response(['error' => ['La información enviada es incorrecta']], 422);
                    }
                }

                $data = $request->all();
                if($usuario->perfil->nombre == "proveedor"){
                    $data["municipio_id"] = $data["municipio"];
                }
                $usuario->update($data);
                if(Auth::user()->perfil->nombre == "superadministrador" && $usuario->bodegas == 'no' && $usuario->perfil->nombre == 'administrador'){
                    if($request->has('v_bodegas_almacenes') && $request->input('v_bodegas_almacenes')){
                        //se acumula el dinero de las cajas
                        $efectivo = 0;
                        $cajas = Cajas::permitidos()->where('estado','abierta')->where('almacen','no')->get();
                        foreach ($cajas as $c){
                            $caja_usuario = $c->usuarios()->select('cajas_usuarios.id','cajas_usuarios.valor_final')->where('cajas_usuarios.estado','activo')->get();
                            foreach ($caja_usuario as $c_u){
                                //se acumula el valor reacudado en la caja
                                $efectivo += $c_u->valor_final;
                                //se cierra la relacion de la caja con el usuario
                                $sql = 'UPDATE cajas_usuarios SET efectivo_real='.$c_u->valor_final.', estado = "inactivo" where id='.$c_u->id;
                                DB::statement($sql);
                            }
                            //se cierra la caja
                            $c->estado = 'cerrada';
                            $c->save();
                        }
                        //se pasa el efectivo a la caja maestra y establece para bodegas
                        $caja_maestra = Caja::cajasPermitidas()->orderBy('created_at','DESC')->first();
                        if($caja_maestra){
                            $caja_maestra->principal_ab = 'si';
                            if($caja_maestra->estado == 'abierta')
                                $caja_maestra->efectivo_final += $efectivo;
                            $caja_maestra->save();
                        }
                        $usuario->bodegas = 'si';
                        $usuario->admin_bodegas = 'si';
                        $usuario->cambio_bodegas_almacenes = 'si';
                        $usuarios = User::where('usuario_creador_id',$usuario->id)->get();
                        if(count($usuarios)) {
                            foreach ($usuarios as $u) {
                                $u->bodegas = 'si';
                                $u->admin_bodegas = 'no';
                                $u->save();
                            }
                        }else{
                            $usuario->configuracion_usuarios = 'si';
                        }
                        $usuario->save();
                    }
                }
                Session::flash("mensaje","Usuario editado con éxito");
                DB::commit();
                return ["success"=>true,"href"=>url('/usuario')];
            }else{
                return response(['error' => ['Usted no tiene permisos para realizar esta acción']], 422);
            }
        }else{
            return response(['error' => ['Usted no tiene permisos para realizar esta acción']], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function postDestroy($id)
    {
        if(Auth::user()->permitirFuncion("Eliminar","usuarios","configuracion")) {
            $usuario = User::permitidos()->where("usuarios.id",$id)->first();
            if ($usuario && $usuario->exists) {
                $usuario->delete();
                Session::flash("mensaje", "El usuario ha sido eliminado con éxito");
                return ["success" => true];
            } else {
                return ["error" => "La información es incorrecta"];
            }
        }
        return response("Unauthorized",401);
    }

    public function postFiltro(Request $request){
        if($request->has("filtro")){
            $usuarios = $this->listaFiltro($request->get("filtro"));
        }else{
            $usuarios = User::permitidos()->orderBy("usuarios.updated_at","DESC")->paginate(10);
        }
        $usuarios->setPath(url('/usuario'));
        return view("usuario.lista")->with("usuarios",$usuarios);
    }

    public function listaFiltro($filtro){
        $f = "%".$filtro."%";
        return $usuarios = User::permitidos()->where(
            function($query) use ($f){
                $query->where("usuarios.nombres","like",$f)
                    ->orWhere("usuarios.apellidos","like",$f)
                    ->orWhere("usuarios.telefono","like",$f)
                    ->orWhere("usuarios.email","like",$f)
                    ->orWhere("usuarios.nit","like",$f)
                    ->orWhere("usuarios.alias","like",$f);
            }
        )->orderBy("usuarios.updated_at","DESC")->paginate(10);
    }

    public function getListUsuarios(Request $request)
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

        if($orderBy == "nombre")$orderBy = "nombres";
        if($orderBy == "correo")$orderBy = "email";

        $usuarios = User::permitidos()->where("usuarios.id","<>",Auth::user()->id);

        $usuarios = $usuarios->orderBy($orderBy, $sortColumnDir);
        $totalRegistros = $usuarios->count();
        if ($search['value'] != null) {
            $f = "%".$search['value']."%";
            $usuarios = $usuarios->where(
                function($query) use ($f){
                    $query->where("nombres","like",$f)
                        ->orWhere("apellidos","like",$f)
                        ->orWhere("alias","like",$f)
                        ->orWhere("email","like",$f)
                        ->orWhere("telefono","like",$f);
                }
            );
        }

        $parcialRegistros = $usuarios->count();
        $usuarios = $usuarios->skip($start)->take($length)->get();

        $object = new \stdClass();
        if($parcialRegistros > 0){
            foreach ($usuarios as $value) {
                $myArray[]=(object) array(
                    'id'=>$value->id,
                    'nombre'=>$value->nombres." ".$value->apellidos,
                    'perfil'=>$value->perfil->nombre,
                    'correo'=>$value->email,
                    'telefono'=>$value->telefono,
                    'alias'=>$value->alias);
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