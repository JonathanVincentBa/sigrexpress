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
        Schema::create('persona', function (Blueprint $table) {
            $table->id('id_persona');
            $table->foreignId('id_empresa')
                ->constrained('empresa', 'id_empresa')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_cargo')
                ->constrained('cargo', 'id_cargo')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->char('tipo_persona', 1)->comment('C: Cliente, P: Proveedor, E: Empleado');
            $table->string('nombre', 100);
            $table->string('direccion', 500);
            $table->char('tipo_dni', 1)->comment('C: Cédula, R: RUC, P: Pasaporte');
            $table->string('num_dni', 20);
            $table->string('telefono', 20);
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
        Schema::dropIfExists('persona');
    }
};
