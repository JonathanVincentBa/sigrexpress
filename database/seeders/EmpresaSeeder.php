<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EmpresaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $empresas = [
            [
                'id_empresa' => 1,
                'razon_social' => 'COMPAÑÍA DE TRANSPORTE DE CARGA PESADA Y LIVIANA JHETRO S.A.',
                'ruc' => '1715881056001',
                'direccion' => 'ALGARROBOS E4-115 Y FRANCISCO AGUIRRE',
                'telefono' => '025113136',
                'email' => 'transportejhetro@gmail.com',
                'representante' => 'PEDRO CHAVEZ',
                'porcentaje_impuesto' => 0.00,
                'logo' => 'logo_jhetro.png',
                'estado' => '1',
                'updated_at' => '2018-05-29 14:22:47',
                'created_at' => '2018-03-26 10:00:00',
            ]
        ];

        foreach ($empresas as $row) {
            // Usamos 'ruc' como llave de búsqueda para evitar duplicados e ignorar la restricción del campo id
            DB::table('empresa')->updateOrInsert(['ruc' => $row['ruc']], $row);
        }
    }
}