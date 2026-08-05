<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Resources\GenreResource;
use App\Http\Controllers\Controller;
use App\Services\GenreService;

/**
 * @group Genres
 * 
 * APIs for managing movie genres.
 * Genres are used to categorize movies (e.g., Action, Comedy, Drama, Horror, etc.)
 */
class GenreController extends Controller
{
    protected $genreService;

    public function __construct(GenreService $genreService)
    {
        $this->genreService = $genreService;
    }

    /**
     * Get all genres
     * 
     * Get a list of all movie genres in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "GenreId": 1,
     *     "Name": "Action",
     *     "Description": "Action movies with high-energy sequences",
     *     "Slug": "action",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "GenreId": 2,
     *     "Name": "Comedy",
     *     "Description": "Comedy movies that make you laugh",
     *     "Slug": "comedy",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:05:00"
     *   },
     *   {
     *     "GenreId": 3,
     *     "Name": "Drama",
     *     "Description": "Drama movies with emotional stories",
     *     "Slug": "drama",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:10:00"
     *   }
     * ]
     */
    public function index()
    {
        $genres = $this->genreService->getAll();
        return GenreResource::collection($genres);
    }

    /**
     * Create a new genre
     * 
     * Create a new movie genre. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Name string required The genre name. Example: Action
     * @bodyParam Description string The genre description. Example: Action movies with high-energy sequences
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Active
     * 
     * @response 201 {
     *   "GenreId": 1,
     *   "Name": "Action",
     *   "Description": "Action movies with high-energy sequences",
     *   "Slug": "action",
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Name": ["The name field is required."]
     *   }
     * }
     * @response 401 {
     *   "message": "Unauthenticated"
     * }
     * @response 403 {
     *   "message": "Forbidden - Admin only"
     * }
     */
    public function store(StoreGenreRequest $request)
    {
        $genre = $this->genreService->create($request->validated());
        return new GenreResource($genre);
    }

    /**
     * Get a specific genre
     * 
     * Get detailed information about a specific genre.
     * 
     * @unauthenticated
     * 
     * @urlParam GenreId int required The ID of the genre. Example: 1
     * 
     * @response 200 {
     *   "GenreId": 1,
     *   "Name": "Action",
     *   "Description": "Action movies with high-energy sequences",
     *   "Slug": "action",
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Genre not found"
     * }
     */
    public function show($GenreId)
    {
        $genre = $this->genreService->getById($GenreId);
        return new GenreResource($genre);
    }

    /**
     * Update a genre
     * 
     * Update an existing genre. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam GenreId int required The ID of the genre. Example: 1
     * @bodyParam Name string The genre name. Example: Action & Adventure
     * @bodyParam Description string The genre description. Example: Action and adventure movies
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Inactive
     * 
     * @response 200 {
     *   "GenreId": 1,
     *   "Name": "Action & Adventure",
     *   "Description": "Action and adventure movies",
     *   "Slug": "action-adventure",
     *   "Status": "Inactive",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Genre not found"
     * }
     */
    public function update(StoreGenreRequest $request, $GenreId)
    {
        $genre = $this->genreService->update($GenreId, $request->validated());
        return new GenreResource($genre);
    }

    /**
     * Delete a genre
     * 
     * Delete a genre from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam GenreId int required The ID of the genre. Example: 1
     * 
     * @response 200 {
     *   "message": "Genre deleted successfully"
     * }
     * @response 404 {
     *   "message": "Genre not found"
     * }
     */
    public function destroy($GenreId)
    {
        $this->genreService->delete($GenreId);
        return response()->json(['message' => 'Genre deleted successfully']);
    }
}