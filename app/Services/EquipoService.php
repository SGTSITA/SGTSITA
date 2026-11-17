<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\Empresas;
use Illuminate\Database\Eloquent\Collection;

class EquipoService
{
    /**
     * Obtiene los tractocamiones / camiones activos de una empresa para reportería,
     * asignaciones y catálogo de imputación de gastos.
     *
     * @param int|null $idEmpresa
     * @return Collection
     */
    public function getTractocamiones(?int $idEmpresa = null): Collection
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return new Collection();
        }

        return Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Tractos / Camiones')
            ->where('activo', 1)
            ->orderBy('id_equipo', 'asc')
            ->get();
    }

    /**
     * Obtiene todos los equipos de una empresa por tipo opcional.
     *
     * @param int|null $idEmpresa
     * @param string|null $tipo
     * @return Collection
     */
    public function getEquiposPorEmpresa(?int $idEmpresa = null, ?string $tipo = null): Collection
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return new Collection();
        }

        $query = Equipo::where('id_empresa', $idEmpresa);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }
        $query->where('activo', 1);

        return $query->orderBy('id_equipo', 'asc')->get();
    }

    /**
     * Valida si la empresa actual (o especificada) opera bajo el modelo multi-equipo
     * (H00-GLOBAL o requiere_unidad_gasto).
     *
     * @param int|null $idEmpresa
     * @return bool
     */
    public function esEmpresaGlobal(?int $idEmpresa = null): bool
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return false;
        }

        $empresa = Empresas::withoutGlobalScopes()->find($idEmpresa);

        return (bool) ($empresa?->esGlobalMultiEquipo() ?? false);
    }

    /**
     * Devuelve el texto formateado estándar para selects (id_equipo - marca).
     *
     * @param Equipo $equipo
     * @return string
     */
    public function formatTextoSelect(Equipo $equipo): string
    {
        return $equipo->texto_select;
    }
}
