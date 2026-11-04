<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_sucursal
 * @property int $id_empresa
 * @property int $id_ciudad
 * @property string $nombre
 * @property string $direccion
 * @property string $telefono
 * @property string $email
 * @property string $estado
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'id_empresa',
    'id_ciudad',
    'nombre',
    'direccion',
    'telefono',
    'email',
    'estado',
])]
class Sucursal extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sucursal';

    protected $primaryKey = 'id_sucursal';

    /**
     * @return BelongsTo<Empresa, $this>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * @return BelongsTo<Ciudad, $this>
     */
    public function ciudad(): BelongsTo
    {
        return $this->belongsTo(Ciudad::class, 'id_ciudad', 'id_ciudad');
    }

    /**
     * @return HasMany<Cabecera, $this>
     */
    public function cabeceras(): HasMany
    {
        return $this->hasMany(Cabecera::class, 'id_sucursal', 'id_sucursal');
    }
}
