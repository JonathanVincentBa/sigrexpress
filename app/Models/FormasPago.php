<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_formas_pago
 * @property string $descripcion
 * @property string $estado
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'descripcion',
    'estado',
])]
class FormasPago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'formas_pago';

    protected $primaryKey = 'id_formas_pago';

    /**
     * @return HasMany<Cabecera, $this>
     */
    public function cabeceras(): HasMany
    {
        return $this->hasMany(Cabecera::class, 'id_forma_pago', 'id_formas_pago');
    }
}

