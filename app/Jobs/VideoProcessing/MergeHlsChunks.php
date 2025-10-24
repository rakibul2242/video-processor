<?php

namespace App\Jobs\VideoProcessing;

use App\Models\Video;
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
        try {
            $dir = storage_path("app/public/chunks/{$this->filename}/hls");
            $files = glob("{$dir}/chunk_*.m3u8");

            if (empty($files)) {
                \Log::warning("No HLS chunks found to merge", ['directory' => $dir]);
                return;
            }

            // Sort files to maintain correct order
            natsort($files);
            $files = array_values($files);

            $masterPlaylist = "#EXTM3U\n#EXT-X-VERSION:3\n";

            foreach ($files as $file) {
                $chunkName = basename($file);
                $masterPlaylist .= "#EXTINF:600,\n{$chunkName}\n";
            }

            $masterPlaylist .= "#EXT-X-ENDLIST\n";

            file_put_contents("{$dir}/index.m3u8", $masterPlaylist);

            Video::create([
                'hls_path' => "{$dir}/index.m3u8"
            ]);

            \Log::info("Master playlist created", [
                'filename' => $this->filename,
                'chunks_count' => count($files)
            ]);

        } catch (\Exception $e) {
            \Log::error("MergeHlsChunks failed", [
                'error' => $e->getMessage(),
                'filename' => $this->filename
            ]);
            throw $e;
        }
    }
}
