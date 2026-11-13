<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Models\Scopes\AssignedBranchScope;

/**
 * @property int $id_cabecera
 * @property int $id_sucursal
 * @property int $id_motivo_traslado
 * @property int $id_forma_pago
 * @property int $id_users
 * @property int $id_persona
 * @property int|null $id_users_update
 * @property Carbon|null $fecha_cancelacion
 * @property string $ciudad_origen
 * @property string $ciudad_destino
 * @property string $nom_remitente
 * @property string $dni_remitente
 * @property string $direccion_remitente
 * @property string $fono_remitente
 * @property string $nom_destinatario
 * @property string|null $dni_destinatario
 * @property string|null $direccion_destinatario
 * @property string|null $fono_destinatario
 * @property int $num_guia
 * @property int $num_guia_trans
 * @property Carbon $fecha_emision
 * @property Carbon|null $fecha_recepcion
 * @property string|null $guia_rem_cliente
 * @property string|null $factura_cliente
 * @property float $flete
 * @property float|null $recargo
 * @property float $valor_guia
 * @property float|null $prima
 * @property string|null $nombre_recibe
 * @property string|null $dni_recibe
 * @property string|null $estatus_entrega
 * @property string|null $estatus_cobro
 * @property string $estado
 * @property string|null $nota
 * @property Carbon|null $deleted_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'id_sucursal',
    'id_motivo_traslado',
    'id_forma_pago',
    'id_users',
    'id_persona',
    'id_users_update',
    'fecha_cancelacion',
    'ciudad_origen',
    'ciudad_destino',
    'nom_remitente',
    'dni_remitente',
    'direccion_remitente',
    'fono_remitente',
    'nom_destinatario',
    'dni_destinatario',
    'direccion_destinatario',
    'fono_destinatario',
    'num_guia',
    'num_guia_trans',
    'fecha_emision',
    'fecha_recepcion',
    'guia_rem_cliente',
    'factura_cliente',
    'flete',
    'recargo',
    'valor_guia',
    'prima',
    'nombre_recibe',
    'dni_recibe',
    'estatus_entrega',
    'estado_tracking',
    'estatus_cobro',
    'estado',
    'nota',
])]
class Cabecera extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cabecera';

    protected $primaryKey = 'id_cabecera';

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
            'fecha_cancelacion' => 'datetime',
            'fecha_emision' => 'datetime',
            'fecha_recepcion' => 'datetime',
            'num_guia' => 'integer',
            'num_guia_trans' => 'integer',
            'flete' => 'decimal:2',
            'recargo' => 'decimal:2',
            'valor_guia' => 'decimal:2',
            'prima' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Sucursal, $this>
     */
    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal', 'id_sucursal');
    }

    /**
     * @return BelongsTo<MotivoTraslado, $this>
     */
    public function motivoTraslado(): BelongsTo
    {
        return $this->belongsTo(MotivoTraslado::class, 'id_motivo_traslado', 'id_motivo_traslado');
    }

    /**
     * @return BelongsTo<FormasPago, $this>
     */
    public function formaPago(): BelongsTo
    {
        return $this->belongsTo(FormasPago::class, 'id_forma_pago', 'id_formas_pago');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users', 'id');
    }

    /**
     * @return BelongsTo<Persona, $this>
     */
    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'id_persona', 'id_persona');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function usuarioUpdate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_users_update', 'id');
    }

    /**
     * @return HasMany<Detalle, $this>
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(Detalle::class, 'id_cabecera', 'id_cabecera');
    }

    /**
     * @return HasMany<HistorialEstadoGuia, $this>
     */
    public function historialEstados(): HasMany
    {
        return $this->hasMany(HistorialEstadoGuia::class, 'id_cabecera', 'id_cabecera');
    }

    /**
     * @return BelongsToMany<Manifiesto, $this>
     */
    public function manifiestos(): BelongsToMany
    {
        return $this->belongsToMany(
            Manifiesto::class,
            'manifiesto_guia',
            'id_cabecera',
            'id_manifiesto',
            'id_cabecera',
            'id_manifiesto',
        );
    }
}
