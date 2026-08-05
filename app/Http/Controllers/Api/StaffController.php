<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreStaffRequest;
use App\Http\Resources\StaffResource;
use App\Services\StaffService;

/**
 * @group Staff
 * 
 * APIs for managing cinema staff members.
 * Staff members are employees who work at the cinema.
 * They have limited permissions compared to Admin.
 */
class StaffController extends Controller
{
    protected $service;

    public function __construct(StaffService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all staff members
     * 
     * Get a list of all staff members in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "StaffId": 1,
     *     "UserId": 1,
     *     "FullName": "Trần Văn B",
     *     "Email": "staff1@gauphim.com",
     *     "PhoneNumber": "0987654321",
     *     "Position": "Manager",
     *     "Department": "Operations",
     *     "Status": "Active",
     *     "HireDate": "2024-01-01",
     *     "CinemaId": 1,
     *     "CinemaName": "CGV Vincom Center",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "StaffId": 2,
     *     "UserId": 2,
     *     "FullName": "Lê Thị C",
     *     "Email": "staff2@gauphim.com",
     *     "PhoneNumber": "0987654322",
     *     "Position": "Ticket Seller",
     *     "Department": "Box Office",
     *     "Status": "Active",
     *     "HireDate": "2024-02-01",
     *     "CinemaId": 1,
     *     "CinemaName": "CGV Vincom Center",
     *     "CreatedAt": "2024-02-01 10:00:00"
     *   },
     *   {
     *     "StaffId": 3,
     *     "UserId": 3,
     *     "FullName": "Phạm Văn D",
     *     "Email": "staff3@gauphim.com",
     *     "PhoneNumber": "0987654323",
     *     "Position": "Usher",
     *     "Department": "Customer Service",
     *     "Status": "Inactive",
     *     "HireDate": "2024-03-01",
     *     "CinemaId": 2,
     *     "CinemaName": "Lotte Cinema",
     *     "CreatedAt": "2024-03-01 10:00:00"
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
        return StaffResource::collection($this->service->getAll());
    }

    /**
     * Create a new staff member
     * 
     * Create a new staff member and associate with a user account. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam UserId int required The ID of the user account. Example: 1
     * @bodyParam Position string required The staff position. Example: Manager
     * @bodyParam Department string required The department. Example: Operations
     * @bodyParam Status string The staff status. Enum: Active, Inactive. Example: Active
     * @bodyParam HireDate string The hire date. Example: 2024-01-01
     * @bodyParam CinemaId int required The ID of the cinema. Example: 1
     * 
     * @response 201 {
     *   "StaffId": 1,
     *   "UserId": 1,
     *   "FullName": "Trần Văn B",
     *   "Email": "staff1@gauphim.com",
     *   "PhoneNumber": "0987654321",
     *   "Position": "Manager",
     *   "Department": "Operations",
     *   "Status": "Active",
     *   "HireDate": "2024-01-01",
     *   "CinemaId": 1,
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "UserId": ["The user id field is required."],
     *     "Position": ["The position field is required."],
     *     "Department": ["The department field is required."],
     *     "CinemaId": ["The cinema id field is required."]
     *   }
     * }
     */
    public function store(StoreStaffRequest $request)
    {
        $staff = $this->service->create($request->validated());

        return new StaffResource($staff);
    }

    /**
     * Get a specific staff member
     * 
     * Get detailed information about a specific staff member. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam StaffId int required The ID of the staff member. Example: 1
     * 
     * @response 200 {
     *   "StaffId": 1,
     *   "UserId": 1,
     *   "FullName": "Trần Văn B",
     *   "Email": "staff1@gauphim.com",
     *   "PhoneNumber": "0987654321",
     *   "Position": "Manager",
     *   "Department": "Operations",
     *   "Status": "Active",
     *   "HireDate": "2024-01-01",
     *   "CinemaId": 1,
     *   "CinemaName": "CGV Vincom Center",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Staff not found"
     * }
     */
    public function show($StaffId)
    {
        $staff = $this->service->find($StaffId);
        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        return new StaffResource($staff);
    }

    /**
     * Update a staff member
     * 
     * Update an existing staff member. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam StaffId int required The ID of the staff member. Example: 1
     * @bodyParam UserId int The ID of the user account. Example: 1
     * @bodyParam Position string The staff position. Example: Senior Manager
     * @bodyParam Department string The department. Example: Operations
     * @bodyParam Status string The staff status. Enum: Active, Inactive. Example: Inactive
     * @bodyParam HireDate string The hire date. Example: 2024-01-01
     * @bodyParam CinemaId int The ID of the cinema. Example: 2
     * 
     * @response 200 {
     *   "StaffId": 1,
     *   "UserId": 1,
     *   "Position": "Senior Manager",
     *   "Status": "Inactive",
     *   "CinemaId": 2
     * }
     * @response 404 {
     *   "message": "Staff not found"
     * }
     */
    public function update(StoreStaffRequest $request, $StaffId)
    {
        $staff = $this->service->update($StaffId, $request->validated());
        if (!$staff) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        return new StaffResource($staff);
    }

    /**
     * Delete a staff member
     * 
     * Delete a staff member from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam StaffId int required The ID of the staff member. Example: 1
     * 
     * @response 200 {
     *   "message": "Staff deleted successfully"
     * }
     * @response 404 {
     *   "message": "Staff not found"
     * }
     */
    public function destroy($StaffId)
    {
        $deleted = $this->service->delete($StaffId);
        if (!$deleted) {
            return response()->json(['message' => 'Staff not found'], 404);
        }
        return response()->json(['message' => 'Staff deleted successfully']);
    }
}