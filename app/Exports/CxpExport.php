<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Events\AfterSheet;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class CxpExport implements FromView, ShouldAutoSize
{
    use Exportable;

    public $vCotizaciones;
    public $vFechaCarbon;
    public $vBancosOficiales;
    public $vBancosNoOficiales;
    public $vCotizacion;
    public $vUser;

    protected bool $ajustarAltura;

    public function __construct($cotizaciones, $fechaCarbon, $bancosOficiales, $bancosNoOficiales, $cotizacion, $user, bool $ajustarAltura = false)
    {
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vBancosOficiales = $bancosOficiales;
        $this->vBancosNoOficiales = $bancosNoOficiales;
        $this->vCotizacion = $cotizacion;
        $this->vUser = $user;
        $this->ajustarAltura = $ajustarAltura;
    }

    public function view(): View
    {
        $cotizaciones = $this->vCotizaciones;
        $fechaCarbon = $this->vFechaCarbon;
        $bancos_oficiales = $this->vBancosOficiales;
        $bancos_no_oficiales = $this->vBancosNoOficiales;
        $cotizacion = $this->vCotizacion;
        $user = $this->vUser;
        $isExcel = true;

        //  dd($cotizacion);

        return view('reporteria.cxp.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user', 'isExcel'));

    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {



                if (!$this->ajustarAltura) {
                    return;
                }

                Log::info('paso aki');

                $sheet = $event->sheet->getDelegate();

                foreach ($sheet->getRowIterator() as $row) {
                    $rowIndex = $row->getRowIndex();

                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);

                    foreach ($cellIterator as $cell) {

                        $value = trim((string) $cell->getValue());

                        if ($value === 'AJUSTAR_CUENTAS') {
                            dd('SI ENCONTRO', $value, $rowIndex);
                        }
                    }
                }
            }
        ];
    }
}
