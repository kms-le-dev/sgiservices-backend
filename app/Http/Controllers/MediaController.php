<?php

namespace App\Http\Controllers;

use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
	public function index()
	{
		$items = Media::latest()->get();
		return response()->json($items);
	}

	public function getByCategory($category)
	{
		$items = Media::where('category', $category)->latest()->get();
		return response()->json($items);
	}

	public function store(Request $request)
	{
		$user = $request->user();
		if (!$user || !$user->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}


		$data = $request->validate([
			'title' => 'nullable|string|max:255',
			'url' => 'nullable|string',
			'category' => 'nullable|string',
			'description' => 'nullable|string',
		]);

		// ensure category key exists to avoid DB errors if column is non-nullable
		if (!array_key_exists('category', $data) || $data['category'] === null) {
			$data['category'] = '';
		}

		// if client uploaded a file, store it on the 'public' disk and verify it's an image
		if ($request->hasFile('image')) {
			$request->validate(['image' => 'file|image|max:5120']);
			$path = $request->file('image')->store('uploads', 'public');
			// vérifier le contenu sur le chemin réel du disque public
			$realPath = Storage::disk('public')->path($path);
			if (@getimagesize($realPath) === false) {
				Storage::disk('public')->delete($path);
				return response()->json(['message' => 'The image field must be an image.'], 422);
			}
			// stocker le chemin relatif en base et utiliser l'accessor pour retourner l'URL
			$data['url'] = $path;
		}

		$media = Media::create(array_merge($data, ['user_id' => $user->id]));

		return response()->json($media, 201);
	}

	public function update(Request $request, $id)
	{
		$user = $request->user();
		if (!$user || !$user->isAdmin()) {
			return response()->json(['message' => 'Unauthorized'], 403);
		}

		$media = Media::findOrFail($id);

		$data = $request->validate([
			'title' => 'nullable|string|max:255',
			'url' => 'nullable|string',
			'category' => 'nullable|string',
			'description' => 'nullable|string',
			'image' => 'nullable|image|max:5120',
		]);

		if ($request->hasFile('image')) {
			$path = $request->file('image')->store('uploads', 'public');
			$data['url'] = $path;
		}

		$media->update($data);
		return response()->json($media);
	}
}

