<?php

namespace App\Pipes\Bookmark;

use App\Enums\MetadataStatus;
use App\Models\Bookmark;
use Closure;
use Illuminate\Support\Facades\Log;

class SaveMetadata
{
    /**
     * Save the extracted metadata to the bookmark
     *
     * @return mixed
     */
    public function handle(Bookmark $bookmark, Closure $next)
    {
        try {
            $updates = [
                'metadata_status' => MetadataStatus::COMPLETED->value,
                'metadata_error' => null,
            ];

            if (! empty($bookmark->extracted_title) && empty($bookmark->title)) {
                $updates['title'] = $bookmark->extracted_title;
            }

            if (! empty($bookmark->extracted_description) && empty($bookmark->description)) {
                $updates['description'] = $bookmark->extracted_description;
            }

            $bookmark->update($updates);

            return $next($bookmark);
        } catch (\Exception $e) {
            Log::error('Error saving bookmark metadata', [
                'bookmark_id' => $bookmark->id,
                'url' => $bookmark->url,
                'error' => $e->getMessage(),
            ]);

            $bookmark->update([
                'metadata_status' => MetadataStatus::FAILED->value,
                'metadata_error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to save bookmark metadata: {$e->getMessage()}");
        }
    }
}
