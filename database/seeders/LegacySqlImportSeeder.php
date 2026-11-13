<?php

namespace Database\Seeders;

use App\Services\LegacySqlDumpReader;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Spatie\Permission\Models\Role;

class LegacySqlImportSeeder extends Seeder
{
    /**
     * @var array<string, string>
     */
    private const LEGACY_ROLE_NAMES = [
        '1' => 'Administrador',
        '2' => 'Secretaria',
        '3' => 'Chofer',
        '4' => 'CLIENTE',
    ];

    /**
     * Import the legacy catalogs and operational data without replacing local records.
     */
    public function run(): void
    {
        $files = config('legacy-import.files');

        if (! is_array($files)) {
            throw new RuntimeException('La configuración de archivos heredados no es válida.');
        }

        foreach (array_merge(
            array_keys($this->tableDefinitions()),
            ['rol', 'users'],
        ) as $fileKey) {
            $path = $files[$fileKey] ?? null;

            if (! is_string($path) || $path === '' || ! is_file($path) || ! is_readable($path)) {
                throw new RuntimeException("Configure un archivo SQL legible para importar {$fileKey}.");
            }
        }

        if (! DB::getSchemaBuilder()->hasTable('legacy_import_mappings')) {
            throw new RuntimeException('Ejecute las migraciones antes de importar archivos heredados.');
        }

        foreach (array_keys($this->tableDefinitions()) as $table) {
            if (! DB::getSchemaBuilder()->hasTable($table)) {
                throw new RuntimeException("La tabla {$table} no existe; ejecute las migraciones antes de importar.");
            }
        }

        $reader = app(LegacySqlDumpReader::class);
        $chunkSize = max(1, (int) config('legacy-import.chunk_size', 500));

        $this->call(LegacyAccessControlSeeder::class);
        $this->importRoles($reader, $files['rol'], $chunkSize);

        foreach (['empresa', 'cargo', 'ciudad', 'formas_pago', 'motivo_traslado', 'sucursal'] as $sourceTable) {
            $this->importTable($reader, $files[$sourceTable], $sourceTable, $chunkSize);
        }

        $this->call(NormalizeLegacyBranchCitySeeder::class);

        $this->importTable($reader, $files['persona'], 'persona', $chunkSize);
        $this->call(LegacyUsersSeeder::class);
        $this->importTable($reader, $files['cabecera'], 'cabecera', $chunkSize);
        $this->importTable($reader, $files['detalle'], 'detalle', $chunkSize);
    }

    /**
     * @return array<string, array{
     *     table: string,
     *     primary_key: string,
     *     columns: list<string>
     * }>
     */
    private function tableDefinitions(): array
    {
        return [
            'empresa' => [
                'table' => 'empresa',
                'primary_key' => 'id_empresa',
                'columns' => ['razon_social', 'ruc', 'direccion', 'telefono', 'email', 'representante', 'porcentaje_impuesto', 'logo', 'estado', 'created_at', 'updated_at'],
            ],
            'cargo' => [
                'table' => 'cargo',
                'primary_key' => 'id_cargo',
                'columns' => ['descripcion', 'estado', 'created_at', 'updated_at'],
            ],
            'ciudad' => [
                'table' => 'ciudad',
                'primary_key' => 'id_ciudad',
                'columns' => ['descripcion', 'estado', 'created_at', 'updated_at'],
            ],
            'formas_pago' => [
                'table' => 'formas_pago',
                'primary_key' => 'id_formas_pago',
                'columns' => ['descripcion', 'estado', 'created_at', 'updated_at'],
            ],
            'motivo_traslado' => [
                'table' => 'motivo_traslado',
                'primary_key' => 'id_motivo_traslado',
                'columns' => ['descripcion', 'estado', 'created_at', 'updated_at'],
            ],
            'sucursal' => [
                'table' => 'sucursal',
                'primary_key' => 'id_sucursal',
                'columns' => ['id_empresa', 'id_ciudad', 'direccion', 'telefono', 'email', 'estado', 'created_at', 'updated_at'],
            ],
            'persona' => [
                'table' => 'persona',
                'primary_key' => 'id_persona',
                'columns' => ['id_empresa', 'id_cargo', 'tipo_persona', 'nombre', 'direccion', 'tipo_dni', 'num_dni', 'telefono', 'estado', 'created_at', 'updated_at'],
            ],
            'cabecera' => [
                'table' => 'cabecera',
                'primary_key' => 'id_cabecera',
                'columns' => [
                    'id_sucursal', 'id_motivo_traslado', 'id_forma_pago', 'id_users', 'id_persona', 'id_users_update',
                    'fecha_cancelacion', 'ciudad_origen', 'ciudad_destino', 'nom_remitente', 'dni_remitente',
                    'direccion_remitente', 'fono_remitente', 'nom_destinatario', 'dni_destinatario',
                    'direccion_destinatario', 'fono_destinatario', 'num_guia', 'num_guia_trans', 'fecha_emision',
                    'fecha_recepcion', 'guia_rem_cliente', 'factura_cliente', 'flete', 'recargo', 'valor_guia',
                    'prima', 'nombre_recibe', 'dni_recibe', 'estatus_entrega', 'estatus_cobro', 'estado', 'nota',
                    'created_at', 'updated_at',
                ],
            ],
            'detalle' => [
                'table' => 'detalle',
                'primary_key' => 'id_detalle',
                'columns' => ['id_cabecera', 'cantidad', 'descripcion', 'v_unitario', 'v_parcial', 'created_at', 'updated_at'],
            ],
        ];
    }

