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
        Schema::create('detalle', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('id_cabecera')
                ->constrained('cabecera', 'id_cabecera')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
            $table->integer('cantidad');
            $table->string('descripcion', 100);
            $table->decimal('v_unitario', 10, 2);
            $table->decimal('v_parcial', 10, 2);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle');
    }
};
