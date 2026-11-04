<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cabecera')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('detalle')->whereNull('id_cabecera')->exists()) {
            throw new RuntimeException('No se puede revertir la nulabilidad de detalle.id_cabecera mientras existan detalles sin cabecera asociada.');
        }

        Schema::table('detalle', function (Blueprint $table) {
            $table->unsignedBigInteger('id_cabecera')->nullable(false)->change();
        });
    }
};
