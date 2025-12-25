<?php

namespace App\Http\Controllers\Api;

use App\Models\Distributor;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDistributorRequest;
use App\Http\Resources\DistributorResource;
use App\Http\Controllers\Controller;
use App\Services\DistributorService;

class DistributorController extends Controller
{
    protected $distributorService;

    public function __construct(DistributorService $distributorService)
    {
        $this->distributorService = $distributorService;
    }

    public function index()
    {
        $distributors = $this->distributorService->getAll();
        return DistributorResource::collection($distributors);
    }

    public function store(StoreDistributorRequest $request)
    {
        $distributor = $this->distributorService->create($request->validated());
        return new DistributorResource($distributor);
    }

    public function show($distributorId)
    {
        $distributor = $this->distributorService->getById($distributorId);
        return new DistributorResource($distributor);
    }

    public function update(StoreDistributorRequest $request, $distributorId)
    {
        $distributor = $this->distributorService->update($distributorId, $request->validated());
        return new DistributorResource($distributor);
    }

    public function destroy($distributorId)
    {
        $this->distributorService->delete($distributorId);
        return response()->json(['message' => 'distributor deleted successfully']);
    }
}