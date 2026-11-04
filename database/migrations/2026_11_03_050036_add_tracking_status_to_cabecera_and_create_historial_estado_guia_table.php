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
        Schema::table('cabecera', function (Blueprint $table) {
            $table->string('estado_tracking', 20)->default('EN BODEGA')->after('estatus_entrega');
            $table->index(['estado', 'estado_tracking']);
        });

        Schema::create('historial_estado_guia', function (Blueprint $table) {
            $table->id('id_historial_estado_guia');
            $table->foreignId('id_cabecera')
                ->constrained('cabecera', 'id_cabecera')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->foreignId('id_usuario')
                ->nullable()
                ->constrained('users', 'id')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->string('estado', 20);
            $table->text('observacion')->nullable();
            $table->timestamps();
            $table->index(['id_cabecera', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historial_estado_guia');

        Schema::table('cabecera', function (Blueprint $table) {
            $table->dropIndex(['estado', 'estado_tracking']);
            $table->dropColumn('estado_tracking');
        });
    }
};
