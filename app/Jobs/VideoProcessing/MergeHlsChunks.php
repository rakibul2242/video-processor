<?php

namespace App\Jobs\VideoProcessing;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class MergeHlsChunks implements ShouldQueue
{
    use Queueable, SerializesModels, Dispatchable, InteractsWithQueue;
    public $filename;
    /**
     * Create a new job instance.
     */
    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $dir = storage_path("app/public/chunks/hls/{$this->filename}");
        $files = glob("{$dir}/chunk_*.m3u8");

        if (empty($files))
            return;

        // Sort files to maintain correct order
        sort($files);

        $masterPlaylist = "#EXTM3U\n#EXT-X-VERSION:3\n";

        foreach ($files as $file) {
            $chunkName = basename($file);
            $masterPlaylist .= "#EXTINF:600,\n{$chunkName}\n";
        }

        file_put_contents("{$dir}/index.m3u8", $masterPlaylist);
    }
}
