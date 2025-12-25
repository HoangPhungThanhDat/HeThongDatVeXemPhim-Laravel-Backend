<?php

namespace App\Services;

use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\Auth;

class RoomService
{
    protected $roomRepository;

    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    public function getAll()
    {
        return $this->roomRepository->getAll();
    }

    public function getById($id)
    {
        return $this->roomRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->roomRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->roomRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->roomRepository->delete($id);
    }
}
