<?php

namespace App\Http\Controllers\Api;

use App\Models\Wishlist;
use Illuminate\Http\Request;
use App\Http\Requests\StoreWishlistRequest;
use App\Http\Resources\WishlistResource;
use App\Http\Controllers\Controller;
use App\Services\WishlistService;

class WishlistController extends Controller
{
    protected $wishlistService;

    public function __construct(WishlistService $wishlistService)
    {
        $this->wishlistService = $wishlistService;
    }

    public function index()
    {
        $wishlists = $this->wishlistService->getAll();
        return WishlistResource::collection($wishlists);
    }

    public function store(StoreWishlistRequest $request)
    {
        $wishlist = $this->wishlistService->create($request->validated());
        return new WishlistResource($wishlist);
    }

    public function show($WishlistId)
    {
        $wishlist = $this->wishlistService->getById($WishlistId);
        return new WishlistResource($wishlist);
    }

    public function update(StoreWishlistRequest $request, $WishlistId)
    {
        $wishlist = $this->wishlistService->update($WishlistId, $request->validated());
        return new WishlistResource($wishlist);
    }

    public function destroy($WishlistId)
    {
        $this->wishlistService->delete($WishlistId);
        return response()->json(['message' => 'wishlist deleted successfully']);
    }
}

