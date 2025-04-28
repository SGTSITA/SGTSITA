<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CuentaGlobal;

class CuentaGlobalController extends Controller
{
    public function show()
    {
        return response()->json(CuentaGlobal::first());
    }

    public function update(Request $request)
    {
        $cuenta = CuentaGlobal::first() ?? new CuentaGlobal();
        $cuenta->fill($request->only(['nombre_beneficiario', 'banco', 'cuenta', 'clabe']));
        $cuenta->save();

        return response()->json(['success' => true]);
    }
}
