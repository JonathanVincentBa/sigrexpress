<?php

use App\Models\User;
use Database\Seeders\LegacyAccessControlSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('creates official roles and permissions and migrates mapped legacy assignments idempotently', function () {
    $legacyAdministrator = Role::query()->create([
        'name' => 'ADMINISTRADOR',
        'guard_name' => 'web',
    ]);
    $legacySecretary = Role::query()->create([
        'name' => 'SECRETARIA',
        'guard_name' => 'web',
    ]);
    $administrator = User::factory()->create();
    $secretary = User::factory()->create();
    $administrator->assignRole($legacyAdministrator);
    $secretary->assignRole($legacySecretary);

    DB::table('legacy_import_mappings')->insert([
        [
            'source_table' => 'rol',
            'source_id' => '1',
            'target_id' => $legacyAdministrator->id,
            'checksum' => str_repeat('a', 64),
            'created_at' => now(),
            'updated_at' => now(),
        ],
        [
            'source_table' => 'rol',
            'source_id' => '2',
            'target_id' => $legacySecretary->id,
            'checksum' => str_repeat('b', 64),
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    app(LegacyAccessControlSeeder::class)->run();
    app(LegacyAccessControlSeeder::class)->run();

    $administratorRole = Role::query()->where('name', 'Administrador')->firstOrFail();
    $secretaryRole = Role::query()->where('name', 'Secretaria')->firstOrFail();
    $driverRole = Role::query()->where('name', 'Chofer')->firstOrFail();
    $clientRole = Role::query()->where('name', 'CLIENTE')->firstOrFail();
    $superAdminRole = Role::query()->where('name', 'Super Admin')->firstOrFail();
    $allPermissions = Permission::query()->where('guard_name', 'web')->orderBy('name')->pluck('name')->all();

    expect($administrator->fresh()->hasRole('Administrador'))->toBeTrue()
        ->and($administrator->fresh()->hasRole('ADMINISTRADOR'))->toBeFalse()
        ->and($secretary->fresh()->hasRole('Secretaria'))->toBeTrue()
        ->and($administratorRole->permissions->pluck('name')->sort()->values()->all())->toBe($allPermissions)
        ->and($superAdminRole->permissions->pluck('name')->sort()->values()->all())->toBe($allPermissions)
        ->and($secretaryRole->permissions->pluck('name')->sort()->values()->all())->toBe([
            'cajas.cerrar',
            'cajas.gestionar',
            'cajas.ver',
            'clientes.gestionar',
            'clientes.ver',
            'consultas.ver',
            'guias.crear',
            'guias.editar',
            'guias.ver',
            'reportes.ver',
        ])
        ->and($driverRole->permissions->pluck('name')->sort()->values()->all())->toBe([
            'app_chofer.acceder',
            'guias.ver',
            'manifiestos.actualizar',
            'manifiestos.ver',
        ])
        ->and($clientRole->permissions->pluck('name')->all())->toBe(['guias.consultar']);

    $this->assertDatabaseCount('roles', 5);
    $this->assertDatabaseCount('permissions', 21);
    $this->assertDatabaseHas('legacy_import_mappings', [
        'source_table' => 'rol',
        'source_id' => '1',
        'target_id' => $administratorRole->id,
    ]);
});
