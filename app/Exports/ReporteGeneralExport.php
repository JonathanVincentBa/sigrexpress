<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteGeneralExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    /**
     * @param  Collection<int, mixed>  $guias
     * @param  array<string, mixed>  $filtros
     */
    public function __construct(
        protected Collection $guias,
        protected string $titulo = 'Reporte Gerencial',
        protected array $filtros = []
    ) {}

    public function collection(): Collection
    {
        return $this->guias;
    }

    /**
     * @return list<string>
     */
    public function headings(): array
    {
        return [
            'N.º Guía',
            'Fecha Emisión',
            'Ciudad Origen',
            'Ciudad Destino',
            'Remitente',
            'Destinatario',
            'Forma de Pago',
            'Estado Cobro',
            'Cantidad',
            'Valor Guía ($)',
            'Usuario Emisión',
            'Observaciones / Nota',
        ];
    }

    /**
     * @param  mixed  $guia
     * @return list<mixed>
     */
    public function map(mixed $guia): array
    {
        return [
            $guia->num_guia ?? '',
            ! empty($guia->fecha_emision) ? date('d/m/Y', strtotime((string) $guia->fecha_emision)) : '',
            $guia->ciudad_origen ?? '',
            $guia->ciudad_destino ?? '',
            $guia->nom_remitente ?? '',
            $guia->nom_destinatario ?? '',
            $guia->forma_pago ?? '',
            $guia->estatus_cobro ?? '—',
            (int) ($guia->cantidad ?? 0),
            (float) ($guia->valor_guia ?? 0),
            $guia->usuario_nombre ?? '',
            $guia->nota ?? '',
        ];
    }

    /**
     * @return array<int|string, mixed>
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'],
                ],
            ],
        ];
    }
}
