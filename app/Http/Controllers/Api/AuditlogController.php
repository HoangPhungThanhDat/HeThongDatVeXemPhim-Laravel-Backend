<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreAuditlogRequest;
use App\Http\Resources\AuditlogResource;
use App\Models\Auditlog;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AuditlogService;

/**
 * @group Audit Logs
 * 
 * APIs for managing audit logs. Audit logs track all activities in the system.
 * These endpoints are only accessible by Admin users.
 */
class AuditlogController extends Controller
{
    protected $auditlogService;

    public function __construct(AuditlogService $auditlogService)
    {
        $this->auditlogService = $auditlogService;
        // Chỉ Admin mới được thêm/sửa/xóa
        $this->middleware(['auth:api','checkrole:Admin'])
             ->only(['store', 'update', 'destroy']);
    }

    /**
     * Get all audit logs
     * 
     * Get a list of all audit logs in the system.
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "LogId": 1,
     *     "UserId": 1,
     *     "Action": "USER_LOGIN",
     *     "Description": "User admin@gauphim.com logged in",
     *     "IPAddress": "127.0.0.1",
     *     "UserAgent": "Mozilla/5.0...",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "LogId": 2,
     *     "UserId": 1,
     *     "Action": "MOVIE_CREATED",
     *     "Description": "Movie 'Avengers' created",
     *     "IPAddress": "127.0.0.1",
     *     "UserAgent": "Mozilla/5.0...",
     *     "CreatedAt": "2024-01-01 10:05:00"
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
        $auditlogs = $this->auditlogService->getAll();
        return AuditlogResource::collection($auditlogs);
    }

    /**
     * Create a new audit log
     * 
     * Create a new audit log entry. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam UserId int required The ID of the user performing the action. Example: 1
     * @bodyParam Action string required The action name. Example: USER_LOGIN
     * @bodyParam Description string Description of the action. Example: User admin@gauphim.com logged in
     * @bodyParam IPAddress string The IP address of the user. Example: 127.0.0.1
     * @bodyParam UserAgent string The user agent of the request. Example: Mozilla/5.0
     * 
     * @response 201 {
     *   "LogId": 1,
     *   "UserId": 1,
     *   "Action": "USER_LOGIN",
     *   "Description": "User admin@gauphim.com logged in",
     *   "IPAddress": "127.0.0.1",
     *   "UserAgent": "Mozilla/5.0...",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "UserId": ["The user id field is required."],
     *     "Action": ["The action field is required."]
     *   }
     * }
     */
    public function store(StoreAuditlogRequest $request)
    {
        $auditlog = $this->auditlogService->create($request->validated());
        return new AuditlogResource($auditlog);
    }

    /**
     * Get a specific audit log
     * 
     * Get detailed information about a specific audit log entry.
     * 
     * @authenticated
     * 
     * @urlParam LogId int required The ID of the audit log. Example: 1
     * 
     * @response 200 {
     *   "LogId": 1,
     *   "UserId": 1,
     *   "Action": "USER_LOGIN",
     *   "Description": "User admin@gauphim.com logged in",
     *   "IPAddress": "127.0.0.1",
     *   "UserAgent": "Mozilla/5.0...",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Audit log not found"
     * }
     */
    public function show($LogId)
    {
        $auditlog = $this->auditlogService->getById($LogId);
        return new AuditlogResource($auditlog);
    }

    /**
     * Update an audit log
     * 
     * Update an existing audit log entry. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam LogId int required The ID of the audit log. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam Action string The action name. Example: USER_LOGOUT
     * @bodyParam Description string Description of the action. Example: User admin@gauphim.com logged out
     * @bodyParam IPAddress string The IP address. Example: 127.0.0.1
     * @bodyParam UserAgent string The user agent. Example: Mozilla/5.0
     * 
     * @response 200 {
     *   "LogId": 1,
     *   "UserId": 1,
     *   "Action": "USER_LOGOUT",
     *   "Description": "User admin@gauphim.com logged out",
     *   "IPAddress": "127.0.0.1",
     *   "UserAgent": "Mozilla/5.0...",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Audit log not found"
     * }
     */
    public function update(StoreAuditlogRequest $request, $LogId)
    {
        $auditlog = $this->auditlogService->update($LogId, $request->validated());
        return new AuditlogResource($auditlog);
    }

    /**
     * Delete an audit log
     * 
     * Delete an audit log entry from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam LogId int required The ID of the audit log. Example: 1
     * 
     * @response 200 {
     *   "message": "Audit log deleted successfully"
     * }
     * @response 404 {
     *   "message": "Audit log not found"
     * }
     */
    public function destroy($LogId)
    {
        $this->auditlogService->delete($LogId);
        return response()->json(['message' => 'Audit log deleted successfully']);
    }
}