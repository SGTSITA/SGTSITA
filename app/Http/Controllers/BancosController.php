<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\BancoDinero;
use App\Models\BancoDineroOpe;
use App\Models\Bancos;
use App\Models\Cotizaciones;
use App\Models\GastosGenerales;
use App\Models\BancoSaldoDiario;
use App\Models\MovimientoBancario;
use Illuminate\Support\Facades\Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Empresas;
use Barryvdh\DomPDF\Facade\Pdf;

class BancosController extends Controller
{
    public function index()
    {
        $bancos = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        foreach ($bancos as $banco) {
            $cotizaciones = Cotizaciones::where('id_banco1', '=', $banco->id)->orwhere('id_banco2', '=', $banco->id)->get();
            $proveedores = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                        ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                        ->where('asignaciones.id_camion', '=', null)
                        ->where('cotizaciones.id_prove_banco1', '=', $banco->id)
                        ->orWhere('cotizaciones.id_prove_banco2', '=', $banco->id)
                        ->select('cotizaciones.*')
                        ->get();

            $banco_dinero_entrada = BancoDinero::where('tipo', '=', 'Entrada')
            ->where(function ($query) use ($banco) {
                $query->where('id_banco1', '=', $banco->id)
                      ->orWhere('id_banco2', '=', $banco->id);
            })
            ->get();

            $banco_dinero_salida = BancoDinero::where('tipo', '=', 'Salida')
            ->where(function ($query) use ($banco) {
                $query->where('id_banco1', '=', $banco->id)
                      ->orWhere('id_banco2', '=', $banco->id);
            })
            ->get();

            $banco_dinero_salida_ope = BancoDineroOpe::whereIn('tipo', ['Entrada', 'Salida'])
            ->where(function ($query) use ($banco) {
                $query->where('id_banco1', '=', $banco->id)
                      ->orWhere('id_banco2', '=', $banco->id);
            })
            ->get();

            $gastos_generales = GastosGenerales::where('id_banco1', '=', $banco->id)->where('is_active', 1)->get();

            $banco_entrada = 0;
            $banco_salida = 0;

            foreach ($banco_dinero_entrada as $item) {
                if ($item->id_banco1 == $banco->id) {
                    $banco_entrada += $item->monto1;
                }
                if ($item->id_banco2 == $banco->id) {
                    $banco_entrada += $item->monto2;
                }
            }

            foreach ($banco_dinero_salida as $item) {
                if ($item->id_banco1 == $banco->id) {
                    $banco_salida += $item->monto1;
                }
                if ($item->id_banco2 == $banco->id) {
                    $banco_salida += $item->monto2;
                }
            }

            $total = 0;

            foreach ($cotizaciones as $item) {
                if ($item->id_banco1 == $banco->id) {
                    $total += $item->monto1;
                }
                if ($item->id_banco2 == $banco->id) {
                    $total += $item->monto2;
                }
            }

            $pagos = 0;
            $pagos_salida = 0;

            foreach ($proveedores as $item) {
                if ($item->id_prove_banco1 == $banco->id) {
                    $pagos += $item->prove_monto1;
                }
                if ($item->id_prove_banco2 == $banco->id) {
                    $pagos += $item->prove_monto2;
                }
            }

            foreach ($banco_dinero_salida_ope as $item) {
                if ($item->id_banco1 == $banco->id) {
                    $pagos_salida += $item->monto1;
                }
                if ($item->id_banco2 == $banco->id) {
                    $pagos_salida += $item->monto2;
                }
            }

            $gastos_extras = 0;
            foreach ($gastos_generales as $item) {
                $gastos_extras += $item->monto1;
            }

            $total_pagos = 0;
            $total_pagos = $pagos + $pagos_salida + $banco_salida + $gastos_extras;
            $saldo = 0;
            $saldo = ($banco->saldo_inicial + $total + $banco_entrada) - $total_pagos;

            // Actualizar el saldo del banco actual en la base de datos
            $banco->saldo = $saldo;
            // $banco->save();
        }
        return view('bancos.index', compact('bancos'));
    }

    public function list()
    {
        return $bancos = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->get();
    }

