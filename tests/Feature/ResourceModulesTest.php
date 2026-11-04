<?php

use App\Livewire\Resources\CabeceraManager;
use App\Livewire\Resources\CiudadManager;
use App\Livewire\Resources\EmpresaManager;
use App\Livewire\Resources\GuiaEstadoManager;
use App\Livewire\Resources\ManifiestoManager;
use App\Livewire\Resources\SucursalManager;
use App\Livewire\Resources\UserManager;
use App\Models\Cabecera;
use App\Models\Cargo;
use App\Models\Ciudad;
use App\Models\Empresa;
use App\Models\FormasPago;
use App\Models\Manifiesto;
use App\Models\MotivoTraslado;
use App\Models\Persona;
use App\Models\Sucursal;
use App\Models\User;
use App\Models\Vehiculo;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\NormalizeLegacyBranchCitySeeder;
use Database\Seeders\OperationalDemoSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

function createGuideReferences(): array
{
    $company = Empresa::query()->create([
        'razon_social' => 'Transportes Test S.A.',
        'ruc' => '1790000000001',
        'direccion' => 'Av. Principal',
        'telefono' => '022000000',
        'email' => 'test@transportes.example',
        'representante' => 'Representante Test',
        'porcentaje_impuesto' => 15,
        'estado' => '1',
    ]);
    $cargo = Cargo::query()->create(['descripcion' => 'Cliente', 'estado' => '1']);
    $city = Ciudad::query()->create(['descripcion' => 'Quito', 'estado' => '1']);
    $branch = Sucursal::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_ciudad' => $city->id_ciudad,
        'nombre' => 'Agencia de prueba',
        'direccion' => 'Av. Principal',
        'telefono' => '022000000',
        'email' => 'sucursal@transportes.example',
        'estado' => '1',
    ]);
    $reason = MotivoTraslado::query()->create(['descripcion' => 'Venta', 'estado' => '1']);
    $payment = FormasPago::query()->create(['descripcion' => 'Efectivo', 'estado' => '1']);
    $person = Persona::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_cargo' => $cargo->id_cargo,
        'tipo_persona' => 'C',
        'nombre' => 'Cliente de Prueba',
        'direccion' => 'Calle 1',
        'tipo_dni' => 'C',
        'num_dni' => '1712345678',
        'telefono' => '0991234567',
        'estado' => '1',
    ]);

    return [$branch, $reason, $payment, $city, $company, $cargo, $person];
}

it('requires authentication for every resource module', function () {
    foreach ([
        'resources.empresa.index',
        'resources.sucursal.index',
        'resources.ciudad.index',
        'resources.cargo.index',
        'resources.motivo-traslado.index',
        'resources.formas-pago.index',
        'resources.persona.index',
        'resources.users.index',
        'resources.roles.index',
        'resources.cabecera.index',
        'resources.detalle.index',
        'resources.vehiculo.index',
        'resources.guia-estado.index',
        'resources.manifiesto.index',
        'reports.index',
        'reports.export.pdf',
        'reports.export.excel',
    ] as $routeName) {
        $this->get(route($routeName))->assertRedirect(route('login'));
    }
});

