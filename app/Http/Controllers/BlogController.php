<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BlogController extends Controller
{
    public function index()
    {
        return response()->json(Blog::latest()->get());
    }

    public function store(Request $request)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }


        // debug: log incoming request summary to help diagnose upload issues
        Log::info('BlogController@store request', [
            'hasFile_image' => $request->hasFile('image'),
            'file' => $request->file('image') ? $request->file('image')->getClientOriginalName() : null,
            'file_mime' => $request->file('image') ? $request->file('image')->getClientMimeType() : null,
            'file_size' => $request->file('image') ? $request->file('image')->getSize() : null,
            '_FILES' => isset($_FILES) ? $_FILES : null,
        ]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'published_at' => 'nullable|date',
            'image' => 'nullable',
        ]);

        // Accept either an uploaded file (preferred) or a string path/URL from the client.
        if ($request->hasFile('image')) {
            $request->validate(['image' => 'file|image|max:5120']);
            // store on the public disk like MediaController so paths and accessors are consistent
            $path = $request->file('image')->store('uploads', 'public');
            Log::info('BlogController@store stored file', ['path' => $path, 'storage_full' => Storage::disk('public')->path($path)]);
            $realPath = Storage::disk('public')->path($path);
            if (@getimagesize($realPath) === false) {
                Storage::disk('public')->delete($path);
                return response()->json(['message' => 'The image field must be an image.'], 422);
            }
            // store relative path in DB; accessor will expose full URL
            $data['featured_image'] = $path;
        } elseif ($request->filled('image')) {
            // client may pass an existing media path or absolute URL
            $img = $request->input('image');
            $data['featured_image'] = $img;
        }

        $data['slug'] = Str::slug($data['title']).'-'.Str::random(6);
        $data['user_id'] = $user->id;

        // map frontend `description` to `body` if provided
        if ($request->filled('description') && empty($data['body'])) {
            $data['body'] = $request->input('description');
        }

        $blog = Blog::create($data);

        return response()->json($blog, 201);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $blog = Blog::findOrFail($id);

        $update = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'published_at' => 'nullable|date',
        ]);

        if ($request->hasFile('image')) {
            $request->validate(['image' => 'file|image|max:5120']);
            $path = $request->file('image')->store('uploads', 'public');
            $realPath = Storage::disk('public')->path($path);
            if (@getimagesize($realPath) === false) {
                Storage::disk('public')->delete($path);
                return response()->json(['message' => 'The image field must be an image.'], 422);
            }
            $update['featured_image'] = $path;
        } elseif ($request->filled('image')) {
            $update['featured_image'] = $request->input('image');
        }

        $blog->update($update);
        return response()->json($blog);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $blog = Blog::findOrFail($id);
        $blog->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
