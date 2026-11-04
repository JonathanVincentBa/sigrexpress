<?php

use App\Models\User;
use Database\Seeders\LegacyUsersSeeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

function createLegacyRoleMapping(string $sourceId = '1'): Role
{
    $role = Role::query()->create(['name' => 'SECRETARIA', 'guard_name' => 'web']);

    DB::table('legacy_import_mappings')->insert([
        'source_table' => 'rol',
        'source_id' => $sourceId,
        'target_id' => $role->getKey(),
        'checksum' => str_repeat('a', 64),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return $role;
}

it('imports legacy users idempotently and assigns Spatie roles by name', function () {
    createLegacyRoleMapping();
    $dumpPath = tempnam(sys_get_temp_dir(), 'sigr-users-');
    $passwordHash = bcrypt('legacy-password');

    $dump = <<<'SQL'
INSERT INTO `users` (`id`, `id_rol`, `id_persona`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `estado`) VALUES
(701, 1, 0, '  Usuaria De Prueba  ', '  TEST.USER@EXAMPLE.TEST ', '$passwordHash', 'legacy-session-token', '2026-01-02 03:04:05', '2026-01-02 03:04:05', '1'),
(702, 0, 0, 'Usuario Sin Rol', 'no-role@example.test', '$passwordHash', NULL, '2026-01-02 03:04:05', '2026-01-02 03:04:05', '');
SQL;
    file_put_contents($dumpPath, str_replace('$passwordHash', $passwordHash, $dump));

    try {
        config([
            'legacy-import.files.users' => $dumpPath,
        ]);

        app(LegacyUsersSeeder::class)->run();
        app(LegacyUsersSeeder::class)->run();

        $secretary = User::query()->findOrFail(701);
        $unassigned = User::query()->findOrFail(702);

        expect($secretary->name)->toBe('USUARIA DE PRUEBA')
            ->and($secretary->email)->toBe('test.user@example.test')
            ->and(password_verify('legacy-password', $secretary->password))->toBeTrue()
            ->and($secretary->remember_token)->toBeNull()
            ->and($secretary->hasRole('SECRETARIA'))->toBeTrue()
            ->and($unassigned->estado)->toBe('0')
            ->and($unassigned->remember_token)->toBeNull()
            ->and($unassigned->roles()->count())->toBe(0);

        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseCount('legacy_import_mappings', 3);
    } finally {
        unlink($dumpPath);
    }
});

it('refuses to assign an unmapped legacy role', function () {
    createLegacyRoleMapping();
    $dumpPath = tempnam(sys_get_temp_dir(), 'sigr-users-');

    file_put_contents($dumpPath, <<<'SQL'
INSERT INTO `users` (`id`, `id_rol`, `id_persona`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `estado`) VALUES
(704, 9, 0, 'Unknown Role', 'unknown@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-01-02 03:04:05', '2026-01-02 03:04:05', '1');
SQL);

    try {
        config([
            'legacy-import.files.users' => $dumpPath,
        ]);

        expect(fn () => app(LegacyUsersSeeder::class)->run())
            ->toThrow(RuntimeException::class, 'El id_rol 9 de users:704 no tiene un rol Spatie importado.');

        $this->assertDatabaseMissing('users', ['id' => 704]);
        $this->assertDatabaseCount('legacy_import_mappings', 1);
    } finally {
        unlink($dumpPath);
    }
});

it('does not overwrite a local user with a colliding legacy identifier', function () {
    createLegacyRoleMapping();
    $user = User::factory()->create(['id' => 703, 'email' => 'existing@example.test']);
    $dumpPath = tempnam(sys_get_temp_dir(), 'sigr-users-');

    file_put_contents($dumpPath, <<<'SQL'
INSERT INTO `users` (`id`, `id_rol`, `id_persona`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `estado`) VALUES
(703, 1, 0, 'Different User', 'different@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-01-02 03:04:05', '2026-01-02 03:04:05', '1');
SQL);

    try {
        config([
            'legacy-import.files.users' => $dumpPath,
        ]);

        expect(fn () => app(LegacyUsersSeeder::class)->run())
            ->toThrow(RuntimeException::class, 'Colisión de identificador o correo al importar users:703; no se modificaron usuarios existentes.');

        expect($user->fresh()->email)->toBe('existing@example.test');
        $this->assertDatabaseCount('legacy_import_mappings', 1);
    } finally {
        unlink($dumpPath);
    }
});

it('does not import a legacy user whose email belongs to another local user', function () {
    createLegacyRoleMapping();
    $user = User::factory()->create(['id' => 705, 'email' => 'existing@example.test']);
    $dumpPath = tempnam(sys_get_temp_dir(), 'sigr-users-');

    file_put_contents($dumpPath, <<<'SQL'
INSERT INTO `users` (`id`, `id_rol`, `id_persona`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `estado`) VALUES
(706, 1, 0, 'Different User', 'EXISTING@EXAMPLE.TEST', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-01-02 03:04:05', '2026-01-02 03:04:05', '1');
SQL);

    try {
        config([
            'legacy-import.files.users' => $dumpPath,
        ]);

        expect(fn () => app(LegacyUsersSeeder::class)->run())
            ->toThrow(RuntimeException::class, 'Colisión de identificador o correo al importar users:706; no se modificaron usuarios existentes.');

        expect($user->fresh()->email)->toBe('existing@example.test');
        $this->assertDatabaseMissing('users', ['id' => 706]);
    } finally {
        unlink($dumpPath);
    }
});

it('refuses to link a legacy user to a person without an import mapping', function () {
    createLegacyRoleMapping();
    $dumpPath = tempnam(sys_get_temp_dir(), 'sigr-users-');

    file_put_contents($dumpPath, <<<'SQL'
INSERT INTO `users` (`id`, `id_rol`, `id_persona`, `name`, `email`, `password`, `remember_token`, `created_at`, `updated_at`, `estado`) VALUES
(707, 1, 9999, 'Unmapped Person', 'unmapped@example.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '2026-01-02 03:04:05', '2026-01-02 03:04:05', '1');
SQL);

    try {
        config([
            'legacy-import.files.users' => $dumpPath,
        ]);

        expect(fn () => app(LegacyUsersSeeder::class)->run())
            ->toThrow(RuntimeException::class, 'La persona heredada 9999 de users:707 debe importarse antes.');

        $this->assertDatabaseMissing('users', ['id' => 707]);
        $this->assertDatabaseCount('legacy_import_mappings', 1);
    } finally {
        unlink($dumpPath);
    }
});
