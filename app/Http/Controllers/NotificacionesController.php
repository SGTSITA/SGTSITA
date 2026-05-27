<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\NotificacionesService;
use App\Models\NotificacionTipo;
use App\Models\NotificacionRegla;
use App\Models\Notificacion;
use App\Models\User;
use Illuminate\Validation\Rule;

class NotificacionesController extends Controller
{
 protected $notificacionesService;

    public function __construct(NotificacionesService $notificacionesService)
    {
        $this->notificacionesService = $notificacionesService;
        $this->middleware('auth');
    }


    public function index()
    {
       if (!auth()->user()->can('notificacions')) {
        abort(403);
    }

     $data = $this->notificacionesService->getDataIndex();

        return view('notificaciones.index', $data);
    }

    public function storeTipo(Request $request)
    {
        if (!auth()->user()->can('notificaciones-create')) {
            abort(403);
        }

        $data = $request->validate([
            'clave' => [
                'required',
                'string',
                'max:255',
                'unique:notificacion_tipos,clave',
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'descripcion' => [
                'nullable',
                'string',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        try {
            $this->notificacionesService->crearTipo($data);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Tipo de notificación creado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo crear el tipo de notificación.');
        }
    }

    public function updateTipo(Request $request, NotificacionTipo $tipo)
    {
        if (!auth()->user()->can('notificaciones-edit')) {
            abort(403);
        }

        $data = $request->validate([
            'clave' => [
                'required',
                'string',
                'max:255',
                Rule::unique('notificacion_tipos', 'clave')->ignore($tipo->id),
            ],
            'nombre' => [
                'required',
                'string',
                'max:255',
            ],
            'descripcion' => [
                'nullable',
                'string',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        try {
            $this->notificacionesService->actualizarTipo($tipo, $data);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Tipo de notificación actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo actualizar el tipo de notificación.');
        }
    }

    public function destroyTipo(NotificacionTipo $tipo)
    {
        if (!auth()->user()->can('notificaciones-delete')) {
            abort(403);
        }

        try {
            $this->notificacionesService->eliminarTipo($tipo);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Tipo de notificación eliminado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'No se pudo eliminar el tipo. Puede tener reglas o notificaciones relacionadas.');
        }
    }

    public function toggleTipo(NotificacionTipo $tipo)
    {
        if (!auth()->user()->can('notificaciones-edit')) {
            abort(403);
        }

        try {
            $this->notificacionesService->toggleTipo($tipo);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Estatus del tipo actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'No se pudo cambiar el estatus del tipo.');
        }
    }

    public function storeRegla(Request $request)
    {
        if (!auth()->user()->can('notificaciones-create')) {
            abort(403);
        }

        $data = $request->validate([
            'notificacion_tipo_id' => [
                'required',
                'exists:notificacion_tipos,id',
            ],
            'empresa_id' => [
                'nullable',
                'exists:empresas,id',
            ],
            'notificar_empresa' => [
                'nullable',
                'boolean',
            ],
            'notificar_cliente' => [
                'nullable',
                'boolean',
            ],
            'notificar_proveedor' => [
                'nullable',
                'boolean',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        try {
            $this->notificacionesService->crearRegla($data);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Regla de notificación creada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo crear la regla de notificación.');
        }
    }

    public function updateRegla(Request $request, NotificacionRegla $regla)
    {
        if (!auth()->user()->can('notificaciones-edit')) {
            abort(403);
        }

        $data = $request->validate([
            'notificacion_tipo_id' => [
                'required',
                'exists:notificacion_tipos,id',
            ],
            'empresa_id' => [
                'nullable',
                'exists:empresas,id',
            ],
            'notificar_empresa' => [
                'nullable',
                'boolean',
            ],
            'notificar_cliente' => [
                'nullable',
                'boolean',
            ],
            'notificar_proveedor' => [
                'nullable',
                'boolean',
            ],
            'activo' => [
                'required',
                'boolean',
            ],
        ]);

        try {
            $this->notificacionesService->actualizarRegla($regla, $data);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Regla de notificación actualizada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo actualizar la regla de notificación.');
        }
    }

    public function destroyRegla(NotificacionRegla $regla)
    {
        if (!auth()->user()->can('notificaciones-delete')) {
            abort(403);
        }

        try {
            $this->notificacionesService->eliminarRegla($regla);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Regla de notificación eliminada correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'No se pudo eliminar la regla de notificación.');
        }
    }

    public function toggleRegla(NotificacionRegla $regla)
    {
        if (!auth()->user()->can('notificaciones-edit')) {
            abort(403);
        }

        try {
            $this->notificacionesService->toggleRegla($regla);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Estatus de la regla actualizado correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'No se pudo cambiar el estatus de la regla.');
        }
    }

    public function storeUsuarioRegla(Request $request)
    {
        if (!auth()->user()->can('notificaciones-create')) {
            abort(403);
        }

        $data = $request->validate([
            'notificacion_regla_id' => [
                'required',
                'exists:notificacion_reglas,id',
            ],
            'user_id' => [
                'required',
                'exists:users,id',
            ],
        ]);

        try {
            $this->notificacionesService->asignarUsuarioRegla(
                $data['notificacion_regla_id'],
                $data['user_id']
            );

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Usuario asignado a la regla correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'No se pudo asignar el usuario a la regla. Puede que ya esté asignado.');
        }
    }

    public function destroyUsuarioRegla(NotificacionRegla $regla, User $usuario)
    {
        if (!auth()->user()->can('notificaciones-delete')) {
            abort(403);
        }

        try {
            $this->notificacionesService->quitarUsuarioRegla($regla, $usuario);

            return redirect()
                ->route('notificaciones.index')
                ->with('success', 'Usuario removido de la regla correctamente.');
        } catch (\Throwable $e) {
            report($e);

            return redirect()
                ->back()
                ->with('error', 'No se pudo remover el usuario de la regla.');
        }
    }

    public function misNotificaciones()
{
    return view('notificaciones.mis_notificaciones', [
        'notificaciones' => $this->notificacionesService->listarUsuarioCompleto(auth()->id()),
    ]);
}

public function misNotificacionesClientes()
{
//dd($this->notificacionesService->listarUsuarioCompleto(auth()->id()));
    return view('notificaciones.mis_notificaciones_clientes', [
        'notificaciones' => $this->notificacionesService->listarUsuarioCompleto(auth()->id()),
    ]);
}

public function listarUsuario()
{
    $notificaciones = $this->notificacionesService->listarUsuario(auth()->id(), 10);
    $noLeidas = $this->notificacionesService->contarNoLeidas(auth()->id());

    return response()->json([
        'success' => true,
        'no_leidas' => $noLeidas,
        'data' => $notificaciones,
    ]);
}

public function contadorUsuario()
{
    return response()->json([
        'success' => true,
        'no_leidas' => $this->notificacionesService->contarNoLeidas(auth()->id()),
    ]);
}

public function marcarLeidaUsuario(Notificacion $notificacion)
{
    $ok = $this->notificacionesService->marcarComoLeidaUsuario(
        $notificacion,
        auth()->id()
    );

    return response()->json([
        'success' => $ok,
    ]);
}

public function marcarTodasLeidasUsuario()
{
    $total = $this->notificacionesService->marcarTodasComoLeidas(auth()->id());

    return response()->json([
        'success' => true,
        'total' => $total,
    ]);
}
}
