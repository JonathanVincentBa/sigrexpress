<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Scopes\AssignedBranchScope;

#[Fillable(['id_cabecera', 'id_usuario', 'estado', 'observacion'])]
class HistorialEstadoGuia extends Model
{
    protected $table = 'historial_estado_guia';

    protected $primaryKey = 'id_historial_estado_guia';

    protected static function booted(): void
    {
        static::addGlobalScope(new AssignedBranchScope);
    }

    /**
     * @return BelongsTo<Cabecera, $this>
     */
    public function guia(): BelongsTo
    {
        return $this->belongsTo(Cabecera::class, 'id_cabecera', 'id_cabecera');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
