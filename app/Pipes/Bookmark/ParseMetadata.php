<?php

namespace App\Pipes\Bookmark;

use App\Models\Bookmark;
use Closure;
use DOMDocument;
use Illuminate\Support\Facades\Log;

class ParseMetadata
{
    /**
     * Parse the webpage metadata
     *
     * @return mixed
     */
    public function handle(Bookmark $bookmark, Closure $next)
    {
        try {
            if (empty($bookmark->html_content)) {
                throw new \Exception('No HTML content to parse');
            }

            $dom = new DOMDocument;

            libxml_use_internal_errors(true);
            $dom->loadHTML($bookmark->html_content);
            libxml_clear_errors();

            $title = $this->extractTitle($dom);
            if (! empty($title) && empty($bookmark->title)) {
                $bookmark->extracted_title = $title;
            }

            $description = $this->extractDescription($dom);
            if (! empty($description) && empty($bookmark->description)) {
                $bookmark->extracted_description = $description;
            }

            unset($bookmark->html_content);

            return $next($bookmark);
        } catch (\Exception $e) {
            Log::error('Error parsing webpage metadata', [
                'bookmark_id' => $bookmark->id,
                'url' => $bookmark->url,
                'error' => $e->getMessage(),
            ]);

            throw new \Exception("Failed to parse webpage metadata: {$e->getMessage()}");
        }
    }

    private function extractTitle(DOMDocument $dom): ?string
    {

        $titleTags = $dom->getElementsByTagName('title');

        if ($titleTags->length > 0) {
            return trim($titleTags->item(0)->textContent);
        }

        return null;
    }


    private function extractDescription(DOMDocument $dom): ?string
    {
        $metaDescription = $this->getMetaContent($dom, 'name', 'description');
        if (! empty($metaDescription)) {
            return $metaDescription;
        }

        return null;
    }

    private function getMetaContent(DOMDocument $dom, string $attribute, string $value): ?string
    {
        $metas = $dom->getElementsByTagName('meta');

        for ($i = 0; $i < $metas->length; $i++) {
            $meta = $metas->item($i);

            if ($meta->hasAttribute($attribute) && $meta->getAttribute($attribute) === $value) {
                return trim($meta->getAttribute('content'));
            }
        }

        return null;
    }
}
