<?php namespace App\Http\Controllers;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;

class PedidoController extends Controller {

    public function __construct(){
        $this->middleware("auth");
        $this->middleware("modConfiguracion");
        $this->middleware("modPedido");
        $this->middleware("modCaja");
        $this->middleware("terminosCondiciones");
    }

	public function getIndex(){
        $categorias = Categoria::permitidos()->get();

        return view('pedido.index')->with('categorias',$categorias);
    }
    public function getShow($id_categoria){
        $categoria = Categoria::permitidos()->where('id',$id_categoria)->first();
        $productosByCategoria = Producto::permitidos()->where("estado","Activo")->where('categoria_id',$id_categoria)->get();
        //dd($productosByCategoria);
        return view('pedido.lista_detalle')->with('productosByCategoria',$productosByCategoria)->with('categoria',$categoria);
    }
}
