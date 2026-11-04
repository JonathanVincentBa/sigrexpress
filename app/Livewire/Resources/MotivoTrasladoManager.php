<?php

namespace App\Livewire\Resources;

use App\Models\MotivoTraslado;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Motivos de Traslado')]
class MotivoTrasladoManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => MotivoTraslado::class,
            'title' => 'Motivos de traslado',
            'description' => 'Administra las causas autorizadas para el traslado de mercadería.',
            'view' => 'livewire.resources.motivo-traslado-manager',
            'searchPlaceholder' => 'Buscar por motivo de traslado...',
            'search' => ['descripcion'],
            'columns' => [
                ['key' => 'descripcion', 'label' => 'Motivo'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'fields' => [
                'descripcion' => ['label' => 'Descripción del motivo', 'type' => 'text', 'rules' => 'required|string|max:100', 'wide' => true],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