    public function cambiarEstado(Request $request, $id)
    {
        $banco = Bancos::withTrashed()->findOrFail($id);

        if ($request->estado == 1) {
            // Si se activa, restaurar y actualizar estado
            $banco->restore();
            $banco->estado = 1;
        } else {
            // Si se inactiva, cambiar estado y hacer soft delete
            $banco->estado = 0;
            $banco->delete(); // Esto llenará `deleted_at`
        }

        $banco->save();

        return response()->json(['success' => true, 'estado' => $banco->estado, 'deleted_at' => $banco->deleted_at]);
    }


    public function store(Request $request)
    {

        $banco = new Bancos();
        $banco->nombre_beneficiario = $request->get('nombre_beneficiario');
        $banco->nombre_banco = $request->get('nombre_banco');
        $banco->cuenta_bancaria = $request->get('cuenta_bancaria');
        $banco->clabe = $request->get('clabe');
        $banco->saldo_inicial = $request->get('saldo_inicial');
        $banco->saldo = 0;
        $banco->save();

        return redirect()->route('index.bancos')
            ->with('success', 'Banco creado exitosamente.');

    }

    public function edit($id)
    {
        $banco = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->where('id', '=', $id)->first();
        // $saldoInicial = $banco->saldo_inicial;
        $startOfWeek = Carbon::now()->format('Y-m-d');
        $endOfWeek = Carbon::now()->endOfWeek();
        $fecha = date('Y-m-d');
        $fechaBanco = date('2024-10-21');

        $saldoDiario = BancoSaldoDiario::where([["fecha","=",$startOfWeek],["id_banco",$banco->id]]);
        $saldoInicial = ($saldoDiario->exists()) ? $saldoDiario->first()->saldo_inicial : 0;

        //Movimientos
        $movimientosBancarios = MovimientoBancario::where('id_banco', $banco->id)
        ->where('is_active', 1)
        ->whereBetween('fecha_movimiento', [$startOfWeek, $endOfWeek])
        ->get();

        $cotizaciones = Cotizaciones::where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $proveedores = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                    ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                    ->whereBetween('cotizaciones.fecha_pago_proveedor', [$startOfWeek, $endOfWeek])
                    ->where('asignaciones.id_camion', '=', null)
                    ->where(function ($query) use ($id) {
                        $query->where('cotizaciones.id_prove_banco1', '=', $id)
                              ->orWhere('cotizaciones.id_prove_banco2', '=', $id);
                    })
                    ->select('cotizaciones.*')
                    ->get();

        $banco_dinero_entrada = BancoDinero::where('tipo', '=', 'Entrada')
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida = BancoDinero::where('tipo', '=', 'Salida')
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida_ope = BancoDineroOpe::whereIn('tipo', ['Entrada','Salida'])
        ->where('contenedores', '=', null)
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();
        $empresaPropiaUsuario = Empresas::where('id', auth()->user()->id_empresa)
            ->value('empresa_propia') == 1;
        $banco_dinero_salida_ope_varios = BancoDineroOpe::whereIn('tipo', ['Entrada','Salida'])
        ->where('id_cotizacion', '=', null)
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $gastos_generales = GastosGenerales::where('id_banco1', '=', $id)
        ->where('is_active', 1)
        ->whereBetween('fecha', [$startOfWeek, $endOfWeek])
        ->get();

        // Calculo del saldo final
        $totalSalidas = $banco_dinero_salida->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $proveedores->where('fecha_pago_proveedor', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_prove_banco1 == $id ? $item->prove_monto1 : ($item->id_prove_banco2 == $id ? $item->prove_monto2 : 0);
        })->sum()
        + $banco_dinero_salida_ope->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $banco_dinero_salida_ope_varios->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $gastos_generales->where('fecha', '>=', $fechaBanco)
        ->sum('monto1')
        + $movimientosBancarios->where('tipo_movimiento', 0)->sum('monto');

        $totalEntradas = $cotizaciones->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $banco_dinero_entrada->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $movimientosBancarios->where('tipo_movimiento', 1)->sum('monto');


        $saldoFinal = $saldoInicial + $totalEntradas - $totalSalidas;

