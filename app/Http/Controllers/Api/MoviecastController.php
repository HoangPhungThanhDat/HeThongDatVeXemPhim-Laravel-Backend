<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMoviecastRequest;
use App\Http\Resources\MoviecastResource;
use App\Services\MoviecastService;
use App\Models\Moviecast;
use Illuminate\Http\Request;

/**
 * @group Movie Casts
 * 
 * APIs for managing movie casts (actors, directors, etc.).
 * Each movie cast member can be associated with multiple movies.
 */
class MoviecastController extends Controller
{
    protected $service;

    public function __construct(MoviecastService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api', 'checkrole:Admin'])->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all movie casts
     * 
     * Get a list of all movie cast members in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "CastId": 1,
     *     "Name": "Robert Downey Jr.",
     *     "Role": "Actor",
     *     "Character": "Tony Stark / Iron Man",
     *     "Biography": "American actor known for Iron Man",
     *     "ImageUrl": "https://res.cloudinary.com/.../casts/robert.jpg",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "CastId": 2,
     *     "Name": "Christopher Nolan",
     *     "Role": "Director",
     *     "Character": null,
     *     "Biography": "British-American filmmaker",
     *     "ImageUrl": "https://res.cloudinary.com/.../casts/nolan.jpg",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:05:00"
     *   }
     * ]
     */
    public function index()
    {
        return MoviecastResource::collection($this->service->getAll());
    }

    /**
     * Get paginated movie casts
     * 
     * Get a paginated list of movie cast members with search and filter options.
     * 
     * @unauthenticated
     * 
     * @queryParam page int The page number. Default: 1. Example: 1
     * @queryParam limit int Number of items per page. Default: 10. Max: 100. Example: 10
     * @queryParam search string Search by name. Example: Robert
     * @queryParam role string Filter by role. Enum: Actor, Director, Producer, Writer. Example: Actor
     * 
     * @response 200 {
     *   "data": [
     *     {
     *       "CastId": 1,
     *       "Name": "Robert Downey Jr.",
     *       "Role": "Actor",
     *       "Character": "Tony Stark / Iron Man",
     *       "ImageUrl": "https://res.cloudinary.com/.../casts/robert.jpg"
     *     }
     *   ],
     *   "total": 100,
     *   "page": 1,
     *   "limit": 10,
     *   "totalPages": 10
     * }
     */
    public function getPaged(Request $request)
    {
        $page   = max(1, (int) $request->query('page',  1));
        $limit  = min(100, max(1, (int) $request->query('limit', 10)));
        $search = $request->query('search', '');
        $role   = $request->query('role', '');

        $query = Moviecast::with('movie');

        if ($search) {
            $query->where('Name', 'like', "%{$search}%");
        }

        if ($role) {
            $query->where('Role', $role);
        }

        $total      = $query->count();
        $totalPages = (int) ceil($total / $limit);
        $items      = $query->orderBy('CastId', 'desc')
                            ->skip(($page - 1) * $limit)
                            ->take($limit)
                            ->get();

        return response()->json([
            'data'       => MoviecastResource::collection($items),
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Create a new movie cast
     * 
     * Create a new movie cast member. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Name string required The cast member name. Example: Robert Downey Jr.
     * @bodyParam Role string required The role. Enum: Actor, Director, Producer, Writer. Example: Actor
     * @bodyParam Character string The character name played. Example: Tony Stark / Iron Man
     * @bodyParam Biography string The cast member biography. Example: American actor known for Iron Man
     * @bodyParam ImageUrl file The cast image (jpg, png, webp). Max 2MB.
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Active
     * 
     * @response 201 {
     *   "CastId": 1,
     *   "Name": "Robert Downey Jr.",
     *   "Role": "Actor",
     *   "Character": "Tony Stark / Iron Man",
     *   "Biography": "American actor known for Iron Man",
     *   "ImageUrl": "https://res.cloudinary.com/.../casts/robert.jpg",
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Name": ["The name field is required."],
     *     "Role": ["The role field is required."]
     *   }
     * }
     */
    public function store(StoreMoviecastRequest $request)
    {
        $moviecast = $this->service->create($request->validated());
        return new MoviecastResource($moviecast);
    }

    /**
     * Get a specific movie cast
     * 
     * Get detailed information about a specific movie cast member.
     * 
     * @unauthenticated
     * 
     * @urlParam CastId int required The ID of the cast member. Example: 1
     * 
     * @response 200 {
     *   "CastId": 1,
     *   "Name": "Robert Downey Jr.",
     *   "Role": "Actor",
     *   "Character": "Tony Stark / Iron Man",
     *   "Biography": "American actor known for Iron Man",
     *   "ImageUrl": "https://res.cloudinary.com/.../casts/robert.jpg",
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Movie cast not found"
     * }
     */
    public function show($CastId)
    {
        $moviecast = $this->service->find($CastId);
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }

    /**
     * Update a movie cast
     * 
     * Update an existing movie cast member. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam CastId int required The ID of the cast member. Example: 1
     * @bodyParam Name string The cast member name. Example: Robert Downey Jr. Updated
     * @bodyParam Role string The role. Enum: Actor, Director, Producer, Writer. Example: Actor
     * @bodyParam Character string The character name. Example: Tony Stark
     * @bodyParam Biography string The biography. Example: Updated biography
     * @bodyParam ImageUrl file The cast image (jpg, png, webp). Max 2MB.
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Inactive
     * 
     * @response 200 {
     *   "CastId": 1,
     *   "Name": "Robert Downey Jr. Updated",
     *   "Role": "Actor",
     *   "Character": "Tony Stark",
     *   "Status": "Inactive"
     * }
     * @response 404 {
     *   "message": "Movie cast not found"
     * }
     */
    public function update(StoreMoviecastRequest $request, $CastId)
    {
        $moviecast = $this->service->update($CastId, $request->validated());
        if (!$moviecast) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return new MoviecastResource($moviecast);
    }

    /**
     * Delete a movie cast
     * 
     * Delete a movie cast member from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam CastId int required The ID of the cast member. Example: 1
     * 
     * @response 200 {
     *   "message": "Movie cast deleted successfully"
     * }
     * @response 404 {
     *   "message": "Movie cast not found"
     * }
     */
    public function destroy($CastId)
    {
        $deleted = $this->service->delete($CastId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie cast not found'], 404);
        }
        return response()->json(['message' => 'Movie cast deleted successfully']);
    }
}