it('renders every authenticated resource module', function () {
    $this->actingAs(User::factory()->create());

    foreach ([
        'resources.empresa.index',
        'resources.sucursal.index',
        'resources.ciudad.index',
        'resources.cargo.index',
        'resources.motivo-traslado.index',
        'resources.formas-pago.index',
        'resources.persona.index',
        'resources.users.index',
        'resources.roles.index',
        'resources.cabecera.index',
        'resources.detalle.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }
});

it('shows validation messages in Spanish', function () {
    $validator = Validator::make([], ['nombre' => ['required']]);

    expect($validator->errors()->first('nombre'))->toBe('El campo nombre es obligatorio.');
});

it('creates a catalog record through its resource modal', function () {
    $this->actingAs(User::factory()->create());

    $component = Livewire::test(CiudadManager::class)
        ->call('create')
        ->assertSet('showModal', true)
        ->set('formData.descripcion', 'Cuenca')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('ciudad', ['descripcion' => 'CUENCA', 'estado' => '1']);
    $city = Ciudad::query()->where('descripcion', 'CUENCA')->firstOrFail();

    $component
        ->call('view', $city->id_ciudad)
        ->assertSet('showModal', true)
        ->assertSet('isViewing', true)
        ->call('edit', $city->id_ciudad)
        ->assertSet('isViewing', false)
        ->set('formData.descripcion', 'Cuenca Actualizada')
        ->call('save')
        ->assertHasNoErrors()
        ->call('prepareDelete', $city->id_ciudad)
        ->assertSet('showDeleteModal', true)
        ->call('confirmDelete')
        ->assertSet('showDeleteModal', false);

    $this->assertSoftDeleted('ciudad', ['id_ciudad' => $city->id_ciudad]);
});

it('normalizes legacy branch labels and preserves their branch data', function () {
    [$branch, , , $city, $company] = createGuideReferences();
    $branch->update(['nombre' => 'Agencia QUITO SUR']);

    $legacyQuitoAlias = Ciudad::query()->create(['descripcion' => 'QUITO SUR', 'estado' => '1']);
    $branch->update(['id_ciudad' => $legacyQuitoAlias->id_ciudad]);

    $versalles = Ciudad::query()->create(['descripcion' => 'Versalles', 'estado' => '1']);
    Ciudad::query()->create(['descripcion' => 'BODEGA VERSALLES', 'estado' => '1']);

    $firstManta = Ciudad::query()->create(['descripcion' => 'manta', 'estado' => '1']);
    $duplicateManta = Ciudad::query()->create(['descripcion' => 'MANTA', 'estado' => '1']);
    $branchForDuplicate = Sucursal::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_ciudad' => $duplicateManta->id_ciudad,
        'nombre' => 'Agencia Manta',
        'direccion' => 'Dirección que debe conservarse',
        'telefono' => '0991234567',
        'email' => 'manta@example.test',
        'estado' => '1',
    ]);
    $cuenca = Ciudad::query()->create(['descripcion' => 'Cuenca', 'estado' => '1']);
    $branchWithDefaultName = Sucursal::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_ciudad' => $cuenca->id_ciudad,
        'nombre' => 'Sucursal',
        'direccion' => 'Av. Cuenca',
        'telefono' => '072000000',
        'email' => 'cuenca@example.test',
        'estado' => '1',
    ]);

    $seeder = app(NormalizeLegacyBranchCitySeeder::class);
    $seeder->run();
    $branchCountAfterFirstRun = Sucursal::query()->count();
    $seeder->run();

    $branch->refresh();
    $branchForDuplicate->refresh();
    $branchWithDefaultName->refresh();

    expect($branch->nombre)->toBe('Agencia Quito Sur')
        ->and($branch->id_ciudad)->toBe($city->id_ciudad)
        ->and($branch->direccion)->toBe('Av. Principal')
        ->and($branchForDuplicate->id_ciudad)->toBe($firstManta->id_ciudad)
        ->and($branchForDuplicate->direccion)->toBe('Dirección que debe conservarse')
        ->and(Sucursal::query()->count())->toBe($branchCountAfterFirstRun);
    expect($branchWithDefaultName->nombre)->toBe('Agencia Cuenca');

    $this->assertDatabaseMissing('ciudad', ['descripcion' => 'QUITO SUR']);
    $this->assertDatabaseMissing('ciudad', ['id_ciudad' => $duplicateManta->id_ciudad]);
    $this->assertDatabaseHas('sucursal', [
        'id_empresa' => $company->id_empresa,
        'id_ciudad' => $versalles->id_ciudad,
        'nombre' => 'Bodega Versalles',
        'direccion' => '',
        'telefono' => '',
        'email' => '',
    ]);
});

