<?php

namespace App\Http\Controllers\Api;

use App\Models\Loginhistory;
use Illuminate\Http\Request;
use App\Http\Requests\StoreLoginhistoryRequest;
use App\Http\Resources\LoginhistoryResource;
use App\Http\Controllers\Controller;
use App\Services\LoginhistoryService;

/**
 * @group Login History
 * 
 * APIs for managing user login history.
 * Tracks all user login activities including timestamps, IP addresses, and device information.
 */
class LoginhistoryController extends Controller
{
    protected $loginhistoryService;

    public function __construct(LoginhistoryService $loginhistoryService)
    {
        $this->loginhistoryService = $loginhistoryService;
    }

    /**
     * Get all login history records
     * 
     * Get a list of all user login history records. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "LoginId": 1,
     *     "UserId": 1,
     *     "FullName": "Nguyễn Văn A",
     *     "Email": "admin@gauphim.com",
     *     "LoginAt": "2024-01-01 10:00:00",
     *     "LogoutAt": "2024-01-01 12:00:00",
     *     "IPAddress": "192.168.1.1",
     *     "UserAgent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36",
     *     "Device": "Windows Desktop",
     *     "Status": "Success"
     *   },
     *   {
     *     "LoginId": 2,
     *     "UserId": 2,
     *     "FullName": "Trần Thị B",
     *     "Email": "user@example.com",
     *     "LoginAt": "2024-01-01 10:30:00",
     *     "LogoutAt": null,
     *     "IPAddress": "192.168.1.2",
     *     "UserAgent": "Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15",
     *     "Device": "iPhone",
     *     "Status": "Success"
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
        $loginhistorys = $this->loginhistoryService->getAll();
        return LoginhistoryResource::collection($loginhistorys);
    }

    /**
     * Create a new login history record
     * 
     * Create a new login history entry. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam UserId int required The ID of the user. Example: 1
     * @bodyParam LoginAt string required The login timestamp. Example: 2024-01-01 10:00:00
     * @bodyParam LogoutAt string The logout timestamp. Example: 2024-01-01 12:00:00
     * @bodyParam IPAddress string The IP address. Example: 192.168.1.1
     * @bodyParam UserAgent string The user agent string. Example: Mozilla/5.0...
     * @bodyParam Device string The device name. Example: Windows Desktop
     * @bodyParam Status string The login status. Enum: Success, Failed. Example: Success
     * 
     * @response 201 {
     *   "LoginId": 1,
     *   "UserId": 1,
     *   "LoginAt": "2024-01-01 10:00:00",
     *   "LogoutAt": "2024-01-01 12:00:00",
     *   "IPAddress": "192.168.1.1",
     *   "UserAgent": "Mozilla/5.0...",
     *   "Device": "Windows Desktop",
     *   "Status": "Success"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "UserId": ["The user id field is required."],
     *     "LoginAt": ["The login at field is required."]
     *   }
     * }
     */
    public function store(StoreLoginhistoryRequest $request)
    {
        $loginhistory = $this->loginhistoryService->create($request->validated());
        return new LoginhistoryResource($loginhistory);
    }

    /**
     * Get a specific login history record
     * 
     * Get detailed information about a specific login history record. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam LoginId int required The ID of the login history record. Example: 1
     * 
     * @response 200 {
     *   "LoginId": 1,
     *   "UserId": 1,
     *   "FullName": "Nguyễn Văn A",
     *   "Email": "admin@gauphim.com",
     *   "LoginAt": "2024-01-01 10:00:00",
     *   "LogoutAt": "2024-01-01 12:00:00",
     *   "IPAddress": "192.168.1.1",
     *   "UserAgent": "Mozilla/5.0...",
     *   "Device": "Windows Desktop",
     *   "Status": "Success"
     * }
     * @response 404 {
     *   "message": "Login history record not found"
     * }
     */
    public function show($LoginId)
    {
        $loginhistory = $this->loginhistoryService->getById($LoginId);
        return new LoginhistoryResource($loginhistory);
    }

    /**
     * Update a login history record
     * 
     * Update an existing login history record. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam LoginId int required The ID of the login history record. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam LoginAt string The login timestamp. Example: 2024-01-01 10:00:00
     * @bodyParam LogoutAt string The logout timestamp. Example: 2024-01-01 12:00:00
     * @bodyParam IPAddress string The IP address. Example: 192.168.1.1
     * @bodyParam UserAgent string The user agent. Example: Mozilla/5.0...
     * @bodyParam Device string The device name. Example: Windows Desktop
     * @bodyParam Status string The login status. Enum: Success, Failed. Example: Failed
     * 
     * @response 200 {
     *   "LoginId": 1,
     *   "UserId": 1,
     *   "LoginAt": "2024-01-01 10:00:00",
     *   "LogoutAt": "2024-01-01 12:00:00",
     *   "IPAddress": "192.168.1.1",
     *   "UserAgent": "Mozilla/5.0...",
     *   "Device": "Windows Desktop",
     *   "Status": "Failed"
     * }
     * @response 404 {
     *   "message": "Login history record not found"
     * }
     */
    public function update(StoreLoginhistoryRequest $request, $LoginId)
    {
        $loginhistory = $this->loginhistoryService->update($LoginId, $request->validated());
        return new LoginhistoryResource($loginhistory);
    }

    /**
     * Delete a login history record
     * 
     * Delete a login history record from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam LoginId int required The ID of the login history record. Example: 1
     * 
     * @response 200 {
     *   "message": "Login history record deleted successfully"
     * }
     * @response 404 {
     *   "message": "Login history record not found"
     * }
     */
    public function destroy($LoginId)
    {
        $this->loginhistoryService->delete($LoginId);
        return response()->json(['message' => 'Login history record deleted successfully']);
    }
}