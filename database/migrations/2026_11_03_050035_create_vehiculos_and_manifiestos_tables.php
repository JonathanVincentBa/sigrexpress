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
        Schema::create('vehiculos', function (Blueprint $table) {
            $table->id('id_vehiculo');
            $table->string('placa', 15)->unique();
            $table->string('marca', 80);
            $table->string('modelo', 80);
            $table->decimal('capacidad', 10, 2)->unsigned();
            $table->foreignId('id_chofer')
                ->nullable()
                ->constrained('persona', 'id_persona')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->char('estado', 1)->default('1');
            $table->timestamps();
        });

        Schema::create('manifiestos', function (Blueprint $table) {
            $table->id('id_manifiesto');
            $table->string('codigo', 30)->unique();
            $table->foreignId('id_vehiculo')
                ->constrained('vehiculos', 'id_vehiculo')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_chofer')
                ->constrained('persona', 'id_persona')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_usuario')
                ->constrained('users', 'id')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->dateTime('fecha_salida');
            $table->dateTime('fecha_cierre')->nullable();
            $table->string('estado', 20)->default('ASIGNADO');
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->index(['estado', 'fecha_salida']);
        });

        Schema::create('manifiesto_guia', function (Blueprint $table) {
            $table->foreignId('id_manifiesto')
                ->constrained('manifiestos', 'id_manifiesto')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('id_cabecera')
                ->constrained('cabecera', 'id_cabecera')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->primary(['id_manifiesto', 'id_cabecera']);
            $table->unique('id_cabecera');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manifiesto_guia');
        Schema::dropIfExists('manifiestos');
        Schema::dropIfExists('vehiculos');
    }
};