it('creates a branch using its name and normalized city table', function () {
    $this->actingAs(User::factory()->create());
    [, , , $city, $company] = createGuideReferences();

    Livewire::test(SucursalManager::class)
        ->call('create')
        ->set('formData.nombre', 'Agencia Quito Norte')
        ->set('formData.id_empresa', $company->id_empresa)
        ->set('formData.id_ciudad', $city->id_ciudad)
        ->set('formData.direccion', 'Av. Norte')
        ->set('formData.telefono', '022000000')
        ->set('formData.email', 'norte@example.test')
        ->set('formData.estado', '1')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('sucursal', [
        'nombre' => 'AGENCIA QUITO NORTE',
        'id_empresa' => $company->id_empresa,
        'id_ciudad' => $city->id_ciudad,
    ]);
});

it('does not assign a missing legacy branch to an arbitrary company', function () {
    foreach (['Empresa Uno', 'Empresa Dos'] as $name) {
        Empresa::query()->create([
            'razon_social' => $name,
            'ruc' => $name === 'Empresa Uno' ? '1790000000001' : '1790000000002',
            'direccion' => 'Av. Principal',
            'telefono' => '022000000',
            'email' => 'empresa@example.test',
            'representante' => 'Representante',
            'porcentaje_impuesto' => 15,
            'estado' => '1',
        ]);
    }

    Ciudad::query()->create(['descripcion' => 'BODEGA VERSALLES', 'estado' => '1']);

    expect(fn () => app(NormalizeLegacyBranchCitySeeder::class)->run())
        ->toThrow(RuntimeException::class);

    $this->assertDatabaseHas('ciudad', ['descripcion' => 'BODEGA VERSALLES']);
    $this->assertDatabaseMissing('ciudad', ['descripcion' => 'Versalles']);
    $this->assertDatabaseCount('sucursal', 0);
});

it('creates a user and assigns its selected role', function () {
    $this->actingAs(User::factory()->create());
    $role = Role::query()->create(['name' => 'Operador', 'guard_name' => 'web']);

    Livewire::test(UserManager::class)
        ->call('create')
        ->set('name', '  Usuario Operador  ')
        ->set('email', ' Operador@Example.Test ')
        ->set('password', 'password-segura')
        ->set('password_confirmation', 'password-segura')
        ->set('roles', [$role->id])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $user = User::query()->where('email', 'operador@example.test')->firstOrFail();

    expect(Hash::check('password-segura', $user->password))->toBeTrue();
    expect($user->hasRole($role))->toBeTrue()
        ->and($user->name)->toBe('USUARIO OPERADOR')
        ->and($user->email)->toBe('operador@example.test');
});

it('saves a guide and calculates each detail line and guide charge', function () {
    $user = User::factory()->create();
    [$branch, $reason, $payment, $city, , , $person] = createGuideReferences();
    $this->actingAs($user);

    Livewire::test(CabeceraManager::class)
        ->call('create')
        ->set('formData.id_sucursal', $branch->id_sucursal)
        ->set('formData.id_motivo_traslado', $reason->id_motivo_traslado)
        ->set('formData.id_forma_pago', $payment->id_formas_pago)
        ->set('formData.ciudad_origen', $city->descripcion)
        ->set('formData.ciudad_destino', $city->descripcion)
        ->set('formData.nom_destinatario', 'Destinatario de Prueba')
        ->set('formData.dni_destinatario', '1712345678')
        ->set('formData.direccion_destinatario', 'Calle 2')
        ->set('formData.fono_destinatario', '0990000000')
        ->set('formData.flete', '12.50')
        ->set('formData.recargo', '2.00')
        ->set('formData.prima', '2.75')
        ->set('detalles.0.cantidad', 2)
        ->set('detalles.0.descripcion', 'Caja de repuestos')
        ->set('detalles.0.v_unitario', '30.00')
        ->call('addDetail')
        ->assertSet('showDetailDeleteModal', false)
        ->call('prepareRemoveDetail', 1)
        ->assertSet('showDetailDeleteModal', true)
        ->call('confirmRemoveDetail')
        ->assertSet('showDetailDeleteModal', false)
        ->call('selectClient', $person->id_persona)
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $guide = Cabecera::query()->with('detalles')->firstOrFail();

    expect((float) $guide->valor_guia)->toBe(17.25);
    expect((float) $guide->detalles->sole()->v_parcial)->toBe(60.0);
    expect($guide->nom_destinatario)->toBe('DESTINATARIO DE PRUEBA')
        ->and($guide->detalles->sole()->descripcion)->toBe('CAJA DE REPUESTOS');
    expect($guide->id_users)->toBe($user->id);
});

