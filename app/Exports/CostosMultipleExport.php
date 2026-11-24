<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class CostosMultipleExport implements WithMultipleSheets
{
    public function __construct(
        protected array $datosCotizacion,
        protected array $datosProveedor
    ) {}

    public function sheets(): array
    {
        return [
            new CostosCotizacionSheet($this->datosCotizacion),
            new CostosProveedorSheet($this->datosProveedor),
        ];
    }
}
