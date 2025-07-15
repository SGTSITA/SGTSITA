<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class VXCExport implements FromView
{
    protected $cotizaciones, $totalGenerado, $retenido, $pagoNeto;

    public function __construct($cotizaciones, $totalGenerado, $retenido, $pagoNeto)
    {
        $this->cotizaciones = $cotizaciones;
        $this->totalGenerado = $totalGenerado;
        $this->retenido = $retenido;
        $this->pagoNeto = $pagoNeto;
    }

    public function view(): View
    {
        return view('reporteria.vxc.xlsx', [
            'cotizaciones' => $this->cotizaciones,
            'totalGenerado' => $this->totalGenerado,
            'retenido' => $this->retenido,
            'pagoNeto' => $this->pagoNeto
        ]);
    }
}
