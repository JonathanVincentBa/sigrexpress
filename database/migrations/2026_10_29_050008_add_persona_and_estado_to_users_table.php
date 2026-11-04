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
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('id_persona')
                ->nullable()
                ->after('id')
                ->constrained('persona', 'id_persona')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->char('estado', 1)->default('1')->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_persona']);
            $table->dropColumn(['id_persona', 'estado']);
        });
    }
};
