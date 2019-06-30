<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class RoleAssign extends Controller
{
    public function index()
    {
    	$user = User::with('roles')->latest()->paginate(10);
    	return view('backend.assignrole.index', compact('users'));
    }

    public function create()
    {
    	$roles = Role::latest()->get();
    	return view('backend.assignrole.create', compact('role'));
    }


    public function store(Request $request)
    {
    	$request->validate([
    		'name' => 'required|string|max:255',
    		'email' => 'required|string|email|max:255|unique:users',
    		'password' => 'required|string|min:8'

    	]);

    	$user = User::create([
    		'name'  => $request->name,
    		'email' => $request->email,
    		'password' => Hash::make($request->password)

    	]);

    	$user->assignRole($request->role);

    	return redirect()->route('assignrole.index');
    }


    public function edit($id)
    {
    	$user = User::with('roles')->findOrFail($id);
    	$roles = Role::latest()->get();

    	return view('backend.assignrole.edit', compact('user', 'roles'));
    }


    public function update(Request $request, $id)
    {
    	$request->validate([
    		'name' => 'required|string|max:255',
    		'email' => 'required|string|email|max:255|unique:users,email,'.$id
    	]);


    	$user = User::findOrFail($id);

    	$user->update([
    		'name' => $request->name,
    		'email' => $request->email
    	]);

    	$user->syncRoles($request->selectedrole);
    	return redirect()->route('assignrole.index');
    }

    public function destroy($id)
    {
    	$user = User::findOrFail($id);

    	return back();
    }
}
