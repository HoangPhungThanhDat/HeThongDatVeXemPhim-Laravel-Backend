<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Services\ReviewService;

/**
 * @group Reviews
 * 
 * APIs for managing movie reviews and ratings.
 * Users can rate and review movies they have watched.
 */
class ReviewController extends Controller
{
    protected $service;

    public function __construct(ReviewService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all reviews
     * 
     * Get a list of all reviews in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "ReviewId": 1,
     *     "MovieId": 1,
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Rating": 5,
     *     "Comment": "Tuyệt vời! Phim rất hay và hấp dẫn.",
     *     "Status": "Published",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "ReviewId": 2,
     *     "MovieId": 1,
     *     "UserId": 2,
     *     "FullName": "Trần Thị B",
     *     "Rating": 4,
     *     "Comment": "Phim hay nhưng hơi dài.",
     *     "Status": "Published",
     *     "CreatedAt": "2024-01-01 10:30:00"
     *   },
     *   {
     *     "ReviewId": 3,
     *     "MovieId": 2,
     *     "UserId": 3,
     *     "FullName": "Lê Văn C",
     *     "Rating": 2,
     *     "Comment": "Phim không như kỳ vọng.",
     *     "Status": "Pending",
     *     "CreatedAt": "2024-01-01 11:00:00"
     *   }
     * ]
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function index()
    {
        return ReviewResource::collection($this->service->getAll());
    }

    /**
     * Create a new review
     * 
     * Create a new review for a movie. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam MovieId int required The ID of the movie being reviewed. Example: 1
     * @bodyParam UserId int required The ID of the user writing the review. Example: 1
     * @bodyParam Rating int required The rating from 1-5. Example: 5
     * @bodyParam Comment string required The review comment. Example: Tuyệt vời! Phim rất hay.
     * @bodyParam Status string The review status. Enum: Pending, Published, Hidden. Example: Published
     * 
     * @response 201 {
     *   "ReviewId": 1,
     *   "MovieId": 1,
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Rating": 5,
     *   "Comment": "Tuyệt vời! Phim rất hay.",
     *   "Status": "Published",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "MovieId": ["The movie id field is required."],
     *     "UserId": ["The user id field is required."],
     *     "Rating": ["The rating field is required."],
     *     "Comment": ["The comment field is required."]
     *   }
     * }
     */
    public function store(StoreReviewRequest $request)
    {
        $review = $this->service->create($request->validated());

        return new ReviewResource($review);
    }

    /**
     * Get a specific review
     * 
     * Get detailed information about a specific review. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ReviewId int required The ID of the review. Example: 1
     * 
     * @response 200 {
     *   "ReviewId": 1,
     *   "MovieId": 1,
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Rating": 5,
     *   "Comment": "Tuyệt vời! Phim rất hay và hấp dẫn.",
     *   "Status": "Published",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Review not found"
     * }
     */
    public function show($ReviewId)
    {
        $review = $this->service->find($ReviewId);
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return new ReviewResource($review);
    }

    /**
     * Update a review
     * 
     * Update an existing review. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ReviewId int required The ID of the review. Example: 1
     * @bodyParam MovieId int The ID of the movie. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam Rating int The rating from 1-5. Example: 4
     * @bodyParam Comment string The review comment. Example: Phim hay nhưng hơi dài.
     * @bodyParam Status string The review status. Enum: Pending, Published, Hidden. Example: Hidden
     * 
     * @response 200 {
     *   "ReviewId": 1,
     *   "MovieId": 1,
     *   "UserId": 1,
     *   "Rating": 4,
     *   "Comment": "Phim hay nhưng hơi dài.",
     *   "Status": "Hidden",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Review not found"
     * }
     */
    public function update(StoreReviewRequest $request, $ReviewId)
    {
        $review = $this->service->update($ReviewId, $request->validated());
        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return new ReviewResource($review);
    }

    /**
     * Delete a review
     * 
     * Delete a review from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam ReviewId int required The ID of the review. Example: 1
     * 
     * @response 200 {
     *   "message": "Review deleted successfully"
     * }
     * @response 404 {
     *   "message": "Review not found"
     * }
     */
    public function destroy($ReviewId)
    {
        $deleted = $this->service->delete($ReviewId);
        if (!$deleted) {
            return response()->json(['message' => 'Review not found'], 404);
        }
        return response()->json(['message' => 'Review deleted successfully']);
    }
}