<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CxcExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    use Exportable;

    public $vCotizaciones; 
    public $vFechaCarbon;
    public $vBancosOficiales;
    public $vBancosNoOficiales;
    public $vCotizacion;
    public $vUser;

    public function __construct($cotizaciones, $fechaCarbon, $bancosOficiales, $bancosNoOficiales, $cotizacion, $user)
    {
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vBancosOficiales = $bancosOficiales;
        $this->vBancosNoOficiales = $bancosNoOficiales;
        $this->vCotizacion = $cotizacion;
        $this->vUser = $user;
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

        return view('reporteria.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user', 'isExcel'));
    }

    public function styles(Worksheet $sheet)
{
    // Combinar celdas para el nombre de la empresa (por ejemplo, A1:D1)
    $sheet->mergeCells('A1:D1');
    $sheet->mergeCells('A5:D5');
    // Retornar los estilos
    return [
        // Estilo para la celda combinada de la cabecera principal (nombre de la empresa)
        'A1' => [
            'font' => [
                'bold' => true,
                'size' => 20, // Tamaño de letra para el nombre de la empresa
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4CAF50'], // Color de fondo verde
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ],
        
        // Estilo para el cuerpo de la tabla (todas las celdas de A2 hacia abajo)
        'A2:Z1000' => [
            'font' => [
                'size' => 20, // Tamaño de letra para el contenido
                'color' => ['rgb' => '000000'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ],
    ];
}

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;

            // Asegurarse de que el ancho de las columnas de A a Z se ajuste a 20
            foreach (range('A', 'Z') as $column) {
                $sheet->getColumnDimension($column)->setWidth(20); // Establecer el ancho de la columna a 20
            }

            // Configuraciones de impresión
            $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

            // Establecer márgenes de la página
            $sheet->getPageMargins()->setTop(0.2);  // Aumento del margen superior
            $sheet->getPageMargins()->setRight(0.2); // Aumento del margen derecho
            $sheet->getPageMargins()->setLeft(0.2);  // Aumento del margen izquierdo
            $sheet->getPageMargins()->setBottom(0.2); // Aumento del margen inferior

            // Ajustar el contenido a una sola página de ancho
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            // Cambiar el tamaño del papel a tamaño carta (8.5 x 11 pulgadas)
            $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
        },
    ];
}

}

