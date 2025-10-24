<?php

namespace App\Livewire;

use App\Jobs\VideoProcessing\SplitVideoIntoChunks;
use App\Models\Video;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class VideoProcess extends Component
{
    use WithFileUploads;

    public $videos, $video;

    protected $rules = [
        'video' => 'required|file|mimes:mp4|max:1024000',
    ];

    public function mount()
    {
        $this->videos = Video::latest()->get();
    }

    public function uploadVideo()
    {
        $this->validate();

        $originalName = pathinfo($this->video->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $this->video->getClientOriginalExtension();
        $generatedName = Str::slug($originalName) . '-' . time();
        $safeName = "{$generatedName}.{$extension}";

        $this->video->storeAs("chunks/{$generatedName}", $safeName, 'public');

        SplitVideoIntoChunks::dispatch($generatedName, $safeName);

        $this->reset('video');
        session()->flash('success', 'Video uploaded successfully.');
    }

    public function render()
    {
        return view('livewire.video-process');
    }
}
