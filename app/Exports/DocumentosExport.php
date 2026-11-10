<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use App\Models\User;

class DocumentosExport implements FromView,ShouldAutoSize
{
    use Exportable;
    public $vCotizaciones; 
    public $vFechaCarbon;
    public $vCotizacion;
    public $vUser;
    public $vSepararUnidades;

    public function __construct($cotizaciones, $fechaCarbon, $cotizacion, $user, $separarUnidades = false){
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vCotizacion = $cotizacion;
        $this->vUser = $user;
        $this->vSepararUnidades = (bool)$separarUnidades;
    }

    public function view() : View
    {
        $cotizaciones = $this->vCotizaciones;
        $fechaCarbon = $this->vFechaCarbon;
        $cotizacion = $this->vCotizacion;
        $user = $this->vUser;
        $separarUnidades = $this->vSepararUnidades;
        $isExcel = true;
        
        return view('reporteria.documentos.xlsx', compact('cotizaciones', 'fechaCarbon', 'cotizacion', 'user', 'isExcel', 'separarUnidades'));
    }
}

