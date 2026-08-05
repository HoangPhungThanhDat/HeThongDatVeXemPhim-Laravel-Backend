<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Resources\RoleResource;
use App\Services\RoleService;

/**
 * @group Roles
 * 
 * APIs for managing user roles and permissions.
 * Roles define what actions a user can perform in the system.
 * Available roles: Admin, Staff, User.
 */
class RoleController extends Controller
{
    protected $service;

    public function __construct(RoleService $service)
    {
        $this->service = $service;
        $this->middleware(['auth:api','checkrole:Admin'])->only(['store','update','destroy']);
    }

    /**
     * Get all roles
     * 
     * Get a list of all user roles in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "RoleId": 1,
     *     "RoleName": "Admin",
     *     "Description": "Full system access with all permissions",
     *     "Permissions": ["view_dashboard", "manage_users", "manage_movies", "manage_cinemas", "manage_orders", "manage_payments"],
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:00:00"
     *   },
     *   {
     *     "RoleId": 2,
     *     "RoleName": "Staff",
     *     "Description": "Staff access with limited permissions",
     *     "Permissions": ["view_dashboard", "manage_orders", "manage_tickets", "view_reports"],
     *     "Status": "Active",
     *     "CreatedAt": "2024-01-01 10:05:00"
     *   },
     *   {
     *     "RoleId": 3,
     *     "RoleName": "User",
     *     "Description": "Regular user access",
     *     "Permissions": ["view_movies", "book_tickets", "view_orders", "write_reviews"],
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
        return RoleResource::collection($this->service->getAll());
    }

    /**
     * Create a new role
     * 
     * Create a new user role with permissions. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam RoleName string required The role name. Example: Manager
     * @bodyParam Description string The role description. Example: Manager access with elevated permissions
     * @bodyParam Permissions array The list of permissions. Example: ["view_dashboard", "manage_orders"]
     * @bodyParam Status string The role status. Enum: Active, Inactive. Example: Active
     * 
     * @response 201 {
     *   "RoleId": 4,
     *   "RoleName": "Manager",
     *   "Description": "Manager access with elevated permissions",
     *   "Permissions": ["view_dashboard", "manage_orders"],
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "RoleName": ["The role name field is required."]
     *   }
     * }
     */
    public function store(StoreRoleRequest $request)
    {
        $role = $this->service->create($request->validated());

        return new RoleResource($role);
    }

    /**
     * Get a specific role
     * 
     * Get detailed information about a specific role. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam RoleId int required The ID of the role. Example: 1
     * 
     * @response 200 {
     *   "RoleId": 1,
     *   "RoleName": "Admin",
     *   "Description": "Full system access with all permissions",
     *   "Permissions": ["view_dashboard", "manage_users", "manage_movies", "manage_cinemas", "manage_orders", "manage_payments"],
     *   "Status": "Active",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     */
    public function show($RoleId)
    {
        $role = $this->service->find($RoleId);
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return new RoleResource($role);
    }

    /**
     * Update a role
     * 
     * Update an existing role. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam RoleId int required The ID of the role. Example: 1
     * @bodyParam RoleName string The role name. Example: Super Admin
     * @bodyParam Description string The role description. Example: Super Admin with full system access
     * @bodyParam Permissions array The list of permissions. Example: ["view_dashboard", "manage_users", "manage_system"]
     * @bodyParam Status string The role status. Enum: Active, Inactive. Example: Inactive
     * 
     * @response 200 {
     *   "RoleId": 1,
     *   "RoleName": "Super Admin",
     *   "Description": "Super Admin with full system access",
     *   "Permissions": ["view_dashboard", "manage_users", "manage_system"],
     *   "Status": "Inactive",
     *   "CreatedAt": "2024-01-01 10:00:00"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     */
    public function update(StoreRoleRequest $request, $RoleId)
    {
        $role = $this->service->update($RoleId, $request->validated());
        if (!$role) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return new RoleResource($role);
    }

    /**
     * Delete a role
     * 
     * Delete a role from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam RoleId int required The ID of the role. Example: 1
     * 
     * @response 200 {
     *   "message": "Role deleted successfully"
     * }
     * @response 404 {
     *   "message": "Role not found"
     * }
     */
    public function destroy($RoleId)
    {
        $deleted = $this->service->delete($RoleId);
        if (!$deleted) {
            return response()->json(['message' => 'Role not found'], 404);
        }
        return response()->json(['message' => 'Role deleted successfully']);
    }
}