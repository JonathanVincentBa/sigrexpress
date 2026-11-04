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
        Schema::table('cabecera', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sucursal')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::table('cabecera')->whereNull('id_sucursal')->exists()) {
            throw new RuntimeException('No se puede revertir la nulabilidad de cabecera.id_sucursal mientras existan guías sin sucursal asociada.');
        }

        Schema::table('cabecera', function (Blueprint $table) {
            $table->unsignedBigInteger('id_sucursal')->nullable(false)->change();
        });
    }
};