    private function importRoles(LegacySqlDumpReader $reader, string $path, int $chunkSize): void
    {
        $batch = [];

        foreach ($reader->rows($path, 'rol') as $row) {
            $batch[] = $row;

            if (count($batch) >= $chunkSize) {
                $this->importRoleBatch($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->importRoleBatch($batch);
        }
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     */
    private function importRoleBatch(array $rows): void
    {
        DB::transaction(function () use ($rows): void {
            $seenIds = [];

            foreach ($rows as $row) {
                $sourceId = $this->positiveId($row['id_rol'] ?? null, 'rol', 'id_rol');

                if (isset($seenIds[$sourceId])) {
                    throw new RuntimeException("El archivo rol.sql repite el identificador {$sourceId}.");
                }

                $seenIds[$sourceId] = true;
                $name = self::LEGACY_ROLE_NAMES[(string) $sourceId] ?? null;

                if ($name === null) {
                    throw new RuntimeException("El id_rol {$sourceId} no tiene un rol Spatie oficial definido.");
                }

                $checksum = hash('sha256', serialize($row));
                $mapping = DB::table('legacy_import_mappings')
                    ->where('source_table', 'rol')
                    ->where('source_id', (string) $sourceId)
                    ->first();

                if ($mapping !== null) {
                    if (! hash_equals($mapping->checksum, $checksum)) {
                        throw new RuntimeException("El rol heredado {$sourceId} cambió desde su importación anterior.");
                    }

                    if (! Role::query()->whereKey($mapping->target_id)->where('guard_name', 'web')->exists()) {
                        throw new RuntimeException("Falta el rol Spatie mapeado para rol:{$sourceId}.");
                    }

                    continue;
                }

                $role = Role::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);

                DB::table('legacy_import_mappings')->insert([
                    'source_table' => 'rol',
                    'source_id' => (string) $sourceId,
                    'target_id' => $role->getKey(),
                    'checksum' => $checksum,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });
    }

    private function importTable(LegacySqlDumpReader $reader, string $path, string $sourceTable, int $chunkSize): void
    {
        $definition = $this->tableDefinitions()[$sourceTable];
        $batch = [];

        foreach ($reader->rows($path, $sourceTable) as $row) {
            $batch[] = $row;

            if (count($batch) >= $chunkSize) {
                $this->importTableBatch($sourceTable, $definition, $batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            $this->importTableBatch($sourceTable, $definition, $batch);
        }
    }

    /**
     * @param  array{table: string, primary_key: string, columns: list<string>}  $definition
     * @param  list<array<string, string|null>>  $rows
     */
    private function importTableBatch(string $sourceTable, array $definition, array $rows): void
    {
        $table = $definition['table'];
        $primaryKey = $definition['primary_key'];

        DB::transaction(function () use ($sourceTable, $definition, $rows, $table, $primaryKey): void {
            $preparedRows = [];
            $sourceIds = [];

            foreach ($rows as $row) {
                $sourceId = $this->positiveId($row[$primaryKey] ?? null, $sourceTable, $primaryKey);

                if (isset($sourceIds[$sourceId])) {
                    throw new RuntimeException("El archivo {$sourceTable}.sql repite el identificador {$sourceId}.");
                }

                $sourceIds[$sourceId] = true;
                $preparedRows[$sourceId] = [
                    'source' => $row,
                    'attributes' => $this->attributesFor($definition['columns'], $row),
                    'checksum' => hash('sha256', serialize($row)),
                ];
            }

            if ($sourceTable === 'detalle') {
                $this->clearMissingDetailReferences($preparedRows);
            }

            $mappings = DB::table('legacy_import_mappings')
                ->where('source_table', $sourceTable)
                ->whereIn('source_id', array_map('strval', array_keys($preparedRows)))
                ->get()
                ->keyBy('source_id');
            $mappedTargetIds = $mappings->pluck('target_id')->unique()->all();
            $existingMappedTargetIds = DB::table($table)
                ->whereIn($primaryKey, $mappedTargetIds)
                ->pluck($primaryKey)
                ->mapWithKeys(fn (int $id): array => [(string) $id => true])
                ->all();

            $pendingRows = [];
            $pendingMappings = [];

            foreach ($preparedRows as $sourceId => $prepared) {
                $mapping = $mappings->get((string) $sourceId);

                if ($mapping !== null) {
                    if (! hash_equals($mapping->checksum, $prepared['checksum'])) {
                        throw new RuntimeException("El registro {$sourceTable}:{$sourceId} cambió desde su importación anterior.");
                    }

                    if (! isset($existingMappedTargetIds[(string) $mapping->target_id])) {
                        throw new RuntimeException("Falta el registro local mapeado para {$sourceTable}:{$sourceId}.");
                    }

                    continue;
                }

                $attributes = $prepared['attributes'];
                $attributes[$primaryKey] = $sourceId;
                $pendingRows[] = $attributes;
                $pendingMappings[] = [
                    'source_table' => $sourceTable,
                    'source_id' => (string) $sourceId,
                    'target_id' => $sourceId,
                    'checksum' => $prepared['checksum'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if ($pendingRows === []) {
                return;
            }

            $existingIds = DB::table($table)
                ->whereIn($primaryKey, array_column($pendingRows, $primaryKey))
                ->pluck($primaryKey)
                ->all();

            if ($existingIds !== []) {
                throw new RuntimeException("Colisión de identificador al importar {$sourceTable}:".implode(', ', $existingIds).'; no se modificaron registros existentes.');
            }

            DB::table($table)->insert($pendingRows);
            DB::table('legacy_import_mappings')->insert($pendingMappings);
        });
    }

    /**
     * @param  list<string>  $columns
     * @param  array<string, string|null>  $row
     * @return array<string, mixed>
     */
    private function attributesFor(array $columns, array $row): array
    {
        $attributes = [];

        foreach ($columns as $column) {
            if (! array_key_exists($column, $row)) {
                continue;
            }

            $value = $row[$column];

            if (is_string($value)) {
                $value = trim($value);

                if (preg_match('/^0000-00-00(?: 00:00:00)?$/', $value) === 1) {
                    $value = null;
                } else {
                    $value = $column === 'email' ? mb_strtolower($value) : mb_strtoupper($value);
                }
            }

            if (in_array($column, ['id_cabecera', 'id_persona', 'id_sucursal', 'id_users_update'], true) && in_array($value, [null, '', '0', 0], true)) {
                $value = null;
            }

            $attributes[$column] = $value;
        }

        return $attributes;
    }

    /**
     * @param  array<int, array{source: array<string, string|null>, attributes: array<string, mixed>, checksum: string}>  $preparedRows
     */
    private function clearMissingDetailReferences(array &$preparedRows): void
    {
        $guideIds = collect($preparedRows)
            ->pluck('attributes.id_cabecera')
            ->filter(fn (mixed $id): bool => $id !== null)
            ->unique()
            ->values()
            ->all();
        $existingGuideIds = DB::table('cabecera')
            ->whereIn('id_cabecera', $guideIds)
            ->pluck('id_cabecera')
            ->mapWithKeys(fn (int $id): array => [(string) $id => true])
            ->all();

        foreach ($preparedRows as $sourceId => &$prepared) {
            $guideId = $prepared['attributes']['id_cabecera'] ?? null;

            if ($guideId === null || isset($existingGuideIds[(string) $guideId])) {
                continue;
            }

            $prepared['attributes']['id_cabecera'] = null;
            $this->command?->warn("El detalle heredado {$sourceId} refiere a la cabecera {$guideId} inexistente; se conservó sin relación.");
        }

        unset($prepared);
    }

    private function positiveId(?string $value, string $table, string $column): int
    {
        if ($value === null || ! ctype_digit($value) || (int) $value < 1) {
            throw new RuntimeException("El identificador {$column} del registro {$table} no es válido.");
        }

        return (int) $value;
    }
}
