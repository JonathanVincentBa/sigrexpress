<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
});

test('dashboard shows branch names and keeps historical guides without a branch separate', function () {
    $user = User::factory()->create(['estado' => '1']);
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
    $cityId = DB::table('ciudad')->insertGetId([
        'descripcion' => 'QUITO',
        'estado' => '1',
    ], 'id_ciudad');
    $branchId = DB::table('sucursal')->insertGetId([
        'id_empresa' => $companyId,
        'id_ciudad' => $cityId,
        'nombre' => 'AGENCIA QUITO',
        'direccion' => 'DIRECCION',
        'telefono' => '0990000000',
        'email' => 'agencia@example.test',
        'estado' => '1',
    ], 'id_sucursal');
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

    $createGuide = function (int $guideNumber, ?int $guideBranchId, string $issuedAt, float $value) use ($user, $personId, $paymentId, $reasonId): void {
        DB::table('cabecera')->insert([
            'id_sucursal' => $guideBranchId,
            'id_motivo_traslado' => $reasonId,
            'id_forma_pago' => $paymentId,
            'id_users' => $user->id,
            'id_persona' => $personId,
            'ciudad_origen' => 'QUITO',
            'ciudad_destino' => 'CUENCA',
            'nom_remitente' => 'REMITENTE',
            'dni_remitente' => '0100000000',
            'direccion_remitente' => 'DIRECCION',
            'fono_remitente' => '0990000000',
            'nom_destinatario' => 'DESTINATARIO',
            'num_guia' => $guideNumber,
            'num_guia_trans' => 0,
            'fecha_emision' => $issuedAt,
            'flete' => $value,
            'valor_guia' => $value,
            'estado' => '1',
        ]);
    };

    $createGuide(1001, $branchId, now()->subYear()->toDateTimeString(), 10000);
    $createGuide(1002, null, now()->subYear()->toDateTimeString(), 25000000);
    $createGuide(1003, $branchId, now()->toDateTimeString(), 5);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Valor emitido por sucursal este mes')
        ->assertSee('Valor histórico emitido por sucursal')
        ->assertSee('AGENCIA QUITO')
        ->assertSee('Sin sucursal asignada')
        ->assertSee('$10,005.00')
        ->assertSee('$25,000,000.00');
});
