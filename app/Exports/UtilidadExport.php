<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;

class UtilidadExport implements FromView, ShouldAutoSize
{
    use Exportable;

    protected $vCotizaciones; 
    protected $vFechaCarbon;
    protected $vCotizacion;
    protected $vUser;
    protected $vGastos;
    protected $vGastosGenerales;
    protected $vUtilidad;
    protected $vFechaInicio;
    protected $vFechaFin;
    protected $vTotalRows;
    protected $vSelectedRows;

    public function __construct($cotizaciones, $fechaCarbon, $cotizacion, $user, $gastos, $gastosGenerales, $utilidad, $fechaInicio, $fechaFin, $totalRows, $selectedRows)
    {
        $this->vCotizaciones     = $cotizaciones;
        $this->vFechaCarbon      = $fechaCarbon;
        $this->vCotizacion       = $cotizacion;
        $this->vUser             = $user;
        $this->vGastos           = $gastos;
        $this->vGastosGenerales  = $gastosGenerales;
        $this->vUtilidad         = $utilidad;
        $this->vFechaInicio      = $fechaInicio;
        $this->vFechaFin         = $fechaFin;
        $this->vTotalRows        = $totalRows;
        $this->vSelectedRows     = $selectedRows;
    }

    public function view() : View
    {
        return view('reporteria.utilidad.excel', [
            'cotizaciones'    => $this->vCotizaciones,
            'fechaCarbon'     => $this->vFechaCarbon,
            'cotizacion'      => $this->vCotizacion,
            'user'            => $this->vUser,
            'gastos'          => $this->vGastos,
            'gastosGenerales' => $this->vGastosGenerales,
            'utilidad'        => $this->vUtilidad,
            'fechaInicio'     => $this->vFechaInicio,
            'fechaFin'        => $this->vFechaFin,
            'totalRows'       => $this->vTotalRows,
            'selectedRows'    => $this->vSelectedRows,
        ]);
    }
}
