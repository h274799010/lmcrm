<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminController;
use App\Models\Role;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Validator;

class SettingsController extends AdminController
{
    public function __construct()
    {
        view()->share('type', 'settings');
    }

    public function roles()
    {
        $roles = Role::whereIn('slug', ['dealmaker', 'leadbayer'])->get();

        return view('admin.settings.roles')->with([
            'roles' => $roles
        ]);
    }

    public function roleUpdate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|min:5',
            'description' => 'required|min:5'
        ]);

        $role_id = (int)$request->input('role');

        $validator->after(function($validator) use ($role_id)
        {
            if(!$role_id) {
                $validator->errors()->add('role', trans('admin/settings.roleUndefined'));
            }
        });

        if ($validator->fails())
        {
            return $validator->messages();
        }

        $locale = App::getLocale();

        $role = Role::find($request->input('role'));
        $role->translateOrNew($locale)->name = $request->input('name');
        $role->translateOrNew($locale)->description = $request->input('description');
        $role->save();

        if($request->ajax()) {
            return trans('admin/settings.roleUpdated');
        } else {
            return redirect()->route('admin.settings.roles');
        }
    }
}
