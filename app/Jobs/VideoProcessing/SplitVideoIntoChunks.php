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
    public $folderName, $fileName;

    /**
     * Create a new job instance.
     */
    public function __construct(string $folderName, string $fileName)
    {
        $this->folderName = $folderName;
        $this->fileName = $fileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $inputPath = storage_path("app/public/chunks/{$this->folderName}/{$this->fileName}");
            $chunkDir = storage_path("app/public/chunks/{$this->folderName}/split");

            if (!file_exists($chunkDir)) {
                mkdir($chunkDir, 0777, true);
            }

            $seconds = 10; // per chunk in seconds
            $cmd = sprintf(
                'ffmpeg -i "%s" -c copy -map 0 -segment_time %d -f segment -reset_timestamps 1 "%s/chunk_%%03d.mp4" 2>&1',
                $inputPath,
                $seconds,
                $chunkDir
            );

            shell_exec($cmd);

            $chunkFiles = glob("{$chunkDir}/chunk_*.mp4");

            if (empty($chunkFiles)) {
                throw new \Exception("No chunks were created");
            }

            $jobs = [];
            foreach ($chunkFiles as $chunkFile) {
                $jobs[] = new ConvertChunkToHls($this->folderName, basename($chunkFile));
            }
            $jobs[] = new MergeHlsChunks($this->folderName);

            ConvertChunkToHls::withChain($jobs)->dispatch($this->folderName, basename($chunkFiles[0]));

        } catch (\Exception $e) {
            \Log::error("SplitVideoIntoChunks failed", [
                'error' => $e->getMessage(),
                'folder' => $this->folderName,
                'file' => $this->fileName
            ]);
            report($e);
        }
    }
}
