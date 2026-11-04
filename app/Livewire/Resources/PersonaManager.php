<?php

namespace App\Livewire\Resources;

use App\Models\Cargo;
use App\Models\Empresa;
use App\Models\Persona;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Personas y Empleados')]
class PersonaManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => Persona::class,
            'title' => 'Personas y empleados',
            'description' => 'Gestiona clientes, proveedores y colaboradores en un solo directorio.',
            'view' => 'livewire.resources.persona-manager',
            'searchPlaceholder' => 'Buscar por nombre, identificación o teléfono...',
            'search' => ['nombre', 'num_dni', 'telefono'],
            'columns' => [
                ['key' => 'nombre', 'label' => 'Nombres y apellidos'],
                ['key' => 'num_dni', 'label' => 'Identificación'],
                ['key' => 'tipo_persona', 'label' => 'Tipo', 'map' => ['C' => 'Cliente', 'P' => 'Proveedor', 'E' => 'Empleado']],
                ['key' => 'empresa.razon_social', 'label' => 'Empresa'],
                ['key' => 'cargo.descripcion', 'label' => 'Cargo'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'with' => ['empresa', 'cargo'],
            'filters' => [
                'tipo_persona' => [
                    'label' => 'Tipo de persona',
                    'options' => ['C' => 'Clientes', 'P' => 'Proveedores', 'E' => 'Empleados'],
                ],
            ],
            'fields' => [
                'id_empresa' => [
                    'label' => 'Empresa',
                    'type' => 'select',
                    'rules' => 'required|exists:empresa,id_empresa',
                    'options' => Empresa::query()->where('estado', '1')->orderBy('razon_social')->pluck('razon_social', 'id_empresa')->all(),
                ],
                'id_cargo' => [
                    'label' => 'Cargo',
                    'type' => 'select',
                    'rules' => 'required|exists:cargo,id_cargo',
                    'options' => Cargo::query()->where('estado', '1')->orderBy('descripcion')->pluck('descripcion', 'id_cargo')->all(),
                ],
                'tipo_persona' => ['label' => 'Tipo de persona', 'type' => 'select', 'rules' => 'required|in:C,P,E', 'default' => 'C', 'options' => ['C' => 'Cliente', 'P' => 'Proveedor', 'E' => 'Empleado']],
                'nombre' => ['label' => 'Nombres y apellidos', 'type' => 'text', 'rules' => 'required|string|max:100', 'wide' => true],
                'direccion' => ['label' => 'Dirección', 'type' => 'textarea', 'rules' => 'required|string|max:500', 'wide' => true],
                'tipo_dni' => ['label' => 'Tipo de identificación', 'type' => 'select', 'rules' => 'required|in:C,R,P', 'default' => 'C', 'options' => ['C' => 'Cédula', 'R' => 'RUC', 'P' => 'Pasaporte']],
                'num_dni' => ['label' => 'Número de identificación', 'type' => 'text', 'rules' => 'required|string|max:20'],
                'telefono' => ['label' => 'Teléfono', 'type' => 'text', 'rules' => 'required|string|max:20'],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
