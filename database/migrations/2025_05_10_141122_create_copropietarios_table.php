<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCopropietariosTable extends Migration
{
    public function up()
    {
        Schema::create('copropietarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('telefono')->nullable();
            $table->string('correo')->nullable();
            $table->enum('tipo', ['propietario', 'arrendatario'])->default('propietario');
            $table->string('numero_departamento');
            $table->string('estacionamiento')->nullable();
            $table->string('bodega')->nullable();
            $table->unsignedBigInteger('propietario_id')->nullable();
            $table->timestamps();

            $table->foreign('propietario_id')
                ->references('id')
                ->on('copropietarios')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('copropietarios');
    }
}

