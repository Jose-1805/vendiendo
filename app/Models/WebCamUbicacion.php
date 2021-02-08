<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WebCamUbicacion extends Model {


	protected $table = "web_cam_ubicacion";
    protected $fillable = [
					        'id',
							'usuario_creator_id',
							'usuario_id',
							'nombre',
							'created_at',
							'updated_at'
						];


}

