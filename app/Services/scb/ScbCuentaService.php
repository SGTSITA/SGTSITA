<?php

namespace App\Services\Scb;

use App\Models\ScbBancoModuloCuenta;

class ScbCuentaService
{
    public function getAll()
    {
        return ScbBancoModuloCuenta::query()
            ->with('banco')
            ->orderByDesc('id')
            ->get();
    }

    public function findById(int $id): ScbBancoModuloCuenta
    {
        return ScbBancoModuloCuenta::findOrFail($id);
    }

    public function create(array $data): ScbBancoModuloCuenta
    {
        return ScbBancoModuloCuenta::create([
            'banco_id' => $data['banco_id'],
            'beneficiario' => $data['beneficiario'] ?? null,
            'numero_cuenta' => $data['numero_cuenta'] ?? null,
            'clabe' => $data['clabe'] ?? null,
            'moneda' => $data['moneda'] ?? 'MXN',
            'saldo_inicial' => $data['saldo_inicial'] ?? 0,
            'activo' => !empty($data['activo']),
        ]);
    }

    public function update(int $id, array $data): ScbBancoModuloCuenta
    {
        $cuenta = $this->findById($id);

        $cuenta->update([
            'banco_id' => $data['banco_id'],
            'beneficiario' => $data['beneficiario'] ?? null,
            'numero_cuenta' => $data['numero_cuenta'] ?? null,
            'clabe' => $data['clabe'] ?? null,
            'moneda' => $data['moneda'] ?? 'MXN',
            'saldo_inicial' => $data['saldo_inicial'] ?? 0,
            'activo' => !empty($data['activo']),
        ]);

        return $cuenta;
    }

    public function delete(int $id): void
    {
        $cuenta = $this->findById($id);
            $stadoActual = $cuenta->activo;
            $cuenta->update([
                'activo' => !$stadoActual,
             ]);
        //$cuenta->delete(); --- IGNORE ---
    }
}
