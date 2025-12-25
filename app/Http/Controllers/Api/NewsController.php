<?php

namespace App\Http\Controllers\Api;

use App\Models\News;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNewsRequest;
use App\Http\Resources\NewsResource;
use App\Http\Controllers\Controller;
use App\Services\NewsService;

class NewsController extends Controller
{
    protected $newsService;

    public function __construct(NewsService $newsService)
    {
        $this->newsService = $newsService;
    }

    public function index()
    {
        $newss = $this->newsService->getAll();
        return NewsResource::collection($newss);
    }

    public function store(StoreNewsRequest $request)
    {
        $news = $this->newsService->create($request->validated());
        return new NewsResource($news);
    }

    public function show($NewsId)
    {
        $news = $this->newsService->getById($NewsId);
        return new NewsResource($news);
    }

    public function update(StoreNewsRequest $request, $NewsId)
    {
        $news = $this->newsService->update($NewsId, $request->validated());
        return new NewsResource($news);
    }

    public function destroy($NewsId)
    {
        $this->newsService->delete($NewsId);
        return response()->json(['message' => 'news deleted successfully']);
    }
}
