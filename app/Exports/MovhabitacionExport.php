<?php

namespace App\Exports;

use App\Models\Movhabitacion;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class MovhabitacionExport implements 
    FromArray,
    WithStyles,
    WithColumnWidths,
    ShouldAutoSize,
    WithEvents,
    WithTitle
{
    protected $movimientos;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($movimientos, $fecha_inicio, $fecha_fin)
    {
        $this->movimientos = $movimientos;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

public function array(): array
{
    $data = [];

    // Fila vacía para dejar espacio
    $data[] = [''];
    $data[] = [''];
    
    // Encabezados
    $data[] = [
        'Habitación',
        'Huespedes',
        'Ingreso',
        'Salida Programada',
        'Salida Real',
        'Estadía',
        'Precio Hab.',
        'Early CheckIn',
        'Late Checkout',
        'Descuento',
        'Total',
        'Medio Pago',
        'Moneda',
        'Comentario',
    ];

    foreach ($this->movimientos as $mov) {
        // 🧍 Huespedes (concatenar todos)
        $huespedes = $mov->huespeds->map(function ($h) {
            return optional($h->persona?->personamaestro)->nrodoc . ' - ' .
                   ($h->persona->nombres ?? '') . ' ' .
                   ($h->persona->apellidos ?? '');
        })->filter()->implode("\n");

        if (empty($huespedes)) {
            $huespedes = '0 - CLIENTES VARIOS';
        }

        // 📅 Ingreso y salida
        $fechaIngreso = \Carbon\Carbon::parse($mov->fechaingreso)->format('Y-m-d H:i:s');
        $fechaSalida = $mov->fechasalida 
            ? \Carbon\Carbon::parse($mov->fechasalida)->format('Y-m-d H:i:s') 
            : '-';
        $fechaSalidaReal = $mov->fecha_salida_real 
            ? \Carbon\Carbon::parse($mov->fecha_salida_real)->format('Y-m-d H:i:s') 
            : '-';

        // ⏱ Estadia
        $estadia = $mov->horas
            ? $mov->horas . ' hrs'
            : ($mov->dias == 1 ? '1 noche' : number_format($mov->dias, 0) . ' noches');

        // 💰 Pagos agrupados por medio
        $pagosAgrupados = $mov->movventas
            ->flatMap(function ($venta) {
                return optional($venta->movcaja?->detMovcaja)->all() ?? [];
            })
            ->groupBy(fn($item) => optional($item->Mediopago)->descripcion ?? 'Desconocido')
            ->map(fn($items) => $items->sum('monto'));

        $pagosTexto = '';
        foreach ($pagosAgrupados as $medio => $monto) {
            $pagosTexto .= $medio . ': ' . number_format($monto, 2) . "\n";
        }

        $data[] = [
            $mov->habitacion->numero ?? 'N/A',
            $huespedes,
            $fechaIngreso,
            $fechaSalida,
            $fechaSalidaReal,
            $estadia,
            number_format($mov->preciohabitacion, 2),
            number_format($mov->early_checkin ?? 0, 2),
            number_format($mov->late_checkout ?? 0, 2),
            number_format($mov->descuento ?? 0, 2),
            number_format($mov->total, 2),
            $pagosTexto,
            $mov->moneda,
            $mov->comentario ?? '-',
        ];
    }

    return $data;
}


    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 25,
            'C' => 15,
            'D' => 20,
            'E' => 20,
            'F' => 10,
            'G' => 15,
            'H' => 15,
            'I' => 15,
            'J' => 15,
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
        return 'Alojamientos';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet->mergeCells('A1:J1');
                $sheet->mergeCells('A2:J2');

                $sheet->setCellValue('A1', 'Alojamientos');
                $sheet->setCellValue('A2', "Desde {$this->fecha_inicio} hasta {$this->fecha_fin}");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 12],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle('A3:N3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => 'center'],
                ]);
            },
        ];
    }

    private function getEstadoTexto($estado)
    {
        return [
            'R' => 'Reservado',
            'O' => 'Ocupado',
            'C' => 'Finalizado',
            'X' => 'Cancelado',
        ][$estado] ?? 'Desconocido';
    }
}
