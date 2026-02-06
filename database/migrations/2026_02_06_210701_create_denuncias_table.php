<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('denuncias', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('prestamista_id');

            // Ciudadano denunciado
            $table->string('cedula');
            $table->string('nombres');
            $table->string('apellidos');
            $table->text('descripcion_deuda');

            // Evidencias
            $table->json('imagenes')->nullable();

            // Datos contacto
            $table->string('nombre_reportante');
            $table->string('celular');

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
        Schema::dropIfExists('denuncias');
    }
};
