<?php namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class WebCam extends Model {

	protected $table = "web_cam";
    protected $fillable = [
					        'id',
							'usuario_creator_id', 
							'usuario_id',
							'nombre',
							'alias',
							'url',
							'usuario_acceso',
							'pass_acceso',
							'ubicacion_id',
							'created_at',
							'updated_at'
						];

	public function ubicacionID(){
    	return $this->belongsTo('App\Models\WebCamUbicacion','ubicacion_id','id');
    }
	public function userID(){
    	return $this->belongsTo('App\User','usuario_id','id');
    }
}

