<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
	public function index(Request $request)
	{
		$user = $request->user();
		if (!$user || !$user->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		return response()->json(User::all());
	}

	/**
	 * Update a user (admin only)
	 */
	public function update(Request $request, $id)
	{
		$actor = $request->user();
		if (!$actor || !$actor->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$user = User::findOrFail($id);

		$data = $request->only(['name', 'email', 'role', 'phone']);
		foreach ($data as $k => $v) {
			if (!is_null($v)) $user->{$k} = $v;
		}

		if ($request->filled('password')) {
			$user->password = Hash::make($request->input('password'));
		}

		$user->save();

		return response()->json($user);
	}

	/**
	 * Delete a user (admin only)
	 */
	public function destroy(Request $request, $id)
	{
		$actor = $request->user();
		if (!$actor || !$actor->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$user = User::findOrFail($id);
		$user->delete();

		return response()->json(['message' => 'User deleted']);
	}
}

