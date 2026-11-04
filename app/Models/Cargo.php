<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_cargo
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
class Cargo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cargo';

    protected $primaryKey = 'id_cargo';

    /**
     * @return HasMany<Persona, $this>
     */
    public function personas(): HasMany
    {
        return $this->hasMany(Persona::class, 'id_cargo', 'id_cargo');
    }
}

