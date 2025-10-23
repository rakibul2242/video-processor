<?php

namespace App\Jobs\VideoProcessing;

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
    public $chunkFile;

    /**
     * Create a new job instance.
     */
    public function __construct($chunkFile)
    {
        $this->chunkFile = $chunkFile;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $chunkFilePath = $this->chunkFile;
        $filename = pathinfo($chunkFilePath, PATHINFO_FILENAME);
        $parentDir = pathinfo($chunkFilePath, PATHINFO_DIRNAME);
        $mainFilename = basename(dirname($parentDir));
        $outputDir = "chunks/{$mainFilename}/hls/";

        \Log::info('ConvertChunkToHls', [
            'chunkFile' => $chunkFilePath,
            'chunkFilename' => $filename,
            'mainFilename' => $mainFilename,
            'outputDir' => $outputDir,
        ]);


        FFMpeg::fromDisk('public')
            ->open("chunks/{$mainFilename}/split/{$filename}.mp4")
            ->exportForHLS()
            ->toDisk('public')
            ->addFormat((new X264())->setKiloBitrate(1000))
            ->save("{$outputDir}{$filename}.m3u8");
    }
}
