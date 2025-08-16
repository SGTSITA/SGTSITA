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
        ]);

        $interval = RastreoIntervals::where('task_name', 'rastreo_gps_interval')->first();
        $interval->interval = $request->interval;
        $interval->save();

        return redirect()->back()->with('success', 'Intervalo actualizado');
    }
}
