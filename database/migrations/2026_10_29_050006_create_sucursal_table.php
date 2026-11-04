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
        Schema::create('sucursal', function (Blueprint $table) {
            $table->id('id_sucursal');
            $table->foreignId('id_empresa')
                ->constrained('empresa', 'id_empresa')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_ciudad')
                ->constrained('ciudad', 'id_ciudad')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->string('direccion', 100);
            $table->string('telefono', 20);
            $table->string('email', 100);
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
        Schema::dropIfExists('sucursal');
    }
};
