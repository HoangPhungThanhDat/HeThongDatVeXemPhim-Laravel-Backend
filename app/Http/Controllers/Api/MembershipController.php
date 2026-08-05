<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMembershipRequest;
use App\Http\Resources\MembershipResource;
use App\Services\MembershipService;

/**
 * @group Memberships
 * 
 * APIs for managing user memberships.
 * Memberships provide benefits like discounts, priority booking, and special offers.
 */
class MembershipController extends Controller
{
    protected $service;

    public function __construct(MembershipService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all memberships
     * 
     * Get a list of all membership plans available in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "MembershipId": 1,
     *     "Name": "Silver",
     *     "Description": "Silver membership with basic benefits",
     *     "Price": 100000,
     *     "Duration": 30,
     *     "DiscountPercent": 5,
     *     "PriorityBooking": false,
     *     "FreePopcorn": false,
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "MembershipId": 2,
     *     "Name": "Gold",
     *     "Description": "Gold membership with premium benefits",
     *     "Price": 250000,
     *     "Duration": 30,
     *     "DiscountPercent": 15,
     *     "PriorityBooking": true,
     *     "FreePopcorn": true,
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:05:00"
     *   },
     *   {
     *     "MembershipId": 3,
     *     "Name": "Platinum",
     *     "Description": "Platinum membership with VIP benefits",
     *     "Price": 500000,
     *     "Duration": 30,
     *     "DiscountPercent": 25,
     *     "PriorityBooking": true,
     *     "FreePopcorn": true,
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:10:00"
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
        return MembershipResource::collection($this->service->getAll());
    }

    /**
     * Create a new membership
     * 
     * Create a new membership plan. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam Name string required The membership name. Example: Silver
     * @bodyParam Description string The membership description. Example: Silver membership with basic benefits
     * @bodyParam Price numeric required The membership price in VND. Example: 100000
     * @bodyParam Duration int required The membership duration in days. Example: 30
     * @bodyParam DiscountPercent int The discount percentage. Example: 5
     * @bodyParam PriorityBooking boolean Priority booking access. Example: false
     * @bodyParam FreePopcorn boolean Free popcorn benefit. Example: false
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Active
     * 
     * @response 201 {
     *   "MembershipId": 1,
     *   "Name": "Silver",
     *   "Description": "Silver membership with basic benefits",
     *   "Price": 100000,
     *   "Duration": 30,
     *   "DiscountPercent": 5,
     *   "PriorityBooking": false,
     *   "FreePopcorn": false,
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "Name": ["The name field is required."],
     *     "Price": ["The price field is required."],
     *     "Duration": ["The duration field is required."]
     *   }
     * }
     */
    public function store(StoreMembershipRequest $request)
    {
        $membership = $this->service->create($request->validated());

        return new MembershipResource($membership);
    }

    /**
     * Get a specific membership
     * 
     * Get detailed information about a specific membership plan. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam MembershipId int required The ID of the membership. Example: 1
     * 
     * @response 200 {
     *   "MembershipId": 1,
     *   "Name": "Silver",
     *   "Description": "Silver membership with basic benefits",
     *   "Price": 100000,
     *   "Duration": 30,
     *   "DiscountPercent": 5,
     *   "PriorityBooking": false,
     *   "FreePopcorn": false,
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Membership not found"
     * }
     */
    public function show($MembershipId)
    {
        $membership = $this->service->find($MembershipId);
        if (!$membership) {
            return response()->json(['message' => 'Membership not found'], 404);
        }
        return new MembershipResource($membership);
    }

    /**
     * Update a membership
     * 
     * Update an existing membership plan. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam MembershipId int required The ID of the membership. Example: 1
     * @bodyParam Name string The membership name. Example: Silver Premium
     * @bodyParam Description string The membership description. Example: Silver premium membership
     * @bodyParam Price numeric The price in VND. Example: 150000
     * @bodyParam Duration int The duration in days. Example: 30
     * @bodyParam DiscountPercent int The discount percentage. Example: 8
     * @bodyParam PriorityBooking boolean Priority booking access. Example: true
     * @bodyParam FreePopcorn boolean Free popcorn benefit. Example: true
     * @bodyParam Status string The status. Enum: Active, Inactive. Example: Inactive
     * 
     * @response 200 {
     *   "MembershipId": 1,
     *   "Name": "Silver Premium",
     *   "Description": "Silver premium membership",
     *   "Price": 150000,
     *   "Duration": 30,
     *   "DiscountPercent": 8,
     *   "PriorityBooking": true,
     *   "FreePopcorn": true,
     *   "Status": "Inactive",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Membership not found"
     * }
     */
    public function update(StoreMembershipRequest $request, $MembershipId)
    {
        $membership = $this->service->update($MembershipId, $request->validated());
        if (!$membership) {
            return response()->json(['message' => 'Membership not found'], 404);
        }
        return new MembershipResource($membership);
    }

    /**
     * Delete a membership
     * 
     * Delete a membership plan from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam MembershipId int required The ID of the membership. Example: 1
     * 
     * @response 200 {
     *   "message": "Membership deleted successfully"
     * }
     * @response 404 {
     *   "message": "Membership not found"
     * }
     */
    public function destroy($MembershipId)
    {
        $deleted = $this->service->delete($MembershipId);
        if (!$deleted) {
            return response()->json(['message' => 'Membership not found'], 404);
        }
        return response()->json(['message' => 'Membership deleted successfully']);
    }
}