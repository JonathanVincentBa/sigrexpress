<?php

namespace Database\Seeders;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AssignLegacyGuideBranchesSeeder extends Seeder
{
    /**
     * Map legacy guide origins to the corresponding active branch names.
     *
     * Machachi is omitted because two active branch records have the same name.
     *
     * @var array<string, string>
     */
    private const ORIGIN_BRANCHES = [
        'QUITO' => 'AGENCIA QUITO',
        'QUITO SUR' => 'AGENCIA QUITO SUR',
        'GUAYAQUIL' => 'AGENCIA GUAYAQUIL',
        'VERSALLES' => 'AGENCIA VERSALLES',
        'AMBATO' => 'AGENCIA AMBATO',
        'CUENCA' => 'AGENCIA CUENCA',
        'VALLE SAN RAFAEL' => 'AGENCIA SAN RAFAEL',
        'TUMBACO' => 'AGENCIA TUMBACO',
        'LATACUNGA' => 'AGENCIA LATACUNGA',
        'RIOBAMBA' => 'AGENCIA RIOBAMBA',
        'QUITO CALDERON' => 'AGENCIA CALDERON',
        'BODEGA A2' => 'BODEGA A2',
        'QUITO COLON' => 'AGENCIA COLON',
        'IBARRA' => 'AGENCIA IBARRA',
        'TAMBILLO' => 'AGENCIA TAMBILLO',
        'SANGOLQUI' => 'AGENCIA SANGOLQUI',
        'EL TRIUNFO' => 'AGENCIA EL TRIUNFO',
        'OTAVALO' => 'AGENCIA OTAVALO',
        'QUITO CENTRO TEJAR' => 'AGENCIA EL TEJAR',
        'LA TRONCAL' => 'AGENCIA LA TRONCAL',
        'CAYAMBE' => 'AGENCIA CAYAMBE',
        'MILAGRO' => 'AGENCIA MILAGRO',
        'RIOBAMBA 2' => 'AGENCIA RIOBAMBA 2',
        'QUITO PRENSA SR HUACA' => 'AGENCIA LA PRENSA',
        'BUCAY' => 'AGENCIA BUCAY',
        'SANTO DOMINGO' => 'AGENCIA SANTO DOMINGO',
        'GUAMANI' => 'AGENCIA GUAMANI',
        'CUMANDA' => 'AGENCIA CUMANDA',
        'ALOAG' => 'AGENCIA ALOAG',
        'QUITUMBE' => 'AGENCIA QUITUMBE',
        'GUALACEO' => 'AGENCIA GUALACEO',
        'GUAYAQUIL LUQUE' => 'AGENCIA LUQUE',
        'EL GUAMBRA' => 'AGENCIA QUITO',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $branchesByName = DB::table('sucursal')
            ->where('estado', '1')
            ->whereNull('deleted_at')
            ->orderBy('id_sucursal')
            ->get(['id_sucursal', 'nombre'])
            ->groupBy(fn (object $branch): string => $this->normalize($branch->nombre));
        $branchIdsByOrigin = $this->resolveBranchIds($branchesByName);

        if ($branchIdsByOrigin === []) {
            $this->command?->warn('No se encontraron sucursales activas no ambiguas para asignar guías.');

            return;
        }

        $chunkSize = max(1, (int) config('legacy-import.chunk_size', 10000));
        $updatedGuides = 0;
        $case = 'CASE UPPER(TRIM(ciudad_origen))';

        foreach ($branchIdsByOrigin as $origin => $branchId) {
            $case .= ' WHEN '.DB::connection()->getPdo()->quote($origin).' THEN '.(int) $branchId;
        }

        $case .= ' ELSE id_sucursal END';

        DB::table('cabecera')
            ->select('id_cabecera')
            ->selectRaw('UPPER(TRIM(ciudad_origen)) AS normalized_origin')
            ->whereIn(DB::raw('UPPER(TRIM(ciudad_origen))'), array_keys($branchIdsByOrigin))
            ->where(function ($query): void {
                $this->whereBranchIsMissing($query);
            })
            ->chunkById($chunkSize, function (Collection $guides) use ($branchIdsByOrigin, $case, &$updatedGuides): void {
                $guideIds = $guides->pluck('id_cabecera')->all();

                $updatedGuides += DB::table('cabecera')
                    ->whereIn('id_cabecera', $guideIds)
                    ->whereIn(DB::raw('UPPER(TRIM(ciudad_origen))'), array_keys($branchIdsByOrigin))
                    ->where(function ($query): void {
                        $this->whereBranchIsMissing($query);
                    })
                    ->update(['id_sucursal' => DB::raw($case)]);
            }, 'id_cabecera');

        $this->command?->info("Assigned {$updatedGuides} legacy guides to their matching branches.");
        $this->command?->warn('Guides with origin MACHACHI remain unassigned because two active branches share that name.');
    }

    /**
     * @param  Collection<string, Collection<int, object>>  $branchesByName
     * @return array<string, int>
     */
    private function resolveBranchIds(Collection $branchesByName): array
    {
        $branchIdsByOrigin = [];

        foreach (self::ORIGIN_BRANCHES as $origin => $branchName) {
            $branches = $branchesByName->get($this->normalize($branchName), collect());

            if ($branches->count() !== 1) {
                $this->command?->warn(
                    $branches->isEmpty()
                        ? "No active branch matches origin [{$origin}]; its guides were left unchanged."
                        : "Origin [{$origin}] matches multiple active branches; its guides were left unchanged."
                );

                continue;
            }

            $branchIdsByOrigin[$origin] = (int) $branches->first()->id_sucursal;
        }

        return $branchIdsByOrigin;
    }

    private function normalize(string $value): string
    {
        return Str::of(trim($value))
            ->ascii()
            ->upper()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }

    private function whereBranchIsMissing(Builder $query): void
    {
        $query
            ->whereNull('id_sucursal')
            ->orWhere('id_sucursal', 0)
            ->orWhereNotExists(function (Builder $subquery): void {
                $subquery
                    ->selectRaw('1')
                    ->from('sucursal as guide_branch')
                    ->whereColumn('guide_branch.id_sucursal', 'cabecera.id_sucursal');
            });
    }
}
