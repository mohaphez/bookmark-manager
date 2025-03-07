<?php

namespace App\Jobs;

use App\Enums\MetadataStatus;
use App\Models\Bookmark;
use App\Pipes\Bookmark\FetchWebpage;
use App\Pipes\Bookmark\ParseMetadata;
use App\Pipes\Bookmark\SaveMetadata;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class FetchBookmarkMetadata implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = [30, 60, 120];

    public function __construct(
        public Bookmark $bookmark
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {

            app(Pipeline::class)
                ->send($this->bookmark)
                ->through([
                    FetchWebpage::class,
                    ParseMetadata::class,
                    SaveMetadata::class,
                ])
                ->thenReturn();

        } catch (\Exception $e) {
            Log::error('Failed to update bookmark metadata', [
                'bookmark_id' => $this->bookmark->id,
                'url' => $this->bookmark->url,
                'error' => $e->getMessage(),
            ]);

            if ($this->attempts() >= $this->tries) {
                $this->fail($e);
            } else {
                $this->release($this->backoff[$this->attempts() - 1]);
            }
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Exception $exception): void
    {
        $this->bookmark->update([
            'metadata_status' => MetadataStatus::FAILED->value,
            'metadata_error' => $exception->getMessage(),
        ]);
    }
}
