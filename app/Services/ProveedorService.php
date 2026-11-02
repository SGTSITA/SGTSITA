<?php

namespace App\Services;

use App\Models\Configuracion;
use App\Models\CuentasBancarias;
use App\Models\Empresas;
use App\Models\Proveedor;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ProveedorService
{
    /**
     * Obtiene los proveedores del catálogo principal filtrados por permisos del usuario.
     */
    public function getProveedoresCatalogoPrincipal($user): Collection
    {
        if ($user->es_admin) {
            return Proveedor::CatalogoPrincipal()
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return Proveedor::CatalogoPrincipal()
            ->where('id_empresa', $user->id_empresa)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene las empresas disponibles para asignar proveedores según el usuario.
     */
    public function getEmpresasDisponibles($user): Collection
    {
        if ($user->es_admin) {
            return Empresas::orderBy('nombre')->get();
        }

        return Empresas::where('id', $user->id_empresa)->orderBy('nombre')->get();
    }

    /**
     * Obtiene la lista completa de proveedores con relación de empresa (para AG Grid).
     */
    public function getProveedoresList($user): Collection
    {
        $query = Proveedor::with('empresa')->orderBy('created_at', 'desc');

        if (!$user->es_admin) {
            $query->where('id_empresa', $user->id_empresa);
        }

        return $query->get();
    }

    /**
     * Valida si un RFC ya existe en los proveedores activos.
     */
    public function existeRfc(?string $rfc): bool
    {
        if (empty($rfc)) {
            return false;
        }

        return Proveedor::where('rfc', $rfc)->exists();
    }

    /**
     * Crea un nuevo proveedor soportando caso 'lista' (empresa existente) o 'mep' (nueva empresa y usuario).
     */
    public function storeProveedor(array $data): Proveedor
    {
        return DB::transaction(function () use ($data) {
            if ($data['tipo_empresa'] === 'lista') {
                return Proveedor::create([
                    'nombre' => $data['nombre'],
                    'correo' => $data['correo'],
                    'telefono' => $data['telefono'],
                    'direccion' => $data['direccion'] ?? null,
                    'regimen_fiscal' => $data['regimen_fiscal'] ?? null,
                    'rfc' => $data['rfc'] ?? null,
                    'tipo' => $data['tipo'],
                    'fecha' => now()->format('Y-m-d'),
                    'id_empresa' => $data['id_empresa'],
                    'tipo_viaje' => $data['tipo_viaje'],
                ]);
            }

            // CASO 2: Crear nueva empresa (MEP)
            $config = Configuracion::create([
                'nombre_sistema' => strtoupper($data['nombre']),
                'color_principal' => '#000000',
                'color_iconos_sidebar' => '#000000',
                'color_iconos_cards' => '#000000',
                'color_boton_add' => '#000000',
                'icon_boton_add' => '#000000',
                'color_boton_save' => '#000000',
                'icon_boton_save' => '#000000',
                'color_boton_close' => '#000000',
                'icon_boton_close' => '#000000',
            ]);

            $empresa = Empresas::create([
                'nombre' => strtoupper($data['nombre']),
                'rfc' => $data['rfc'] ?? null,
                'direccion' => $data['direccion'] ?? null,
                'telefono' => $data['telefono'],
                'correo' => $data['correo'],
                'id_tipo_empresa' => 2, // Proveedor
                'id_configuracion' => $config->id,
            ]);

            Proveedor::$forceEmpresaFromAuth = false;

            $proveedor = Proveedor::create([
                'nombre' => $data['nombre'],
                'correo' => $data['correo'],
                'telefono' => $data['telefono'],
                'direccion' => $data['direccion'] ?? null,
                'regimen_fiscal' => $data['regimen_fiscal'] ?? null,
                'rfc' => $data['rfc'] ?? null,
                'tipo' => $data['tipo'],
                'fecha' => now()->format('Y-m-d'),
                'id_empresa' => $empresa->id,
                'tipo_viaje' => $data['tipo_viaje'],
            ]);

            Proveedor::$forceEmpresaFromAuth = true;

            $user = User::create([
                'name' => $data['nombre'],
                'email' => $data['correo'],
                'password' => Hash::make($data['password']),
                'id_empresa' => $empresa->id,
                'es_admin' => false,
                'id_cliente' => 0,
            ]);

            $user->assignRole('Proveedor Directo');

            return $proveedor;
        });
    }

    /**
     * Actualiza un proveedor existente.
     */
    public function updateProveedor(int $id, array $data): bool
    {
        $proveedor = Proveedor::findOrFail($id);
        return $proveedor->update($data);
    }

    /**
     * Registra una cuenta bancaria para un proveedor.
     */
    public function storeCuentaBancaria(array $data): CuentasBancarias
    {
        return CuentasBancarias::create([
            'nombre_beneficiario' => $data['nombre_beneficiario'],
            'id_proveedores' => $data['id_proveedores'],
            'cuenta_bancaria' => $data['cuenta_bancaria'],
            'nombre_banco' => $data['nombre_banco'],
            'cuenta_clabe' => $data['cuenta_clabe'],
        ]);
    }

    /**
     * Define una cuenta bancaria como prioritaria (cuenta_1 o cuenta_2) para un proveedor.
     */
    public function definirCuentaPrioridad(int $idCuenta, int $tipo, bool $checked): CuentasBancarias
    {
        $cuenta = CuentasBancarias::findOrFail($idCuenta);

        // Limpiar prioridad del mismo tipo en las demás cuentas del proveedor
        CuentasBancarias::where('id_proveedores', $cuenta->id_proveedores)->update([
            "cuenta_{$tipo}" => false
        ]);

        $cuenta->update(["cuenta_{$tipo}" => $checked]);

        return $cuenta;
    }

    /**
     * Obtiene el proveedor y todas sus cuentas bancarias (incluyendo eliminadas lógicamente).
     */
    public function getCuentasBancariasConProveedor(int $idProveedor): array
    {
        $proveedor = Proveedor::findOrFail($idProveedor);
        $cuentas = CuentasBancarias::withTrashed()
            ->where('id_proveedores', $idProveedor)
            ->get();

        return [
            'proveedor' => $proveedor,
            'cuentas' => $cuentas
        ];
    }

    /**
     * Cambia el estado activo/inactivo (SoftDelete) de una cuenta bancaria.
     */
    public function cambiarEstadoCuentaBancaria(int $idCuenta, bool $activo): CuentasBancarias
    {
        $cuenta = CuentasBancarias::withTrashed()->findOrFail($idCuenta);

        if ($activo) {
            $cuenta->restore();
        } else {
            $cuenta->delete();
        }

        return $cuenta;
    }

    /**
     * Elimina lógicamente una cuenta bancaria.
     */
    public function deleteCuentaBancaria(int $idCuenta): bool
    {
        $cuenta = CuentasBancarias::findOrFail($idCuenta);
        return (bool) $cuenta->delete();
    }

    /**
     * Restaura una cuenta bancaria eliminada lógicamente.
     */
    public function restoreCuentaBancaria(int $idCuenta): bool
    {
        $cuenta = CuentasBancarias::onlyTrashed()->findOrFail($idCuenta);
        return (bool) $cuenta->restore();
    }
}
