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
        Schema::create('legacy_import_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('source_table', 32);
            $table->string('source_id', 64);
            $table->unsignedBigInteger('target_id');
            $table->char('checksum', 64);
            $table->timestamps();

            $table->unique(['source_table', 'source_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legacy_import_mappings');
    }
};
