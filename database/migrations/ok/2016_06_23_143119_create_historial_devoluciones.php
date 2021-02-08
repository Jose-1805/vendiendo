<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHistorialDevoluciones extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('historial_devoluciones', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('compra_id');
			$table->integer('elemento_id');
			$table->integer('cantidad');
			$table->string('tipo_elemento',50);
			$table->string('motivo',255);
			$table->timestamps();
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('historial_devoluciones');
	}

}
