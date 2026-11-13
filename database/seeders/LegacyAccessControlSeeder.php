<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class LegacyAccessControlSeeder extends Seeder
{
    private const LEGACY_ROLE_NAMES = [
        '1' => 'Administrador',
        '2' => 'Secretaria',
        '3' => 'Chofer',
        '4' => 'CLIENTE',
    ];

    /**
     * @var list<string>
     */
    private const PERMISSIONS = [
        'guias.ver',
        'guias.crear',
        'guias.editar',
        'guias.anular',
        'guias.consultar',
        'clientes.ver',
        'clientes.gestionar',
        'cajas.ver',
        'cajas.gestionar',
        'cajas.cerrar',
        'consultas.ver',
        'reportes.ver',
        'reportes.exportar',
        'usuarios.ver',
        'usuarios.gestionar',
        'roles.gestionar',
        'nomina.ver',
        'nomina.gestionar',
        'app_chofer.acceder',
        'manifiestos.ver',
        'manifiestos.actualizar',
    ];

    /**
     * @var array<string, list<string>|'*'>
     */
    private const ROLE_PERMISSIONS = [
        'Super Admin' => '*',
        'Administrador' => '*',
        'Secretaria' => [
            'guias.ver',
            'guias.crear',
            'guias.editar',
            'clientes.ver',
            'clientes.gestionar',
            'cajas.ver',
            'cajas.gestionar',
            'cajas.cerrar',
            'consultas.ver',
            'reportes.ver',
        ],
        'Chofer' => [
            'guias.ver',
            'app_chofer.acceder',
            'manifiestos.ver',
            'manifiestos.actualizar',
        ],
        'CLIENTE' => [
            'guias.consultar',
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        DB::transaction(function (): void {
            $permissions = collect(self::PERMISSIONS)
                ->mapWithKeys(fn (string $name): array => [
                    $name => Permission::query()->firstOrCreate([
                        'name' => $name,
                        'guard_name' => 'web',
                    ]),
                ]);
            $legacyMappings = $this->legacyRoleMappings();
            $roles = $this->resolveRoles($legacyMappings);

            $this->migrateLegacyRoleAssignments($legacyMappings, $roles);

            foreach (self::ROLE_PERMISSIONS as $name => $permissionNames) {
                $rolePermissions = $permissionNames === '*'
                    ? $permissions->values()
                    : collect($permissionNames)->map(fn (string $permission): Permission => $permissions->get($permission));

                $roles[$name]->syncPermissions($rolePermissions);
            }
        });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    /**
     * @return Collection<string, object>
     */
    private function legacyRoleMappings(): Collection
    {
        if (! Schema::hasTable('legacy_import_mappings')) {
            return collect();
        }

        return DB::table('legacy_import_mappings')
            ->where('source_table', 'rol')
            ->whereIn('source_id', array_keys(self::LEGACY_ROLE_NAMES))
            ->get()
            ->keyBy('source_id');
    }

    /**
     * @param  Collection<string, object>  $legacyMappings
     * @return array<string, Role>
     */
    private function resolveRoles(Collection $legacyMappings): array
    {
        $roles = [];
        $resolvedRoleIds = [];

        foreach (array_keys(self::ROLE_PERMISSIONS) as $name) {
            $legacySourceId = array_search($name, self::LEGACY_ROLE_NAMES, true);
            $mapping = $legacySourceId === false ? null : $legacyMappings->get($legacySourceId);
            $mappedRole = $mapping === null
                ? null
                : Role::query()->where('guard_name', 'web')->find($mapping->target_id);

            if ($mapping !== null && $mappedRole === null) {
                throw new RuntimeException("Falta el rol Spatie mapeado para rol:{$legacySourceId}.");
            }

            $matchingRoles = Role::query()
                ->where('guard_name', 'web')
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->get();
            $role = $mappedRole ?? $matchingRoles->first();
            $role ??= Role::query()->create([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            if (isset($resolvedRoleIds[$role->getKey()])) {
                throw new RuntimeException('Dos roles heredados apuntan al mismo rol Spatie; revise legacy_import_mappings.');
            }

            $resolvedRoleIds[$role->getKey()] = true;

            foreach ($matchingRoles as $matchingRole) {
                if ($matchingRole->is($role)) {
                    continue;
                }

                $this->moveRoleAssignments($matchingRole, $role);
                $matchingRole->delete();
            }

            if ($role->name !== $name) {
                $role->name = $name;
                $role->save();
            }

            $roles[$name] = $role;
        }

        return $roles;
    }

    /**
     * @param  Collection<string, object>  $legacyMappings
     * @param  array<string, Role>  $roles
     */
    private function migrateLegacyRoleAssignments(Collection $legacyMappings, array $roles): void
    {
        foreach (self::LEGACY_ROLE_NAMES as $sourceId => $name) {
            $mapping = $legacyMappings->get($sourceId);

            if ($mapping === null) {
                continue;
            }

            $legacyRole = Role::query()->where('guard_name', 'web')->find($mapping->target_id);

            if ($legacyRole === null) {
                throw new RuntimeException("Falta el rol Spatie mapeado para rol:{$sourceId}.");
            }

            $canonicalRole = $roles[$name];

            if (! $legacyRole->is($canonicalRole)) {
                $this->moveRoleAssignments($legacyRole, $canonicalRole);

                if (! $legacyRole->users()->exists()) {
                    $legacyRole->delete();
                }
            }

            DB::table('legacy_import_mappings')
                ->where('id', $mapping->id)
                ->update([
                    'target_id' => $canonicalRole->getKey(),
                    'updated_at' => now(),
                ]);
        }
    }

    private function moveRoleAssignments(Role $sourceRole, Role $targetRole): void
    {
        $sourceRole->users()
            ->orderBy('users.id')
            ->chunkById(100, function (Collection $users) use ($sourceRole, $targetRole): void {
                foreach ($users as $user) {
                    if (! $user instanceof User) {
                        continue;
                    }

                    $user->assignRole($targetRole);
                    $user->removeRole($sourceRole);
                }
            });
    }
}
