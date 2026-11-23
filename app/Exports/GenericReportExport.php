<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericReportExport implements FromCollection, ShouldAutoSize, WithTitle, WithStyles
{
    private string $title;
    private string $subtitle;
    private array $columns;
    private array $rows;

    public function __construct(string $title, string $subtitle, array $columns, array $rows)
    {
        $this->title = $title;
        $this->subtitle = $subtitle;
        $this->columns = array_values($columns);
        $this->rows = $rows;
    }

    public function collection(): Collection
    {
        $data = collect();

        $data->push([$this->title]);
        $data->push([$this->subtitle]);
        $data->push($this->columns);

        foreach ($this->rows as $row) {
            $data->push($row);
        }

        return $data;
    }

    public function title(): string
    {
        return substr($this->title, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true]],
            3 => ['font' => ['bold' => true]],
        ];
    }
}
