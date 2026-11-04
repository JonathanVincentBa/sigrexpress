<?php

use App\Http\Controllers\ManifestPdfController;
use App\Http\Controllers\ReportExportController;
use App\Livewire\DashboardManager;
use App\Livewire\Resources\CabeceraManager;
use App\Livewire\Resources\CargoManager;
use App\Livewire\Resources\CiudadManager;
use App\Livewire\Resources\DetalleManager;
use App\Livewire\Resources\EmpresaManager;
use App\Livewire\Resources\FormasPagoManager;
use App\Livewire\Resources\GuiaEstadoManager;
use App\Livewire\Resources\ManifiestoManager;
use App\Livewire\Resources\MotivoTrasladoManager;
use App\Livewire\Resources\PersonaManager;
use App\Livewire\Resources\ReportesManager;
use App\Livewire\Resources\RoleManager;
use App\Livewire\Resources\SucursalManager;
use App\Livewire\Resources\UserManager;
use App\Livewire\Resources\VehiculoManager;
use App\Livewire\Tracking\TrackingGuia;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::view('/', 'welcome')->name('home');
Route::livewire('/seguimiento-guia', TrackingGuia::class)->name('tracking.guia');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::livewire('dashboard', DashboardManager::class)->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::livewire('resources/vehiculos', VehiculoManager::class)->name('resources.vehiculo.index');
    Route::livewire('resources/estados-guia', GuiaEstadoManager::class)->name('resources.guia-estado.index');
    Route::livewire('resources/manifiestos', ManifiestoManager::class)->name('resources.manifiesto.index');
    Route::livewire('reportes', ReportesManager::class)->name('reports.index');
    Route::get('reportes/exportar/pdf', [ReportExportController::class, 'downloadPdf'])->name('reports.export.pdf');
    Route::get('reportes/exportar/excel', [ReportExportController::class, 'downloadExcel'])->name('reports.export.excel');
    Route::get('manifiestos/{manifest}/pdf', ManifestPdfController::class)->name('manifiestos.pdf');
    Route::get('manual-ayuda', function () {
        $exists = Storage::disk('public')->exists('manual/manual_usuario.pdf');

        return view('help.manual', [
            'manualExists' => $exists,
            'manualUrl' => $exists ? Storage::disk('public')->url('manual/manual_usuario.pdf') : null,
        ]);
    })->name('manual.index');
    Route::get('manual-ayuda/descargar', function () {
        abort_unless(Storage::disk('public')->exists('manual/manual_usuario.pdf'), 404);

        return Storage::disk('public')->download('manual/manual_usuario.pdf', 'Manual-Usuario-SIGRExpress.pdf');
    })->name('manual.download');

    Route::livewire('resources/empresas', EmpresaManager::class)->name('resources.empresa.index');
    Route::livewire('resources/sucursales', SucursalManager::class)->name('resources.sucursal.index');
    Route::livewire('resources/ciudades', CiudadManager::class)->name('resources.ciudad.index');
    Route::livewire('resources/cargos', CargoManager::class)->name('resources.cargo.index');
    Route::livewire('resources/motivos-traslado', MotivoTrasladoManager::class)->name('resources.motivo-traslado.index');
    Route::livewire('resources/formas-pago', FormasPagoManager::class)->name('resources.formas-pago.index');
    Route::livewire('resources/personas', PersonaManager::class)->name('resources.persona.index');
    Route::livewire('resources/usuarios', UserManager::class)->name('resources.users.index');
    Route::livewire('resources/roles', RoleManager::class)->name('resources.roles.index');
    Route::livewire('resources/guias', CabeceraManager::class)->name('resources.cabecera.index');
    Route::livewire('resources/detalles', DetalleManager::class)->name('resources.detalle.index');
});

require __DIR__.'/settings.php';
