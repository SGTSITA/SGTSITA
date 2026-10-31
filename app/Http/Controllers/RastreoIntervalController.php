<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\RastreoIntervals;

class RastreoIntervalController extends Controller
{
    public function index()
    {
        $intervals = RastreoIntervals::where('task_name', 'rastreo_gps_interval')->first();

        return view('scheduler.index', compact('intervals'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'interval' => 'required|string',
            'alerta_distancia' => 'nullable|boolean',
            'metros_alerta' => 'nullable|integer|min:1',
        ]);

        $interval = RastreoIntervals::where('task_name', 'rastreo_gps_interval')->first();
        $interval->interval = $request->interval;
        $interval->alerta_distancia = $request->has('alerta_distancia') ? (bool)$request->alerta_distancia : false;
        $interval->metros_alerta = $request->input('metros_alerta', 50);
        $interval->save();

        return redirect()->back()->with('success', 'Configuraciones actualizadas correctamente');
    }
}
