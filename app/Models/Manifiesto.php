<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable([
    'codigo',
    'id_vehiculo',
    'id_chofer',
    'id_usuario',
    'fecha_salida',
    'fecha_cierre',
    'estado',
    'observacion',
])]
class Manifiesto extends Model
{
    protected $table = 'manifiestos';

    protected $primaryKey = 'id_manifiesto';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_salida' => 'datetime',
            'fecha_cierre' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<Vehiculo, $this>
     */
    public function vehiculo(): BelongsTo
    {
        return $this->belongsTo(Vehiculo::class, 'id_vehiculo', 'id_vehiculo');
    }

    /**
     * @return BelongsTo<Persona, $this>
     */
    public function chofer(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_chofer', 'id_persona');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * @return BelongsToMany<Cabecera, $this>
     */
    public function guias(): BelongsToMany
    {
        return $this->belongsToMany(
            Cabecera::class,
            'manifiesto_guia',
            'id_manifiesto',
            'id_cabecera',
            'id_manifiesto',
            'id_cabecera',
        );
    }
}
