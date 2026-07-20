<?php

namespace App\Services;

use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\ComprobanteGastos;

class CotizacionesService
{
    /**
     * Get operator files by container number(s).
     *
     * @param string $numContenedor
     * @return array
     */
    public function getOperatorFilesByContenedor($numContenedor)
    {
        $numContenedor = preg_replace('/\s+/', '*', $numContenedor);
        $contenedores = explode('*', $numContenedor);
        $filesList = [];
        $uniquePaths = [];

        foreach ($contenedores as $cont) {
            $contenedor = DocumCotizacion::where('num_contenedor', $cont)->first();
            if ($contenedor) {
                $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
                if ($asignacion) {
                    $idAsignacion = $asignacion->id;

                    $comprobantes = ComprobanteGastos::where('id_asignacion', $idAsignacion)
                        ->where('tipo', '!=', 'diesel')
                        ->get();

                    foreach ($comprobantes as $comp) {
                        if (!empty($comp->imagen) && !in_array($comp->imagen, $uniquePaths)) {
                            $uniquePaths[] = $comp->imagen;
                        }
                    }
                }
            }
        }

        foreach ($uniquePaths as $relativePath) {
            $relativePath = ltrim($relativePath, '/');
            $fullPath = public_path($relativePath);
            if (file_exists($fullPath)) {
                $labelTipo = 'Evidencia / Otro';
                if (str_contains($relativePath, 'carga_contenedor')) {
                    $labelTipo = 'Evidencia de Carga';
                } elseif (str_contains($relativePath, 'entrega_contenedor')) {
                    $labelTipo = 'Evidencia de Entrega';
                } elseif (str_contains($relativePath, 'diesel')) {
                    $labelTipo = 'Comprobante Diésel';
                }

                $filesList[] = [
                    'name' => basename($relativePath),
                    'url' => asset($relativePath),
                    'size' => round(filesize($fullPath) / 1024, 2) . ' KB',
                    'date' => date("d/m/Y H:i:s", filemtime($fullPath)),
                    'tipo' => $labelTipo
                ];
            }
        }

        return $filesList;
    }
}
