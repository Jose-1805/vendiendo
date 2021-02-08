<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUnidadesP extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create("t_unidades_p",function(Blueprint $table){
			$table->increments('id');
			$table->string('nombre',100);
			$table->string('sigla',20);
			$table->integer('usuario_id');
			$table->integer('usuario_id_creator');
			$table->timestamps();

			//$table->primary("id");
			$table->foreign('usuario_id')->references('id')->on('usuarios')->onDelete('cascade');
			$table->foreign('usuario_id_creator')->references('id')->on('usuarios')->onDelete('cascade');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		//
	}

}