it('prefills a quick customer from the guide search and selects the saved customer', function () {
    $this->actingAs(User::factory()->create());
    [, , , , $company, $cargo] = createGuideReferences();

    $component = Livewire::test(CabeceraManager::class)
        ->call('create')
        ->set('clientSearch', 'Prueba')
        ->assertSee('Cliente de Prueba')
        ->assertDontSee('Crear nuevo cliente')
        ->set('clientSearch', '1798765432001')
        ->assertSee('No hay clientes que coincidan con la búsqueda.')
        ->assertSee('Crear nuevo cliente')
        ->call('openQuickCustomerModal')
        ->assertSet('showQuickCustomerModal', true)
        ->assertSet('quickCustomer.num_dni', '1798765432001')
        ->set('quickCustomer.nombre', 'Cliente Registrado Rápido')
        ->set('quickCustomer.id_empresa', $company->id_empresa)
        ->set('quickCustomer.id_cargo', $cargo->id_cargo)
        ->set('quickCustomer.tipo_dni', 'R')
        ->set('quickCustomer.direccion', 'Av. de Prueba')
        ->set('quickCustomer.telefono', '0991111111')
        ->call('saveQuickCustomer')
        ->assertHasNoErrors()
        ->assertSet('showQuickCustomerModal', false);

    $customer = Persona::query()->where('num_dni', '1798765432001')->firstOrFail();

    $component
        ->assertSet('formData.id_persona', $customer->id_persona)
        ->assertSet('formData.nom_remitente', 'CLIENTE REGISTRADO RÁPIDO');
});

it('stores company logos and normalizes text while preserving lowercase emails', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    Livewire::test(EmpresaManager::class)
        ->call('create')
        ->set('formData.razon_social', '  Empresa de Prueba  ')
        ->set('formData.ruc', '1790000000001')
        ->set('formData.direccion', '  Av. Principal  ')
        ->set('formData.telefono', ' 022000000 ')
        ->set('formData.email', ' Contacto@Empresa.Test ')
        ->set('formData.representante', '  Representante Legal  ')
        ->set('formData.porcentaje_impuesto', '15')
        ->set('formData.estado', '1')
        ->set('uploads.logo', UploadedFile::fake()->createWithContent(
            'empresa.png',
            base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+/rWoAAAAASUVORK5CYII='),
        ))
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('swal');

    $company = Empresa::query()->where('ruc', '1790000000001')->firstOrFail();

    expect($company->razon_social)->toBe('EMPRESA DE PRUEBA')
        ->and($company->direccion)->toBe('AV. PRINCIPAL')
        ->and($company->representante)->toBe('REPRESENTANTE LEGAL')
        ->and($company->email)->toBe('contacto@empresa.test')
        ->and($company->logo)->toStartWith('logos/');

    Storage::disk('public')->assertExists($company->logo);
});

