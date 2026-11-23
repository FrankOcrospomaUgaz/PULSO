<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovreservaReportExport implements
    FromArray,
    WithStyles,
    WithColumnWidths,
    ShouldAutoSize,
    WithEvents,
    WithTitle
{
    protected $reservas;
    protected $fechaInicio;
    protected $fechaFin;

    public function __construct($reservas, $fechaInicio, $fechaFin)
    {
        $this->reservas = $reservas;
        $this->fechaInicio = $fechaInicio;
        $this->fechaFin = $fechaFin;
    }

    public function array(): array
    {
        $data = [];

        $data[] = [''];
        $data[] = [''];
        $data[] = [
            'Codigo',
            'Habitacion(es)',
            'Cliente',
            'Celular',
            'Origen',
            'F. reserva',
            'F. inicio',
            'F. salida',
            'Dias',
            'Personas',
            'Total',
            'Adelanto',
            'Medio pago',
            'Estado',
            'Situacion',
            'Usuario',
            'Email',
            'Observacion',
        ];

        foreach ($this->reservas as $reserva) {
            $habitaciones = collect(json_decode($reserva->json_habitaciones))
                ->pluck('numero')
                ->map(fn($numero) => trim((string) $numero))
                ->filter()
                ->join(', ');

            $cliente = trim(
                (optional($reserva->movimiento?->Persona?->personamaestro)->nrodoc ?? 'N/A') .
                ' - ' .
                ($reserva->movimiento?->persona ?? 'N/A')
            );

            $usuario = strtoupper($reserva->movimiento?->responsable?->usuario?->name ?? 'N/A');
            $situacion = $reserva->movimiento?->situacion ?? 'Desactivado';

            $data[] = [
                str_pad((string) ($reserva->movimiento?->numero ?? $reserva->id), 8, '0', STR_PAD_LEFT),
                $habitaciones,
                $cliente,
                $reserva->celular ?? '',
                $reserva->origen?->descripcion ?? '-',
                $this->formatDateTime($reserva->fechavencimiento),
                $this->formatDateTime($reserva->fechainicio),
                $this->formatDateTime($reserva->fechasalida),
                $reserva->dias ?? 0,
                $reserva->nropersonas ?? 0,
                number_format((float) $reserva->total, 2, '.', ''),
                number_format((float) ($reserva->adelanto ?? 0), 2, '.', ''),
                $reserva->mediopago?->descripcion ?? '-',
                $reserva->estado,
                $situacion,
                $usuario,
                $reserva->email ?? '-',
                $reserva->observacion ?? '-',
            ];
        }

        $data[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL',
            number_format((float) $this->reservas->sum('total'), 2, '.', ''),
            number_format((float) $this->reservas->sum('adelanto'), 2, '.', ''),
            '',
            '',
            '',
            '',
            '',
            '',
        ];

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 12,
            'B' => 20,
            'C' => 28,
            'D' => 16,
            'E' => 18,
            'F' => 18,
            'G' => 18,
            'H' => 18,
            'I' => 10,
            'J' => 10,
            'K' => 12,
            'L' => 12,
            'M' => 16,
            'N' => 14,
            'O' => 14,
            'P' => 18,
            'Q' => 24,
            'R' => 28,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'size' => 12]],
            3 => ['font' => ['bold' => true]],
        ];
    }

    public function title(): string
    {
        return 'Reservas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $this->reservas->count() + 4;

                $sheet->mergeCells('A1:R1');
                $sheet->mergeCells('A2:R2');
                $sheet->setCellValue('A1', 'Reservas de habitaciones');
                $sheet->setCellValue('A2', 'Desde ' . ($this->fechaInicio ?: '-') . ' hasta ' . ($this->fechaFin ?: '-'));

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 12],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle('A3:R3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle("J{$lastRow}:L{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                ]);
            },
        ];
    }

    private function formatDateTime($value): string
    {
        if (!$value) {
            return '-';
        }

        return Carbon::parse($value)->format('Y-m-d H:i:s');
    }
}
