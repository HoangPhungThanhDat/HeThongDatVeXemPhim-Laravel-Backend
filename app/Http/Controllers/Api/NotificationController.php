<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Controllers\Controller;
use App\Services\NotificationService;

/**
 * @group Notifications
 * 
 * APIs for managing system notifications.
 * Notifications are used to inform users about important updates,
 * promotions, bookings, and system events.
 */
class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications
     * 
     * Get a list of all notifications in the system. (Admin only)
     * 
     * @authenticated
     * 
     * @response 200 [
     *   {
     *     "NotificationId": 1,
     *     "UserId": 1,
     *     "Title": "Chào mừng đến với Gấu Phim",
     *     "Message": "Chào mừng bạn đã đăng ký tài khoản thành công!",
     *     "Type": "System",
     *     "IsRead": false,
     *     "Link": "/profile",
     *     "CreatedAt": "2024-01-01 10:00:00",
     *     "ReadAt": null
     *   },
     *   {
     *     "NotificationId": 2,
     *     "UserId": 1,
     *     "Title": "Vé xem phim đã được đặt",
     *     "Message": "Bạn đã đặt vé thành công cho phim Avengers: Endgame",
     *     "Type": "Booking",
     *     "IsRead": true,
     *     "Link": "/orders/1",
     *     "CreatedAt": "2024-01-01 10:30:00",
     *     "ReadAt": "2024-01-01 11:00:00"
     *   },
     *   {
     *     "NotificationId": 3,
     *     "UserId": 2,
     *     "Title": "Khuyến mãi đặc biệt",
     *     "Message": "Giảm 20% cho tất cả vé xem phim trong tuần này!",
     *     "Type": "Promotion",
     *     "IsRead": false,
     *     "Link": "/promotions",
     *     "CreatedAt": "2024-01-01 11:00:00",
     *     "ReadAt": null
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
        $notifications = $this->notificationService->getAll();
        return NotificationResource::collection($notifications);
    }

    /**
     * Create a new notification
     * 
     * Create a new notification for a user. (Admin only)
     * 
     * @authenticated
     * 
     * @bodyParam UserId int required The ID of the user receiving the notification. Example: 1
     * @bodyParam Title string required The notification title. Example: Chào mừng đến với Gấu Phim
     * @bodyParam Message string required The notification message. Example: Chào mừng bạn đã đăng ký tài khoản!
     * @bodyParam Type string The notification type. Enum: System, Booking, Promotion, Alert. Example: System
     * @bodyParam Link string The link to redirect when clicked. Example: /profile
     * 
     * @response 201 {
     *   "NotificationId": 1,
     *   "UserId": 1,
     *   "Title": "Chào mừng đến với Gấu Phim",
     *   "Message": "Chào mừng bạn đã đăng ký tài khoản thành công!",
     *   "Type": "System",
     *   "IsRead": false,
     *   "Link": "/profile",
     *   "CreatedAt": "2024-01-01 10:00:00",
     *   "ReadAt": null
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "UserId": ["The user id field is required."],
     *     "Title": ["The title field is required."],
     *     "Message": ["The message field is required."]
     *   }
     * }
     */
    public function store(StoreNotificationRequest $request)
    {
        $notification = $this->notificationService->create($request->validated());
        return new NotificationResource($notification);
    }

    /**
     * Get a specific notification
     * 
     * Get detailed information about a specific notification. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam NotificationId int required The ID of the notification. Example: 1
     * 
     * @response 200 {
     *   "NotificationId": 1,
     *   "UserId": 1,
     *   "Title": "Chào mừng đến với Gấu Phim",
     *   "Message": "Chào mừng bạn đã đăng ký tài khoản thành công!",
     *   "Type": "System",
     *   "IsRead": false,
     *   "Link": "/profile",
     *   "CreatedAt": "2024-01-01 10:00:00",
     *   "ReadAt": null
     * }
     * @response 404 {
     *   "message": "Notification not found"
     * }
     */
    public function show($NotificationId)
    {
        $notification = $this->notificationService->getById($NotificationId);
        return new NotificationResource($notification);
    }

    /**
     * Update a notification
     * 
     * Update an existing notification. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam NotificationId int required The ID of the notification. Example: 1
     * @bodyParam UserId int The ID of the user. Example: 1
     * @bodyParam Title string The notification title. Example: Chào mừng bạn đến với Gấu Phim!
     * @bodyParam Message string The notification message. Example: Chào mừng bạn đã đăng ký thành công!
     * @bodyParam Type string The notification type. Enum: System, Booking, Promotion, Alert. Example: System
     * @bodyParam Link string The link to redirect. Example: /profile
     * @bodyParam IsRead boolean Whether the notification has been read. Example: true
     * 
     * @response 200 {
     *   "NotificationId": 1,
     *   "UserId": 1,
     *   "Title": "Chào mừng bạn đến với Gấu Phim!",
     *   "Message": "Chào mừng bạn đã đăng ký thành công!",
     *   "Type": "System",
     *   "IsRead": true,
     *   "Link": "/profile",
     *   "CreatedAt": "2024-01-01 10:00:00",
     *   "ReadAt": "2024-01-01 11:00:00"
     * }
     * @response 404 {
     *   "message": "Notification not found"
     * }
     */
    public function update(StoreNotificationRequest $request, $NotificationId)
    {
        $notification = $this->notificationService->update($NotificationId, $request->validated());
        return new NotificationResource($notification);
    }

    /**
     * Delete a notification
     * 
     * Delete a notification from the system. (Admin only)
     * 
     * @authenticated
     * 
     * @urlParam NotificationId int required The ID of the notification. Example: 1
     * 
     * @response 200 {
     *   "message": "Notification deleted successfully"
     * }
     * @response 404 {
     *   "message": "Notification not found"
     * }
     */
    public function destroy($NotificationId)
    {
        $this->notificationService->delete($NotificationId);
        return response()->json(['message' => 'Notification deleted successfully']);
    }
}