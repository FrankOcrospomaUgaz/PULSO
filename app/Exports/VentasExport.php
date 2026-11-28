<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class VentasExport implements FromCollection, WithStyles, WithColumnWidths, ShouldAutoSize, WithEvents, WithTitle
{
    protected $ventas;
    protected $fecha_inicio;
    protected $fecha_fin;
    protected $totalVendido;

    public function __construct($ventas, $fecha_inicio, $fecha_fin)
    {
        $this->ventas = $ventas;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
        $this->totalVendido = $ventas->sum('total'); // **Calcular total vendido**
    }
    
    public function collection()
    {
        $data = collect();

        // **Fila 1 - Título**
        $data->push(['Reporte de Ventas']);

        // **Fila 2 - Fechas**
        $data->push(["Desde {$this->fecha_inicio} hasta {$this->fecha_fin}"]);

        // **Fila 3 - Encabezados**
        $data->push([
            'Comprobante', 'Tipo', 'Moneda', 'Subtotal', 'IGV', 'Total', 'Fecha', 'Usuario', 'Persona',
            'Responsable', 'Tipo de detalle', 'Por consumo', 'Tipo de pago', 'Detalles', 'Habitación'
        ]);

        // **Agregar las ventas**
        foreach ($this->ventas as $venta) {
            $detalles = collect($venta->detMovventas)->map(function ($detalle) {
                return intval($detalle->cantidad) . ' ' . $detalle->descripcion . ' : ' . number_format($detalle->monto, 2, '.', '');
            })->implode(" | ");

            $data->push([
                "{$venta->serie}-{$venta->movimiento->numero}-{$venta->anio}",
                $venta->movimiento->tipodocumento->nombre ?? 'N/A',
                $venta->moneda,
                (float) $venta->subtotal,
                (float) $venta->igv,
                (float) $venta->total,
                $venta->movimiento->fecha,
                $venta->movimiento->usuario ?? 'N/A',
                ($venta->movimiento->Persona->personamaestro->nrodoc ?? 'N/A') . ' - ' . ($venta->movimiento->persona ?? 'N/A'),
                ($venta->movimiento->Responsable->personamaestro->nrodoc ?? 'N/A') . ' - ' . ($venta->movimiento->responsable ?? 'N/A'),
                $venta->tipo_detalle,
                $venta->consumo,
                $venta->tipo_pago,
                $detalles,
                ''
            ]);
        }

        // **Fila de Total Vendido**
        $data->push([
            '', '', '', '', 'TOTAL VENDIDO:', (float) $this->totalVendido, '', '', '', '', '', '', '', '', ''
        ]);

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, 'B' => 15, 'C' => 10, 'D' => 12, 'E' => 12, 'F' => 12,
            'G' => 15, 'H' => 20, 'I' => 25, 'J' => 25, 'K' => 15, 'L' => 12,
            'M' => 15, 'N' => 40, 'O' => 12
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]], // **Título grande y negrita**
            2 => ['font' => ['italic' => true, 'size' => 12]], // **Fechas en cursiva**
            3 => ['font' => ['bold' => true]], // **Encabezados en negrita**
        ];
    }

    public function title(): string
    {
        return 'Ventas';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;
                $lastRow = $this->ventas->count() + 4; // **Fila del Total Vendido**

                // **Centrar título y fechas**
                $sheet->mergeCells('A1:O1');
                $sheet->mergeCells('A2:O2');
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'alignment' => ['horizontal' => 'center']
                ]);

                // **Encabezados en azul**
                $sheet->getStyle('A3:O3')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '4F81BD']],
                    'alignment' => ['horizontal' => 'center']
                ]);

                // **Total Vendido con negrita**
                $sheet->getStyle("E{$lastRow}:F{$lastRow}")->applyFromArray([
                    'font' => ['bold' => true],
                    'alignment' => ['horizontal' => 'right']
                ]);
            },
        ];
    }
}
