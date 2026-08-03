<?php

namespace App\Services;

use App\Models\DocumCotizacion;
use App\Models\Asignaciones;
use App\Models\BitacoraViajeOperador;

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

                    // Retrieve operator files from BitacoraViajeOperador
                    $bitacoras = BitacoraViajeOperador::where('id_asignacion', $idAsignacion)->get();
                    foreach ($bitacoras as $bitacora) {
                        // 1. Start trip / loading photos
                        if (!empty($bitacora->fotos_carga)) {
                            $decodedCarga = json_decode($bitacora->fotos_carga, true);
                            if (is_array($decodedCarga)) {
                                foreach ($decodedCarga as $path) {
                                    if (!empty($path) && !isset($uniquePaths[$path])) {
                                        $uniquePaths[$path] = 'Evidencia de Carga';
                                    }
                                }
                            }
                        }
                        // 2. End trip / delivery photos
                        if (!empty($bitacora->fotos_fin)) {
                            $decodedFin = json_decode($bitacora->fotos_fin, true);
                            if (is_array($decodedFin)) {
                                foreach ($decodedFin as $path) {
                                    if (!empty($path) && !isset($uniquePaths[$path])) {
                                        $uniquePaths[$path] = 'Conclusión de Viaje';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        foreach ($uniquePaths as $relativePath => $labelTipo) {
            $relativePath = ltrim($relativePath, '/');
            $fullPath = public_path($relativePath);
            if (file_exists($fullPath)) {
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