it('shows Spanish validation messages for required catalog fields', function () {
    Livewire::test(CiudadManager::class)
        ->call('create')
        ->set('formData.descripcion', '')
        ->call('save')
        ->assertHasErrors(['formData.descripcion' => 'required'])
        ->assertSee('El campo descripción es obligatorio.');
});

it('rejects non-image company logos without creating a company', function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create());

    Livewire::test(EmpresaManager::class)
        ->call('create')
        ->set('uploads.logo', UploadedFile::fake()->create('logo.pdf', 10, 'application/pdf'))
        ->call('save')
        ->assertHasErrors(['uploads.logo' => 'image']);

    $this->assertDatabaseCount('empresa', 0);
    Storage::disk('public')->assertEmpty();
});

it('preserves the administrator password when the configured seeder runs again', function () {
    config()->set('app.admin_name', 'Administrador de Prueba');
    config()->set('app.admin_email', 'admin@example.test');
    config()->set('app.admin_password', 'Password-Inicial-123');

    app(AdminUserSeeder::class)->run();
    $admin = User::query()->where('email', 'admin@example.test')->firstOrFail();
    $originalPasswordHash = $admin->password;

    config()->set('app.admin_password', 'Otra-Password-456');
    app(AdminUserSeeder::class)->run();
    $admin->refresh();

    expect(User::query()->where('email', 'admin@example.test')->count())->toBe(1)
        ->and($admin->password)->toBe($originalPasswordHash)
        ->and($admin->hasRole('Super Admin'))->toBeTrue();
});

it('seeds operational demonstration records idempotently in local environments', function () {
    User::factory()->create();

    app(OperationalDemoSeeder::class)->run();
    app(OperationalDemoSeeder::class)->run();

    $this->assertDatabaseCount('cabecera', 1);
    $this->assertDatabaseCount('detalle', 1);
    $this->assertDatabaseCount('vehiculos', 1);
});

it('records each guide status change in its history', function () {
    $user = User::factory()->create();
    [$branch, $reason, $payment, $city, , , $person] = createGuideReferences();
    $guide = createOperationalGuide($user, $branch, $reason, $payment, $city, $person);
    $this->actingAs($user);

    Livewire::test(GuiaEstadoManager::class)
        ->call('edit', $guide->id_cabecera)
        ->set('estado', 'EN CAMINO')
        ->set('observacion', '  Sale del centro de distribución  ')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('cabecera', [
        'id_cabecera' => $guide->id_cabecera,
        'estado_tracking' => 'EN CAMINO',
    ]);
    $this->assertDatabaseHas('historial_estado_guia', [
        'id_cabecera' => $guide->id_cabecera,
        'id_usuario' => $user->id,
        'estado' => 'EN CAMINO',
        'observacion' => 'SALE DEL CENTRO DE DISTRIBUCIÓN',
    ]);
});

it('assigns eligible guides to a vehicle and records their route status', function () {
    $user = User::factory()->create();
    [$branch, $reason, $payment, $city, $company, $cargo, $person] = createGuideReferences();
    $guide = createOperationalGuide($user, $branch, $reason, $payment, $city, $person);
    $driver = Persona::query()->create([
        'id_empresa' => $company->id_empresa,
        'id_cargo' => $cargo->id_cargo,
        'tipo_persona' => 'E',
        'nombre' => 'Chofer de Prueba',
        'direccion' => 'Calle 2',
        'tipo_dni' => 'C',
        'num_dni' => '0912345678',
        'telefono' => '0992222222',
        'estado' => '1',
    ]);
    $vehicle = Vehiculo::query()->create([
        'placa' => 'ABC-1234',
        'marca' => 'Marca',
        'modelo' => 'Modelo',
        'capacidad' => 1000,
        'id_chofer' => $driver->id_persona,
        'estado' => '1',
    ]);
    $this->actingAs($user);

    Livewire::test(ManifiestoManager::class)
        ->call('create')
        ->set('idVehiculo', (string) $vehicle->id_vehiculo)
        ->set('idChofer', (string) $driver->id_persona)
        ->set('selectedGuides', [(string) $guide->id_cabecera])
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('showModal', false);

    $manifest = Manifiesto::query()->firstOrFail();

    $this->assertDatabaseHas('manifiesto_guia', [
        'id_manifiesto' => $manifest->id_manifiesto,
        'id_cabecera' => $guide->id_cabecera,
    ]);
    $this->assertDatabaseHas('cabecera', [
        'id_cabecera' => $guide->id_cabecera,
        'estado_tracking' => 'EN CAMINO',
    ]);
    $this->assertDatabaseHas('historial_estado_guia', [
        'id_cabecera' => $guide->id_cabecera,
        'estado' => 'EN CAMINO',
    ]);
});

