<?php

namespace App\Services;

use App\Repositories\ScheduleRepository;
use Illuminate\Support\Facades\Auth;


class ScheduleService
{
    protected $scheduleRepository;

    public function __construct(ScheduleRepository $scheduleRepository)
    {
        $this->scheduleRepository = $scheduleRepository;
    }

    public function getAll()
    {
        return $this->scheduleRepository->getAll();
    }

    public function getById($id)
    {
        return $this->scheduleRepository->getById($id);
    }

    public function create(array $data)
    {
        $data['CreatedBy'] = Auth::user()->UserId;
        return $this->scheduleRepository->create($data);
    }

    public function update($id, array $data)
    {
        $data['UpdatedBy'] = Auth::user()->UserId;
        return $this->scheduleRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->scheduleRepository->delete($id);
    }
}
