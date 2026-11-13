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
        Schema::table('users', function (Blueprint $table): void {
            $table->foreignId('id_sucursal')
                ->nullable()
                ->after('id_persona')
                ->constrained('sucursal', 'id_sucursal')
                ->nullOnDelete();
        });

        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->foreignId('id_sucursal')
                ->nullable()
                ->after('id_vehiculo')
                ->constrained('sucursal', 'id_sucursal')
                ->nullOnDelete();
        });

        Schema::table('manifiestos', function (Blueprint $table): void {
            $table->foreignId('id_sucursal')
                ->nullable()
                ->after('id_manifiesto')
                ->constrained('sucursal', 'id_sucursal')
                ->nullOnDelete();
        });

        // 1. Map users from historical guides in cabecera
        if (Schema::hasTable('cabecera') && Schema::hasColumn('cabecera', 'id_sucursal')) {
            $dominantUserBranches = DB::table('cabecera')
                ->select('id_users', 'id_sucursal', DB::raw('count(*) as total'))
                ->whereNotNull('id_sucursal')
                ->whereNotNull('id_users')
                ->groupBy('id_users', 'id_sucursal')
                ->orderByDesc('total')
                ->get()
                ->groupBy('id_users');

            foreach ($dominantUserBranches as $userId => $branches) {
                $dominantBranchId = $branches->first()->id_sucursal;
                DB::table('users')
                    ->where('id', $userId)
                    ->whereNull('id_sucursal')
                    ->update(['id_sucursal' => $dominantBranchId]);
            }
        }

        // 2. Map remaining users by matching persona address/name with sucursales
        if (Schema::hasTable('sucursal') && Schema::hasTable('persona')) {
            $allBranches = DB::table('sucursal')->get();
            $usersWithoutBranch = DB::table('users')
                ->leftJoin('persona', 'users.id_persona', '=', 'persona.id_persona')
                ->whereNull('users.id_sucursal')
                ->select('users.id', 'persona.direccion')
                ->get();

            foreach ($usersWithoutBranch as $u) {
                if (! empty($u->direccion)) {
                    $dirUpper = mb_strtoupper(trim((string) $u->direccion));
                    $matchedBranch = $allBranches->first(function (object $branch) use ($dirUpper): bool {
                        return str_contains(mb_strtoupper((string) $branch->nombre), $dirUpper);
                    });

                    if ($matchedBranch) {
                        DB::table('users')->where('id', $u->id)->update(['id_sucursal' => $matchedBranch->id_sucursal]);
                    }
                }
            }

            // 2b. For any remaining secretarias, ensure they have a default branch assigned
            if (Schema::hasTable('model_has_roles') && Schema::hasTable('roles')) {
                $secretaryRole = DB::table('roles')->where('name', 'Secretaria')->first();
                if ($secretaryRole) {
                    $secretaryUserIds = DB::table('model_has_roles')
                        ->where('role_id', $secretaryRole->id)
                        ->pluck('model_id');

                    $defaultBranchId = DB::table('sucursal')->value('id_sucursal');
                    if ($defaultBranchId) {
                        DB::table('users')
                            ->whereIn('id', $secretaryUserIds)
                            ->whereNull('id_sucursal')
                            ->update(['id_sucursal' => $defaultBranchId]);
                    }
                }
            }
        }

        // 3. Map manifests from vehicles
        $manifestVehicles = DB::table('manifiestos')
            ->join('vehiculos', 'vehiculos.id_vehiculo', '=', 'manifiestos.id_vehiculo')
            ->whereNotNull('vehiculos.id_sucursal')
            ->select('manifiestos.id_manifiesto', 'vehiculos.id_sucursal')
            ->get();

        foreach ($manifestVehicles as $item) {
            DB::table('manifiestos')
                ->where('id_manifiesto', $item->id_manifiesto)
                ->whereNull('id_sucursal')
                ->update(['id_sucursal' => $item->id_sucursal]);
        }

        // 4. Map manifests from single-branch guides
        if (Schema::hasTable('manifiesto_guia') && Schema::hasTable('cabecera')) {
            $singleBranchManifests = DB::table('manifiesto_guia')
                ->join('cabecera', 'cabecera.id_cabecera', '=', 'manifiesto_guia.id_cabecera')
                ->whereNotNull('cabecera.id_sucursal')
                ->groupBy('manifiesto_guia.id_manifiesto')
                ->havingRaw('COUNT(DISTINCT cabecera.id_sucursal) = 1')
                ->selectRaw('manifiesto_guia.id_manifiesto, MIN(cabecera.id_sucursal) as id_sucursal')
                ->get();

            foreach ($singleBranchManifests as $manifest) {
                DB::table('manifiestos')
                    ->where('id_manifiesto', $manifest->id_manifiesto)
                    ->whereNull('id_sucursal')
                    ->update(['id_sucursal' => $manifest->id_sucursal]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('manifiestos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_sucursal');
        });

        Schema::table('vehiculos', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_sucursal');
        });

        Schema::table('users', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('id_sucursal');
        });
    }
};
