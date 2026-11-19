<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SecretariaUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function (): void {
            // Buscamos sucursales específicas por nombre o ciudad, o tomamos una activa de respaldo
            $sucursalQuito = Sucursal::query()->where('nombre', 'like', '%QUITO%')->where('estado', '1')->first() 
                ?? Sucursal::query()->where('estado', '1')->orderBy('id_sucursal')->first();

            $sucursalIbarra = Sucursal::query()->where('nombre', 'like', '%IBARRA%')->where('estado', '1')->first() 
                ?? $sucursalQuito;

            $secretarias = [
                [
                    'name' => 'IRIS CAROLINA ALIENDRES VILLANUEVA',
                    'email' => 'iris.c.aliendres@gmail.com',
                    'id_sucursal' => $sucursalQuito?->id_sucursal, // Asignamos la sucursal correspondiente
                ],
                [
                    'name' => 'ELIZABETH ALIENDRES',
                    'email' => 'elizabra1407@hotmail.com',
                    'id_sucursal' => $sucursalIbarra?->id_sucursal,
                ],
            ];

            foreach ($secretarias as $data) {
                $user = User::query()->where('email', $data['email'])->first();

                if ($user === null) {
                    $user = User::query()->create([
                        'name' => $data['name'],
                        'email' => $data['email'],
                        'password' => Hash::make('Password123*'),
                        'id_sucursal' => $data['id_sucursal'],
                        'estado' => '1',
                    ]);
                } else {
                    $user->update([
                        'id_sucursal' => $data['id_sucursal'],
                        'estado' => '1',
                    ]);
                }

                if ($user->email_verified_at === null) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                if (! $user->hasRole('Secretaria')) {
                    $user->assignRole('Secretaria');
                }
            }
        });
    }
}