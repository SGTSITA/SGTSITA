<?php
namespace App\Services\Scb;

use App\Models\ScbUnidadModulo;

class ScbUnidadService
{
    public function getAll()
    {
        return ScbUnidadModulo::query()
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ScbUnidadModulo
    {
        return ScbUnidadModulo::findOrFail($id);
    }

    public function create(array $data): ScbUnidadModulo
    {
        return ScbUnidadModulo::create([
            'descripcion' => $data['descripcion'],
            'placas' => $data['placas'] ?? null,
            'activo' => !empty($data['activo']),
        ]);
    }

    public function update(int $id, array $data): ScbUnidadModulo
    {
        $unidad = $this->findById($id);

        $unidad->update([
            'descripcion' => $data['descripcion'],
            'placas' => $data['placas'] ?? null,
            'activo' => !empty($data['activo']),
        ]);

        return $unidad;
    }

    public function delete(int $id): void
    {
        $unidad = $this->findById($id);

        $stadoActual = $unidad->activo;
         $unidad->update([
          'activo' => !$stadoActual,
        ]);
    }
}