it('renders every new authenticated management page', function () {
    $this->actingAs(User::factory()->create());

    foreach ([
        'dashboard',
        'resources.vehiculo.index',
        'resources.guia-estado.index',
        'resources.manifiesto.index',
        'reports.index',
        'manual.index',
    ] as $routeName) {
        $this->get(route($routeName))->assertOk();
    }
});

it('exports a validated management report as a PDF', function () {
    $user = User::factory()->create();
    [$branch, $reason, $payment, $city, , , $person] = createGuideReferences();
    createOperationalGuide($user, $branch, $reason, $payment, $city, $person);
    $this->actingAs($user);

    $response = $this->get(route('reports.export.pdf', [
        'tipo' => 'clientes',
        'fecha_desde' => now()->toDateString(),
        'fecha_hasta' => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/pdf');
    expect($response->getContent())->toStartWith('%PDF-');
});

it('exports a protected Excel report with the selected filters', function () {
    $user = User::factory()->create();
    [$branch, $reason, $payment, $city, , , $person] = createGuideReferences();
    createOperationalGuide($user, $branch, $reason, $payment, $city, $person);
    $this->actingAs($user);

    $response = $this->get(route('reports.export.excel', [
        'tipo' => 'clientes',
        'fecha_desde' => now()->toDateString(),
        'fecha_hasta' => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    expect($response->baseResponse->headers->get('content-disposition'))->toContain('.xlsx');

    $filePath = $response->baseResponse->getFile()->getPathname();
    $archive = new ZipArchive;

    try {
        expect($archive->open($filePath))->toBeTrue();
        expect($archive->getFromName('xl/worksheets/sheet1.xml'))->toContain('sheetProtection');
    } finally {
        $archive->close();

        if (is_file($filePath)) {
            unlink($filePath);
        }
    }
});

function createOperationalGuide(
    User $user,
    Sucursal $branch,
    MotivoTraslado $reason,
    FormasPago $payment,
    Ciudad $city,
    Persona $person,
): Cabecera {
    return Cabecera::query()->create([
        'id_sucursal' => $branch->id_sucursal,
        'id_motivo_traslado' => $reason->id_motivo_traslado,
        'id_forma_pago' => $payment->id_formas_pago,
        'id_users' => $user->id,
        'id_persona' => $person->id_persona,
        'ciudad_origen' => $city->descripcion,
        'ciudad_destino' => $city->descripcion,
        'nom_remitente' => 'REMITENTE DE PRUEBA',
        'dni_remitente' => '1712345678',
        'direccion_remitente' => 'CALLE 1',
        'fono_remitente' => '0991111111',
        'nom_destinatario' => 'DESTINATARIO DE PRUEBA',
        'dni_destinatario' => '0912345678',
        'direccion_destinatario' => 'CALLE 2',
        'fono_destinatario' => '0992222222',
        'num_guia' => 123456789,
        'num_guia_trans' => 1,
        'fecha_emision' => now(),
        'flete' => 10,
        'valor_guia' => 10,
        'estatus_cobro' => 'Pendiente',
        'estado' => '1',
        'estado_tracking' => 'EN BODEGA',
    ]);
}
