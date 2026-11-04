<?php

namespace App\Livewire\Resources;

use App\Models\Persona;
use App\Models\Vehiculo;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Gestión de Vehículos')]
class VehiculoManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => Vehiculo::class,
            'title' => 'Vehículos',
            'description' => 'Administra la flota y el personal conductor asignado.',
            'view' => 'livewire.resources.vehiculo-manager',
            'searchPlaceholder' => 'Buscar por placa, marca o modelo...',
            'search' => ['placa', 'marca', 'modelo'],
            'with' => ['chofer'],
            'columns' => [
                ['key' => 'placa', 'label' => 'Placa'],
                ['key' => 'marca', 'label' => 'Marca'],
                ['key' => 'modelo', 'label' => 'Modelo'],
                ['key' => 'capacidad', 'label' => 'Capacidad', 'suffix' => ' kg'],
                ['key' => 'chofer.nombre', 'label' => 'Chofer'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'fields' => [
                'placa' => [
                    'label' => 'Placa',
                    'type' => 'text',
                    'rules' => ['required', 'string', 'max:15', Rule::unique('vehiculos', 'placa')->ignore($this->selectedId, 'id_vehiculo')],
                ],
                'marca' => ['label' => 'Marca', 'type' => 'text', 'rules' => 'required|string|max:80'],
                'modelo' => ['label' => 'Modelo', 'type' => 'text', 'rules' => 'required|string|max:80'],
                'capacidad' => ['label' => 'Capacidad (kg)', 'type' => 'number', 'rules' => 'required|numeric|min:0.01|max:99999999.99'],
                'id_chofer' => [
                    'label' => 'Chofer asignado',
                    'type' => 'select',
                    'rules' => ['nullable', Rule::exists('persona', 'id_persona')->where(fn ($query) => $query->where('tipo_persona', 'E')->where('estado', '1'))],
                    'options' => Persona::query()
                        ->where('tipo_persona', 'E')
                        ->where('estado', '1')
                        ->orderBy('nombre')
                        ->pluck('nombre', 'id_persona')
                        ->all(),
                ],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
