<?php

namespace App\Jobs\VideoProcessing;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SplitVideoIntoChunks implements ShouldQueue
{
    use Queueable, SerializesModels, InteractsWithQueue, Dispatchable;
    public $filePath;

    /**
     * Create a new job instance.
     */
    public function __construct(string $filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $filename = pathinfo($this->filePath, PATHINFO_FILENAME);
            $chunkDir = storage_path("app/public/chunks/{$filename}/split");

            if (!file_exists($chunkDir)) {
                mkdir($chunkDir, 0777, true);
            }

            $seconds = 600;
            $cmd = sprintf(
                'ffmpeg -i "%s" -c copy -map 0 -segment_time %d -f segment -reset_timestamps 1 "%s/chunk_%%03d.mp4"',
                $this->filePath,
                $seconds,
                $chunkDir
            );
            shell_exec($cmd);

            $chunkFiles = glob("{$chunkDir}/chunk_*.mp4");

            $jobs = [];
            foreach ($chunkFiles as $chunkFile) {
                $jobs[] = new ConvertChunkToHls($chunkFile);
            }
            $jobs[] = new MergeHlsChunks($filename);

            ConvertChunkToHls::withChain($jobs)->dispatch($chunkFiles[0] ?? null);

        } catch (\Exception $e) {
            report($e);
        }
    }
}
