<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMoviegenreRequest;
use App\Http\Resources\MoviegenreResource;
use App\Services\MoviegenreService;

/**
 * @group Movie Genres
 * 
 * APIs for managing movie-genre relationships.
 * This allows assigning genres to movies (many-to-many relationship).
 */
class MoviegenreController extends Controller
{
    protected $service;

    public function __construct(MoviegenreService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all movie-genre relationships
     * 
     * Get a list of all movie-genre assignments in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "MovieGenreId": 1,
     *     "MovieId": 1,
     *     "GenreId": 1,
     *     "MovieTitle": "Avengers: Endgame",
     *     "GenreName": "Action",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "MovieGenreId": 2,
     *     "MovieId": 1,
     *     "GenreId": 2,
     *     "MovieTitle": "Avengers: Endgame",
     *     "GenreName": "Adventure",
     *     "CreatedAt": "2024-01-01 10:01:00"
     *   },
     *   {
     *     "MovieGenreId": 3,
     *     "MovieId": 2,
     *     "GenreId": 3,
     *     "MovieTitle": "The Dark Knight",
     *     "GenreName": "Drama",
     *     "CreatedAt": "2024-01-01 10:02:00"
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
        return MoviegenreResource::collection($this->service->getAll());
    }

    /**
     * Create a new movie-genre relationship
     * 
     * Assign a genre to a movie. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam MovieId int required The ID of the movie. Example: 1
     * @bodyParam GenreId int required The ID of the genre. Example: 1
     * 
     * @response 201 {
     *   "MovieGenreId": 1,
     *   "MovieId": 1,
     *   "GenreId": 1,
     *   "MovieTitle": "Avengers: Endgame",
     *   "GenreName": "Action",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "MovieId": ["The movie id field is required."],
     *     "GenreId": ["The genre id field is required."]
     *   }
     * }
     * @response 409 {
     *   "message": "This genre is already assigned to this movie."
     * }
     */
    public function store(StoreMoviegenreRequest $request)
    {
        $moviegenre = $this->service->create($request->validated());

        return new MoviegenreResource($moviegenre);
    }

    /**
     * Get a specific movie-genre relationship
     * 
     * Get detailed information about a specific movie-genre assignment. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam MovieGenreId int required The ID of the movie-genre relationship. Example: 1
     * 
     * @response 200 {
     *   "MovieGenreId": 1,
     *   "MovieId": 1,
     *   "GenreId": 1,
     *   "MovieTitle": "Avengers: Endgame",
     *   "GenreName": "Action",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Movie genre not found"
     * }
     */
    public function show($MovieGenreId)
    {
        $moviegenre = $this->service->find($MovieGenreId);
        if (!$moviegenre) {
            return response()->json(['message' => 'Movie genre not found'], 404);
        }
        return new MoviegenreResource($moviegenre);
    }

    /**
     * Update a movie-genre relationship
     * 
     * Update an existing movie-genre assignment (change genre or movie). (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam MovieGenreId int required The ID of the movie-genre relationship. Example: 1
     * @bodyParam MovieId int The ID of the movie. Example: 1
     * @bodyParam GenreId int The ID of the genre. Example: 2
     * 
     * @response 200 {
     *   "MovieGenreId": 1,
     *   "MovieId": 1,
     *   "GenreId": 2,
     *   "MovieTitle": "Avengers: Endgame",
     *   "GenreName": "Adventure",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Movie genre not found"
     * }
     * @response 409 {
     *   "message": "This genre is already assigned to this movie."
     * }
     */
    public function update(StoreMoviegenreRequest $request, $MovieGenreId)
    {
        $moviegenre = $this->service->update($MovieGenreId, $request->validated());
        if (!$moviegenre) {
            return response()->json(['message' => 'Movie genre not found'], 404);
        }
        return new MoviegenreResource($moviegenre);
    }

    /**
     * Delete a movie-genre relationship
     * 
     * Remove a genre assignment from a movie. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam MovieGenreId int required The ID of the movie-genre relationship. Example: 1
     * 
     * @response 200 {
     *   "message": "Movie genre deleted successfully"
     * }
     * @response 404 {
     *   "message": "Movie genre not found"
     * }
     */
    public function destroy($MovieGenreId)
    {
        $deleted = $this->service->delete($MovieGenreId);
        if (!$deleted) {
            return response()->json(['message' => 'Movie genre not found'], 404);
        }
        return response()->json(['message' => 'Movie genre deleted successfully']);
    }
}