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

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    public function handle(): void
    {
        try {
            $dir = storage_path("app/public/chunks/{$this->filename}/hls");

            $files = glob("{$dir}/chunk_???_0_1000.m3u8");

            \Log::info("Found chunk files for merging:", [
                'folder' => $this->filename,
                'files' => $files
            ]);

            if (empty($files)) {
                \Log::warning("No HLS chunks found to merge", ['directory' => $dir]);
                return;
            }

            natsort($files);
            $files = array_values($files);

            $finalMediaPlaylist = "#EXTM3U\n#EXT-X-VERSION:3\n#EXT-X-TARGETDURATION:11\n#EXT-X-PLAYLIST-TYPE:VOD\n";

            $isFirstChunk = true;

            foreach ($files as $file) {
                if (file_exists($file)) {
                    $chunkContent = file_get_contents($file);

                    $lines = explode("\n", $chunkContent);
                    $segmentLines = [];

                    foreach ($lines as $line) {
                        if (
                            str_starts_with($line, '#EXTM3U') ||
                            str_starts_with($line, '#EXT-X-VERSION') ||
                            str_starts_with($line, '#EXT-X-TARGETDURATION') ||
                            str_starts_with($line, '#EXT-X-MEDIA-SEQUENCE') ||
                            str_starts_with($line, '#EXT-X-ENDLIST')
                        ) {
                            continue;
                        }

                        if ($isFirstChunk === false && str_starts_with($line, '#EXTINF')) {
                            $finalMediaPlaylist .= "#EXT-X-DISCONTINUITY\n";
                            $isFirstChunk = true;
                        }

                        if (!empty(trim($line))) {
                            $finalMediaPlaylist .= $line . "\n";
                        }
                    }

                    $isFirstChunk = false;

                }
            }

            $finalMediaPlaylist .= "#EXT-X-ENDLIST\n";

            $masterPlaylistPath = "chunks/{$this->filename}/hls/master.m3u8";
            file_put_contents("{$dir}/master.m3u8", $finalMediaPlaylist);

            Video::create([
                'hls_path' => $masterPlaylistPath
            ]);

            \Log::info("Final Media Playlist created successfully", [
                'filename' => $this->filename,
                'chunks_count' => count($files),
                'master_path' => $masterPlaylistPath
            ]);

        } catch (\Exception $e) {
            \Log::error("Failed to create final media playlist", [
                'filename' => $this->filename,
                'error' => $e->getMessage()
            ]);
        }
    }
}