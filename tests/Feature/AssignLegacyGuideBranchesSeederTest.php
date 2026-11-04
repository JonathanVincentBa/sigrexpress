<?php

use App\Models\User;
use Database\Seeders\AssignLegacyGuideBranchesSeeder;
use Illuminate\Support\Facades\DB;

it('assigns guides to uniquely matched branches and leaves ambiguous or unknown origins unchanged', function () {
    $companyId = DB::table('empresa')->insertGetId([
        'razon_social' => 'EMPRESA DE PRUEBA',
        'ruc' => '1790000000001',
        'direccion' => 'DIRECCION',
        'telefono' => '0990000000',
        'email' => 'empresa@example.test',
        'representante' => 'REPRESENTANTE',
        'porcentaje_impuesto' => 0,
        'estado' => '1',
    ], 'id_empresa');
    $quitoId = DB::table('ciudad')->insertGetId([
        'descripcion' => 'QUITO',
        'estado' => '1',
    ], 'id_ciudad');
    $machachiId = DB::table('ciudad')->insertGetId([
        'descripcion' => 'MACHACHI',
        'estado' => '1',
    ], 'id_ciudad');
    $createBranch = function (string $name, int $cityId) use ($companyId): int {
        return DB::table('sucursal')->insertGetId([
            'id_empresa' => $companyId,
            'id_ciudad' => $cityId,
            'nombre' => $name,
            'direccion' => 'DIRECCION',
            'telefono' => '0990000000',
            'email' => 'sucursal@example.test',
            'estado' => '1',
        ], 'id_sucursal');
    };
    $quitoBranchId = $createBranch('Agencia QUITO', $quitoId);
    $quitoSurBranchId = $createBranch('Agencia Quito Sur', $quitoId);
    $machachiBranchId = $createBranch('Agencia MACHACHI', $machachiId);
    $duplicateMachachiBranchId = $createBranch('Agencia MACHACHI', $machachiId);
    $cargoId = DB::table('cargo')->insertGetId([
        'descripcion' => 'CLIENTE',
        'estado' => '1',
    ], 'id_cargo');
    $personId = DB::table('persona')->insertGetId([
        'id_empresa' => $companyId,
        'id_cargo' => $cargoId,
        'tipo_persona' => 'C',
        'nombre' => 'CLIENTE DE PRUEBA',
        'direccion' => 'DIRECCION',
        'tipo_dni' => 'C',
        'num_dni' => '0100000000',
        'telefono' => '0990000000',
        'estado' => '1',
    ], 'id_persona');
    $paymentId = DB::table('formas_pago')->insertGetId([
        'descripcion' => 'EFECTIVO',
        'estado' => '1',
    ], 'id_formas_pago');
    $reasonId = DB::table('motivo_traslado')->insertGetId([
        'descripcion' => 'VENTA',
        'estado' => '1',
    ], 'id_motivo_traslado');
    $user = User::factory()->create();

    $createGuide = function (int $guideNumber, string $origin, ?int $branchId = null) use ($reasonId, $paymentId, $user, $personId): void {
        DB::table('cabecera')->insert([
            'id_sucursal' => $branchId,
            'id_motivo_traslado' => $reasonId,
            'id_forma_pago' => $paymentId,
            'id_users' => $user->id,
            'id_persona' => $personId,
            'ciudad_origen' => $origin,
            'ciudad_destino' => 'CUENCA',
            'nom_remitente' => 'REMITENTE',
            'dni_remitente' => '0100000000',
            'direccion_remitente' => 'DIRECCION',
            'fono_remitente' => '0990000000',
            'nom_destinatario' => 'DESTINATARIO',
            'num_guia' => $guideNumber,
            'num_guia_trans' => 0,
            'fecha_emision' => '2026-01-01 10:00:00',
            'flete' => 10,
            'valor_guia' => 10,
            'estado' => '1',
        ]);
    };

    $createGuide(1001, 'QUITO');
    $createGuide(1002, 'QUITO SUR');
    $createGuide(1003, 'MACHACHI');
    $createGuide(1004, 'ORIGEN DESCONOCIDO');
    $createGuide(1005, 'MACHACHI', $duplicateMachachiBranchId);

    app(AssignLegacyGuideBranchesSeeder::class)->run();
    app(AssignLegacyGuideBranchesSeeder::class)->run();

    $this->assertDatabaseHas('cabecera', ['num_guia' => 1001, 'id_sucursal' => $quitoBranchId]);
    $this->assertDatabaseHas('cabecera', ['num_guia' => 1002, 'id_sucursal' => $quitoSurBranchId]);
    $this->assertDatabaseHas('cabecera', ['num_guia' => 1003, 'id_sucursal' => null]);
    $this->assertDatabaseHas('cabecera', ['num_guia' => 1004, 'id_sucursal' => null]);
    $this->assertDatabaseHas('cabecera', ['num_guia' => 1005, 'id_sucursal' => $duplicateMachachiBranchId]);
    expect($machachiBranchId)->not->toBe($duplicateMachachiBranchId);
});
