<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class VideoProcess extends Component
{
    public function render()
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

        return view('livewire.video-process', ['videos' => $videoPaths]);
    }
}
