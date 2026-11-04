<?php

namespace Database\Seeders;

use App\Models\Cabecera;
use App\Models\Cargo;
use App\Models\Ciudad;
use App\Models\Detalle;
use App\Models\Empresa;
use App\Models\FormasPago;
use App\Models\MotivoTraslado;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Vehiculo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class OperationalDemoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if (! app()->environment(['local', 'testing'])) {
            throw new RuntimeException('Los registros demostrativos solo se pueden crear en entornos local o testing.');
        }

        DB::transaction(function (): void {
            $user = User::query()->where('estado', '1')->orderBy('id')->first();

            if ($user === null) {
                throw new RuntimeException('Crea o importa un usuario activo antes de ejecutar OperationalDemoSeeder.');
            }

            $company = Empresa::query()->where('estado', '1')->orderBy('id_empresa')->first()
                ?? Empresa::query()->firstOrCreate(
                    ['ruc' => '0000000000001'],
                    [
                        'razon_social' => 'EMPRESA DEMOSTRATIVA',
                        'direccion' => 'DIRECCIÓN DE PRUEBA',
                        'telefono' => '0000000000',
                        'email' => 'demo@example.test',
                        'representante' => 'REPRESENTANTE DE PRUEBA',
                        'porcentaje_impuesto' => 0,
                        'estado' => '1',
                    ],
                );
            $city = Ciudad::query()->firstOrCreate(
                ['descripcion' => 'CIUDAD DEMOSTRATIVA'],
                ['estado' => '1'],
            );
            $branch = Sucursal::query()->firstOrCreate(
                [
                    'nombre' => 'AGENCIA DEMOSTRATIVA',
                    'id_empresa' => $company->id_empresa,
                    'id_ciudad' => $city->id_ciudad,
                ],
                [
                    'direccion' => 'DIRECCIÓN DE PRUEBA',
                    'telefono' => '0000000000',
                    'email' => 'agencia-demo@example.test',
                    'estado' => '1',
                ],
            );
            $clientJob = Cargo::query()->firstOrCreate(
                ['descripcion' => 'CLIENTE DEMOSTRATIVO'],
                ['estado' => '1'],
            );
            $driverJob = Cargo::query()->firstOrCreate(
                ['descripcion' => 'CHOFER DEMOSTRATIVO'],
                ['estado' => '1'],
            );
            $client = Persona::query()->firstOrCreate(
                ['num_dni' => '0000000000', 'tipo_persona' => 'C', 'nombre' => 'CLIENTE DEMOSTRATIVO'],
                [
                    'id_empresa' => $company->id_empresa,
                    'id_cargo' => $clientJob->id_cargo,
                    'direccion' => 'DIRECCIÓN DE PRUEBA',
                    'tipo_dni' => 'C',
                    'telefono' => '0000000000',
                    'estado' => '1',
                ],
            );
            $driver = Persona::query()->firstOrCreate(
                ['num_dni' => '0000000001', 'tipo_persona' => 'E', 'nombre' => 'CHOFER DEMOSTRATIVO'],
                [
                    'id_empresa' => $company->id_empresa,
                    'id_cargo' => $driverJob->id_cargo,
                    'direccion' => 'DIRECCIÓN DE PRUEBA',
                    'tipo_dni' => 'C',
                    'telefono' => '0000000001',
                    'estado' => '1',
                ],
            );
            $reason = MotivoTraslado::query()->firstOrCreate(
                ['descripcion' => 'TRASLADO DEMOSTRATIVO'],
                ['estado' => '1'],
            );
            $payment = FormasPago::query()->firstOrCreate(
                ['descripcion' => 'PAGO DEMOSTRATIVO'],
                ['estado' => '1'],
            );
            $guide = Cabecera::query()->where('nota', 'REGISTRO DEMOSTRATIVO SIGREXPRESS')->first();

            if ($guide === null) {
                $guide = Cabecera::query()->create([
                    'num_guia' => (int) Cabecera::withTrashed()->max('num_guia') + 1,
                    'id_sucursal' => $branch->id_sucursal,
                    'id_motivo_traslado' => $reason->id_motivo_traslado,
                    'id_forma_pago' => $payment->id_formas_pago,
                    'id_users' => $user->id,
                    'id_persona' => $client->id_persona,
                    'ciudad_origen' => $city->descripcion,
                    'ciudad_destino' => $city->descripcion,
                    'nom_remitente' => 'CLIENTE DEMOSTRATIVO',
                    'dni_remitente' => $client->num_dni,
                    'direccion_remitente' => $client->direccion,
                    'fono_remitente' => $client->telefono,
                    'nom_destinatario' => 'DESTINATARIO DEMOSTRATIVO',
                    'dni_destinatario' => '0000000002',
                    'direccion_destinatario' => 'DIRECCIÓN DE PRUEBA',
                    'fono_destinatario' => '0000000002',
                    'num_guia_trans' => 0,
                    'fecha_emision' => now(),
                    'flete' => 1,
                    'valor_guia' => 1,
                    'estatus_cobro' => 'Pendiente',
                    'estado' => '1',
                    'estado_tracking' => 'EN BODEGA',
                    'nota' => 'REGISTRO DEMOSTRATIVO SIGREXPRESS',
                ]);
            }

            Detalle::query()->firstOrCreate(
                ['id_cabecera' => $guide->id_cabecera, 'descripcion' => 'PAQUETE DEMOSTRATIVO'],
                ['cantidad' => 1, 'v_unitario' => 1, 'v_parcial' => 1],
            );

            $vehicle = Vehiculo::query()
                ->where('marca', 'MARCA DEMOSTRATIVA')
                ->where('modelo', 'MODELO DEMOSTRATIVO')
                ->first();

            if ($vehicle === null) {
                $plateNumber = 1;

                do {
                    $plate = sprintf('DEMO-%03d', $plateNumber++);
                } while (Vehiculo::query()->where('placa', $plate)->exists());

                Vehiculo::query()->create([
                    'placa' => $plate,
                    'marca' => 'MARCA DEMOSTRATIVA',
                    'modelo' => 'MODELO DEMOSTRATIVO',
                    'capacidad' => 1000,
                    'id_chofer' => $driver->id_persona,
                    'estado' => '1',
                ]);
            }
        });
    }
}
