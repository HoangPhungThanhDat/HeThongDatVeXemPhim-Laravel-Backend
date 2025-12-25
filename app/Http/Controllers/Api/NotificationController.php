<?php

namespace App\Http\Controllers\Api;

use App\Models\Notification;
use Illuminate\Http\Request;
use App\Http\Requests\StoreNotificationRequest;
use App\Http\Resources\NotificationResource;
use App\Http\Controllers\Controller;
use App\Services\NotificationService;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function index()
    {
        $notifications = $this->notificationService->getAll();
        return NotificationResource::collection($notifications);
    }

    public function store(StoreNotificationRequest $request)
    {
        $notification = $this->notificationService->create($request->validated());
        return new NotificationResource($notification);
    }

    public function show($NotificationId)
    {
        $notification = $this->notificationService->getById($NotificationId);
        return new NotificationResource($notification);
    }

    public function update(StoreNotificationRequest $request, $NotificationId)
    {
        $notification = $this->notificationService->update($NotificationId, $request->validated());
        return new NotificationResource($notification);
    }

    public function destroy($NotificationId)
    {
        $this->notificationService->delete($NotificationId);
        return response()->json(['message' => 'notification deleted successfully']);
    }
}
