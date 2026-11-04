<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_motivo_traslado
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
class MotivoTraslado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'motivo_traslado';

    protected $primaryKey = 'id_motivo_traslado';

    /**
     * @return HasMany<Cabecera, $this>
     */
    public function cabeceras(): HasMany
    {
        return $this->hasMany(Cabecera::class, 'id_motivo_traslado', 'id_motivo_traslado');
    }
}

