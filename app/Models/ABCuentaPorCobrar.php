<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Models\Producto;
use App\Models\MateriaPrima;
use Illuminate\Support\Facades\DB;

class ABCuentaPorCobrar extends Model {

    protected $connection = 'mysql_alm';
    protected $table = 'cuentas_por_cobrar_proveedores';
    protected $fillable = [
        'compra_id',
        'elemento_id',
        'historial_costo_id',
        'cantidad_devolucion',
        'valor_devolucion',
        'tipo_elemento',
        'motivo',
        'tipo_compra',
        'estado',
        'fecha_devolucion',
        'usuario_id',
        'proveedor_id',
        'forma_pago'
    ];
    public static function GenerateReceivable($compra_id,$elemento_id,$producto_historial_id,$cantidad_dev,$valor_dev,$motivo,$tipo_compra,$estado,$proveedor_id){

        $user = Auth::user();
        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        
        $cuentaCobrar = ABCuentaporCobrar::create([
            'compra_id' => $compra_id,
            'producto_historial_id' => $producto_historial_id,
            'elemento_id' => $elemento_id,
            'cantidad_devolucion' => $cantidad_dev,
            'valor_devolucion' => $valor_dev,
            'motivo' => $motivo,
            'tipo_compra' => $tipo_compra,
            'estado' => $estado,
            'fecha_devolucion' => $fecha_actual,
            'usuario_id' => $user->id,
            'proveedor_id' => $proveedor_id
        ]);

        return $cuentaCobrar;
    }
    public function proveedor(){
        return $this->belongsTo('App\Models\Proveedor');
    }

    public function usuario(){
        return $this->belongsTo('App\User');
    }
    public function compra(){
        if(Auth::user()->bodegas == 'si')
            return $this->belongsTo('App\Models\ABCompra');
        else
            return $this->belongsTo('App\Models\Compra');
    }
    public function producto(){
        if(Auth::user()->bodegas == 'si')
            return $this->belongsTo('App\Models\ABProducto','elemento_id');
        else
            return $this->belongsTo('App\Models\Producto','elemento_id');
    }
    public function materia(){
        return $this->belongsTo(MateriaPrima::class,'elemento_id');
    }
    public static function CollectReceivable($id_cxp,$estado,$forma_pago){

        $fecha_actual = date("Y"). "-". date("m") . "-" . date("d");
        $usuario_id = Auth::user()->userAdminId();
        $continuar = false;

        $cuentaXcobrar = CuentaporCobrar::find($id_cxp);
        $cuentaXcobrar->estado = $estado;
        $cuentaXcobrar->forma_pago = $forma_pago;
        $cuentaXcobrar->save();
        
        if ($cuentaXcobrar){
            $continuar = true;
            if ($forma_pago == 'Efectivo'){
                $caja = Caja::abierta();

                $caja->efectivo_final += $cuentaXcobrar->valor_devolucion;
                $caja->save();
                //dd($caja->efectivo_final);
                $continuar = true;

            }else if ($forma_pago == 'Mercancia'){
                //actualiza inventario
                if ($cuentaXcobrar->tipo_compra == 'MateriaPrima'){
                    $elemento = MateriaPrima::find($cuentaXcobrar->elemento_id);
                    $elemento->stock += $cuentaXcobrar->cantidad_devolucion;
                    $elemento->save();

                } else if ($cuentaXcobrar->tipo_compra == 'Producto'){
                    $prod_hist = ProductoHistorial::find($cuentaXcobrar->producto_historial_id);
                    $elemento = Producto::find($cuentaXcobrar->elemento_id);
                    $data_precios = [[$prod_hist->precio_costo_nuevo,$cuentaXcobrar->cantidad_devolucion]];
                    $elemento->updatePromedioPonderado($data_precios);
                }
                if ($elemento){
                    //Incremento el valor de la compra con el valor de la devolucion de los productos
                    $compra = Compra::permitidos()->where('id',$cuentaXcobrar->compra_id)->first();
                    $compra->valor += $cuentaXcobrar->valor_devolucion;
                    $compra->save();
                    self::saveProductReturned($cuentaXcobrar);
                    $continuar = true;
                }
            }
        }

        return $continuar;
    }
    public static function saveProductReturned($obj_cuentaXcobrar){

        if ($obj_cuentaXcobrar->tipo_compra == 'MateriaPrima'){
            $existe = DB::table('compras_materias_primas')
                ->where('compra_id', $obj_cuentaXcobrar->compra_id)
                ->where('materia_prima_id',$obj_cuentaXcobrar->elemento_id);
            if (!empty($existe->first())){
                //incrementa la cantidad
                $cantidad = $existe->first()->cantidad + $obj_cuentaXcobrar->cantidad_devolucion;

                $existe->update(array('cantidad' => $cantidad));
            }else{
                //crea nuevo registro
                DB::table('compras_materias_primas')
                    ->insert(array(
                        'compra_id' => $obj_cuentaXcobrar->compra_id,
                        'materia_prima_id' => $obj_cuentaXcobrar->elemento_id,
                        'cantidad' => $obj_cuentaXcobrar->cantidad_devolucion
                    ));
            }
        }else if ($obj_cuentaXcobrar->tipo_compra == 'Producto'){
            $existe = DB::table('compras_productos_historial')
                ->where('compra_id', $obj_cuentaXcobrar->compra_id)
                ->where('producto_historial_id',$obj_cuentaXcobrar->producto_historial_id);
            if (!empty($existe->first())){
                //incrementa la cantidad
                $cantidad = $existe->first()->cantidad + $obj_cuentaXcobrar->cantidad_devolucion;

                $existe->update(array('cantidad' => $cantidad));
            }else{
                //crea nuevo registro
                DB::table('compras_productos_historial')
                    ->insert(array(
                        'compra_id' => $obj_cuentaXcobrar->compra_id,
                        'producto_historial_id' => $obj_cuentaXcobrar->producto_historial_id,
                        'cantidad' => $obj_cuentaXcobrar->cantidad_devolucion
                    ));
            }
        }
        return true;
    }
    public static function getCuentasXCobrar(){
        
        $cuentasXCobrar = CuentaPorCobrar::select()->get();

        return $cuentasXCobrar;
    }
    public static function valorCuentasXCobrar($estado){

        $user = Auth::user();
        $cuentasXCobrar =  CuentaPorCobrar::where('usuario_id',$user->id)->where('estado',$estado)->sum('valor_devolucion');

        return $cuentasXCobrar;
    }
    public static function deleteCuentasXCobrar($id_compra){

        $cuentaXcobrar = CuentaporCobrar::where('compra_id',$id_compra)->delete();
        return $cuentaXcobrar;

    }




}
