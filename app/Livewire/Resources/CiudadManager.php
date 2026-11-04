<?php

namespace App\Livewire\Resources;

use App\Models\Ciudad;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Gestión de Ciudades')]
class CiudadManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => Ciudad::class,
            'title' => 'Ciudades',
            'description' => 'Mantén las ciudades disponibles para el origen y destino de las guías.',
            'view' => 'livewire.resources.ciudad-manager',
            'searchPlaceholder' => 'Buscar por nombre de ciudad...',
            'search' => ['descripcion'],
            'columns' => [
                ['key' => 'descripcion', 'label' => 'Ciudad'],
                ['key' => 'sucursales_count', 'label' => 'Sucursales'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'withCount' => ['sucursales'],
            'fields' => [
                'descripcion' => ['label' => 'Nombre de la ciudad', 'type' => 'text', 'rules' => 'required|string|max:45', 'wide' => true],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
