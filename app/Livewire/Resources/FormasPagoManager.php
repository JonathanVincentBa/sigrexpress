<?php

namespace App\Livewire\Resources;

use App\Models\FormasPago;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Formas de Pago')]
class FormasPagoManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => FormasPago::class,
            'title' => 'Formas de pago',
            'description' => 'Configura los medios de pago disponibles para las guías de transporte.',
            'view' => 'livewire.resources.formas-pago-manager',
            'searchPlaceholder' => 'Buscar por forma de pago...',
            'search' => ['descripcion'],
            'columns' => [
                ['key' => 'descripcion', 'label' => 'Forma de pago'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'fields' => [
                'descripcion' => ['label' => 'Descripción', 'type' => 'text', 'rules' => 'required|string|max:45', 'wide' => true],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
