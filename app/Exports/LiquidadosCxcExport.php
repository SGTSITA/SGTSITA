<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use App\Models\User;

class LiquidadosCxcExport implements FromView,ShouldAutoSize
{
    use Exportable;
    public $vCotizaciones; 
    public $vFechaCarbon;
    public $vBancosOficiales;
    public $vBancosNoOficiales;
    public $vRegistrosBanco;
    public $vUser;
    public $vCotizacionFirst;

    public function __construct($cotizaciones, $fechaCarbon, $bancosOficiales, $bancosNoOficiales,$registrosBanco, $user, $cotizacion_first){
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vBancosOficiales = $bancosOficiales;
        $this->vBancosNoOficiales = $bancosNoOficiales;
        $this->vRegistrosBanco = $registrosBanco;
        $this->vUser = $user;
        $this->vCotizacionFirst = $cotizacion_first;
        
        
    }

    public function view() : View
    {
        $cotizaciones = $this->vCotizaciones;
        $fechaCarbon = $this->vFechaCarbon;
        $bancos_oficiales = $this->vBancosOficiales;
        $bancos_no_oficiales = $this->vBancosNoOficiales;
        $registrosBanco = $this->vRegistrosBanco;
        $user = $this->vUser;
        $cotizacion_first = $this->vCotizacionFirst;
        $isExcel = true;
        
        return view('reporteria.liquidados.cxc.pdf',compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'registrosBanco', 'user', 'cotizacion_first', 'isExcel'));
    }
}
