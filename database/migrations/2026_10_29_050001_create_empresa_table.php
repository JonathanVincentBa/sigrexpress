<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('empresa', function (Blueprint $table) {
            $table->id('id_empresa');
            $table->string('razon_social', 100);
            $table->string('ruc', 45);
            $table->string('direccion', 100);
            $table->string('telefono', 20);
            $table->string('email', 100);
            $table->string('representante', 100);
            $table->decimal('porcentaje_impuesto', 5, 2);
            $table->string('logo', 100)->nullable();
            $table->char('estado', 1)->default('1');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
