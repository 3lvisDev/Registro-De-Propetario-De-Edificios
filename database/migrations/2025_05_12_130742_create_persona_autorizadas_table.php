<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('persona_autorizadas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo');
            $table->string('rut_pasaporte');
            $table->string('departamento');
            $table->string('patente')->nullable();
            $table->unsignedBigInteger('copropietario_id')->nullable();
            $table->timestamps();

            $table->foreign('copropietario_id')
                  ->references('id')
                  ->on('copropietarios')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('persona_autorizadas');
    }
};

