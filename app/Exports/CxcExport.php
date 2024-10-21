<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use App\Models\User;

class CxcExport implements FromView,ShouldAutoSize
{
    use Exportable;
    public $vCotizaciones; 
    public $vFechaCarbon;
    public $vBancosOficiales;
    public $vBancosNoOficiales;
    public $vCotizacion;
    public $vUser;

    public function __construct($cotizaciones, $fechaCarbon, $bancosOficiales, $bancosNoOficiales, $cotizacion,  $user){
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vBancosOficiales = $bancosOficiales;
        $this->vBancosNoOficiales = $bancosNoOficiales;
        $this->vCotizacion = $cotizacion;
        $this->vUser = $user;
        
    }

    public function view() : View
    {
        $cotizaciones = $this->vCotizaciones;
        $fechaCarbon = $this->vFechaCarbon;
        $bancos_oficiales = $this->vBancosOficiales;
        $bancos_no_oficiales = $this->vBancosNoOficiales;
        $cotizacion = $this->vCotizacion;
        $user = $this->vUser;
        $isExcel = true;
       
        return view('reporteria.cxc.pdf',compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user','isExcel'));
    }
}
