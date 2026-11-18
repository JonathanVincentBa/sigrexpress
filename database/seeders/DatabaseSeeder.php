<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            // 1. Importa todos los SQL heredados, normaliza ciudades y migra los usuarios con sus IDs originales
            LegacySqlImportSeeder::class,

            // 2. Localiza tu usuario administrador (o lo crea si no existía) y le asigna el rol Super Admin
            AdminUserSeeder::class,

            // 3. Asigna las agencias activas a las guías históricas que quedaron sin sucursal
            AssignLegacyGuideBranchesSeeder::class,
        ]);
    }
}