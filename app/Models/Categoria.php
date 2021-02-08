<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\User;

class Categoria extends Model {
    protected $table = 'categorias';
    protected $fillable = ['nombre','descripcion','negocio','usuario_id','usuario_creador_id'];
    protected $guarded = "id";
    
    public static function permitidos(){
        $user = Auth::user();
        $perfil=$user->perfil;
        if($perfil->nombre == 'superadministrador'){
            return Categoria::where('negocio','si');
        }else if($perfil->nombre == 'administrador'){
            if($user->bodegas == 'si' && $user->admin_bodegas == 'no')
                return Categoria::where("usuario_id",$user->userAdminId())->where('negocio','no');

            return Categoria::where("usuario_id",$user->id)->where('negocio','no');
        }else if($perfil->nombre == "usuario"){
            $usuario_creador = User::find($user->usuario_creador_id);
            if($usuario_creador){
                return Categoria::where('usuario_id',$usuario_creador->id)->where('negocio','no');
            }
        }
    }

    public static function lista(){
        $categorias = Categoria::permitidos()->get();
        $categorias_aux = [];
        foreach ($categorias as $c){
            $categorias_aux[$c->id] = $c->nombre;
        }
        return $categorias_aux;
    }

    public static function listaNegocios(){
        if(Auth::user()->perfil->nombre == "superadministrador") {
            $categorias = Categoria::where("negocio", "si")->get();
        }else if(Auth::user()->perfil->nombre == "proveedor"){
            $misCategorias = Auth::user()->categorias;
            $ids = [];
            foreach ($misCategorias as $mc){
                $ids[] = $mc->id;
            }
            $categorias = Categoria::where("negocio", "si")->whereIn("id",$ids)->get();
        }else{
            $categorias = [];
        }
        $categorias_aux = [];
        foreach ($categorias as $c){
            $categorias_aux[$c->id] = $c->nombre;
        }
        return $categorias_aux;
    }

    public static function listaNegociosObj(){
        if(Auth::user()->perfil->nombre == "superadministrador") {
            $categorias = Categoria::where("negocio", "si")->get();
        }else if(Auth::user()->perfil->nombre == "proveedor"){
            $categorias = Auth::user()->categorias;
        }else{
            $categorias = null;
        }
        return $categorias;
    }

    public static function lista_api(){
        $categorias = Categoria::permitidos()->get();

        $categorias_aux = [];
        $i=0;
        foreach ($categorias as $c){
            $categorias_aux[$i] = [ "id" => $c->id, "nombre" => $c->nombre];
            $i++;
        }
        return $categorias_aux;

    }
    public static function listaCategorias(){
        $categorias = Categoria::where('negocio','=','si')->get();

        $categorias_aux = [];
        $i=0;
        foreach ($categorias as $c){
            $categorias_aux[$i] = [ "id" => $c->id, "nombre" => $c->nombre];
            $i++;
        }
        return $categorias_aux;
    }
    public static function listaCategoriasByNegocio($negocio_id){
        $categorias = Categoria::where('negocio','=','no')
            ->where('usuario_id',$negocio_id)
            ->get();

        $categorias_aux = [];
        $i=0;
        foreach ($categorias as $c){
            $categorias_aux[$i] = [ "id" => $c->id, "nombre" => $c->nombre,"descripcion"=> $c->descripcion];
            $i++;
        }
        return $categorias_aux;
    }

    public function productos(){
        if(Auth::user()->bodegas == 'si')
            return $this->hasMany("App\Models\ABProducto");
        else
            return $this->hasMany("App\Models\Producto");
    }

}
