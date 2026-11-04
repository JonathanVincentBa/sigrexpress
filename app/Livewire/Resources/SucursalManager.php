<?php

namespace App\Livewire\Resources;

use App\Models\Ciudad;
use App\Models\Empresa;
use App\Models\Sucursal;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Gestión de Sucursales')]
class SucursalManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => Sucursal::class,
            'title' => 'Sucursales',
            'description' => 'Administra agencias y sus ciudades y empresas asociadas.',
            'view' => 'livewire.resources.sucursal-manager',
            'searchPlaceholder' => 'Buscar por sucursal, empresa, ciudad o dirección...',
            'search' => ['nombre', 'empresa.razon_social', 'ciudad.descripcion', 'direccion', 'telefono'],
            'columns' => [
                ['key' => 'nombre', 'label' => 'Nombre de sucursal'],
                ['key' => 'empresa.razon_social', 'label' => 'Empresa'],
                ['key' => 'ciudad.descripcion', 'label' => 'Ciudad'],
                ['key' => 'direccion', 'label' => 'Dirección'],
                ['key' => 'telefono', 'label' => 'Teléfono'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'with' => ['empresa', 'ciudad'],
            'fields' => [
                'nombre' => ['label' => 'Nombre de la sucursal', 'type' => 'text', 'rules' => 'required|string|max:100', 'placeholder' => 'Ej.: Agencia Quito Sur', 'wide' => true],
                'id_empresa' => [
                    'label' => 'Empresa',
                    'type' => 'select',
                    'rules' => 'required|exists:empresa,id_empresa',
                    'options' => Empresa::query()->where('estado', '1')->orderBy('razon_social')->pluck('razon_social', 'id_empresa')->all(),
                    'wide' => true,
                ],
                'id_ciudad' => [
                    'label' => 'Ciudad',
                    'type' => 'select',
                    'rules' => 'required|exists:ciudad,id_ciudad',
                    'options' => Ciudad::query()->where('estado', '1')->orderBy('descripcion')->pluck('descripcion', 'id_ciudad')->all(),
                ],
                'direccion' => ['label' => 'Dirección', 'type' => 'text', 'rules' => 'required|string|max:100', 'wide' => true],
                'telefono' => ['label' => 'Teléfono', 'type' => 'text', 'rules' => 'required|string|max:20'],
                'email' => ['label' => 'Correo electrónico', 'type' => 'email', 'rules' => 'required|email|max:100'],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
