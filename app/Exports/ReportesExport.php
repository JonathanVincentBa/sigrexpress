<?php

namespace App\Exports;

use App\Models\Cabecera;
use Illuminate\Database\Eloquent\Builder;
use LogicException;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithSheetProtection;

class ReportesExport implements FromQuery, WithHeadings, WithMapping, WithSheetProtection
{
    private ?string $protectionPassword = null;

    /**
     * @param  Builder<Cabecera>  $query
     */
    public function __construct(private Builder $query) {}

    /**
     * @return Builder<Cabecera>
     */
    public function query(): Builder
    {
        return $this->query;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'Número de guía',
            'Fecha de emisión',
            'Ciudad de origen',
            'Ciudad de destino',
            'Cliente / remitente',
            'Destinatario',
            'Sucursal',
            'Usuario',
            'Estado de cobro',
            'Valor',
        ];
    }

    /**
     * @return array<int, mixed>
     */
    public function map(mixed $row): array
    {
        if (! $row instanceof Cabecera) {
            throw new LogicException('La consulta del reporte debe devolver guías de remisión.');
        }

        return [
            $row->num_guia,
            $row->fecha_emision->format('d/m/Y H:i'),
            $row->ciudad_origen,
            $row->ciudad_destino,
            data_get($row, 'persona.nombre') ?? $row->nom_remitente,
            $row->nom_destinatario,
            data_get($row, 'sucursal.nombre', '—'),
            data_get($row, 'usuario.name', '—'),
            $row->estatus_cobro ?? '—',
            (float) $row->valor_guia,
        ];
    }

    public function sheetProtection(): ?string
    {
        return $this->protectionPassword ??= bin2hex(random_bytes(24));
    }
}
