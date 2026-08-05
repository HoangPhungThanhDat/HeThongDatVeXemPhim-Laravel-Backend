<?php

namespace App\Http\Controllers\Api;

use App\Models\Distributor;
use Illuminate\Http\Request;
use App\Http\Requests\StoreDistributorRequest;
use App\Http\Resources\DistributorResource;
use App\Http\Controllers\Controller;
use App\Services\DistributorService;

/**
 * @group Distributors
 * 
 * APIs for managing distributors (film distributors/studios).
 * Distributors are companies that distribute movies to cinemas.
 */
class DistributorController extends Controller
{
    protected $distributorService;

    public function __construct(DistributorService $distributorService)
    {
        $this->distributorService = $distributorService;
    }

    /**
     * Get all distributors
     * 
     * Get a list of all film distributors in the system.
     * 
     * @unauthenticated
     * 
     * @response 200 [
     *   {
     *     "DistributorId": 1,
     *     "Name": "Universal Pictures",
     *     "Description": "One of the major film studios",
     *     "Country": "United States",
     *     "Website": "https://www.universalpictures.com",
     *     "Email": "contact@universalpictures.com",
     *     "Phone": "+1 123-456-7890",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "DistributorId": 2,
     *     "Name": "Warner Bros",
     *     "Description": "Global entertainment company",
     *     "Country": "United States",
     *     "Website": "https://www.warnerbros.com",
     *     "Email": "contact@warnerbros.com",
     *     "Phone": "+1 123-456-7891",
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:05:00"
     *   }
     * ]
     */
    public function index()
    {
        $distributors = $this->distributorService->getAll();
        return DistributorResource::collection($distributors);
    }

    /**
     * Create a new distributor
     * 
     * Create a new film distributor. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Name string required The distributor name. Example: Universal Pictures
     * @bodyParam Description string The distributor description. Example: One of the major film studios
     * @bodyParam Country string The country of origin. Example: United States
     * @bodyParam Website string The distributor website. Example: https://www.universalpictures.com
     * @bodyParam Email string The contact email. Example: contact@universalpictures.com
     * @bodyParam Phone string The contact phone number. Example: +1 123-456-7890
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Active
     * 
     * @response 201 {
     *   "DistributorId": 1,
     *   "Name": "Universal Pictures",
     *   "Description": "One of the major film studios",
     *   "Country": "United States",
     *   "Website": "https://www.universalpictures.com",
     *   "Email": "contact@universalpictures.com",
     *   "Phone": "+1 123-456-7890",
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Name": ["The name field is required."]
     *   }
     * }
     */
    public function store(StoreDistributorRequest $request)
    {
        $distributor = $this->distributorService->create($request->validated());
        return new DistributorResource($distributor);
    }

    /**
     * Get a specific distributor
     * 
     * Get detailed information about a specific distributor.
     * 
     * @unauthenticated
     * 
     * @urlParam distributorId int required The ID of the distributor. Example: 1
     * 
     * @response 200 {
     *   "DistributorId": 1,
     *   "Name": "Universal Pictures",
     *   "Description": "One of the major film studios",
     *   "Country": "United States",
     *   "Website": "https://www.universalpictures.com",
     *   "Email": "contact@universalpictures.com",
     *   "Phone": "+1 123-456-7890",
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Distributor not found"
     * }
     */
    public function show($distributorId)
    {
        $distributor = $this->distributorService->getById($distributorId);
        return new DistributorResource($distributor);
    }

    /**
     * Update a distributor
     * 
     * Update an existing distributor. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam distributorId int required The ID of the distributor. Example: 1
     * @bodyParam Name string The distributor name. Example: Universal Pictures Updated
     * @bodyParam Description string The distributor description. Example: Updated description
     * @bodyParam Country string The country. Example: United States
     * @bodyParam Website string The website. Example: https://www.universalpictures.com
     * @bodyParam Email string The contact email. Example: contact@universalpictures.com
     * @bodyParam Phone string The phone number. Example: +1 123-456-7890
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Inactive
     * 
     * @response 200 {
     *   "DistributorId": 1,
     *   "Name": "Universal Pictures Updated",
     *   "Description": "Updated description",
     *   "Status": "Inactive"
     * }
     * @response 404 {
     *   "message": "Distributor not found"
     * }
     */
    public function update(StoreDistributorRequest $request, $distributorId)
    {
        $distributor = $this->distributorService->update($distributorId, $request->validated());
        return new DistributorResource($distributor);
    }

    /**
     * Delete a distributor
     * 
     * Delete a distributor from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam distributorId int required The ID of the distributor. Example: 1
     * 
     * @response 200 {
     *   "message": "Distributor deleted successfully"
     * }
     * @response 404 {
     *   "message": "Distributor not found"
     * }
     */
    public function destroy($distributorId)
    {
        $this->distributorService->delete($distributorId);
        return response()->json(['message' => 'Distributor deleted successfully']);
    }
}