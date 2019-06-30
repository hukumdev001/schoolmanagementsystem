<?php

namespace App\Http\Controllers;
use App\User;
use App\Parents;
use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;

class ParentsController extends Controller


{

	public function index(){
		 $parents = Parents::with(['user', 'children'])->latest()->paginate(10);
    	return view('backend.parents.index', compact('parents'));

	}

	public function create()
	{
		return view('backend.parents.create');
	}


	public function store(Request $request)
	{
		$request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users',
			'password' => 'required|string|min:8',
			'gender' => 'required|string|max:255',
			'phone' => 'required|string|max:255',
			'current_address' => 'required|string|max:255',
			'permanent_address' => 'required|string|max:255'

		]);


		$user = User::create([
			'name' => $request->name,
			'email' => $request->email,
			'password' => Hash::make($request->password)


		]);

		if($request->hasFile('profile_picture')) {
			$profile = str_slug($user->name).'_'.$user->id.'.'.$request->profile_picture->getClientOriginalExtension();
			$request->profile_picture->move(public_path('images/profile'), $profile);
		} else {
			$profie = 'avatar.png';
		}

		$user->update([
			'profile_picture' => $profile
		]);
		
		$user->parent()->create([
		'gender'  => $request->gender,
		'phone'  => $request->phone,
		'current_address'  => $request->current_address,
		'permanent_address' => $request->permanent_address

		]);


		$user->assignRole('Parent');

		return redirect()->route('parents.index');

	}

	public function edit($id)
	{
		$parent = Parents::with('user')->findOrFail($id);

		return view('backend.parents.edit', compact('parent'));
	}


	public function update(Request $request, $id)
	{
		$parents = Parents::findOrFail($id);

		$request->validate([
			'name'  => 'required|string|max:255',
			'email' => 'required|string|email|max:255|unique:users,email,'.$parents->user_id,
			'gender' => 'required|string',
			'phone' => 'required|string|max:255',
			'current_address' => 'required|string|max:255',
			'permanent_address' => 'required|string|max:255'


		]);


		if($request->hasfile('profile_picture')) {
			$profile = str_slug($parents->user->name). '-' . $parents->user->id. '.' .$request->profile_picture->getClientOriginalExtension();
			$request->profile_picture->move(public_path('images/profile'), $profile);
		} else {
			$profile = $parents->user->profile_picture;
		}


		$parents->user()->update([
			'name' => $request->name,
			'email' => $request->email,
			'profile_picture' => $profile

		]);


		$parents->update([
			'gender' => $request->gender,
			'phone' => $request->phone,
			'current_address' => $request->current_address,
			'permanent_address' => $request->permanent_address

		]);


		return redirect()->route('parents.index');
	}


	public function destroy($id)
	{
		$parent = Parent::findOrFail(
			$id);
		$user->User::findOrFail($parent->user_id);

		$user->removeRole('Parent');


		if($user->delete()) {
			if($user->profile_picture != 'avatar.png') {
				$image_path = public_path().'/images/profile'.$user->profile_picture;
				if(is_file($image_path) && file_exits($image_path)) {
					unlink($image_path);
				}
			}
		}

		$parent->delete();

		return back();
	}
	   


}

