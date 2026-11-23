<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class HuespedExport implements FromView
{
    protected $huespedes;
    protected $fecha_inicio;
    protected $fecha_fin;

    public function __construct($huespedes, $fecha_inicio = null, $fecha_fin = null)
    {
        $this->huespedes = $huespedes;
        $this->fecha_inicio = $fecha_inicio;
        $this->fecha_fin = $fecha_fin;
    }

    public function view(): View
    {
        return view('huesped.reportexcel', [
            'huespedes' => $this->huespedes,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
        ]);
    }
}
