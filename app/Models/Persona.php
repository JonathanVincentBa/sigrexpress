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
 * @property int $id_persona
 * @property int $id_empresa
 * @property int $id_cargo
 * @property string $tipo_persona
 * @property string $nombre
 * @property string $direccion
 * @property string $tipo_dni
 * @property string $num_dni
 * @property string $telefono
 * @property string $estado
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'id_empresa',
    'id_cargo',
    'tipo_persona',
    'nombre',
    'direccion',
    'tipo_dni',
    'num_dni',
    'telefono',
    'estado',
])]
class Persona extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'persona';

    protected $primaryKey = 'id_persona';

    /**
     * @return BelongsTo<Empresa, $this>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class, 'id_empresa', 'id_empresa');
    }

    /**
     * @return BelongsTo<Cargo, $this>
     */
    public function cargo(): BelongsTo
    {
        return $this->belongsTo(Cargo::class, 'id_cargo', 'id_cargo');
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'id_persona', 'id_persona');
    }

    /**
     * @return HasMany<Cabecera, $this>
     */
    public function cabeceras(): HasMany
    {
        return $this->hasMany(Cabecera::class, 'id_persona', 'id_persona');
    }

    /**
     * @return HasMany<Vehiculo, $this>
     */
    public function vehiculosAsignados(): HasMany
    {
        return $this->hasMany(Vehiculo::class, 'id_chofer', 'id_persona');
    }

    /**
     * @return HasMany<Manifiesto, $this>
     */
    public function manifiestos(): HasMany
    {
        return $this->hasMany(Manifiesto::class, 'id_chofer', 'id_persona');
    }
}
