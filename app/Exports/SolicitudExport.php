<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class SolicitudExport implements
    FromArray,
    WithStyles,
    WithColumnWidths,
    ShouldAutoSize,
    WithEvents,
    WithTitle
{
    protected $solicitudes;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($solicitudes, $fecha_inicio, $fecha_fin)
    {
        $this->solicitudes = $solicitudes;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    public function array(): array
    {
        $data = [];
        $data[] = [ // Fila 3 – Encabezados
            ''
        ];
                $data[] = [ // Fila 3 – Encabezados
            ''
        ];
                $data[] = [ // Fila 3 – Encabezados
            'Tipo', 'Número', 'Nro. de documento', 'Persona', 'Moneda',
            'Total', 'Estado', 'Fecha de solicitud', 'F. de envío',
            'F. de respuesta', 'F. de anulación', 'RUC de facturador'
        ];

        // Fila 4+ – Datos
        foreach ($this->solicitudes as $solicitud) {
            $data[] = [
                $solicitud->tipo_documento === 'B' ? 'Boleta' : 'Factura',
                $solicitud->correlativo,
                $solicitud->numero_documento ?? '0',
                $solicitud->cliente->nombre ?? 'CLIENTES VARIOS',
                'PEN',
                $solicitud->total,
                $this->getEstadoTexto($solicitud->estado),
                $solicitud->fecha,
                $solicitud->fecha_envio ?? '',
                $solicitud->fecha_respuesta ?? '',
                $solicitud->fecha_anulacion ?? '',
                '10723124871',
            ];
        }

        return $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 15,
            'C' => 18,
            'D' => 25,
            'E' => 10,
            'F' => 10,
            'G' => 20,
            'H' => 20,
            'I' => 15,
            'J' => 15,
            'K' => 15,
            'L' => 15,
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
        return 'Comprobantes';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                // Combinar celdas para títulos
                $sheet->mergeCells('A1:L1');
                $sheet->mergeCells('A2:L2');

                // Establecer títulos
                $sheet->setCellValue('A1', 'Comprobantes');
                $sheet->setCellValue('A2', "Desde {$this->fecha_inicio} hasta {$this->fecha_fin}");

                // Estilos
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['italic' => true, 'size' => 12],
                    'alignment' => ['horizontal' => 'center'],
                ]);

                $sheet->getStyle('A3:L3')->applyFromArray([
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
            'R' => 'Aún no declarado',
            'E' => 'No registrado',
            'M' => 'Declarado y activo',
            'B' => 'Declarado y anulado',
            'I' => 'Declarado e incorrecto',
            'S' => 'Declarado sin comprobar',
            'T' => 'No tributable',
        ][$estado] ?? 'Desconocido';
    }
}
