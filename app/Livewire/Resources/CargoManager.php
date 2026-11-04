<?php

namespace App\Livewire\Resources;

use App\Models\Cargo;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Gestión de Cargos')]
class CargoManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => Cargo::class,
            'title' => 'Cargos',
            'description' => 'Define los cargos que se asignan al personal de la organización.',
            'view' => 'livewire.resources.cargo-manager',
            'searchPlaceholder' => 'Buscar por descripción del cargo...',
            'search' => ['descripcion'],
            'columns' => [
                ['key' => 'descripcion', 'label' => 'Cargo'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'fields' => [
                'descripcion' => ['label' => 'Nombre del cargo', 'type' => 'text', 'rules' => 'required|string|max:100', 'wide' => true],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
