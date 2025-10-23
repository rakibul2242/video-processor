<?php

namespace App\Http\Controllers;

use App\Jobs\VideoProcessing\SplitVideoIntoChunks;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChunkVideoController extends Controller
{
    public function showUploadForm()
    {
        $videoPaths = [];

        $directories = Storage::disk('public')->directories('chunks');

        foreach ($directories as $dir) {
            $hlsPath = $dir . '/hls';
            if (Storage::disk('public')->exists($hlsPath)) {
                $files = Storage::disk('public')->files($hlsPath);
                foreach ($files as $file) {
                    if (str_ends_with($file, '.m3u8')) {
                        $videoPaths[] = $file;
                    }
                }
            }
        }

        return view('video-upload', ['videos' => $videoPaths]);
    }


    public function chunkUpload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4|max:1024000',
        ]);

        $file = $request->file('video');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $generatedName = Str::slug($originalName) . '-' . time();
        $safeName = "{$generatedName}.$extension";
        $path = $file->storeAs("chunks/{$generatedName}", $safeName, 'public');

        SplitVideoIntoChunks::dispatch(storage_path("app/public/{$path}"));

        return response()->json([
            'success' => true,
            'message' => 'Video upload started',
            'path' => $path
        ]);
    }
}
