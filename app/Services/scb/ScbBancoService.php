<?php

namespace App\Services\scb;

use App\Models\ScbBancoModulo;

class ScbBancoService
{
    public function getAll()
    {
        return ScbBancoModulo::query()
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ScbBancoModulo
    {
        return ScbBancoModulo::findOrFail($id);
    }

    public function create(array $data): ScbBancoModulo
    {
        return ScbBancoModulo::create([
            'nombre' => $data['nombre'],
            'clave' => $data['clave'] ?? null,
            'activo' => !empty($data['activo']),
        ]);
    }

    public function update(int $id, array $data): ScbBancoModulo
    {
        $banco = $this->findById($id);

        $banco->update([
            'nombre' => $data['nombre'],
            'clave' => $data['clave'] ?? null,
            'activo' => !empty($data['activo']),
        ]);

        return $banco;
    }

    public function delete(int $id): void
    {
        $banco = $this->findById($id);
           $stadoActual = $banco->activo;
           $banco->update([
                'activo' => !$stadoActual,
             ]);
        //$banco->delete(); --- IGNORar ---
    }
}
