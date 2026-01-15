<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
	public function register(Request $request)
	{
		$data = $request->validate([
			'name' => 'required|string|max:255',
			'email' => 'required|email|unique:users,email',
			'phone' => 'required|string|max:30',
			'password' => 'required|string|min:6|confirmed',
		]);

		$user = User::create([
			'name' => $data['name'],
			'email' => $data['email'],
			'phone' => $data['phone'],
			'password' => Hash::make($data['password']),
			'role' => 'user',
		]);

		$token = $user->createToken('api-token')->plainTextToken;

		return response()->json(["user" => $user, "token" => $token], 201);
	}

	public function login(Request $request)
	{
		$credentials = $request->validate([
			'email' => 'required|email',
			'password' => 'required|string',
		]);

		if (!Auth::attempt($credentials)) {
			return response()->json(['message' => 'Identifiants invalides'], 401);
		}

		$user = Auth::user();
		$token = $user->createToken('api-token')->plainTextToken;

		return response()->json(["user" => $user, "token" => $token]);
	}

	public function logout(Request $request)
	{
		$user = $request->user();
		// Revoke current token
		if ($user && $request->bearerToken()) {
			$user->currentAccessToken()->delete();
		}
		return response()->json(['message' => 'Logged out']);
	}

	/**
	 * Reset password given identifying info (email required). This endpoint is
	 * intentionally simple: it finds the user by email (and phone if provided)
	 * and updates the password. In a production app you should use tokenized
	 * reset flows (email verification).
	 */
	public function resetPassword(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'email' => 'required|email',
			'phone' => 'sometimes|string',
			'password' => 'required|string|min:6|confirmed',
		]);

		if ($validator->fails()) {
			return response()->json(['message' => 'Validation error', 'errors' => $validator->errors()], 422);
		}

		$email = $request->input('email');
		$phone = $request->input('phone');

		$userQuery = User::where('email', $email);
		if ($phone) {
			$userQuery->where('phone', $phone);
		}

		$user = $userQuery->first();
		if (!$user) {
			return response()->json(['message' => 'Utilisateur introuvable avec ces informations'], 404);
		}

		$user->password = Hash::make($request->input('password'));
		$user->save();

		return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
	}
}
