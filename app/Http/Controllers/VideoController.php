<?php

namespace App\Http\Controllers;

use App\Jobs\ConvertToHls;
use Illuminate\Http\Request;

class VideoController extends Controller
{
    public function showUploadForm()
    {
        $videos = \Storage::disk('public')->files('videos'); // 'videos' folder in storage/app/public
        return view('upload-video', compact('videos'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|file|mimes:mp4,mov,ogg,qt,mkv,avi,wmv|max:1024000',
        ]);

        $file = $request->file('video');
        $fileName = $file->getClientOriginalName();
        $path = $file->storeAs('videos', $fileName, 'public');

        $fullPath = storage_path('app/public/' . $path);

        ConvertToHls::dispatch($fullPath);

        return back()->with('success', $path);
    }
}
