<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReporteGeneralExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping, WithStyles, WithEvents
{
    /**
     * @param  Collection<int, mixed>  $guias
     * @param  array<string, mixed>  $filtros
     * @param  array<string, mixed>  $resumen
     */
    public function __construct(
        protected Collection $guias,
        protected string $titulo = 'Reporte Gerencial',
        protected array $filtros = [],
        protected array $resumen = []
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
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E3A8A'],
                ],
            ],
        ];
    }

    /**
     * @return array<class-string, callable>
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event): void {
                $sheet = $event->sheet->getDelegate();

                $totalGuias = (int) ($this->resumen['total_guias'] ?? $this->guias->count());
                $totalBultos = (int) ($this->resumen['total_bultos'] ?? $this->guias->sum(fn ($g) => (int) ($g->cantidad ?? 0)));
                $totalValor = (float) ($this->resumen['total_valor'] ?? $this->guias->sum(fn ($g) => (float) ($g->valor_guia ?? 0)));

                // Fila 1 es cabecera; los datos empiezan en la fila 2
                $lastDataRow = 1 + $this->guias->count();
                $rowTotalGuias = $lastDataRow + 2;
                $rowTotalBultos = $lastDataRow + 3;
                $rowTotalValor = $lastDataRow + 4;

                // Formato numérico en columnas Cantidad (I) y Valor Guía ($) (J)
                if ($lastDataRow >= 2) {
                    $sheet->getStyle("I2:I{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("J2:J{$lastDataRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                }

                // Etiquetas y valores del resumen final (igual que en el PDF)
                $sheet->mergeCells("A{$rowTotalGuias}:H{$rowTotalGuias}");
                $sheet->setCellValue("A{$rowTotalGuias}", 'TOTAL GUÍAS GENERADAS:');
                $sheet->setCellValue("I{$rowTotalGuias}", $totalGuias);

                $sheet->mergeCells("A{$rowTotalBultos}:H{$rowTotalBultos}");
                $sheet->setCellValue("A{$rowTotalBultos}", 'TOTAL BULTOS / CANTIDAD:');
                $sheet->setCellValue("I{$rowTotalBultos}", $totalBultos);

                $sheet->mergeCells("A{$rowTotalValor}:H{$rowTotalValor}");
                $sheet->setCellValue("A{$rowTotalValor}", 'VALOR TOTAL GUÍAS ($):');
                $sheet->setCellValue("J{$rowTotalValor}", $totalValor);

                // Formatos numéricos del resumen
                $sheet->getStyle("I{$rowTotalGuias}:I{$rowTotalBultos}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("J{$rowTotalValor}")->getNumberFormat()->setFormatCode('#,##0.00');

                // Estilos del bloque de totales
                foreach ([$rowTotalGuias, $rowTotalBultos, $rowTotalValor] as $rowNum) {
                    $range = "A{$rowNum}:J{$rowNum}";
                    $sheet->getStyle($range)->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['argb' => 'FF0F172A'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['argb' => 'FFF1F5F9'],
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FFCBD5E1'],
                            ],
                        ],
                    ]);
                    $sheet->getStyle("A{$rowNum}:H{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("I{$rowNum}:J{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                }

                // Resaltar el valor total final
                $sheet->getStyle("A{$rowTotalValor}:J{$rowTotalValor}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FFDBEAFE'],
                    ],
                ]);
            },
        ];
    }
}