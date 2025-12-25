<?php

namespace App\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Http\Request;
use App\Http\Requests\StoreGenreRequest;
use App\Http\Resources\GenreResource;
use App\Http\Controllers\Controller;
use App\Services\GenreService;
class GenreController extends Controller
{
    protected $genreService;

    public function __construct(GenreService $genreService)
    {
        $this->genreService = $genreService;
    }

    public function index()
    {
        $genres = $this->genreService->getAll();
        return GenreResource::collection($genres);
    }

    public function store(StoreGenreRequest $request)
    {
        $genre = $this->genreService->create($request->validated());
        return new GenreResource($genre);
    }

    public function show($GenreId)
    {
        $genre = $this->genreService->getById($GenreId);
        return new GenreResource($genre);
    }

    public function update(StoreGenreRequest $request, $GenreId)
    {
        $genre = $this->genreService->update($GenreId, $request->validated());
        return new GenreResource($genre);
    }

    public function destroy($GenreId)
    {
        $this->genreService->delete($GenreId);
        return response()->json(['message' => 'genre deleted successfully']);
    }
}
