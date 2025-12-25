<?php

namespace App\Services;

use App\Repositories\NotificationRepository;
use Illuminate\Support\Facades\Auth;

class NotificationService
{
    protected $notificationRepository;

    public function __construct(NotificationRepository $notificationRepository)
    {
        $this->notificationRepository = $notificationRepository;
    }

    public function getAll()
    {
        return $this->notificationRepository->getAll();
    }

    public function getById($id)
    {
        return $this->notificationRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->notificationRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->notificationRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->notificationRepository->delete($id);
    }
}
