<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTableProductoMateriaUnidad extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('t_producto_materia_unidad', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('cantidad');
			$table->integer('unidad_id');
			$table->foreign('unidad_id')->references('id')->on('t_unidades_p')->onDelete('cascade');
			$table->integer('materia_prima_id');
			$table->foreign('materia_prima_id')->references('id')->on('materias_primas')->onDelete('cascade');
			$table->integer('producto_id');
			$table->foreign('producto_id')->references('id')->on('t_productos')->onDelete('cascade');
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
		Schema::drop('t_producto_materia_unidad');
	}

}
