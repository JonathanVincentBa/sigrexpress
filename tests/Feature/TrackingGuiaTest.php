<?php

use App\Livewire\Tracking\TrackingGuia;
use App\Models\Cabecera;
use App\Models\Cargo;
use App\Models\Ciudad;
use App\Models\Empresa;
use App\Models\FormasPago;
use App\Models\MotivoTraslado;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use Livewire\Livewire;

function createTrackableGuide(): Cabecera
{
    $company = Empresa::query()->create([
        'razon_social' => 'Empresa de seguimiento',
        'ruc' => '1790000000001',
        'direccion' => 'Av. Principal',
        'telefono' => '022000000',
        'email' => 'tracking@example.test',
        'representante' => 'Representante',
        'porcentaje_impuesto' => 15,
        'estado' => '1',
    ]);
    $job = Cargo::query()->create(['descripcion' => 'Cliente', 'estado' => '1']);
    $city = Ciudad::query()->create(['descripcion' => 'Quito', 'estado' => '1']);
    $branch = Sucursal::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_ciudad' => $city->id_ciudad,
        'nombre' => 'Agencia Quito',
        'direccion' => 'Av. Principal',
        'telefono' => '022000000',
        'email' => 'branch@example.test',
        'estado' => '1',
    ]);
    $reason = MotivoTraslado::query()->create(['descripcion' => 'Venta', 'estado' => '1']);
    $payment = FormasPago::query()->create(['descripcion' => 'Efectivo', 'estado' => '1']);
    $customer = Persona::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_cargo' => $job->id_cargo,
        'tipo_persona' => 'C',
        'nombre' => 'Cliente de seguimiento',
        'direccion' => 'Calle 1',
        'tipo_dni' => 'C',
        'num_dni' => '1712345678',
        'telefono' => '0991234567',
        'estado' => '1',
    ]);
    $user = User::factory()->create();

    return Cabecera::query()->create([
        'id_sucursal' => $branch->id_sucursal,
        'id_motivo_traslado' => $reason->id_motivo_traslado,
        'id_forma_pago' => $payment->id_formas_pago,
        'id_users' => $user->id,
        'id_persona' => $customer->id_persona,
        'ciudad_origen' => $city->descripcion,
        'ciudad_destino' => 'GUAYAQUIL',
        'nom_remitente' => 'REMITENTE DE PRUEBA',
        'dni_remitente' => '0912345678',
        'direccion_remitente' => 'CALLE 2',
        'fono_remitente' => '0990000000',
        'nom_destinatario' => 'DESTINATARIO PRIVADO',
        'dni_destinatario' => '0923456789',
        'direccion_destinatario' => 'CALLE 3',
        'fono_destinatario' => '0980000000',
        'num_guia' => 87654321,
        'num_guia_trans' => 1,
        'fecha_emision' => now(),
        'flete' => 12.50,
        'valor_guia' => 12.50,
        'estado' => '1',
        'estado_tracking' => 'EN BODEGA',
    ]);
}

it('shows only the guide matching both its number and recipient identification', function () {
    $guide = createTrackableGuide();

    Livewire::test(TrackingGuia::class)
        ->set('num_guia', (string) $guide->num_guia)
        ->set('identificacion', '0923456789')
        ->call('search')
        ->assertSet('matchedGuideId', $guide->id_cabecera)
        ->assertSee('DESTINATARIO PRIVADO')
        ->assertSee('EN BODEGA');
});

it('does not disclose a guide when the recipient identification does not match', function () {
    $guide = createTrackableGuide();

    Livewire::test(TrackingGuia::class)
        ->set('num_guia', (string) $guide->num_guia)
        ->set('identificacion', '0000000000')
        ->call('search')
        ->assertSet('matchedGuideId', null)
        ->assertHasErrors('lookup')
        ->assertDontSee('DESTINATARIO PRIVADO');
});

it('serves the tracking page without requiring authentication', function () {
    $this->get(route('tracking.guia'))->assertOk();
});
