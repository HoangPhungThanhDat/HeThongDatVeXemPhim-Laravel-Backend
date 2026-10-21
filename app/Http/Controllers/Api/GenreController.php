<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Resources\GenreResource;
use App\Http\Controllers\Controller;
use App\Services\GenreService;
use Illuminate\Support\Facades\Log;

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
     */
    public function index()
    {
        try {
            $genres = $this->genreService->getAll();
            
            // ✅ Trả về JSON trực tiếp thay vì dùng Resource để giữ lại trường movieCount
            return response()->json([
                'success' => true,
                'data' => $genres,
                'message' => 'Lấy danh sách thể loại thành công'
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting genres: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Có lỗi xảy ra khi lấy danh sách thể loại',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new genre
     * 
     * Create a new movie genre. (Admin only)
     */
    public function store(StoreGenreRequest $request)
    {
        $genre = $this->genreService->create($request->validated());
        return new GenreResource($genre);
    }

    /**
     * Get a specific genre
     */
    public function show($GenreId)
    {
        $genre = $this->genreService->getById($GenreId);
        return new GenreResource($genre);
    }

    /**
     * Update a genre
     */
    public function update(StoreGenreRequest $request, $GenreId)
    {
        $genre = $this->genreService->update($GenreId, $request->validated());
        return new GenreResource($genre);
    }

    /**
     * Delete a genre
     */
    public function destroy($GenreId)
    {
        $this->genreService->delete($GenreId);
        return response()->json(['message' => 'Genre deleted successfully']);
    }
}