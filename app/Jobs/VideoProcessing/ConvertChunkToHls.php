<?php

namespace App\Jobs\VideoProcessing;

use App\Models\Video;
use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ConvertChunkToHls implements ShouldQueue
{
    use Queueable, Dispatchable, InteractsWithQueue, SerializesModels;
    public $folderName, $chunkFileName;

    /**
     * Create a new job instance.
     */
    public function __construct(string $folderName, string $chunkFileName)
    {
        $this->folderName = $folderName;
        $this->chunkFileName = $chunkFileName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $chunkName = pathinfo($this->chunkFileName, PATHINFO_FILENAME);
            $outputDir = "chunks/{$this->folderName}/hls/";
            $fullOutputDir = storage_path("app/public/{$outputDir}");

            if (!file_exists($fullOutputDir)) {
                mkdir($fullOutputDir, 0777, true);
            }

            \Log::info('Converting chunk to HLS', [
                'folder' => $this->folderName,
                'chunk' => $this->chunkFileName,
                'outputDir' => $outputDir
            ]);

            FFMpeg::fromDisk('public')
                ->open("chunks/{$this->folderName}/split/{$this->chunkFileName}")
                ->exportForHLS()
                ->toDisk('public')
                ->addFormat((new X264())->setKiloBitrate(1000))
                ->save("{$outputDir}{$chunkName}.m3u8");
        } catch (\Exception $e) {
            \Log::error("ConvertChunkToHls failed", [
                'error' => $e->getMessage(),
                'folder' => $this->folderName,
                'chunk' => $this->chunkFileName
            ]);
            throw $e;
        }
    }
}
