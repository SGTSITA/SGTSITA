<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;
use DB;

class PermisosController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            ]);

            $slug = Str::slug($request->name, '-');

            $Permission = new Permission;
            $Permission->name = $slug;

            $Permission->save();
            return redirect()->route('roles.create')
            ->with('success','Permission has been created successfully.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
        'name' => 'required',
        ]);

        $Permission = Permission::find($id);
        $Permission->name = $request->name;
        $Permission->save();
        return redirect()->route('roles.create')
        ->with('edit','Permission Has Been updated successfully');
    }

    public function destroy($id)
    {
        DB::table("permissions")->where('id',$id)->delete();
        return redirect()->route('roles.create')
        ->with('delete','Permission has been deleted successfully');
    }

}
