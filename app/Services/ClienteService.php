<?php

namespace App\Services;

use App\Models\Client;
use App\Models\ClientEmpresa;
use App\Models\Subclientes;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class ClienteService
{
    /**
     * Obtiene los clientes activos de una empresa.
     */
    public function getClientsByEmpresa(?int $idEmpresa = null): Collection
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return new Collection();
        }

        return Client::where('id_empresa', $idEmpresa)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene los subclientes de una empresa.
     */
    public function getSubclientesByEmpresa(?int $idEmpresa = null): Collection
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return new Collection();
        }

        return Subclientes::where('id_empresa', $idEmpresa)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Obtiene la lista formateada de clientes vinculados a una empresa (para selects / AG Grid).
     */
    public function getClientesListForEmpresa(?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return [];
        }

        $clientes = Client::join('client_empresa as ce', 'clients.id', '=', 'ce.id_client')
            ->where('ce.id_empresa', $idEmpresa)
            ->where('is_active', 1)
            ->orderBy('nombre')
            ->get();

        return $clientes->map(function ($c) {
            return [
                "IdCliente" => $c->id,
                "Nombre" => $c->nombre,
                "Correo" => $c->correo,
                "Telefono" => $c->telefono,
                "RFC" => $c->rfc,
                "RegimenFiscal" => $c->regimen_fiscal,
                "Empresa" => $c->nombre_empresa,
                "Direccion" => $c->direccion,
            ];
        })->toArray();
    }

    /**
     * Obtiene los subclientes pertenecientes a un cliente dado.
     */
    public function getSubclientesByCliente(int $idCliente): array
    {
        $subClientes = Subclientes::where('id_cliente', $idCliente)->get();

        return $subClientes->map(function ($s) {
            return [
                "IdSubCliente" => $s->id,
                "SubCliente" => $s->nombre,
                "RFC" => $s->rfc,
                "NombreComercial" => $s->nombre_empresa,
                "CorreoElectronico" => $s->correo,
                "Telefono" => $s->telefono
            ];
        })->toArray();
    }

    /**
     * Registra un cliente, crea su vínculo con la empresa y genera su usuario de acceso.
     */
    public function storeClient(array $data, ?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        return DB::transaction(function () use ($data, $idEmpresa) {
            $client = new Client();
            $client->id_empresa = $idEmpresa;
            $client->nombre = $data['nombre'];
            $client->correo = $data['correo'];
            $client->telefono = $data['telefono'];
            $client->direccion = $data['direccion'] ?? null;
            $client->regimen_fiscal = $data['regimen_fiscal'] ?? null;
            $client->rfc = $data['rfc'] ?? null;
            $client->nombre_empresa = $data['nombre_empresa'] ?? null;
            $client->fecha = date('Y-m-d');
            $client->is_active = 1;
            $client->save();

            ClientEmpresa::create([
                'id_client' => $client->id,
                'id_empresa' => $idEmpresa
            ]);

            // Crear usuario para el cliente
            $welcomePassword = strtoupper(uniqid());
            $clientUser = [
                'name' => $data['nombre'],
                'email' => $data['correo'],
                'password' => Hash::make($welcomePassword),
                'id_empresa' => $idEmpresa,
                'id_cliente' => $client->id
            ];

            $user = User::create($clientUser);
            $user->assignRole([3]); // Rol de cliente

            // Notificación por correo
            try {
                $emails = array_filter([$data['correo'], env('MAIL_NOTIFICATIONS')]);
                if (!empty($emails)) {
                    Mail::to($emails)->send(new \App\Mail\WelcomeMail($client, $welcomePassword));
                }
            } catch (\Throwable $e) {
                Log::warning('No se pudo enviar correo de bienvenida al cliente: ' . $e->getMessage());
            }

            return [
                'client' => $client,
                'password' => $welcomePassword
            ];
        });
    }

    /**
     * Actualiza los datos generales de un cliente.
     */
    public function updateClient(int $idClient, array $data): bool
    {
        return (bool) Client::where('id', $idClient)->update([
            'nombre' => $data['nombre'],
            'rfc' => $data['rfc'] ?? null,
            'regimen_fiscal' => $data['regimen_fiscal'] ?? null,
            'nombre_empresa' => $data['nombre_empresa'] ?? null,
            'correo' => $data['correo'],
            'telefono' => $data['telefono'],
            'direccion' => $data['direccion'] ?? null,
        ]);
    }

    /**
     * Conmuta la vinculación de un cliente con una empresa.
     */
    public function toggleEmpresaVinculacion(int $idClient, int $idEmpresa, bool $checked): void
    {
        if ($checked) {
            DB::table('client_empresa')->updateOrInsert([
                'id_client' => $idClient,
                'id_empresa' => $idEmpresa,
            ], [
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            DB::table('client_empresa')
                ->where('id_client', $idClient)
                ->where('id_empresa', $idEmpresa)
                ->delete();
        }
    }

    /**
     * Obtiene los IDs de las empresas vinculadas a un cliente.
     */
    public function getEmpresasVinculadas(int $idClient): array
    {
        return DB::table('client_empresa')
            ->where('id_client', $idClient)
            ->pluck('id_empresa')
            ->toArray();
    }

    /**
     * Registra un nuevo subcliente.
     */
    public function storeSubcliente(array $data, ?int $idEmpresa = null): Subclientes
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $subcliente = new Subclientes();
        $subcliente->id_cliente = $data['id_client'] ?? $data['id_cliente'] ?? null;
        $subcliente->id_empresa = $idEmpresa;
        $subcliente->nombre = $data['nombre'];
        $subcliente->correo = $data['correo'];
        $subcliente->telefono = $data['telefono'];
        $subcliente->direccion = $data['direccion'] ?? null;
        $subcliente->regimen_fiscal = $data['regimen_fiscal'] ?? null;
        $subcliente->rfc = $data['rfc'] ?? null;
        $subcliente->nombre_empresa = $data['nombre_empresa'] ?? null;
        $subcliente->fecha = date('Y-m-d');
        $subcliente->save();

        return $subcliente;
    }

    /**
     * Actualiza un subcliente.
     */
    public function updateSubcliente(int $idSubcliente, array $data): bool
    {
        $subcliente = Subclientes::findOrFail($idSubcliente);
        return $subcliente->update($data);
    }

    /**
     * Elimina un cliente.
     */
    public function deleteClient(int $idClient): ?bool
    {
        return Client::findOrFail($idClient)->delete();
    }
}
