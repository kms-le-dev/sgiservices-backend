<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServiceController extends Controller
{
	public function index(Request $request)
	{
		$query = Service::query();
		if ($request->has('category')) {
			$query->where('category', $request->query('category'));
		}
		return response()->json($query->latest()->get());
	}

	public function store(Request $request)
	{
		$user = $request->user();
		if (!$user || !$user->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		// Accept either a file (multipart FormData) or a string URL/path for `image`.
		// We don't force a type here because uploaded files are not strings.
		$data = $request->validate([
			'title' => 'required|string|max:255',
			'description' => 'nullable|string',
			'category' => 'required|string',
			'price' => 'nullable|numeric',
			'image' => 'nullable',
		]);

		// handle uploaded image only if a file was actually sent
		if ($request->hasFile('image')) {
			$file = $request->file('image');
			// basic Laravel validation for uploaded file
			$request->validate(['image' => 'file|image|max:5120']);
			if (!$file->isValid()) {
				return response()->json(['message' => 'Erreur lors de l\'upload du fichier.'], 422);
			}
			// use the uploaded tmp file path for image content check (more reliable)
			if (@getimagesize($file->getPathname()) === false) {
				return response()->json(['message' => 'The image field must be an image.'], 422);
			}
			// store on the public disk so files are placed in storage/app/public/uploads
			$path = $file->store('uploads', 'public');
			// store the relative path in DB; the model accessor will provide the absolute URL
			$data['image'] = $path;
		}
		// if client didn't send a file but provided an image URL, accept it
		elseif ($request->filled('image')) {
			$data['image'] = $request->input('image');
		}

		$service = Service::create(array_merge($data, ['user_id' => $user->id]));

		return response()->json($service, 201);
	}

	public function update(Request $request, $id)
	{
		$user = $request->user();
		if (!$user || !$user->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$service = Service::findOrFail($id);

		// Allow file upload or image path/url string when updating
		$update = $request->validate([
			'title' => 'required|string|max:255',
			'description' => 'nullable|string',
			'category' => 'required|string',
			'price' => 'nullable|numeric',
			'image' => 'nullable',
		]);

		if ($request->hasFile('image')) {
			$file = $request->file('image');
			$request->validate(['image' => 'file|image|max:5120']);
			if (!$file->isValid()) {
				return response()->json(['message' => 'Erreur lors de l\'upload du fichier.'], 422);
			}
			if (@getimagesize($file->getPathname()) === false) {
				return response()->json(['message' => 'The image field must be an image.'], 422);
			}
			// store on the public disk so files are placed in storage/app/public/uploads
			$path = $file->store('uploads', 'public');
			$update['image'] = $path;
		}
		// allow update with an image URL (e.g. selecting an existing media)
		elseif ($request->filled('image')) {
			$update['image'] = $request->input('image');
		}

		$service->update($update);

		return response()->json($service);
	}

	public function destroy(Request $request, $id)
	{
		$user = $request->user();
		if (!$user || !$user->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$service = Service::findOrFail($id);
		$service->delete();
		return response()->json(['message' => 'Deleted']);
	}
}

