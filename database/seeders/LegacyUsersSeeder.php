<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\LegacySqlDumpReader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Role;

class LegacyUsersSeeder extends Seeder
{
    /**
     * Import legacy users and assign the Spatie roles mapped from the legacy rol table.
     */
    public function run(): void
    {
        $path = config('legacy-import.files.users');
        if (! is_string($path) || $path === '' || ! is_file($path) || ! is_readable($path)) {
            throw new RuntimeException('Configure LEGACY_USERS_SQL con la ruta de un archivo SQL legible.');
        }

        if (! DB::getSchemaBuilder()->hasTable('legacy_import_mappings')) {
            throw new RuntimeException('Ejecute las migraciones antes de importar usuarios heredados.');
        }

        $this->call(LegacyAccessControlSeeder::class);

        $roleMappings = DB::table('legacy_import_mappings')
            ->where('source_table', 'rol')
            ->get()
            ->keyBy('source_id');
        $roleIds = $roleMappings->pluck('target_id')->unique()->all();
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('id', $roleIds)
            ->get()
            ->keyBy('id');

        foreach ($roleMappings as $roleMapping) {
            if (! $roles->has((int) $roleMapping->target_id)) {
                throw new RuntimeException('Falta crear uno o más roles Spatie antes de importar usuarios heredados.');
            }
        }

        $imported = 0;
        $batch = [];
        $chunkSize = max(1, (int) config('legacy-import.chunk_size', 500));

        foreach (app(LegacySqlDumpReader::class)->rows($path, 'users') as $row) {
            $batch[] = $row;

            if (count($batch) >= $chunkSize) {
                $imported += $this->importBatch($batch, $roleMappings, $roles);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $imported += $this->importBatch($batch, $roleMappings, $roles);
        }

        $this->command?->info("Se importaron {$imported} usuarios heredados.");
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @param  Collection<string, object>  $roleMappings
     * @param  Collection<int, Role>  $roles
     */
    private function importBatch(array $rows, Collection $roleMappings, Collection $roles): int
    {
        return DB::transaction(function () use ($rows, $roleMappings, $roles): int {
            $imported = 0;

            foreach ($rows as $row) {
                $imported += $this->importUser($row, $roleMappings, $roles) ? 1 : 0;
            }

            return $imported;
        });
    }

    /**
     * @param  array<string, string|null>  $row
     * @param  Collection<string, object>  $roleMappings
     * @param  Collection<int, Role>  $roles
     */
    private function importUser(array $row, Collection $roleMappings, Collection $roles): bool
    {
        foreach (['id', 'id_rol', 'id_persona', 'name', 'email', 'password', 'estado'] as $requiredColumn) {
            if (! array_key_exists($requiredColumn, $row)) {
                throw new RuntimeException("Falta la columna {$requiredColumn} en el archivo users.sql.");
            }
        }

        $sourceId = $this->positiveId($row['id'], 'id');
        $legacyRoleId = trim((string) $row['id_rol']);
        $role = null;

        if (! in_array($legacyRoleId, ['', '0'], true)) {
            $roleMapping = $roleMappings->get($legacyRoleId);
            $role = $roleMapping === null ? null : $roles->get((int) $roleMapping->target_id);

            if ($role === null) {
                throw new RuntimeException("El id_rol {$legacyRoleId} de users:{$sourceId} no tiene un rol Spatie importado.");
            }
        }

        $attributes = $this->attributesFor($row, $sourceId);
        $checksum = $this->checksumFor($attributes, $legacyRoleId);
        $previousChecksum = hash('sha256', serialize([
            'attributes' => $attributes,
            'role' => $role === null ? null : mb_strtoupper($role->name),
        ]));
        $mapping = DB::table('legacy_import_mappings')
            ->where('source_table', 'users')
            ->where('source_id', (string) $sourceId)
            ->first();

        if ($mapping !== null) {
            if (
                ! hash_equals($mapping->checksum, $checksum)
                && ! hash_equals($mapping->checksum, $previousChecksum)
            ) {
                throw new RuntimeException("El usuario users:{$sourceId} cambió desde su importación anterior.");
            }

            if (! DB::table('users')->where('id', $mapping->target_id)->exists()) {
                throw new RuntimeException("Falta el usuario local mapeado para users:{$sourceId}.");
            }

            if (! hash_equals($mapping->checksum, $checksum)) {
                DB::table('legacy_import_mappings')
                    ->where('id', $mapping->id)
                    ->update([
                        'checksum' => $checksum,
                        'updated_at' => now(),
                    ]);
            }

            $user = User::query()->findOrFail($mapping->target_id);

            if ($role !== null && ! $user->hasRole($role)) {
                $user->assignRole($role);
            }

            return false;
        }

        $existingUserId = DB::table('users')
            ->where('email', $attributes['email'])
            ->value('id');

        if ($existingUserId !== null) {
            throw new RuntimeException("El correo heredado de users:{$sourceId} ya está asociado al usuario local {$existingUserId}; no se creó un duplicado.");
        }

        if (DB::table('users')->where('id', $sourceId)->exists()) {
            throw new RuntimeException("Colisión de identificador al importar users:{$sourceId}; no se modificaron usuarios existentes.");
        }

        DB::table('users')->insert($attributes);

        if ($role !== null) {
            User::query()->findOrFail($sourceId)->assignRole($role);
        }

        DB::table('legacy_import_mappings')->insert([
            'source_table' => 'users',
            'source_id' => (string) $sourceId,
            'target_id' => $sourceId,
            'checksum' => $checksum,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return true;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function checksumFor(array $attributes, string $legacyRoleId): string
    {
        return hash('sha256', serialize([
            'attributes' => $attributes,
            'legacy_role_id' => $legacyRoleId,
        ]));
    }

    /**
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    private function attributesFor(array $row, int $sourceId): array
    {
        $name = trim((string) $row['name']);
        $email = mb_strtolower(trim((string) $row['email']));
        $password = $row['password'];
        $state = trim((string) $row['estado']);

        if ($name === '' || $email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException("El usuario users:{$sourceId} tiene un nombre o correo no válido.");
        }

        if ($password === null || password_get_info($password)['algoName'] === 'unknown') {
            throw new RuntimeException("La contraseña heredada de users:{$sourceId} no contiene un hash compatible.");
        }

        if (! in_array($state, ['', '0', '1'], true)) {
            throw new RuntimeException("El estado del usuario users:{$sourceId} debe ser 0 o 1.");
        }

        $attributes = [
            'id' => $sourceId,
            'name' => mb_strtoupper($name),
            'email' => $email,
            'password' => $password,
            'remember_token' => null,
            'estado' => $state === '1' ? '1' : '0',
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
        ];

        $legacyPersonId = trim((string) ($row['id_persona'] ?? ''));

        if ($legacyPersonId === '' || $legacyPersonId === '0') {
            $attributes['id_persona'] = null;

            return $attributes;
        }

        $personId = $this->positiveId($legacyPersonId, 'id_persona');
        $personMapping = DB::table('legacy_import_mappings')
            ->where('source_table', 'persona')
            ->where('source_id', (string) $personId)
            ->first();

        if ($personMapping === null || ! DB::table('persona')->where('id_persona', $personMapping->target_id)->exists()) {
            throw new RuntimeException("La persona heredada {$personId} de users:{$sourceId} debe importarse antes.");
        }

        $attributes['id_persona'] = (int) $personMapping->target_id;

        return $attributes;
    }

    private function positiveId(?string $value, string $column): int
    {
        if ($value === null || ! ctype_digit($value) || (int) $value < 1) {
            throw new RuntimeException("El identificador {$column} de users.sql no es válido.");
        }

        return (int) $value;
    }
}
