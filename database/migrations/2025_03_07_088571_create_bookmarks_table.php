<?php

use App\Enums\MetadataStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookmarks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('url');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('metadata_status')->default(MetadataStatus::PENDING->value);
            $table->text('metadata_error')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'url']);
            $table->index('title');
            $table->index('metadata_status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookmarks');
    }
};
