<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Support\ImageUploader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::where('status', '!=', 4)
            ->when($request->q, fn ($q, $t) => $q->where('file_name', 'like', "%{$t}%"))
            ->latest()
            ->paginate(36)->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    /**
     * Upload from the Media page (many files) or from the TinyMCE editor ("file").
     * Returns JSON { location } for the editor.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required_without:files|file|mimes:jpeg,jpg,png,gif,webp,svg,pdf|max:10240',
            'files' => 'required_without:file|array',
            'files.*' => 'file|mimes:jpeg,jpg,png,gif,webp,svg,pdf|max:10240',
        ]);

        $files = $request->hasFile('files') ? $request->file('files') : [$request->file('file')];
        $saved = [];

        foreach ($files as $file) {
            $isImage = str_starts_with((string) $file->getMimeType(), 'image/');
            $path = $isImage ? ImageUploader::store($file, 'media/' . now()->format('Y/m')) : ImageUploader::storeFile($file, 'media/' . now()->format('Y/m'));
            $disk = Storage::disk('public');
            $size = null;
            $dims = [null, null];

            if ($isImage && ($info = @getimagesize($disk->path($path)))) {
                $dims = [$info[0], $info[1]];
            }

            $saved[] = Media::create([
                'user_id' => auth()->id(),
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $isImage ? 'image' : 'document',
                'mime_type' => $disk->mimeType($path) ?: $file->getMimeType(),
                'file_size' => $disk->size($path),
                'disk' => 'public',
                'width' => $dims[0],
                'height' => $dims[1],
                'status' => 1,
            ]);
        }

        if ($request->expectsJson() || $request->hasFile('file')) {
            return response()->json(['location' => $saved[0]->url, 'id' => $saved[0]->id]);
        }

        return back()->with('success', count($saved) . ' ' . \Illuminate\Support\Str::plural('file', count($saved)) . ' uploaded.');
    }

    public function update(Request $request, Media $medium)
    {
        $medium->update($request->validate(['alt_text' => 'nullable|string|max:191', 'caption' => 'nullable|string|max:500']));

        return back()->with('success', 'Details saved.');
    }

    public function destroy(Media $medium)
    {
        ImageUploader::delete($medium->file_path);
        Storage::disk('public')->delete($medium->file_path);
        $medium->update(['status' => 4]);

        return back()->with('success', 'File deleted.');
    }
}
