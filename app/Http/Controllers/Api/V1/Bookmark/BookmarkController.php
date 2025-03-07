<?php

namespace App\Http\Controllers\Api\V1\Bookmark;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Bookmark\StoreBookmarkRequest;
use App\Http\Resources\Api\V1\Bookmark\BookmarkResource;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class BookmarkController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $bookmarks = bookmarkService()->getUserBookmarks($user);

        return $this->success(
            data: BookmarkResource::collection($bookmarks),
            message: __('Bookmarks retrieved successfully')
        );
    }

    public function store(StoreBookmarkRequest $request): JsonResponse
    {
        $validated = $request->validated();

        /** @var User $user */
        $user = Auth::user();
        $bookmark = bookmarkService()->createBookmark($user, $validated);

        return $this->success(
            data: new BookmarkResource($bookmark),
            message: __('Bookmark created successfully'),
            code: 201
        );
    }
}
