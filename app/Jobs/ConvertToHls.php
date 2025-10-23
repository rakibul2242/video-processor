<?php

namespace App\Jobs;

use FFMpeg\Format\Video\X264;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use ProtoneMedia\LaravelFFMpeg\Support\FFMpeg;

class ConvertToHls implements ShouldQueue
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
            $filename = str_replace(storage_path('app/public/'), '', $this->filePath);
            $outputDir = storage_path('app/public/videos/hls/' . pathinfo($filename, PATHINFO_FILENAME));

            if (!file_exists($outputDir)) {
                mkdir($outputDir, 0777, true);
            } else {
                \Log::info('outputDir exists-', [$outputDir]);
            }

            FFMpeg::fromDisk('public')
                ->open($filename)
                ->export()
                ->toDisk('public')
                ->inFormat(new X264())
                ->save('videos/hls/' . pathinfo($filename, PATHINFO_FILENAME) . '/playlist.m3u8');
        } catch (\Exception $e) {
            report($e);
        }
    }

    public function failed(\Exception $e): void
    {
        report($e);
    }
}
