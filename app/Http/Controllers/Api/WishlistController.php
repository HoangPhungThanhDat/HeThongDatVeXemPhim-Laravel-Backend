<?php

namespace App\Http\Controllers\Api;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Http\Requests\StoreWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Http\Controllers\Controller;
use App\Services\WishlistService;

/**
 * @group Wishlists
 * 
 * APIs for managing user wishlists.
 * Users can add movies to their wishlist to keep track of movies they want to watch.
 * Each user can have multiple movies in their wishlist.
 */
class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    /**
     * Get all wishlist items
     * 
     * Get a list of all wishlist items in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "WishlistId": 1,
     *     "UserId": 1,
     *     "MovieId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "user@example.com",
     *     "MovieTitle": "Avengers: Endgame",
     *     "PosterUrl": "https://res.cloudinary.com/.../avengers.jpg",
     *     "AddedAt": "2024-01-01 10:00:00",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "WishlistId": 2,
     *     "UserId": 1,
     *     "MovieId": 2,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "user@example.com",
     *     "MovieTitle": "The Dark Knight",
     *     "PosterUrl": "https://res.cloudinary.com/.../dark-knight.jpg",
     *     "AddedAt": "2024-01-01 10:30:00",
     *     "CreatedAt": "2024-01-01 10:30:00"
     *   },
     *   {
     *     "WishlistId": 3,
     *     "UserId": 2,
     *     "MovieId": 3,
     *     "FullName": "Trần Thị B",
     *     "Email": "user2@example.com",
     *     "MovieTitle": "Inception",
     *     "PosterUrl": "https://res.cloudinary.com/.../inception.jpg",
     *     "AddedAt": "2024-01-01 11:00:00",
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
        $wishlists = $this->wishlistService->getAll();
        return WishlistResource::collection($wishlists);
    }

    /**
     * Add a movie to wishlist
     * 
     * Add a movie to a user's wishlist. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam UserId int required The ID of the user. Example: 1
     * @bodyParam MovieId int required The ID of the movie. Example: 1
     * @bodyParam AddedAt string The time the movie was added. Example: 2024-01-01 10:00:00
     * 
     * @response 201 {
     *   "WishlistId": 1,
     *   "UserId": 1,
     *   "MovieId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "MovieTitle": "Avengers: Endgame",
     *   "AddedAt": "2024-01-01 10:00:00",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "UserId": ["The user id field is required."],
     *     "MovieId": ["The movie id field is required."]
     *   }
     * }
     * @response 409 {
     *   "message": "This movie is already in the user's wishlist."
     * }
     */
    public function store(StoreWishlistRequest $request)
    {
        $wishlist = $this->wishlistService->create($request->validated());
        return new WishlistResource($wishlist);
    }

    /**
     * Get a specific wishlist item
     * 
     * Get detailed information about a specific wishlist item. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam WishlistId int required The ID of the wishlist item. Example: 1
     * 
     * @response 200 {
     *   "WishlistId": 1,
     *   "UserId": 1,
     *   "MovieId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "user@example.com",
     *   "MovieTitle": "Avengers: Endgame",
     *   "PosterUrl": "https://res.cloudinary.com/.../avengers.jpg",
     *   "AddedAt": "2024-01-01 10:00:00",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Wishlist not found"
     * }
     */
    public function show($WishlistId)
    {
        $wishlist = $this->wishlistService->getById($WishlistId);
        return new WishlistResource($wishlist);
    }

    /**
     * Update a wishlist item
     * 
     * Update an existing wishlist item (change movie or user). (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam WishlistId int required The ID of the wishlist item. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam MovieId int The ID of the movie. Example: 2
     * @bodyParam AddedAt string The time the movie was added. Example: 2024-01-01 10:00:00
     * 
     * @response 200 {
     *   "WishlistId": 1,
     *   "UserId": 1,
     *   "MovieId": 2,
     *   "MovieTitle": "The Dark Knight",
     *   "AddedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Wishlist not found"
     * }
     */
    public function update(StoreWishlistRequest $request, $WishlistId)
    {
        $wishlist = $this->wishlistService->update($WishlistId, $request->validated());
        return new WishlistResource($wishlist);
    }

    /**
     * Remove a movie from wishlist
     * 
     * Remove a movie from a user's wishlist. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam WishlistId int required The ID of the wishlist item. Example: 1
     * 
     * @response 200 {
     *   "message": "Wishlist deleted successfully"
     * }
     * @response 404 {
     *   "message": "Wishlist not found"
     * }
     */
    public function destroy($WishlistId)
    {
        $this->wishlistService->delete($WishlistId);
        return response()->json(['message' => 'Wishlist deleted successfully']);
    }
}