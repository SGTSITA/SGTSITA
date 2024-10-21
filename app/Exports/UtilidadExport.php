<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use App\Models\User;

class UtilidadExport implements FromView,ShouldAutoSize
{
    use Exportable;
    public $vCotizaciones; 
    public $vFechaCarbon;
    public $vCotizacion;
    public $vUser;

    public function __construct($cotizaciones, $fechaCarbon, $cotizacion, $user, $gastos){
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vCotizacion = $cotizacion;
        $this->vUser = $user;
        $this->vGastos = $gastos;
        
    }

    public function view() : View
    {
        $cotizaciones = $this->vCotizaciones;
        $fechaCarbon = $this->vFechaCarbon;
        $cotizacion = $this->vCotizacion;
        $user = $this->vUser;
        $gastos = $this->vGastos;
        $isExcel = true;
       
        return view('reporteria.utilidad.pdf',compact('cotizaciones', 'fechaCarbon', 'cotizacion', 'user','gastos','isExcel'));


    }
}