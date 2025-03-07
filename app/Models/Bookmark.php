<?php

namespace App\Models;

use App\Enums\MetadataStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bookmark extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    /**
     * Temporary storage for HTML content during processing
     *
     * @var string|null
     */
    public $html_content = null;

    /**
     * Temporary storage for extracted title during processing
     *
     * @var string|null
     */
    public $extracted_title = null;

    /**
     * Temporary storage for extracted description during processing
     *
     * @var string|null
     */
    public $extracted_description = null;

    protected $fillable = [
        'user_id',
        'url',
        'title',
        'description',
        'metadata_status',
        'metadata_error',
    ];

    protected $dates = [
        'deleted_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata_status' => MetadataStatus::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the bookmark has completed metadata fetching
     */
    public function hasCompletedMetadata(): bool
    {
        return $this->metadata_status === MetadataStatus::COMPLETED;
    }

    /**
     * Check if the bookmark has failed metadata fetching
     */
    public function hasFailedMetadata(): bool
    {
        return $this->metadata_status === MetadataStatus::FAILED;
    }

    /**
     * Check if the bookmark is pending metadata fetching
     */
    public function isPendingMetadata(): bool
    {
        return $this->metadata_status === MetadataStatus::PENDING;
    }

    /**
     * Check if the bookmark is processing metadata fetching
     */
    public function isProcessingMetadata(): bool
    {
        return $this->metadata_status === MetadataStatus::PROCESSING;
    }
}
