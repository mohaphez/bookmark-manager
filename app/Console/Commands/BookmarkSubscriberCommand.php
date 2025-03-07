<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class BookmarkSubscriberCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookmark:subscribe';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Subscribe to the Redis bookmark channel and process incoming bookmarks';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting bookmark subscriber...');

        try {
            bookmarkSubscriber()->subscribe();

            $this->info('Bookmark subscriber stopped');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error in bookmark subscriber: '.$e->getMessage());
            Log::error('Bookmark subscriber error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
