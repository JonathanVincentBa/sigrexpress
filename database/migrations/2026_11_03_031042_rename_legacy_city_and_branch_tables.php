<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->convertToInnoDb('ciudad');
        $this->convertToInnoDb('sucursal');

        $this->dropForeignKeys('sucursal', 'id_ciudad');
        $this->dropForeignKeys('cabecera', 'id_sucursal');

        if (Schema::hasTable('sucursal')) {
            if (! Schema::hasColumn('sucursal', 'nombre')) {
                Schema::table('sucursal', function (Blueprint $table): void {
                    $table->string('nombre', 100)->default('Sucursal');
                });

                if (Schema::hasTable('ciudad')) {
                    DB::table('sucursal')
                        ->join('ciudad', 'sucursal.id_ciudad', '=', 'ciudad.id_ciudad')
                        ->select('sucursal.id_sucursal', 'ciudad.descripcion')
                        ->orderBy('sucursal.id_sucursal')
                        ->get()
                        ->each(function (object $branch): void {
                            DB::table('sucursal')
                                ->where('id_sucursal', $branch->id_sucursal)
                                ->update(['nombre' => 'Agencia '.mb_substr(trim($branch->descripcion), 0, 90)]);
                        });
                }
            }

            if (! Schema::hasColumn('sucursal', 'deleted_at')) {
                Schema::table('sucursal', function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }

        if (Schema::hasTable('ciudad')) {
            if (! Schema::hasColumn('ciudad', 'deleted_at')) {
                Schema::table('ciudad', function (Blueprint $table): void {
                    $table->softDeletes();
                });
            }
        }

        if (Schema::hasTable('sucursal') && Schema::hasTable('ciudad') && ! $this->usesSqlite()) {
            Schema::table('sucursal', function (Blueprint $table): void {
                $table->foreign('id_ciudad', 'sucursal_id_ciudad_foreign')
                    ->references('id_ciudad')
                    ->on('ciudad')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }

        if (
            Schema::hasTable('cabecera')
            && Schema::hasTable('sucursal')
            && ! $this->usesSqlite()
            && $this->usesInnoDb('cabecera')
        ) {
            Schema::table('cabecera', function (Blueprint $table): void {
                $table->foreign('id_sucursal', 'cabecera_id_sucursal_foreign')
                    ->references('id_sucursal')
                    ->on('sucursal')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        $this->dropForeignKeys('cabecera', 'id_sucursal');
        $this->dropForeignKeys('sucursal', 'id_ciudad');

        if (Schema::hasTable('sucursal') && Schema::hasTable('ciudad') && ! $this->usesSqlite()) {
            Schema::table('sucursal', function (Blueprint $table): void {
                $table->foreign('id_ciudad', 'sucursal_id_ciudad_foreign')
                    ->references('id_ciudad')
                    ->on('ciudad')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }

        if (
            Schema::hasTable('cabecera')
            && Schema::hasTable('sucursal')
            && ! $this->usesSqlite()
            && $this->usesInnoDb('cabecera')
        ) {
            Schema::table('cabecera', function (Blueprint $table): void {
                $table->foreign('id_sucursal', 'cabecera_id_sucursal_foreign')
                    ->references('id_sucursal')
                    ->on('sucursal')
                    ->cascadeOnUpdate()
                    ->restrictOnDelete();
            });
        }
    }

    private function dropForeignKeys(string $tableName, string $columnName): void
    {
        if (! Schema::hasTable($tableName) || $this->usesSqlite()) {
            return;
        }

        foreach (Schema::getForeignKeys($tableName) as $foreignKey) {
            if (in_array($columnName, $foreignKey['columns'], true)) {
                Schema::table($tableName, function (Blueprint $table) use ($foreignKey): void {
                    $table->dropForeign($foreignKey['name']);
                });
            }
        }
    }

    private function convertToInnoDb(string $tableName): void
    {
        if (Schema::hasTable($tableName) && DB::connection()->getDriverName() === 'mysql' && ! $this->usesInnoDb($tableName)) {
            DB::statement("ALTER TABLE `{$tableName}` ENGINE = InnoDB");
        }
    }

    private function usesInnoDb(string $tableName): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return true;
        }

        $table = DB::selectOne(
            'SELECT ENGINE FROM information_schema.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?',
            [$tableName]
        );

        return strtoupper((string) ($table->ENGINE ?? '')) === 'INNODB';
    }

    private function usesSqlite(): bool
    {
        return DB::connection()->getDriverName() === 'sqlite';
    }
};
