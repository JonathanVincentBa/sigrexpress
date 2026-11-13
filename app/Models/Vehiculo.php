<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Scopes\AssignedBranchScope;

#[Fillable(['placa', 'marca', 'modelo', 'capacidad', 'id_chofer', 'id_sucursal', 'estado'])]
class Vehiculo extends Model
{
    protected $table = 'vehiculos';

    protected $primaryKey = 'id_vehiculo';

    protected static function booted(): void
    {
        static::addGlobalScope(new AssignedBranchScope);
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'capacidad' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Persona, $this>
     */
    public function chofer(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_chofer', 'id_persona');
    }

    /**
     * @return HasMany<Manifiesto, $this>
     */
    public function manifiestos(): HasMany
    {
        return $this->hasMany(Manifiesto::class, 'id_vehiculo', 'id_vehiculo');
    }
}
