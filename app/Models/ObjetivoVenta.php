<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ObjetivoVenta extends Model {

    protected $table = 'objetivos_ventas';

    protected $fillable = [
        "valor",
        "mes",
        "anio",
        "usuario_id",
        "almacen_id",
    ];

    public static function permitidos(){
        return ObjetivoVenta::where("usuario_id",Auth::user()->userAdminId());
    }

    public function valorAcumulado($almacen = false){
        if(Auth::user()->bodegas == 'si')
            $cantidad = ABFactura::cantidadVendidaMes($this->mes,$this->anio,$almacen);
        else
            $cantidad = Factura::cantidadVendidaMes($this->mes,$this->anio);
        if(!$cantidad)$cantidad = 0;
        return $cantidad;
    }

    public static function GraficaObjetivoVentas(){
        $db = 'vendiendo';
        if(Auth::user()->bodegas == 'si')$db = 'vendiendo_alm';

        if(
            (Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'si')
            ||(Auth::user()->bodegas == 'no')
        ) {
            $datos = DB::select("
             select 
                    month(p.fecha) as mes,
                    year(p.fecha) as anio,
                    
                    (
                        select 
                            sum(subtotal + iva - descuento) as cantidad_vendida  
                        from 
                            $db.facturas 
                        where 
                            usuario_id='" . Auth::user()->userAdminId() . "'
                        
                        and estado <>'anulada'
                        and month(created_at) = month(p.fecha)    
                        
                    ) as valorAcumulado,
                    p.valor
                from 
                    (
                        SELECT 
                            str_to_date(concat_ws('-','01',mes,anio),'%d-%m-%Y') as fecha,
                            SUM(valor) as valor
                        FROM 
                            vendiendo.objetivos_ventas where usuario_id='" . Auth::user()->userAdminId() . "' GROUP BY fecha
                    ) 
                    as p 
                where 
                    p.fecha between DATE_SUB(CURDATE(), INTERVAL 120 DAY) and curdate()   
				order by 
					p.fecha asc 
            ");
        }else if(Auth::user()->bodegas == 'si' && Auth::user()->admin_bodegas == 'no'){
            $almacen = Auth::user()->almacenActual();
            if($almacen) {
                    $datos = DB::select("
                 select 
                        month(p.fecha) as mes,
                        year(p.fecha) as anio,
                        
                        (
                            select 
                                sum(subtotal + iva - descuento) as cantidad_vendida  
                            from 
                                $db.facturas 
                            where 
                                usuario_id='" . Auth::user()->userAdminId() . "' AND $db.facturas.almacen_id = $almacen->id
                            
                            and estado <>'anulada'
                            and month(created_at) = month(p.fecha)    
                            
                        ) as valorAcumulado,
                        p.valor
                    from 
                        (
                            SELECT 
                                str_to_date(concat_ws('-','01',mes,anio),'%d-%m-%Y') as fecha,
                                valor
                            FROM 
                                vendiendo.objetivos_ventas where usuario_id='" . Auth::user()->userAdminId() . "' AND almacen_id = $almacen->id
                        ) 
                        as p 
                    where 
                        p.fecha between DATE_SUB(CURDATE(), INTERVAL 120 DAY) and curdate()   
                    order by 
                        p.fecha asc 
                ");
            }else{
                $datos = [];
            }
        }
        return $datos;
    }

    public function almacen(){
        return $this->belongsTo(Almacen::class,'almacen_id');
    }
}
