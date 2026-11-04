<?php

namespace Database\Seeders;

use App\Models\Ciudad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class NormalizeLegacyBranchCitySeeder extends Seeder
{
    /**
     * @var array<string, array{city: string, branch: string|null}>
     */
    private const BRANCH_ALIASES = [
        'QUITO SUR' => ['city' => 'Quito', 'branch' => 'Agencia Quito Sur'],
        'VALLE SAN RAFAEL' => ['city' => 'Quito', 'branch' => 'Agencia San Rafael'],
        'GUAYAQUIL LUQUE' => ['city' => 'Guayaquil', 'branch' => 'Agencia Luque'],
        'QUITUMBE' => ['city' => 'Quito', 'branch' => 'Agencia Quitumbe'],
        'QUITO COLON' => ['city' => 'Quito', 'branch' => 'Agencia Colón'],
        'BODEGA QUITO' => ['city' => 'Quito', 'branch' => 'Bodega Quito'],
        'QUITO CALDERON' => ['city' => 'Quito', 'branch' => 'Agencia Calderón'],
        'VERSALLES' => ['city' => 'Quito', 'branch' => 'Agencia Versalles'],
        'TUMBACO' => ['city' => 'Quito', 'branch' => 'Agencia Tumbaco'],
        'EL GUAMBRA' => ['city' => 'Quito', 'branch' => null],
        'BODEGA IBARRA' => ['city' => 'Ibarra', 'branch' => 'Bodega Ibarra'],
        'BODEGA OTAVALO' => ['city' => 'Otavalo', 'branch' => 'Bodega Otavalo'],
        'BODEGA ANT IBARRA' => ['city' => 'Ibarra', 'branch' => 'Bodega Antigua Ibarra'],
        'BODEGA VERSALLES' => ['city' => 'Quito', 'branch' => 'Bodega Versalles'],
        'BODEGA CUENCA' => ['city' => 'Cuenca', 'branch' => 'Bodega Cuenca'],
        'BODEGA CAYAMBE' => ['city' => 'Cayambe', 'branch' => 'Bodega Cayambe'],
        'BODEGA A2' => ['city' => 'Ambato', 'branch' => 'Bodega A2'],
        'QUITO CENTRO TEJAR' => ['city' => 'Quito', 'branch' => 'Agencia El Tejar'],
        'NATABUELA IBARRA' => ['city' => 'Ibarra', 'branch' => 'Agencia Natabuela'],
        'QUITO PRENSA SR HUACA' => ['city' => 'Quito', 'branch' => 'Agencia La Prensa'],
        'GUAMANI' => ['city' => 'Quito', 'branch' => 'Agencia Guamaní'],
        'QUITO CALIFORNIA' => ['city' => 'Quito', 'branch' => 'Agencia California'],
    ];

    public function run(): void
    {
        if (! DB::getSchemaBuilder()->hasTable('ciudad') || ! DB::getSchemaBuilder()->hasTable('sucursal')) {
            throw new RuntimeException('Run the legacy city and branch table migration before normalizing this data.');
        }

        $createdBranches = 0;
        $normalizedCities = 0;

        DB::transaction(function () use (&$createdBranches, &$normalizedCities): void {
            $cityCache = Ciudad::withTrashed()->orderBy('id_ciudad')->get();
            $legacyCities = $cityCache
                ->filter(fn (Ciudad $city): bool => isset(self::BRANCH_ALIASES[$this->normalize($city->descripcion)]))
                ->groupBy(fn (Ciudad $city): string => $this->normalize($city->descripcion));

            foreach ($legacyCities as $legacyName => $legacyRows) {
                $mapping = self::BRANCH_ALIASES[$legacyName];
                $isActive = $legacyRows->contains(fn (Ciudad $city): bool => $city->estado === '1');
                $canonicalCity = $this->findOrCreateCity($mapping['city'], $isActive, $cityCache);
                $legacyIds = $legacyRows->pluck('id_ciudad');
                $branches = Sucursal::withTrashed()->whereIn('id_ciudad', $legacyIds)->get();

                if ($branches->isEmpty() && $isActive && $mapping['branch'] !== null) {
                    $company = $this->resolveCompanyForMissingBranch($legacyName);

                    Sucursal::query()->create([
                        'id_empresa' => $company->id_empresa,
                        'id_ciudad' => $canonicalCity->id_ciudad,
                        'nombre' => $mapping['branch'],
                        'direccion' => '',
                        'telefono' => '',
                        'email' => '',
                        'estado' => '1',
                    ]);
                    $createdBranches++;
                } else {
                    foreach ($branches as $branch) {
                        $legacyBranchName = 'Agencia '.mb_substr(trim($legacyRows->first()->descripcion), 0, 90);

                        if ($mapping['branch'] !== null && ($branch->nombre === 'Sucursal' || $branch->nombre === $legacyBranchName)) {
                            $branch->nombre = $mapping['branch'];
                        }

                        $branch->id_ciudad = $canonicalCity->id_ciudad;
                        $branch->saveQuietly();
                    }
                }

                $this->redirectCityMappings($legacyIds, $canonicalCity->id_ciudad);
                Ciudad::withTrashed()->whereIn('id_ciudad', $legacyIds)->forceDelete();
                $normalizedCities++;
            }

            Sucursal::withTrashed()
                ->where('nombre', 'Sucursal')
                ->with(['ciudad' => fn ($query) => $query->withTrashed()])
                ->get()
                ->each(function (Sucursal $branch): void {
                    if ($branch->ciudad === null) {
                        return;
                    }

                    $branch->nombre = 'Agencia '.mb_substr(trim($branch->ciudad->descripcion), 0, 90);
                    $branch->saveQuietly();
                });

            $this->mergeDuplicateCities();
        });

        $this->command?->info(
            "Normalized {$normalizedCities} legacy branch-city labels and created {$createdBranches} missing branch records."
        );
    }

    /**
     * @param  Collection<int, Ciudad>  $cityCache
     */
    private function findOrCreateCity(string $name, bool $isActive, Collection $cityCache): Ciudad
    {
        $normalizedName = $this->normalize($name);
        $city = $cityCache->first(fn (Ciudad $candidate): bool => $this->normalize($candidate->descripcion) === $normalizedName);

        if ($city === null) {
            $city = Ciudad::query()->create([
                'descripcion' => $name,
                'estado' => $isActive ? '1' : '0',
            ]);
            $cityCache->push($city);
        } elseif ($isActive && $city->estado !== '1') {
            if ($city->trashed()) {
                $city->restore();
            }

            $city->estado = '1';
            $city->saveQuietly();
        }

        return $city;
    }

    private function mergeDuplicateCities(): void
    {
        $remainingCities = Ciudad::withTrashed()->orderBy('id_ciudad')->get();

        foreach ($remainingCities->groupBy(fn (Ciudad $city): string => $this->normalize($city->descripcion)) as $cityGroup) {
            if ($cityGroup->count() < 2) {
                continue;
            }

            $primaryCity = $cityGroup->first(fn (Ciudad $city): bool => $city->estado === '1' && $city->deleted_at === null)
                ?? $cityGroup->first(fn (Ciudad $city): bool => $city->estado === '1')
                ?? $cityGroup->first();
            $cityIds = $cityGroup->pluck('id_ciudad');

            Sucursal::withTrashed()
                ->whereIn('id_ciudad', $cityIds)
                ->where('id_ciudad', '!=', $primaryCity->id_ciudad)
                ->update(['id_ciudad' => $primaryCity->id_ciudad]);
            $this->redirectCityMappings($cityIds, $primaryCity->id_ciudad);

            $primaryCity->estado = $cityGroup->contains(fn (Ciudad $city): bool => $city->estado === '1') ? '1' : '0';
            $primaryCity->descripcion = Str::title(mb_strtolower(trim($primaryCity->descripcion)));

            if ($primaryCity->trashed()) {
                $primaryCity->restore();
            }

            $primaryCity->saveQuietly();

            Ciudad::withTrashed()
                ->whereIn('id_ciudad', $cityIds)
                ->where('id_ciudad', '!=', $primaryCity->id_ciudad)
                ->forceDelete();
        }
    }

    private function normalize(string $name): string
    {
        return Str::of(trim($name))
            ->ascii()
            ->upper()
            ->replaceMatches('/\s+/', ' ')
            ->toString();
    }

    /**
     * Keep source mappings valid after legacy city rows are merged or removed.
     *
     * @param  BaseCollection<int, int>  $sourceCityIds
     */
    private function redirectCityMappings(BaseCollection $sourceCityIds, int $targetCityId): void
    {
        DB::table('legacy_import_mappings')
            ->where('source_table', 'ciudad')
            ->where(function ($query) use ($sourceCityIds): void {
                $query
                    ->whereIn('source_id', $sourceCityIds->map(fn (int $id): string => (string) $id))
                    ->orWhereIn('target_id', $sourceCityIds);
            })
            ->update([
                'target_id' => $targetCityId,
                'updated_at' => now(),
            ]);
    }

    private function resolveCompanyForMissingBranch(string $legacyName): Empresa
    {
        $companyIds = Sucursal::withTrashed()->distinct()->pluck('id_empresa');

        if ($companyIds->count() === 1) {
            $company = Empresa::query()->where('id_empresa', $companyIds->first())->first();

            if ($company !== null) {
                return $company;
            }
        }

        if ($companyIds->isEmpty()) {
            if (Empresa::query()->count() === 1) {
                return Empresa::query()->orderBy('id_empresa')->firstOrFail();
            }
        }

        throw new RuntimeException(
            "Cannot preserve active legacy branch [{$legacyName}]: its company is ambiguous. Assign a company before seeding."
        );
    }
}
