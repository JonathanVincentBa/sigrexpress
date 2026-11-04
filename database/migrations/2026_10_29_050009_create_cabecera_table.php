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
        Schema::create('cabecera', function (Blueprint $table) {
            $table->id('id_cabecera');
            $table->foreignId('id_sucursal')
                ->constrained('sucursal', 'id_sucursal')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_motivo_traslado')
                ->constrained('motivo_traslado', 'id_motivo_traslado')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_forma_pago')
                ->constrained('formas_pago', 'id_formas_pago')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_users')
                ->constrained('users', 'id')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_persona')
                ->constrained('persona', 'id_persona')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
            $table->foreignId('id_users_update')
                ->nullable()
                ->constrained('users', 'id')
                ->cascadeOnUpdate()
                ->nullOnDelete();
            $table->dateTime('fecha_cancelacion')->nullable();
            $table->string('ciudad_origen', 50);
            $table->string('ciudad_destino', 50);
            $table->string('nom_remitente', 50);
            $table->string('dni_remitente', 20);
            $table->string('direccion_remitente', 100);
            $table->string('fono_remitente', 20);
            $table->string('nom_destinatario', 50);
            $table->string('dni_destinatario', 20)->nullable();
            $table->string('direccion_destinatario', 100)->nullable();
            $table->string('fono_destinatario', 20)->nullable();
            $table->unsignedBigInteger('num_guia')->unique();
            $table->integer('num_guia_trans');
            $table->dateTime('fecha_emision');
            $table->dateTime('fecha_recepcion')->nullable();
            $table->string('guia_rem_cliente', 45)->nullable();
            $table->string('factura_cliente', 45)->nullable();
            $table->decimal('flete', 10, 2);
            $table->decimal('recargo', 10, 2)->nullable();
            $table->decimal('valor_guia', 10, 2);
            $table->decimal('prima', 10, 2)->nullable();
            $table->string('nombre_recibe', 45)->nullable();
            $table->string('dni_recibe', 45)->nullable();
            $table->char('estatus_entrega', 1)->nullable();
            $table->string('estatus_cobro', 20)->nullable();
            $table->char('estado', 1)->default('1');
            $table->text('nota')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabecera');
    }
};
