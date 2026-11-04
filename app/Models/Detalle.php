<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id_detalle
 * @property int $id_cabecera
 * @property int $cantidad
 * @property string $descripcion
 * @property float $v_unitario
 * @property float $v_parcial
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'id_cabecera',
    'cantidad',
    'descripcion',
    'v_unitario',
    'v_parcial',
])]
class Detalle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detalle';

    protected $primaryKey = 'id_detalle';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'v_unitario' => 'decimal:2',
            'v_parcial' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Cabecera, $this>
     */
    public function cabecera(): BelongsTo
    {
        return $this->belongsTo(Cabecera::class, 'id_cabecera', 'id_cabecera');
    }
}

