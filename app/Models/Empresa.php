<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_empresa
 * @property string $razon_social
 * @property string $ruc
 * @property string $direccion
 * @property string $telefono
 * @property string $email
 * @property string $representante
 * @property float $porcentaje_impuesto
 * @property string|null $logo
 * @property string $estado
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'razon_social',
    'ruc',
    'direccion',
    'telefono',
    'email',
    'representante',
    'porcentaje_impuesto',
    'logo',
    'estado',
])]
class Empresa extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empresa';

    protected $primaryKey = 'id_empresa';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'porcentaje_impuesto' => 'decimal:2',
        ];
    }

    /**
     * @return HasMany<Sucursal, $this>
     */
    public function sucursales(): HasMany
    {
        return $this->hasMany(Sucursal::class, 'id_empresa', 'id_empresa');
    }

    /**
     * @return HasMany<Persona, $this>
     */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'id_empresa', 'id_empresa');
    }
}

