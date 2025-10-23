<?php

namespace App\Http\Controllers;

use App\Jobs\ConvertToHls;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function showUploadForm()
    {
        $allVideos = \Storage::disk('public')->files('videos');
        $videos = [];

        foreach ($allVideos as $video) {
            $filename = pathinfo($video, PATHINFO_FILENAME);
            $hlsPath = "videos/hls/{$filename}/playlist.m3u8";

            if (\Storage::disk('public')->exists($hlsPath)) {
                $videos[] = $hlsPath;
            }
        }

        return view('dashboard', compact('videos'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4|max:102400',
        ]);

        $file = $request->file('video');
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $safeName = Str::slug($originalName) . '-' . time() . '.' . $extension;
        $path = $file->storeAs('videos', $safeName, 'public');

        $fullPath = storage_path('app/public/' . $path);

        ConvertToHls::dispatch($fullPath);

        return back()->with('success', $path);
    }
}
