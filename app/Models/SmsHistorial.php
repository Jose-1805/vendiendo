<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsHistorial extends Model {

    protected $table = "sms_historial";
    protected $fillable = ['sms_id','f_h_programacion','mensaje_id','fecha_ws','estado','telefonos'];

}