        $combined = collect()
        ->merge($cotizaciones)
        ->merge($banco_dinero_entrada)
        ->merge($proveedores)
        ->merge($banco_dinero_salida_ope)
        ->merge($banco_dinero_salida_ope_varios)
        ->merge($banco_dinero_salida)
        ->merge($gastos_generales)
        ->merge($movimientosBancarios)
        ->sortBy(function ($item) {
            if (isset($item->fecha_pago)) {
                return Carbon::parse($item->fecha_pago);
            } elseif (isset($item->fecha_pago_proveedor)) {
                return Carbon::parse($item->fecha_pago_proveedor);
            } elseif (isset($item->fecha)) {
                return Carbon::parse($item->fecha);
            }
            return null;
        });


        return view('bancos.show', compact('combined', 'empresaPropiaUsuario', 'startOfWeek', 'fecha', 'banco', 'cotizaciones', 'proveedores', 'banco_dinero_entrada', 'banco_dinero_salida', 'banco_dinero_salida_ope', 'banco_dinero_salida_ope_varios', 'gastos_generales', 'saldoInicial', 'saldoFinal'));
    }

    public function advance_bancos(Request $request, $id)
    {
        $banco = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->where('id', '=', $id)->first();

        $startOfWeek = $request->get('fecha_de');
        $endOfWeek = $request->get('fecha_hasta');
        $fecha = date('Y-m-d');
        $fechaBanco = date('2024-10-21');

        $saldoDiario = BancoSaldoDiario::where([["fecha","=",$startOfWeek],["id_banco",$banco->id]]);
        $saldoInicial = ($saldoDiario->exists()) ? $saldoDiario->first()->saldo_inicial : 0;

        //Movimientos
        $movimientosBancarios = MovimientoBancario::where('id_banco', $banco->id)
        ->where('is_active', 1)
        ->whereBetween('fecha_movimiento', [$startOfWeek, $endOfWeek])
        ->get();

        $cotizaciones = Cotizaciones::where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $proveedores = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                    ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                    ->whereBetween('cotizaciones.fecha_pago_proveedor', [$startOfWeek, $endOfWeek])
                    ->where('asignaciones.id_camion', '=', null)
                    ->where(function ($query) use ($id) {
                        $query->where('cotizaciones.id_prove_banco1', '=', $id)
                              ->orWhere('cotizaciones.id_prove_banco2', '=', $id);
                    })
                    ->select('cotizaciones.*')
                    ->get();

        $banco_dinero_entrada = BancoDinero::where('tipo', '=', 'Entrada')
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida = BancoDinero::where('tipo', '=', 'Salida')
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida_ope = BancoDineroOpe::where('tipo', '=', 'Salida')
        ->where('contenedores', '=', null)
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida_ope_varios = BancoDineroOpe::where('tipo', '=', 'Salida')
        ->where('id_cotizacion', '=', null)
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $gastos_generales = GastosGenerales::where('id_banco1', '=', $id)
        ->whereBetween('fecha', [$startOfWeek, $endOfWeek])
        ->get();

        // Calculo del saldo final
        $totalSalidas = $banco_dinero_salida->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $proveedores->where('fecha_pago_proveedor', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_prove_banco1 == $id ? $item->prove_monto1 : ($item->id_prove_banco2 == $id ? $item->prove_monto2 : 0);
        })->sum()
        + $banco_dinero_salida_ope->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $banco_dinero_salida_ope_varios->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $gastos_generales->where('fecha', '>=', $fechaBanco)
        ->sum('monto1') + $movimientosBancarios->where('tipo_movimiento', 1)->sum('monto');
        ;

        $totalEntradas = $cotizaciones->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum()
        + $banco_dinero_entrada->where('fecha_pago', '>=', $fechaBanco)
        ->map(function ($item) use ($id) {
            return $item->id_banco1 == $id ? $item->monto1 : ($item->id_banco2 == $id ? $item->monto2 : 0);
        })->sum() + $movimientosBancarios->where('tipo_movimiento', 1)->sum('monto');
        ;

        $saldoFinal = $saldoInicial + $totalEntradas - $totalSalidas;
        $empresaPropiaUsuario = Empresas::where('id', auth()->user()->id_empresa)
            ->value('empresa_propia') == 1;
        $combined = collect()
        ->merge($cotizaciones)
        ->merge($banco_dinero_entrada)
        ->merge($proveedores)
        ->merge($banco_dinero_salida_ope)
        ->merge($banco_dinero_salida_ope_varios)
        ->merge($banco_dinero_salida)
        ->merge($gastos_generales)
        ->merge($movimientosBancarios)
        ->sortBy(function ($item) {
            if (isset($item->fecha_pago)) {
                return Carbon::parse($item->fecha_pago);
            } elseif (isset($item->fecha_pago_proveedor)) {
                return Carbon::parse($item->fecha_pago_proveedor);
            } elseif (isset($item->fecha)) {
                return Carbon::parse($item->fecha);
            }
            return null;
        });

        return view('bancos.show', compact('combined', 'empresaPropiaUsuario', 'startOfWeek', 'endOfWeek', 'fecha', 'banco', 'cotizaciones', 'proveedores', 'banco_dinero_entrada', 'banco_dinero_salida', 'banco_dinero_salida_ope', 'banco_dinero_salida_ope_varios', 'gastos_generales', 'saldoInicial', 'saldoFinal'));
    }

    public function update(Request $request, Bancos $id)
    {
        $id->update($request->all());

        return redirect()->back()->with('success', 'Banco editado exitosamente');
    }

    public function pdf(Request $request, $id)
    {
        $banco = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->where('id', '=', $id)->first();

        if ($request->get('fecha_de') == null) {
            $startOfWeek = Carbon::now()->startOfWeek();
            $endOfWeek = Carbon::now()->endOfWeek();
        } else {
            $startOfWeek = $request->get('fecha_de');
            $endOfWeek = $request->get('fecha_hasta');
        }

        $fecha = date('Y-m-d');

        $cotizaciones = Cotizaciones::where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $proveedores = Cotizaciones::join('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
                    ->join('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
                    ->whereBetween('cotizaciones.fecha_pago_proveedor', [$startOfWeek, $endOfWeek])
                    ->where('asignaciones.id_camion', '=', null)
                    ->where(function ($query) use ($id) {
                        $query->where('cotizaciones.id_prove_banco1', '=', $id)
                              ->orWhere('cotizaciones.id_prove_banco2', '=', $id);
                    })
                    ->select('cotizaciones.*')
                    ->get();

        $banco_dinero_entrada = BancoDinero::where('tipo', '=', 'Entrada')
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida = BancoDinero::where('tipo', '=', 'Salida')
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida_ope = BancoDineroOpe::where('tipo', '=', 'Salida')
        ->where('contenedores', '=', null)
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $banco_dinero_salida_ope_varios = BancoDineroOpe::where('tipo', '=', 'Salida')
        ->where('id_cotizacion', '=', null)
        ->where(function ($query) use ($id) {
            $query->where('id_banco1', '=', $id)
                  ->orWhere('id_banco2', '=', $id);
        })
        ->whereBetween('fecha_pago', [$startOfWeek, $endOfWeek])
        ->get();

        $gastos_generales = GastosGenerales::where('id_banco1', '=', $id)
        ->whereBetween('fecha', [$startOfWeek, $endOfWeek])
        ->get();

        $combined = collect()
        ->merge($cotizaciones)
        ->merge($banco_dinero_entrada)
        ->merge($proveedores)
        ->merge($banco_dinero_salida_ope)
        ->merge($banco_dinero_salida_ope_varios)
        ->merge($banco_dinero_salida)
        ->merge($gastos_generales)
        ->sortBy(function ($item) {
            if (isset($item->fecha_pago)) {
                return Carbon::parse($item->fecha_pago);
            } elseif (isset($item->fecha_pago_proveedor)) {
                return Carbon::parse($item->fecha_pago_proveedor);
            } elseif (isset($item->fecha)) {
                return Carbon::parse($item->fecha);
            }
            return null;
        });

        $penultimaTotal = 0;
        $ultimaTotal = 0;

        foreach ($combined as $item) {

            if (isset($item->fecha_pago)) {
                $amount = isset($item->id_banco1) && $item->id_banco1 == $banco->id
                    ? $item->monto1
                    : $item->monto2;

                if (!isset($item->id_operador)) {
                    $penultimaTotal += $amount;
                } else {
                    $ultimaTotal += $amount;
                }
            } elseif (isset($item->fecha_pago_proveedor)) {
                $amount = isset($item->id_prove_banco1) && $item->id_prove_banco1 == $banco->id
                    ? $item->prove_monto1
                    : $item->prove_monto2;
                $ultimaTotal += $amount;
            } elseif (isset($item->fecha)) {
                $amount = $item->monto1;
                $ultimaTotal += $amount;
            }
        }

        $diferencia = $penultimaTotal - $ultimaTotal;

        $pdf = PDF::loadView('bancos.pdf_banco', compact('combined', 'startOfWeek', 'fecha', 'banco', 'ultimaTotal', 'penultimaTotal', 'diferencia'));
        //   return $pdf->stream();
        return $pdf->download('Reporte Banco.pdf');
    }

    public static function saldos_diarios()
    {
        $saldoDiarios = BancoSaldoDiario::where('fecha', Carbon::now()->format('Y-m-d'));
        if (!$saldoDiarios->exists()) {
            $bancos = Bancos::selectRaw('id as id_banco,now() as fecha, saldo as saldo_inicial, saldo as saldo_final')->get()->toArray();
            BancoSaldoDiario::insert($bancos);
        }
    }

    public function registrar_movimiento(Request $r)
    {
        try {
            DB::beginTransaction();
            $movimiento = ['id_banco' => $r->bank,
            'tipo_movimiento' => $r->tipoTransaccion,
            'descripcion_movimiento' => $r->txtDescripcion,
            'monto' => $r->txtMonto,
            'fecha_movimiento' => date('Y-m-d'),
            'is_active' => true];
            MovimientoBancario::insert($movimiento);

            $operacion = (intval($r->tipoTransaccion) == 1) ? '+' : '-';

            Bancos::where('id', $r->get('bank'))
                ->update([
                    "saldo" => DB::raw("saldo $operacion ".$r->txtMonto)
                ]);

            DB::commit();
            return response()->json(["Titulo" => "Movimiento Exitoso", "Mensaje" => "Movimiento aplicado correctamente", "TMensaje" => "success"]);
        } catch (\Throwable $t) {
            DB::rollback();
            return response()->json(["Titulo" => "Error", "Mensaje" => "Error: ".$t->getMessage(), "TMensaje" => "error"]);

        }
    }

    public function cambiarCuentaGlobal(Request $request, $id)
    {
        $bancoSeleccionado = Bancos::findOrFail($id);

        if ($bancoSeleccionado->cuenta_global) {
            // Si ya era cuenta global, simplemente lo apagamos
            $bancoSeleccionado->cuenta_global = false;
            $bancoSeleccionado->save();

            return response()->json(['success' => true, 'message' => 'Cuenta global desactivada.']);
        } else {
            // Verificar si ya existe un banco con cuenta_global activa
            $cuentaGlobalExistente = Bancos::where('cuenta_global', true)->where('id_empresa', auth()->user()->id_empresa)->first();

            if ($cuentaGlobalExistente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe una cuenta global activa. Debes desactivarla primero.'
                ]);
            }

            // Activamos este banco como cuenta global
            $bancoSeleccionado->cuenta_global = true;
            $bancoSeleccionado->save();

            return response()->json(['success' => true, 'message' => 'Cuenta global activada correctamente.']);
        }
    }


    public function cambiarBanco1(Request $request, $id)
    {
        $banco = Bancos::findOrFail($id);

        // Seguridad: solo bancos de la empresa del usuario
        if ((int)$banco->id_empresa !== (int)auth()->user()->id_empresa) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permisos sobre este banco.'
            ], 403);
        }

        // Verificar si la empresa del usuario es propia
        $empresaPropia = (bool) \App\Models\Empresas::where('id', auth()->user()->id_empresa)
                            ->value('empresa_propia');

        if (!$empresaPropia) {
            return response()->json([
                'success' => false,
                'message' => 'La empresa no está marcada como propia.'
            ], 422);
        }

        // Valor enviado (true o false)
        $activar = (bool) $request->input('value', false);

        if ($activar) {
            // Desactivar todos los demás bancos de esta empresa
            Bancos::where('id_empresa', $banco->id_empresa)
                  ->where('id', '!=', $banco->id)
                  ->update(['banco_1' => false]);

            // Activar este banco
            $banco->banco_1 = true;
            $banco->save();

            return response()->json([
                'success' => true,
                'message' => 'Banco activado como Banco 1.'
            ]);
        } else {
            // Solo desactivar este banco
            $banco->banco_1 = false;
            $banco->save();

            return response()->json([
                'success' => true,
                'message' => 'Banco desactivado como Banco 1.'
            ]);
        }
    }


}
