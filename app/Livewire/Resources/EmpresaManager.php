<?php

namespace App\Livewire\Resources;

use App\Models\Empresa;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

#[Layout('components.layouts.app')]
#[Title('Gestión de Empresas')]
class EmpresaManager extends BaseCatalogManager
{
    protected function definition(): array
    {
        return [
            'model' => Empresa::class,
            'title' => 'Empresas',
            'description' => 'Gestiona razones sociales, datos fiscales y parámetros tributarios.',
            'view' => 'livewire.resources.empresa-manager',
            'searchPlaceholder' => 'Buscar por razón social, RUC o representante...',
            'search' => ['razon_social', 'ruc', 'representante', 'email'],
            'columns' => [
                ['key' => 'razon_social', 'label' => 'Razón social'],
                ['key' => 'ruc', 'label' => 'RUC'],
                ['key' => 'representante', 'label' => 'Representante'],
                ['key' => 'telefono', 'label' => 'Teléfono'],
                ['key' => 'porcentaje_impuesto', 'label' => 'Impuesto', 'suffix' => '%'],
                ['key' => 'estado', 'label' => 'Estado', 'format' => 'status'],
            ],
            'fields' => [
                'razon_social' => ['label' => 'Razón social', 'type' => 'text', 'rules' => 'required|string|max:100', 'wide' => true],
                'ruc' => ['label' => 'RUC', 'type' => 'text', 'rules' => 'required|string|max:45'],
                'direccion' => ['label' => 'Dirección matriz', 'type' => 'text', 'rules' => 'required|string|max:100', 'wide' => true],
                'telefono' => ['label' => 'Teléfono', 'type' => 'text', 'rules' => 'required|string|max:20'],
                'email' => ['label' => 'Correo electrónico', 'type' => 'email', 'rules' => 'required|email|max:100'],
                'representante' => ['label' => 'Representante legal', 'type' => 'text', 'rules' => 'required|string|max:100'],
                'porcentaje_impuesto' => ['label' => 'Porcentaje de impuesto', 'type' => 'number', 'rules' => 'required|numeric|between:0,100', 'default' => '15.00'],
                'logo' => ['label' => 'Logotipo', 'type' => 'file', 'rules' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048', 'wide' => true],
                'estado' => ['label' => 'Estado', 'type' => 'select', 'rules' => 'required|in:0,1', 'default' => '1', 'options' => ['1' => 'Activo', '0' => 'Inactivo']],
            ],
        ];
    }
}
